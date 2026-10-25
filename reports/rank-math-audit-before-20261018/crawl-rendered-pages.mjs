import fs from 'node:fs/promises';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const root = 'reports/rank-math-audit-before-20261018';
const raw = JSON.parse(await fs.readFile(`${root}/rank-math-raw-inventory.json`, 'utf8'));
const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe'
});

async function inspect(entry) {
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  try {
    const response = await page.goto(entry.url, { waitUntil: 'domcontentloaded', timeout: 45000 });
    const data = await page.evaluate(() => {
      const attr = (selector, name) => document.querySelector(selector)?.getAttribute(name) || '';
      const links = [...document.querySelectorAll('main a[href], #content a[href]')];
      const origin = location.origin;
      const unique = values => [...new Set(values)];
      const internal = unique(links.map(a => a.href).filter(href => href.startsWith(origin)));
      const external = unique(links.map(a => a.href).filter(href => /^https?:/i.test(href) && !href.startsWith(origin)));
      const images = [...document.querySelectorAll('main img, #content img')];
      const jsonLd = [...document.querySelectorAll('script[type="application/ld+json"]')];
      return {
        document_title: document.title,
        meta_description: attr('meta[name="description"]', 'content'),
        meta_robots: attr('meta[name="robots"]', 'content'),
        canonical: attr('link[rel="canonical"]', 'href'),
        h1_count: document.querySelectorAll('h1').length,
        h2_count: document.querySelectorAll('h2').length,
        schema_blocks: jsonLd.length,
        schema_types: unique(jsonLd.flatMap(node => {
          try {
            const parsed = JSON.parse(node.textContent || '{}');
            const values = Array.isArray(parsed) ? parsed : [parsed];
            return values.flatMap(value => {
              const graph = Array.isArray(value?.['@graph']) ? value['@graph'] : [value];
              return graph.flatMap(item => Array.isArray(item?.['@type']) ? item['@type'] : (item?.['@type'] ? [item['@type']] : []));
            });
          } catch { return []; }
        })),
        content_image_count: images.length,
        missing_alt_count: images.filter(img => !img.hasAttribute('alt') || !img.getAttribute('alt')?.trim()).length,
        unique_internal_links: internal.length,
        unique_external_links: external.length,
        body_words: (document.querySelector('main, #content')?.innerText || '').trim().split(/\s+/).filter(Boolean).length,
      };
    });
    return { id: entry.id, url: entry.url, http_status: response?.status() || null, ...data, page_errors: errors };
  } catch (error) {
    return { id: entry.id, url: entry.url, error: error.message, page_errors: errors };
  } finally {
    await page.close();
  }
}

const queue = [...raw.pages];
const results = [];
async function worker() {
  while (queue.length) {
    const entry = queue.shift();
    results.push(await inspect(entry));
  }
}

await Promise.all(Array.from({ length: 4 }, worker));
await browser.close();
results.sort((a, b) => a.id - b.id);
const report = {
  generated_at: new Date().toISOString(),
  method: 'Read-only browser crawl of locally rendered published pages.',
  page_count: results.length,
  pages: results
};
await fs.writeFile(`${root}/rendered-page-inventory.json`, JSON.stringify(report, null, 2));
console.log(JSON.stringify({ pages: results.length, errors: results.filter(x => x.error).length, non200: results.filter(x => x.http_status !== 200).map(x => [x.id, x.http_status]) }, null, 2));
