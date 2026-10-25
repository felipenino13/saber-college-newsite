import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const baseUrl = 'http://localhost:8082';
const outputDir = path.resolve('reports/responsive-ux-audit-20261021');
fs.mkdirSync(outputDir, { recursive: true });

const pages = [
  ['home', '/'],
  ['programs', '/saber_college_programs/'],
  ['nursing', '/saber_college_programs/professional-nursing-program/'],
  ['pta', '/saber_college_programs/physical-therapist-assistant-program/'],
  ['nursing-admissions', '/saber_college_programs/professional-nursing-program/nursing-admissions/'],
  ['admissions', '/admissions-and-tuition/'],
  ['apply', '/apply-to-saber-college/'],
  ['student-services', '/student-services/'],
  ['academic-affairs', '/student-services/academic-affairs/'],
  ['general', '/general/'],
  ['manuals', '/general/manuals/'],
  ['gallery', '/general/explore-saber-college-gallery/'],
  ['about', '/about_saber_college/'],
  ['contact', '/about_saber_college/contact/'],
  ['blog', '/saber_college_blog/'],
  ['landing-home', '/lp/'],
  ['landing-campus', '/lp/campus-location/'],
  ['landing-nursing', '/lp/pnp/'],
  ['landing-pta', '/lp/pta/'],
  ['thank-you', '/thank-you/'],
];

const widths = [360, 390, 768, 1024, 1366, 1920];
const screenshotPages = new Set(['home', 'programs', 'nursing', 'landing-nursing']);
const screenshotWidths = new Set([360, 768, 1366]);
const outputName = process.argv[2] || 'responsive-baseline.json';
const executablePath = process.argv[3] || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const browserName = process.argv[4] || 'chrome';

const browser = await chromium.launch({
  headless: true,
  ...(browserName === 'edge' ? { channel: 'msedge' } : { executablePath }),
});
const context = await browser.newContext({ reducedMotion: 'reduce' });
const page = await context.newPage();
const results = [];

