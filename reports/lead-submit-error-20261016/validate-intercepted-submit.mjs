import fs from 'node:fs/promises';
import {createRequire} from 'node:module';
const require=createRequire(import.meta.url);
const {chromium}=require('playwright');

const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const cases=[
  {name:'pta-program',url:'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/',stepped:false},
  {name:'home',url:'http://localhost:8082/',stepped:true}
];
const report={
  checkedAt:new Date().toISOString(),
  method:'Synthetic valid data. Lead endpoint requests were intercepted and fulfilled locally; nothing was transmitted.',
  cases:{}
};

for(const c of cases){
  const page=await browser.newPage({viewport:{width:1280,height:900}});
  const pageErrors=[];
  const consoleErrors=[];
  const intercepted=[];
  page.on('pageerror',e=>pageErrors.push(e.message));
  page.on('console',m=>{if(m.type()==='error')consoleErrors.push(m.text())});

  await page.route('https://api64.ipify.org/**', async route => {
    await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify({ip:'203.0.113.1'})});
  });
  await page.route('https://ipinfo.io/**', async route => {
    await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify({city:'Miami',region:'Florida',country:'US'})});
  });
  await page.route('https://api.verityiq.com/**', async route => {
    const req=route.request();
    intercepted.push({endpoint:'verityiq',method:req.method(),hasPostData:Boolean(req.postData())});
    await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify({status:'intercepted'})});
  });
  await page.route('https://sabercollege.edu/wp-content/uploads/2023/11/send_contact_email_1.php', async route => {
    const req=route.request();
    intercepted.push({endpoint:'contact-email',method:req.method(),hasPostData:Boolean(req.postData())});
    await route.fulfill({status:200,contentType:'text/plain',body:'intercepted'});
  });

  const response=await page.goto(c.url,{waitUntil:'domcontentloaded'});
  await page.locator('label[for="form-field-program-0"]').click();
  if(c.stepped){
    await page.locator('.elementor-element-b7159a9 .e-form__buttons__wrapper__button-next').click();
  }
  await page.locator('#form-field-fname').fill('Test');
  await page.locator('#form-field-lname').fill('QA');
  await page.locator('#form-field-email').fill('qa@example.com');
  await page.locator('#form-field-phone').fill('+12025550123');
  await page.locator('#submit').click();
  await page.waitForTimeout(850);

  report.cases[c.name]={
    httpStatus:response?.status(),
    currentUrl:page.url(),
    pageErrors,
    consoleErrors,
    intercepted,
    interceptedExpectedEndpoints:[...new Set(intercepted.map(x=>x.endpoint))].sort().join(',')==='contact-email,verityiq'
  };
  await page.close();
}

await browser.close();
await fs.writeFile('reports/lead-submit-error-20261016/intercepted-submit-validation.json',JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));
