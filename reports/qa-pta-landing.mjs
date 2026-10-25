import fs from 'node:fs/promises';
import { createRequire } from 'node:module';
const require=createRequire(import.meta.url);
const { chromium }=require('playwright');
const chrome='C:/Program Files/Google/Chrome/Application/chrome.exe';
const browser=await chromium.launch({headless:true,executablePath:chrome});
const report={url:'http://localhost:8082/lp/pta/',checkedAt:new Date().toISOString(),viewports:{}};
for (const [name,width,height] of [['desktop',1440,900],['mobile',390,844]]) {
  const page=await browser.newPage({viewport:{width,height}});
  const consoleErrors=[]; const requestFailures=[];
  page.on('console',m=>{if(m.type()==='error') consoleErrors.push(m.text())});
  page.on('requestfailed',req=>requestFailures.push({url:req.url(),error:req.failure()?.errorText||''}));
  const response=await page.goto(report.url,{waitUntil:'networkidle'});
  await page.evaluate(async()=>{
    for(let y=0;y<document.documentElement.scrollHeight;y+=Math.max(500,innerHeight*0.8)){
      scrollTo(0,y);
      await new Promise(resolve=>setTimeout(resolve,80));
    }
    scrollTo(0,0);
  });
  const pageImages=page.locator('.elementor-1641 img');
  for(let index=0;index<await pageImages.count();index++){
    await pageImages.nth(index).scrollIntoViewIfNeeded();
    await page.waitForTimeout(180);
  }
  await page.evaluate(()=>scrollTo(0,0));
  await page.waitForTimeout(500);
  await page.screenshot({path:`reports/pta-landing-redesign-20261014-${name}.png`,fullPage:true});
  const metrics=await page.evaluate(()=>{
    const visible=el=>{const r=el.getBoundingClientRect(),s=getComputedStyle(el);return r.width>0&&r.height>0&&s.display!=='none'&&s.visibility!=='hidden'};
    const ids=[...document.querySelectorAll('[id]')].map(e=>e.id).filter(Boolean);
    const dup=[...new Set(ids.filter((id,i)=>ids.indexOf(id)!==i))];
    const images=[...document.querySelectorAll('.elementor-1641 img')].map(i=>({src:i.currentSrc||i.src,alt:i.alt,naturalWidth:i.naturalWidth,naturalHeight:i.naturalHeight,visible:visible(i)}));
    return {
      title:document.title,
      lang:document.documentElement.lang,
      statusClass:document.body.className,
      clientWidth:document.documentElement.clientWidth,
      scrollWidth:document.documentElement.scrollWidth,
      horizontalOverflow:document.documentElement.scrollWidth-document.documentElement.clientWidth,
      pageHeight:document.documentElement.scrollHeight,
      topLevelSections:document.querySelectorAll('.elementor-1641 > .e-con').length,
      headings:{h1:[...document.querySelectorAll('.elementor-1641 h1')].map(e=>e.innerText.trim()),h2:[...document.querySelectorAll('.elementor-1641 h2')].map(e=>e.innerText.trim()),h3:[...document.querySelectorAll('.elementor-1641 h3')].map(e=>e.innerText.trim())},
      formVisible:[...document.querySelectorAll('.elementor-1641 form')].some(visible),
      formFields:[...document.querySelectorAll('.elementor-1641 form input')].map(e=>({id:e.id,name:e.name,type:e.type,placeholder:e.placeholder})),
      submitLabels:[...document.querySelectorAll('.elementor-1641 button[type="submit"]')].map(e=>e.innerText.trim()),
      ctaLabels:[...document.querySelectorAll('.elementor-1641 a.elementor-button')].map(e=>e.innerText.trim()),
      images,
      brokenImages:images.filter(i=>i.naturalWidth===0).length,
      emptyImageAlts:images.filter(i=>i.visible&&!i.alt.trim()).length,
      duplicateIds:dup,
      markerPresent:!!document.querySelector('.e-pta-hero'),
    };
  });
  report.viewports[name]={width,height,httpStatus:response?.status(),...metrics,consoleErrors,requestFailures};
  await page.close();
}
await browser.close();
await fs.writeFile('reports/pta-landing-redesign-result-20261014.json',JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));

