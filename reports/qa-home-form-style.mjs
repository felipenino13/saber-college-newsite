import fs from 'node:fs/promises';
import {createRequire} from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const result={url:'http://localhost:8082/',reference:'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/',checkedAt:new Date().toISOString(),viewports:{}};
for(const [name,width,height] of [['desktop',1440,1000],['mobile',390,844]]){
 const page=await browser.newPage({viewport:{width,height}});
 const consoleErrors=[];const requestFailures=[];
 page.on('console',m=>{if(m.type()==='error')consoleErrors.push(m.text())});
 page.on('requestfailed',r=>requestFailures.push({url:r.url(),error:r.failure()?.errorText||''}));
 const response=await page.goto(result.url,{waitUntil:'networkidle'});
 const form=page.locator('.elementor-element-b7159a9');
 await form.scrollIntoViewIfNeeded();
 await page.waitForTimeout(250);
 await form.screenshot({path:`reports/home-form-style-${name}-step1-after-20261016.png`});
 await page.locator('label[for="form-field-program-0"]').click();
 await page.locator('.elementor-element-b7159a9 .e-form__buttons__wrapper__button-next').click();
 await page.waitForTimeout(250);
 await form.screenshot({path:`reports/home-form-style-${name}-step2-after-20261016.png`});
 result.viewports[name]=await page.evaluate(()=>{
  const root=document.querySelector('.elementor-element-b7159a9');
  const visible=e=>{if(!e)return false;const r=e.getBoundingClientRect(),s=getComputedStyle(e);return r.width>0&&r.height>0&&s.visibility!=='hidden'&&s.display!=='none'};
  const css=e=>{const s=getComputedStyle(e);return {minHeight:s.minHeight,padding:s.padding,border:s.border,borderRadius:s.borderRadius,background:s.backgroundColor,color:s.color,font:s.font,boxShadow:s.boxShadow,display:s.display,gap:s.gap,gridTemplateColumns:s.gridTemplateColumns}};
  const ids=[...root.querySelectorAll('[id]')].map(e=>e.id).filter(Boolean);
  return {
   pageWidth:document.documentElement.clientWidth,
   scrollWidth:document.documentElement.scrollWidth,
   horizontalOverflow:document.documentElement.scrollWidth-document.documentElement.clientWidth,
   formVisible:visible(root.querySelector('form')),
   visibleSteps:[...root.querySelectorAll('.e-form__step')].filter(visible).length,
   visibleInputIds:[...root.querySelectorAll('input')].filter(visible).map(e=>e.id).filter(Boolean),
   visibleSubmitLabels:[...root.querySelectorAll('button')].filter(visible).map(e=>e.innerText.trim()),
   inputStyle:css(root.querySelector('input.elementor-field:not([type="radio"])')),
   radioGroupStyle:css(root.querySelector('.elementor-field-type-radio .elementor-field-subgroup')),
   radioLabelStyle:css(root.querySelector('.elementor-field-type-radio input[type="radio"] + label')),
   buttonStyle:css(root.querySelector('.elementor-button')),
   duplicateIds:[...new Set(ids.filter((id,i)=>ids.indexOf(id)!==i))],
   oldScopePresent:document.documentElement.innerHTML.includes('.elementor-2015 .elementor-element.elementor-element-f698ff6'),
   fixedScopePresent:document.documentElement.innerHTML.includes('.elementor-7 .elementor-element.elementor-element-b7159a9')
  };
 });
 result.viewports[name].httpStatus=response?.status();
 result.viewports[name].consoleErrors=consoleErrors;
 result.viewports[name].requestFailures=requestFailures;
 await page.close();
}
await browser.close();
await fs.writeFile('reports/home-form-style-fix-result-20261016.json',JSON.stringify(result,null,2));
console.log(JSON.stringify(result,null,2));
