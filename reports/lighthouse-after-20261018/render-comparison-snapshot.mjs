import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const dir = path.resolve('reports/lighthouse-after-20261018');
const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});
const page = await browser.newPage({ viewport: { width: 1600, height: 1500 }, deviceScaleFactor: 1 });
await page.goto(pathToFileURL(path.join(dir, 'lighthouse-after-comparison.html')).href, { waitUntil: 'load' });
await page.screenshot({ path: path.join(dir, 'lighthouse-after-comparison.png'), fullPage: true });
await browser.close();
console.log(path.join(dir, 'lighthouse-after-comparison.png'));
