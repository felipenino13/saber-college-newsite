import fs from 'node:fs/promises';
import { createRequire } from 'node:module';
const require=createRequire(import.meta.url);
const { chromium }=require('playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
for (const [name,width,height] of [['desktop-before',1440,900],['mobile-before',390,844]]) {
  const page=await browser.newPage({viewport:{width,height}});
  await page.goto('http://localhost:8082/lp/pta/',{waitUntil:'networkidle'});
  await page.screenshot({path:`reports/backups/pta-landing-redesign-20261014/${name}.png`,fullPage:true});
  await page.close();
}
await browser.close();

