import fs from 'node:fs/promises';
import {createRequire} from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const cases=[
 {name:'pta-program',url:'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/',stepped:false},
 {name:'home',url:'http://localhost:8082/',stepped:true}
];
const report={checkedAt:new Date().toISOString(),method:'Synthetic invalid phone; no form submission or lead transmission.',cases:{}};
for(const c of cases){
 const page=await browser.newPage({viewport:{width:1280,height:900}});
 const pageErrors=[];const consoleErrors=[];const leadRequests=[];const responses=[];
 page.on('pageerror',e=>pageErrors.push(e.message));
 page.on('console',m=>{if(m.type()==='error')consoleErrors.push(m.text())});
 page.on('request',r=>{if(/verityiq|send_contact_email/i.test(r.url()))leadRequests.push(r.url())});
 const response=await page.goto(c.url,{waitUntil:'domcontentloaded'});
 await page.locator('label[for="form-field-program-0"]').click();
 if(c.stepped){await page.locator('.elementor-element-b7159a9 .e-form__buttons__wrapper__button-next').click();}
 await page.locator('#form-field-fname').fill('Test');
 await page.locator('#form-field-lname').fill('QA');
 await page.locator('#form-field-email').fill('qa@example.com');
 await page.locator('#form-field-phone').fill('123');
 await page.locator('#submit').click();
 await page.waitForTimeout(700);
 const dom=await page.evaluate(()=>({
   wrongSelectorCount:document.querySelectorAll('#form-field-program').length,
   radioCount:document.querySelectorAll('input[name="form_fields[program]"]').length,
   checkedCount:document.querySelectorAll('input[name="form_fields[program]"]:checked').length,
   markerPresent:document.documentElement.innerHTML.includes('SABER: resolve the checked program radio at submit time.'),
   currentUrl:location.href
 }));
 report.cases[c.name]={httpStatus:response?.status(),...dom,pageErrors,consoleErrors,leadRequests};
 await page.close();
}
await browser.close();
await fs.writeFile('reports/lead-submit-error-20261016/validation-after.json',JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));
