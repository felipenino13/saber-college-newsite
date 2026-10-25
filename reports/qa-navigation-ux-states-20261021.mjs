import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const root = 'http://localhost:8082';
const outputPath = path.resolve('reports/responsive-ux-audit-20261021/navigation-and-states.json');
const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});

const results = {};

{
  const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
  await page.goto(`${root}/`, { waitUntil: 'networkidle' });
  const trigger = page.locator('.elementor-nav-menu--main a.has-submenu:visible').first();
  const submenuId = await trigger.getAttribute('aria-controls');
  const submenu = page.locator(`#${submenuId}`);
  const before = await trigger.getAttribute('aria-expanded');
  await trigger.focus();
  await page.keyboard.press('Space');
  await page.waitForTimeout(150);
  const afterKeyboardOpen = await trigger.getAttribute('aria-expanded');
  const submenuVisible = await submenu.isVisible();
  await page.keyboard.press('Escape');
  await page.waitForTimeout(150);
  const afterKeyboardClose = await trigger.getAttribute('aria-expanded');
  results.desktopMenu = { before, afterKeyboardOpen, submenuVisible, afterKeyboardClose };
  await page.close();
}

{
  const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await page.goto(`${root}/`, { waitUntil: 'networkidle' });
  const toggle = page.locator('.elementor-menu-toggle:visible').first();
  const before = await toggle.getAttribute('aria-expanded');
  await toggle.focus();
  await page.keyboard.press('Enter');
  await page.waitForTimeout(150);
  const afterOpen = await toggle.getAttribute('aria-expanded');
  const mobileMenu = page.locator('.elementor-nav-menu--dropdown:visible').first();
  const menuVisible = await mobileMenu.isVisible();
  const submenuTrigger = mobileMenu.locator('a.has-submenu').first();
  const submenuBefore = await submenuTrigger.getAttribute('aria-expanded');
  await submenuTrigger.focus();
  await page.keyboard.press('Enter');
  await page.waitForTimeout(150);
  const submenuAfter = await submenuTrigger.getAttribute('aria-expanded');
  await toggle.focus();
  await page.keyboard.press('Enter');
  await page.waitForTimeout(150);
  const afterClose = await toggle.getAttribute('aria-expanded');
  results.mobileMenu = {
    before,
    afterOpen,
    menuVisible,
    submenuBefore,
    submenuAfter,
    afterClose,
  };
  await page.close();
}

{
  const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
  const states = [];
  for (const item of [
    ['404', '/responsive-ux-missing-page-test/', 404],
    ['empty-search', '/?s=responsiveuxquerywithnoresults20261021', 200],
    ['thank-you', '/thank-you/', 200],
  ]) {
    const [name, pathname, expectedStatus] = item;
    const response = await page.goto(`${root}${pathname}`, { waitUntil: 'networkidle' });
    states.push({
      name,
      expectedStatus,
      status: response?.status() || 0,
      title: await page.title(),
      h1: await page.locator('h1').allTextContents(),
      mainCount: await page.locator('main,[role="main"]').count(),
      bodyTextLength: (await page.locator('body').innerText()).trim().length,
      linkCount: await page.locator('a[href]:visible').count(),
      searchFieldCount: await page.locator('input[type="search"]:visible').count(),
    });
  }
  results.systemStates = states;
  await page.close();
}

{
  const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
  await page.goto(`${root}/`, { waitUntil: 'networkidle' });
  const button = page.locator('button.elementor-button:visible').first();
  const getStyle = () => button.evaluate((element) => {
    const style = getComputedStyle(element);
    return {
      background: style.backgroundColor,
      color: style.color,
      border: style.borderColor,
      outline: `${style.outlineStyle} ${style.outlineWidth}`,
      shadow: style.boxShadow,
      transform: style.transform,
    };
  });
  const normal = await getStyle();
  await button.hover();
  const hover = await getStyle();
  await button.focus();
  const focus = await getStyle();
  await page.mouse.move((await button.boundingBox()).x + 5, (await button.boundingBox()).y + 5);
  await page.mouse.down();
  const active = await getStyle();
  await page.mouse.up();
  results.primaryButtonStates = {
    normal,
    hover,
    focus,
    active,
    hoverDiffers: JSON.stringify(normal) !== JSON.stringify(hover),
    focusVisible: focus.outline !== 'none 0px' || focus.shadow !== 'none',
    activeDiffers: JSON.stringify(hover) !== JSON.stringify(active),
  };
  await page.close();
}

