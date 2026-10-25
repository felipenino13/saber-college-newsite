import fs from 'node:fs/promises';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const root = 'reports/lighthouse-after-20261018';
const pages = [
  ['home', 'http://localhost:8082/'],
  ['programs', 'http://localhost:8082/saber_college_programs/'],
  ['professional-nursing', 'http://localhost:8082/saber_college_programs/professional-nursing-program/'],
  ['physical-therapist-assistant', 'http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/'],
  ['admissions-tuition', 'http://localhost:8082/admissions-and-tuition/'],
  ['student-services', 'http://localhost:8082/student-services/'],
  ['general', 'http://localhost:8082/general/'],
  ['lp-pnp', 'http://localhost:8082/lp/pnp/'],
  ['lp-pta', 'http://localhost:8082/lp/pta/'],
];

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});

async function inspect(slug, url, viewportName, width, height) {
  const context = await browser.newContext({ viewport: { width, height } });
  const page = await context.newPage();
  const pageErrors = [];
  const consoleErrors = [];
  const requestFailures = [];
  const responses = [];
  page.on('pageerror', error => pageErrors.push(error.message));
  page.on('console', message => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('requestfailed', request => requestFailures.push({
    url: request.url(),
    error: request.failure()?.errorText || '',
  }));
  page.on('response', response => {
    const type = response.headers()['content-type'] || '';
    if (type.includes('image/webp') || response.url().includes('fonts.gstatic.com') || response.url().includes('fonts.googleapis.com')) {
      responses.push({ url: response.url(), status: response.status(), contentType: type });
    }
  });

  const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
  await page.waitForTimeout(1800);
  const initial = await page.evaluate(async () => {
    const visible = element => {
      if (!element) return false;
      const rect = element.getBoundingClientRect();
      const style = getComputedStyle(element);
      return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
    };
    await document.fonts.ready;
    const images = [...document.images];
    const video = document.querySelector('video');
    return {
      title: document.title,
      h1Count: document.querySelectorAll('h1').length,
      h1Text: [...document.querySelectorAll('h1')].map(node => node.innerText.trim()),
      clientWidth: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
      horizontalOverflow: Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth),
      imageCount: images.length,
      brokenImages: images.filter(image => image.complete && image.naturalWidth === 0).map(image => image.currentSrc || image.src),
      visibleForms: [...document.forms].filter(visible).length,
      fonts: {
        nunito: document.fonts.check('16px Nunito'),
        nunitoSans: document.fonts.check('16px "Nunito Sans"'),
      },
      preloads: [...document.querySelectorAll('link[rel="preload"]')].map(link => ({ as: link.as, href: link.href, fetchPriority: link.fetchPriority })),
      video: video ? {
        autoplay: video.autoplay,
        preload: video.preload,
        poster: video.poster,
        sourceSrc: video.querySelector('source')?.getAttribute('src') || '',
        sourceDataSrc: video.querySelector('source')?.dataset.src || '',
      } : null,
    };
  });

  if (slug === 'home') {
    const form = page.locator('.elementor-element-b7159a9');
    if (await form.count()) {
      await form.scrollIntoViewIfNeeded();
      await page.waitForTimeout(500);
      const radio = page.locator('label[for="form-field-program-0"]');
      const next = page.locator('.elementor-element-b7159a9 .e-form__buttons__wrapper__button-next');
      if (await radio.count() && await next.count()) {
        await radio.click();
        await next.click();
        await page.waitForTimeout(350);
      }
    }
  }

  const afterInteraction = await page.evaluate(() => {
    const visible = element => {
      if (!element) return false;
      const rect = element.getBoundingClientRect();
      const style = getComputedStyle(element);
      return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
    };
    const formRoot = document.querySelector('.elementor-element-b7159a9');
    return formRoot ? {
      visibleSteps: [...formRoot.querySelectorAll('.e-form__step')].filter(visible).length,
      visibleInputs: [...formRoot.querySelectorAll('input')].filter(visible).map(input => input.id).filter(Boolean),
      visibleButtons: [...formRoot.querySelectorAll('button')].filter(visible).map(button => button.innerText.trim()),
    } : null;
  });

  if (slug === 'home') {
    await page.screenshot({ path: `${root}/home-${viewportName}-after.png`, fullPage: true });
  }

  await context.close();
  return {
    slug,
    url,
    viewport: viewportName,
    width,
    height,
    httpStatus: response?.status() || null,
    ...initial,
    afterInteraction,
    webpResponses: responses.filter(item => item.contentType.includes('image/webp')).length,
    googleFontResponses: responses.filter(item => item.url.includes('fonts.googleapis.com') || item.url.includes('fonts.gstatic.com')),
    pageErrors,
    consoleErrors,
    requestFailures,
  };
}

const results = [];
for (const [slug, url] of pages) {
  results.push(await inspect(slug, url, 'desktop', 1440, 900));
  results.push(await inspect(slug, url, 'mobile', 390, 844));
}
await browser.close();

const report = {
  checkedAt: new Date().toISOString(),
  results,
  summary: {
    checks: results.length,
    non200: results.filter(item => item.httpStatus !== 200).length,
    pageErrors: results.reduce((sum, item) => sum + item.pageErrors.length, 0),
    consoleErrors: results.reduce((sum, item) => sum + item.consoleErrors.length, 0),
    requestFailures: results.reduce((sum, item) => sum + item.requestFailures.length, 0),
    brokenImages: results.reduce((sum, item) => sum + item.brokenImages.length, 0),
    overflowChecks: results.filter(item => item.horizontalOverflow > 1).map(item => ({ slug: item.slug, viewport: item.viewport, pixels: item.horizontalOverflow })),
    h1Issues: results.filter(item => item.h1Count !== 1).map(item => ({ slug: item.slug, viewport: item.viewport, count: item.h1Count })),
    googleFontRequests: results.reduce((sum, item) => sum + item.googleFontResponses.length, 0),
    webpResponses: results.reduce((sum, item) => sum + item.webpResponses, 0),
  },
};
await fs.writeFile(`${root}/functional-visual-qa.json`, JSON.stringify(report, null, 2));
console.log(JSON.stringify(report.summary, null, 2));
