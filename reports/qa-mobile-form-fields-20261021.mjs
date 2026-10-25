import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const root = 'http://localhost:8082';
const pages = [
  '/',
  '/saber_college_programs/professional-nursing-program/',
  '/saber_college_programs/physical-therapist-assistant-program/',
  '/student-services/saber-survey/',
  '/lp/',
  '/lp/campus-location/',
  '/lp/pnp/',
  '/lp/pta/',
  '/saber-design-system-elementor/',
];

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
const results = [];

for (const pathname of pages) {
  await page.goto(`${root}${pathname}`, { waitUntil: 'networkidle' });
  const fields = await page.locator('form input:not([type="hidden"]), form textarea, form select').evaluateAll((elements) => elements.map((field) => ({
    tag: field.tagName,
    id: field.id,
    name: field.getAttribute('name') || '',
    type: field.getAttribute('type') || '',
    placeholder: field.getAttribute('placeholder') || '',
    autocomplete: field.getAttribute('autocomplete') || '',
    inputmode: field.getAttribute('inputmode') || '',
  })));
  results.push({ pathname, fields });
}

await browser.close();

const allFields = results.flatMap((result) => result.fields);
const personFields = allFields.filter((field) => /name|email|phone|telephone|mobile/i.test(`${field.id} ${field.name} ${field.placeholder}`));
const phoneFields = personFields.filter((field) => /phone|telephone|mobile/i.test(`${field.id} ${field.name} ${field.placeholder}`));
const summary = {
  pages: results.length,
  fields: allFields.length,
  personFields: personFields.length,
  personFieldsWithoutAutocomplete: personFields.filter((field) => !field.autocomplete).length,
  phoneFields: phoneFields.length,
  phoneFieldsWithoutTelInputMode: phoneFields.filter((field) => field.inputmode !== 'tel').length,
};

const outputPath = path.resolve('reports/responsive-ux-audit-20261021/mobile-form-fields-final.json');
fs.writeFileSync(outputPath, JSON.stringify({ summary, results }, null, 2));
console.log(JSON.stringify(summary, null, 2));
