import { createRequire } from 'node:module';
import { pathToFileURL } from 'node:url';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const dir = path.dirname(new URL(import.meta.url).pathname.replace(/^\/(?:([A-Za-z]:))/, '$1'));
const source = path.join(dir, 'manuals-quick-access-component.html');
const output = path.join(dir, 'manuals-quick-access-component.png');
const mobileOutput = path.join(dir, 'manuals-quick-access-component-mobile.png');

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe'
});
const page = await browser.newPage({ viewport: { width: 1600, height: 900 }, deviceScaleFactor: 1 });
await page.goto(pathToFileURL(source).href, { waitUntil: 'networkidle' });
await page.screenshot({ path: output, fullPage: true });
await page.setViewportSize({ width: 750, height: 1100 });
await page.screenshot({ path: mobileOutput, fullPage: true });
await browser.close();
console.log(JSON.stringify({ desktop: output, mobile: mobileOutput }, null, 2));
