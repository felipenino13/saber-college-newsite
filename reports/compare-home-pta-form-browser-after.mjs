import fs from 'node:fs/promises';
import { createRequire } from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const cases=[
 {name:'home',url:'http://localhost:8082/',form:'.elementor-element-b7159a9'},
 {name:'pta',url:'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/',form:'.elementor-element-f698ff6'}
];
const out={};
for(const c of cases){
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 const errors=[];page.on('console',m=>{if(m.type()==='error')errors.push(m.text())});
 await page.goto(c.url,{waitUntil:'networkidle'});
 const form=page.locator(c.form);
 await form.scrollIntoViewIfNeeded();
 await page.waitForTimeout(300);
 await form.screenshot({path:`reports/home-form-style-${c.name}-after-20261016.png`});
 out[c.name]=await form.evaluate(el=>{
   const css=e=>{if(!e)return null;const s=getComputedStyle(e);return {display:s.display,width:s.width,height:s.height,minHeight:s.minHeight,padding:s.padding,border:s.border,borderRadius:s.borderRadius,background:s.backgroundColor,color:s.color,font:s.font,boxShadow:s.boxShadow,gap:s.gap,gridTemplateColumns:s.gridTemplateColumns}};
   const q=s=>el.querySelector(s);
   return {widget:css(el),form:css(q('form')),label:css(q('.elementor-field-group > label')),input:css(q('input.elementor-field:not([type="radio"])')),radioGroup:css(q('.elementor-field-type-radio .elementor-field-subgroup')),radioOption:css(q('.elementor-field-type-radio .elementor-field-option')),radioLabel:css(q('.elementor-field-type-radio input[type="radio"] + label')),button:css(q('.elementor-button[type="submit"]')),stepType:el.getAttribute('data-settings'),html:el.outerHTML.slice(0,2500)};
 });
 out[c.name].consoleErrors=errors;
 await page.close();
}
await browser.close();
await fs.writeFile('reports/home-form-style-after-20261016.json',JSON.stringify(out,null,2));
console.log(JSON.stringify(out,null,2));

