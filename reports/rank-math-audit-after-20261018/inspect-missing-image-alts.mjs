import fs from 'node:fs/promises';
import { createRequire } from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');
const root='reports/rank-math-audit-after-20261018';
const raw=JSON.parse(await fs.readFile(`${root}/rank-math-raw-inventory.json`,'utf8'));
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const queue=[...raw.pages]; const results=[];
async function inspect(entry){
 const page=await browser.newPage({viewport:{width:1280,height:900}});
 try{
  await page.goto(entry.url,{waitUntil:'domcontentloaded',timeout:45000});
  const images=await page.locator('main img, #content img').evaluateAll(nodes=>nodes.filter(img=>!img.hasAttribute('alt')||!img.getAttribute('alt')?.trim()).map(img=>({
    src:img.currentSrc||img.src,
    alt:img.getAttribute('alt'),
    title:img.getAttribute('title')||'',
    class:img.className,
    attachmentId:[...img.classList].find(c=>/^wp-image-\d+$/.test(c))?.replace('wp-image-','')||'',
    nearestHeading:img.closest('section,article,div')?.querySelector('h1,h2,h3,h4')?.textContent?.trim()||''
  })));
  return {id:entry.id,title:entry.title,url:entry.url,images};
 }finally{await page.close()}
}
async function worker(){while(queue.length){const entry=queue.shift();results.push(await inspect(entry));}}
await Promise.all(Array.from({length:4},worker));
await browser.close(); results.sort((a,b)=>a.id-b.id);
await fs.writeFile(`${root}/missing-image-alt-details.json`,JSON.stringify({pages:results.filter(x=>x.images.length)},null,2));
console.log(JSON.stringify({pages:results.filter(x=>x.images.length).length,images:results.reduce((n,x)=>n+x.images.length,0)},null,2));

