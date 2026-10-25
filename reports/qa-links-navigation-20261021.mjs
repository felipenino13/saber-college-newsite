import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium, request } = require('playwright');

const root = 'http://localhost:8082';
const outputDir = path.resolve('reports/responsive-ux-audit-20261021');
const publishedPages = JSON.parse(fs.readFileSync(path.join(outputDir, 'published-pages.json'), 'utf8'));
const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});
const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
const occurrences = [];
const pageChecks = [];

for (const item of publishedPages) {
  const response = await page.goto(item.url, { waitUntil: 'domcontentloaded', timeout: 60_000 });
  await page.waitForTimeout(250);
  const found = await page.evaluate(() => {
    const visible = (element) => {
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
    };
    const anchors = [...document.querySelectorAll('a[href]')].map((anchor) => {
      const labelledBy = anchor.getAttribute('aria-labelledby');
      const labelledText = labelledBy
        ? labelledBy.split(/\s+/).map((id) => document.getElementById(id)?.textContent || '').join(' ')
        : '';
      const imageAlt = [...anchor.querySelectorAll('img')].map((image) => image.alt).join(' ');
      const name = [anchor.getAttribute('aria-label'), labelledText, anchor.textContent, imageAlt, anchor.title]
        .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
      const rawHref = anchor.getAttribute('href') || '';
      let fragmentExists = null;
      if (rawHref.startsWith('#') && rawHref.length > 1) {
        fragmentExists = Boolean(document.getElementById(decodeURIComponent(rawHref.slice(1))));
      }
      return {
        href: anchor.href,
        rawHref,
        name,
        visible: visible(anchor),
        target: anchor.target || '',
        fragmentExists,
        classes: anchor.className || '',
      };
    });
    return {
      anchors,
      breadcrumbLinks: [...document.querySelectorAll('.rank-math-breadcrumb a,.breadcrumbs a,[aria-label*="breadcrumb" i] a')]
        .map((anchor) => ({ href: anchor.href, text: anchor.textContent.trim() })),
      footerLinks: [...document.querySelectorAll('footer a,[data-elementor-type="footer"] a')]
        .map((anchor) => ({ href: anchor.href, text: (anchor.getAttribute('aria-label') || anchor.textContent).trim() })),
    };
  });
  for (const anchor of found.anchors) {
    occurrences.push({ pageId: item.id, pageTitle: item.title, pageUrl: item.url, ...anchor });
  }
  pageChecks.push({
    pageId: item.id,
    pageTitle: item.title,
    pageUrl: item.url,
    status: response?.status() || 0,
    breadcrumbLinks: found.breadcrumbLinks,
    footerLinks: found.footerLinks,
  });
}

await browser.close();

const schemes = {
  http: occurrences.filter((item) => /^https?:/i.test(item.href)),
  tel: occurrences.filter((item) => /^tel:/i.test(item.rawHref)),
  mailto: occurrences.filter((item) => /^mailto:/i.test(item.rawHref)),
};
const placeholders = occurrences.filter((item) => item.rawHref === '#' || /^javascript:/i.test(item.rawHref));
const badFragments = occurrences.filter((item) => item.fragmentExists === false);
const unnamedVisible = occurrences.filter((item) => item.visible && !item.name);

const uniqueHttp = [...new Set(schemes.http.map((item) => item.href.split('#')[0]))];
const api = await request.newContext({ ignoreHTTPSErrors: true, userAgent: 'SABER-local-QA/1.0' });
const statuses = [];
let cursor = 0;
const workers = Array.from({ length: 8 }, async () => {
  while (cursor < uniqueHttp.length) {
    const index = cursor;
    cursor += 1;
    const url = uniqueHttp[index];
    try {
      const response = await api.get(url, { timeout: 20_000, failOnStatusCode: false, maxRedirects: 8 });
      statuses[index] = {
        url,
        status: response.status(),
        ok: response.status() >= 200 && response.status() < 400,
        contentType: response.headers()['content-type'] || '',
      };
    } catch (error) {
      statuses[index] = { url, status: 0, ok: false, error: error.message };
    }
  }
});
await Promise.all(workers);
await api.dispose();

const pdfs = statuses.filter((item) => /\.pdf(?:$|\?)/i.test(item.url) || /application\/pdf/i.test(item.contentType));
const internal = statuses.filter((item) => new URL(item.url).host === 'localhost:8082');
const external = statuses.filter((item) => new URL(item.url).host !== 'localhost:8082');
const malformedPhones = schemes.tel.filter((item) => !/^tel:\+?[0-9().\-\s]+$/i.test(item.rawHref));
const malformedEmails = schemes.mailto.filter((item) => !/^mailto:[^@\s?]+@[^@\s?]+(?:\?.*)?$/i.test(item.rawHref));

const summary = {
  pagesCrawled: publishedPages.length,
  pageStatusFailures: pageChecks.filter((item) => item.status !== 200).length,
  linkOccurrences: occurrences.length,
  uniqueHttpLinks: statuses.length,
  internalLinks: internal.length,
  externalLinks: external.length,
  failedInternalLinks: internal.filter((item) => !item.ok).length,
  failedExternalLinks: external.filter((item) => !item.ok).length,
  placeholders: placeholders.length,
  badFragments: badFragments.length,
  unnamedVisibleLinks: unnamedVisible.length,
  telephoneLinks: schemes.tel.length,
  malformedTelephoneLinks: malformedPhones.length,
  emailLinks: schemes.mailto.length,
  malformedEmailLinks: malformedEmails.length,
  pdfLinks: pdfs.length,
  failedPdfLinks: pdfs.filter((item) => !item.ok).length,
};

const output = {
  summary,
  pageChecks,
  statuses,
  placeholders,
  badFragments,
  unnamedVisible,
  malformedPhones,
  malformedEmails,
  pdfs,
};

fs.writeFileSync(path.join(outputDir, 'links-and-navigation.json'), JSON.stringify(output, null, 2));
console.log(JSON.stringify(summary, null, 2));