for (const [pageName, pathname] of pages) {
  for (const width of widths) {
    const height = width <= 390 ? 844 : width <= 768 ? 900 : 1000;
    await page.setViewportSize({ width, height });
    const consoleErrors = [];
    const listener = (message) => {
      if (message.type() === 'error') consoleErrors.push(message.text());
    };
    page.on('console', listener);
    const response = await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'domcontentloaded', timeout: 60_000 });
    await page.waitForTimeout(450);

    const audit = await page.evaluate(() => {
      const visible = (element) => {
        const style = getComputedStyle(element);
        const rect = element.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
      };
      const viewportWidth = document.documentElement.clientWidth;
      const overflowingElements = [...document.querySelectorAll('body *')]
        .filter(visible)
        .map((element) => {
          const rect = element.getBoundingClientRect();
          return { element, rect };
        })
        .filter(({ rect }) => rect.right > viewportWidth + 2 || rect.left < -2)
        .slice(0, 20)
        .map(({ element, rect }) => ({
          tag: element.tagName,
          id: element.id || '',
          classes: typeof element.className === 'string' ? element.className.slice(0, 160) : '',
          left: Math.round(rect.left),
          right: Math.round(rect.right),
          width: Math.round(rect.width),
        }));

      const clippedText = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,li,.elementor-button-text')]
        .filter(visible)
        .filter((element) => {
          const style = getComputedStyle(element);
          return (
            (style.overflowX === 'hidden' || style.textOverflow === 'ellipsis') &&
            element.scrollWidth > element.clientWidth + 2
          ) || (
            style.overflowY === 'hidden' && element.scrollHeight > element.clientHeight + 2
          );
        })
        .slice(0, 20)
        .map((element) => ({ tag: element.tagName, text: element.textContent.trim().slice(0, 120) }));

      const interactive = [...document.querySelectorAll('a[href],button,input,select,textarea,[role="button"]')].filter(visible);
      const smallTargets = interactive.filter((element) => {
        const rect = element.getBoundingClientRect();
        const inlineTextLink = element.tagName === 'A' && getComputedStyle(element).display === 'inline' && !element.classList.contains('elementor-button');
        const intentionallyVisuallyHidden = element.matches('.screen-reader-text, .sr-only, [aria-hidden="true"]');
        const customControlInput = element.matches('input[type="radio"], input[type="checkbox"]') && Number(getComputedStyle(element).opacity) === 0 && element.labels?.length;
        return !inlineTextLink && !intentionallyVisuallyHidden && !customControlInput && (rect.width < 24 || rect.height < 24);
      }).slice(0, 30).map((element) => {
        const rect = element.getBoundingClientRect();
        return {
          tag: element.tagName,
          text: (element.getAttribute('aria-label') || element.textContent || element.value || '').trim().slice(0, 80),
          width: Math.round(rect.width),
          height: Math.round(rect.height),
          classes: typeof element.className === 'string' ? element.className.slice(0, 120) : '',
        };
      });

      const distortedImages = [...document.images].filter(visible).filter((image) => {
        if (!image.naturalWidth || !image.naturalHeight) return false;
        const rect = image.getBoundingClientRect();
        const naturalRatio = image.naturalWidth / image.naturalHeight;
        const renderedRatio = rect.width / rect.height;
        return Math.abs(renderedRatio / naturalRatio - 1) > 0.08 && getComputedStyle(image).objectFit === 'fill';
      }).map((image) => ({ alt: image.alt, src: image.currentSrc || image.src }));

      const fixedElements = [...document.querySelectorAll('body *')].filter(visible).filter((element) => {
        const position = getComputedStyle(element).position;
        return position === 'fixed' || position === 'sticky';
      }).map((element) => {
        const rect = element.getBoundingClientRect();
        return {
          tag: element.tagName,
          id: element.id || '',
          classes: typeof element.className === 'string' ? element.className.slice(0, 140) : '',
          areaRatio: Number(((rect.width * rect.height) / (innerWidth * innerHeight)).toFixed(3)),
          text: element.textContent.trim().replace(/\s+/g, ' ').slice(0, 80),
        };
      }).filter((item) => item.areaRatio > 0.01);

      const unnamedLinks = [...document.querySelectorAll('a[href],button')].filter(visible).filter((element) => {
        const labelledBy = element.getAttribute('aria-labelledby');
        const labelText = labelledBy ? labelledBy.split(/\s+/).map((id) => document.getElementById(id)?.textContent || '').join(' ') : '';
        const imageAlt = [...element.querySelectorAll('img')].map((image) => image.alt).join(' ');
        return ![
          element.getAttribute('aria-label'), labelText, element.textContent, element.title, imageAlt,
        ].filter(Boolean).join(' ').trim();
      }).map((element) => element.outerHTML.slice(0, 240));

      return {
        title: document.title,
        h1Count: document.querySelectorAll('h1').length,
        mainCount: document.querySelectorAll('main,[role="main"]').length,
        documentOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        overflowingElements,
        clippedText,
        smallTargets,
        distortedImages,
        fixedElements,
        unnamedLinks,
        menuToggleVisible: [...document.querySelectorAll('.elementor-menu-toggle')].some(visible),
        desktopMenuVisible: [...document.querySelectorAll('.elementor-nav-menu--main')].some(visible),
        breadcrumbCount: document.querySelectorAll('.rank-math-breadcrumb,.breadcrumbs,[aria-label*="breadcrumb" i]').length,
        footerCount: document.querySelectorAll('footer,[data-elementor-type="footer"]').length,
        dialogCount: [...document.querySelectorAll('[role="dialog"],dialog,.dialog-widget-content')].filter(visible).length,
        bodyTextLength: document.body.innerText.trim().length,
      };
    });

    if (screenshotPages.has(pageName) && screenshotWidths.has(width) && browserName === 'chrome') {
      await page.screenshot({
        path: path.join(outputDir, `${pageName}-${width}.png`),
        fullPage: true,
        animations: 'disabled',
      });
    }

    results.push({
      browser: browserName,
      page: pageName,
      pathname,
      width,
      status: response?.status() || 0,
      consoleErrors,
      ...audit,
    });
    page.off('console', listener);
  }
}

await browser.close();

const summary = {
  browser: browserName,
  pages: pages.length,
  widths,
  runs: results.length,
  non200Runs: results.filter((result) => result.status !== 200).length,
  overflowRuns: results.filter((result) => result.documentOverflow > 2).length,
  clippedTextRuns: results.filter((result) => result.clippedText.length).length,
  smallTargetRuns: results.filter((result) => result.smallTargets.length).length,
  distortedImageRuns: results.filter((result) => result.distortedImages.length).length,
  unnamedLinkRuns: results.filter((result) => result.unnamedLinks.length).length,
  obstructiveFixedRuns: results.filter((result) => result.fixedElements.some((element) => element.areaRatio > 0.25)).length,
  consoleErrorRuns: results.filter((result) => result.consoleErrors.length).length,
};

fs.writeFileSync(path.join(outputDir, outputName), JSON.stringify({ summary, results }, null, 2));
console.log(JSON.stringify(summary, null, 2));

