import fs from 'node:fs/promises';
import {createRequire} from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');
const targets=JSON.parse(await fs.readFile('scripts/data/rank-math-indexable-optimization-20261018.json','utf8'));
const inventory=JSON.parse(await fs.readFile('reports/rank-math-audit-after-20261018/rank-math-raw-inventory.json','utf8'));
const selected=inventory.pages.filter(x=>targets[String(x.id)]);
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const queue=[...selected], results=[];
async function inspect(entry){
 const page=await browser.newPage({viewport:{width:1280,height:900}});
 try{
  await page.goto(entry.url,{waitUntil:'domcontentloaded',timeout:45000});
  const keyword=targets[String(entry.id)].keyword.toLowerCase();
  const data=await page.evaluate(keyword=>{
   const root=document.querySelector('main, #content')||document.body;
   const text=(root.innerText||'').replace(/\s+/g,' ').trim().toLowerCase();
   const firstTen=text.slice(0,Math.ceil(text.length*.10));
   const headings=[...root.querySelectorAll('h1,h2,h3,h4')].map(x=>x.textContent.replace(/\s+/g,' ').trim());
   const imageAlts=[...root.querySelectorAll('img')].map(x=>x.getAttribute('alt')||'');
   const count=(text.match(new RegExp(keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'g'))||[]).length;
   return {h1:[...root.querySelectorAll('h1')].map(x=>x.textContent.trim()),headings,keyword_in_content:text.includes(keyword),keyword_in_first_10_percent:firstTen.includes(keyword),keyword_in_subheading:headings.slice(1).some(x=>x.toLowerCase().includes(keyword)),keyword_in_image_alt:imageAlts.some(x=>x.toLowerCase().includes(keyword)),keyword_occurrences:count};
  },keyword);
  return {id:entry.id,title:entry.title,keyword,...data};
 }finally{await page.close()}
}
async function worker(){while(queue.length){results.push(await inspect(queue.shift()));}}
await Promise.all(Array.from({length:4},worker)); await browser.close(); results.sort((a,b)=>a.id-b.id);
await fs.writeFile('reports/rank-math-audit-after-20261018/target-keyword-fit-after.json',JSON.stringify({pages:results},null,2));
console.log(JSON.stringify({pages:results.length,inContent:results.filter(x=>x.keyword_in_content).length,inFirstTen:results.filter(x=>x.keyword_in_first_10_percent).length,inSubheading:results.filter(x=>x.keyword_in_subheading).length,inImageAlt:results.filter(x=>x.keyword_in_image_alt).length},null,2));