async function testSimulatedFormResponse(success) {
  const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
  const dialogs = [];
  page.on('dialog', async (dialog) => {
    dialogs.push(dialog.message());
    await dialog.dismiss();
  });
  await page.route('https://api.verityiq.com/**', async (route) => {
    await new Promise((resolve) => setTimeout(resolve, 350));
    await route.fulfill({
      status: success ? 200 : 500,
      contentType: 'text/plain',
      body: success ? 'success' : 'QA simulated error',
    });
  });
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    await new Promise((resolve) => setTimeout(resolve, 350));
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify(success
        ? { success: true, data: { message: 'QA simulated success', data: [] } }
        : { success: false, data: { message: 'QA simulated error', errors: [] } }),
    });
  });
  await page.goto(`${root}/lp/pnp/`, { waitUntil: 'networkidle' });
  const form = page.locator('.elementor-form').first();
  await form.locator('#form-field-fname').fill('QA');
  await form.locator('#form-field-lname').fill('Responsive');
  await form.locator('#form-field-email').fill('qa@example.com');
  await form.locator('#form-field-phone').fill('3055550123');
  const submit = form.locator('button[type="submit"]');
  await submit.click({ force: true });
  await page.waitForTimeout(100);
  const loading = await submit.evaluate((element) => ({
    disabled: element.disabled,
    classes: element.className,
    ariaBusy: element.getAttribute('aria-busy'),
  }));
  await page.waitForTimeout(1000);
  const messages = await form.locator('.elementor-message').allTextContents();
  const currentUrl = page.url();
  const buttonAfter = currentUrl.endsWith('/thank-you/')
    ? { disabled: false, redirected: true }
    : await submit.evaluate((element) => ({ disabled: element.disabled, redirected: false }));
  const result = { success, loading, messages, dialogs, currentUrl, buttonAfter };
  await page.close();
  return result;
}

results.simulatedFormSuccess = await testSimulatedFormResponse(true);
results.simulatedFormError = await testSimulatedFormResponse(false);

await browser.close();

const summary = {
  desktopMenuPass: results.desktopMenu.afterKeyboardOpen === 'true' && results.desktopMenu.submenuVisible && results.desktopMenu.afterKeyboardClose === 'false',
  mobileMenuPass: results.mobileMenu.afterOpen === 'true' && results.mobileMenu.menuVisible && results.mobileMenu.afterClose === 'false',
  mobileSubmenuPass: results.mobileMenu.submenuBefore !== results.mobileMenu.submenuAfter,
  systemStatesPass: results.systemStates.every((state) => state.status === state.expectedStatus && state.h1.length === 1 && state.mainCount === 1 && state.bodyTextLength > 100),
  hoverPass: results.primaryButtonStates.hoverDiffers,
  focusPass: results.primaryButtonStates.focusVisible,
  activePass: results.primaryButtonStates.activeDiffers,
  loadingPass: results.simulatedFormSuccess.loading.disabled || /loading/i.test(results.simulatedFormSuccess.loading.classes),
  successPass: results.simulatedFormSuccess.currentUrl.endsWith('/thank-you/'),
  errorPass: results.simulatedFormError.dialogs.some((message) => /couldn.t process|try again/i.test(message)) && !results.simulatedFormError.buttonAfter.disabled,
};

fs.writeFileSync(outputPath, JSON.stringify({ summary, results }, null, 2));
console.log(JSON.stringify(summary, null, 2));

