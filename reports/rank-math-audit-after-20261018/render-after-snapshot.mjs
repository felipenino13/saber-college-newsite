import { createRequire } from 'node:module';
import { pathToFileURL } from 'node:url';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const dir = path.dirname(new URL(import.meta.url).pathname.replace(/^\/(?:([A-Za-z]:))/, '$1'));
const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
const page = await browser.newPage({ viewport: { width: 1600, height: 1600 }, deviceScaleFactor: 1 });
await page.goto(pathToFileURL(path.join(dir, 'rank-math-after-overview.html')).href, { waitUntil: 'networkidle' });
await page.screenshot({ path: path.join(dir, 'rank-math-after-overview.png'), fullPage: true });
console.log(path.join(dir, 'rank-math-after-overview.png'));
await browser.close();
