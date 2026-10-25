import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const baseUrl = 'http://localhost:8082';
const outputPath = path.resolve('reports/accessibility-audit-20261021/interactions-and-forms.json');
const formPages = [
  ['home', '/'],
  ['nursing', '/saber_college_programs/professional-nursing-program/'],
  ['pta', '/saber_college_programs/physical-therapist-assistant-program/'],
  ['survey', '/student-services/saber-survey/'],
  ['landing-home', '/lp/'],
  ['landing-campus', '/lp/campus-location/'],
  ['landing-nursing', '/lp/pnp/'],
  ['landing-pta', '/lp/pta/'],
  ['design-system', '/saber-design-system-elementor/'],
];

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});

const formResults = [];
for (const [name, pathname] of formPages) {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'networkidle', timeout: 60_000 });
  const forms = await page.$$eval('.elementor-form', (elements) => elements.map((form, formIndex) => {
    const fields = [...form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]),select,textarea')];
    const required = fields.filter((field) => field.required);
    const reportValid = form.reportValidity();
    const invalid = fields.filter((field) => !field.validity.valid);
    const describedErrors = invalid.filter((field) => {
      const describedBy = field.getAttribute('aria-describedby');
      return describedBy && describedBy.split(/\s+/).some((id) => document.getElementById(id));
    });
    const nativeErrors = invalid.filter((field) => Boolean(field.validationMessage));
    return {
      formIndex,
      formName: form.getAttribute('name') || form.dataset.name || '',
      fieldCount: fields.length,
      uniqueFieldIds: new Set(fields.map((field) => field.id).filter(Boolean)).size === fields.filter((field) => field.id).length,
      labelledFields: fields.filter((field) => Boolean(
        field.labels?.length || field.getAttribute('aria-label') || field.getAttribute('aria-labelledby')
      )).length,
      requiredCount: required.length,
      reportValid,
      invalidCount: invalid.length,
      invalidWithNativeError: nativeErrors.length,
      invalidWithDescribedError: describedErrors.length,
      invalidWithAccessibleName: invalid.filter((field) => Boolean(
        field.labels?.length || field.getAttribute('aria-label') || field.getAttribute('aria-labelledby')
      )).length,
    };
  }));
  formResults.push({ name, pathname, forms });
  await page.close();
}

const interactionResults = [];
for (const [name, pathname] of [['home', '/'], ['programs', '/saber_college_programs/']]) {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'networkidle', timeout: 60_000 });
  const titles = page.locator('.e-n-accordion-item-title:visible');
  const count = await titles.count();
  const items = [];
  for (let index = 0; index < count; index += 1) {
    const title = titles.nth(index);
    const before = await title.getAttribute('aria-expanded');
    await title.focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(150);
    const after = await title.getAttribute('aria-expanded');
    items.push({ index, before, after, toggled: before !== after });
  }
  interactionResults.push({ name, type: 'accordion', count, items });
  await page.close();
}

{
  const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle', timeout: 60_000 });
  const toggle = page.locator('.elementor-menu-toggle:visible').first();
  const found = await toggle.count();
  let menu = { found: Boolean(found) };
  if (found) {
    const before = await toggle.getAttribute('aria-expanded');
    const name = await toggle.getAttribute('aria-label');
    await toggle.focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(150);
    const after = await toggle.getAttribute('aria-expanded');
    menu = { found: true, accessibleName: name || '', before, after, toggled: before !== after };
  }
  interactionResults.push({ name: 'mobile-menu', type: 'menu', ...menu });
  await page.close();
}

{
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  await page.goto(`${baseUrl}/saber_college_programs/professional-nursing-program/`, { waitUntil: 'networkidle', timeout: 60_000 });
  const country = page.locator('.iti__selected-country[aria-label]').first();
  const found = await country.count();
  let selector = { found: Boolean(found) };
  if (found) {
    const role = await country.getAttribute('role');
    const tabindex = await country.getAttribute('tabindex');
    await country.focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(150);
    const expanded = await country.getAttribute('aria-expanded');
    selector = { found: true, role, tabindex, expanded };
  }
  interactionResults.push({ name: 'phone-country-selector', type: 'custom-control', ...selector });
  await page.close();
}

await browser.close();

const forms = formResults.flatMap((entry) => entry.forms);
const summary = {
  pagesWithForms: formResults.filter((entry) => entry.forms.length).length,
  forms: forms.length,
  allFieldsLabelled: forms.every((form) => form.labelledFields === form.fieldCount),
  allFieldIdsUnique: forms.every((form) => form.uniqueFieldIds),
  invalidFormsExposeErrors: forms.every((form) => (
    form.reportValid || (
      form.invalidCount > 0 &&
      form.invalidWithAccessibleName === form.invalidCount &&
      form.invalidWithNativeError + form.invalidWithDescribedError >= form.invalidCount
    )
  )),
  accordionKeyboardPass: interactionResults
    .filter((entry) => entry.type === 'accordion')
    .every((entry) => entry.count > 0 && entry.items.every((item) => item.toggled)),
  mobileMenuKeyboardPass: interactionResults.find((entry) => entry.type === 'menu')?.toggled || false,
  phoneSelectorSemanticsPass: (() => {
    const selector = interactionResults.find((entry) => entry.type === 'custom-control');
    return (
      (selector?.role === 'button' && selector?.tabindex === '0' && selector?.expanded === 'true') ||
      (selector?.role === 'img' && selector?.tabindex === '-1')
    );
  })(),
};

fs.writeFileSync(outputPath, JSON.stringify({ summary, formResults, interactionResults }, null, 2));
console.log(JSON.stringify(summary, null, 2));

