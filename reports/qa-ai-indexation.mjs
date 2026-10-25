import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const outputDir = path.resolve('reports/ai-indexation-after-20261021');
fs.mkdirSync(outputDir, { recursive: true });

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
});
const checks = [];
const viewports = [
  { name: 'desktop', width: 1440, height: 1000 },
  { name: 'mobile', width: 390, height: 844 },
];

for (const viewport of viewports) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const response = await page.goto('http://localhost:8082/saber-college-accreditation/', {
    waitUntil: 'networkidle',
    timeout: 60_000,
  });
  const data = await page.evaluate(() => {
    const expected = [
      'https://web02.fldoe.org/CIE/SearchSchools/SchoolSearchDetail.aspx?schoolID=1400',
      'https://council.org/member-directory/',
      'https://www.capteonline.org/programs/master-list-pta',
      'https://cnea.nln.org/accredited-programs',
    ];
    const links = [...document.querySelectorAll('a')].map((link) => ({
      href: link.href,
      target: link.target,
      ariaLabel: link.getAttribute('aria-label') || '',
    }));
    return {
      h1Count: document.querySelectorAll('h1').length,
      overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      officialLinks: expected.map((href) => links.find((link) => link.href === href) || null),
    };
  });
  await page.screenshot({
    path: path.join(outputDir, `accreditation-${viewport.name}.png`),
    fullPage: true,
  });
  checks.push({
    viewport: viewport.name,
    httpStatus: response?.status() || 0,
    ...data,
    consoleErrors,
    pageErrors,
    passed:
      response?.status() === 200 &&
      data.h1Count === 1 &&
      data.overflow <= 1 &&
      data.officialLinks.every((link) => link && link.target === '_blank' && link.ariaLabel) &&
      consoleErrors.length === 0 &&
      pageErrors.length === 0,
  });
  await context.close();
}

await browser.close();
const result = { passed: checks.every((check) => check.passed), checks };
fs.writeFileSync(path.join(outputDir, 'browser-qa.json'), JSON.stringify(result, null, 2));
console.log(JSON.stringify(result, null, 2));
if (!result.passed) process.exitCode = 1;
