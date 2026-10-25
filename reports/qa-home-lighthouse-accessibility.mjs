import fs from 'node:fs/promises';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});

const result = {
  url: 'http://localhost:8082/',
  checkedAt: new Date().toISOString(),
  viewports: {},
};

for (const [name, width, height] of [
  ['desktop', 1440, 1000],
  ['mobile', 390, 844],
]) {
  const context = await browser.newContext({
    viewport: { width, height },
    permissions: ['clipboard-read', 'clipboard-write'],
  });
  const page = await context.newPage();
  const consoleErrors = [];
  const requestFailures = [];

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('requestfailed', (request) => {
    requestFailures.push({ url: request.url(), error: request.failure()?.errorText || '' });
  });

  const response = await page.goto(result.url, { waitUntil: 'networkidle' });
  await page.screenshot({
    path: `reports/home-lighthouse-accessibility-after-20261019/home-${name}-after.png`,
    fullPage: true,
  });

  const form = page.locator('.elementor-element-b7159a9');
  await form.scrollIntoViewIfNeeded();
  let phone = page.locator('.elementor-element-b7159a9 #form-field-phone:visible').first();
  let phoneFound = false;
  for (let step = 0; step < 6; step += 1) {
    phone = page.locator('.elementor-element-b7159a9 #form-field-phone:visible').first();
    phoneFound = (await phone.count()) > 0;
    if (phoneFound) break;

    const visibleRadios = form.locator('input[type="radio"]:visible');
    if (await visibleRadios.count()) {
      const radioId = await visibleRadios.first().getAttribute('id');
      if (radioId) await form.locator(`label[for="${radioId}"]`).click();
    }

    const visibleFields = form.locator('input:not([type="radio"]):not([type="checkbox"]):visible');
    for (let fieldIndex = 0; fieldIndex < await visibleFields.count(); fieldIndex += 1) {
      const field = visibleFields.nth(fieldIndex);
      const type = (await field.getAttribute('type')) || 'text';
      if (type === 'email') await field.fill('qa@example.com');
      else if (type !== 'hidden' && type !== 'submit' && type !== 'button') await field.fill('QA Test');
    }

    const next = form.locator('.e-form__buttons__wrapper__button-next:visible').first();
    if (!(await next.count())) break;
    await next.click();
    await page.waitForTimeout(250);
  }

  let phoneValueAfterPaste = null;
  if (phoneFound) {
    await phone.focus();
    await page.evaluate(async () => navigator.clipboard.writeText('+13055551234'));
    await page.keyboard.press('Control+V');
    await page.waitForTimeout(150);
    phoneValueAfterPaste = await phone.inputValue();
  }

  result.viewports[name] = await page.evaluate(() => {
    const loopItems = [...document.querySelectorAll('.elementor-loop-container > [data-elementor-type="loop-item"]')];
    const socialNames = [...document.querySelectorAll('footer a.elementor-icon[aria-label]')].map((link) => link.getAttribute('aria-label'));
    const logo = document.querySelector('header .elementor-widget-theme-site-logo a img');
    const phoneSeparatorLink = [...document.querySelectorAll('header a[href^="tel:"]')].some((link) => link.textContent.trim() === '|');
    return {
      pageWidth: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
      horizontalOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      loopItems: loopItems.length,
      loopItemsWithRole: loopItems.filter((item) => item.getAttribute('role') === 'listitem').length,
      socialNames,
      logoAlt: logo?.getAttribute('alt') || null,
      phoneSeparatorLink,
      pasteListenerPresent: document.documentElement.innerHTML.includes('addEventListener("paste"'),
    };
  });

  result.viewports[name].httpStatus = response?.status();
  result.viewports[name].phoneFound = phoneFound;
  result.viewports[name].phoneValueAfterPaste = phoneValueAfterPaste;
  result.viewports[name].consoleErrors = consoleErrors;
  result.viewports[name].requestFailures = requestFailures;
  await context.close();
}

await browser.close();
await fs.writeFile(
  'reports/home-lighthouse-accessibility-after-20261019/browser-qa.json',
  JSON.stringify(result, null, 2),
);
console.log(JSON.stringify(result, null, 2));
