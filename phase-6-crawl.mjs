import fs from 'node:fs/promises';

const base = new URL('http://localhost:8082/');
const maxPages = 1000;

const pageQueue = [];
const queuedPages = new Set();
const pageRows = [];
const resourceUrls = new Set();
const resourceRows = [];
const sitemapRows = [];
const errors = [];
let publishedContentCount = 0;

function decodeEntities(value) {
  return value
    .replace(/&amp;/gi, '&')
    .replace(/&quot;/gi, '"')
    .replace(/&#039;|&apos;/gi, "'")
    .replace(/&#x([0-9a-f]+);/gi, (_, hex) => String.fromCodePoint(parseInt(hex, 16)))
    .replace(/&#(\d+);/g, (_, decimal) => String.fromCodePoint(Number(decimal)));
}

function csvCell(value) {
  const text = String(value ?? '');
  return `"${text.replaceAll('"', '""')}"`;
}

async function writeCsv(path, headers, rows) {
  const lines = [headers.map(csvCell).join(',')];
  for (const row of rows) {
    lines.push(headers.map((header) => csvCell(row[header])).join(','));
  }
  await fs.writeFile(path, `${lines.join('\r\n')}\r\n`, 'utf8');
}

function normalizeUrl(value, parent = base) {
  try {
    const url = new URL(decodeEntities(value.trim()), parent);
    if (url.origin !== base.origin) return null;
    url.hash = '';
    url.search = '';
    return url;
  } catch {
    return null;
  }
}

function normalizedPath(value) {
  const url = value instanceof URL ? value : new URL(value);
  const path = decodeURIComponent(url.pathname).replace(/\/+$/, '') || '/';
  return path;
}

function attrs(tag) {
  const result = {};
  const expression = /([:\w-]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g;
  for (const match of tag.matchAll(expression)) {
    result[match[1].toLowerCase()] = decodeEntities(match[2] ?? match[3] ?? match[4] ?? '');
  }
  return result;
}

function addPage(value, parent, discoveredFrom, inSitemap = false, sitemapType = '') {
  const url = normalizeUrl(value, parent);
  if (!url) return;
  if (/\/(wp-admin|wp-login\.php|wp-json|xmlrpc\.php)(\/|$)/i.test(url.pathname)) return;
  if (/\/(feed|comments)(\/|$)/i.test(url.pathname)) return;
  if (/\.(?:pdf|docx?|xlsx?|pptx?|zip|jpe?g|png|gif|webp|svg|avif|mp4|webm|mp3|css|js|xml|txt)$/i.test(url.pathname)) {
    resourceUrls.add(url.href);
    return;
  }

  const key = url.href;
  const existing = pageRows.find((row) => row.url === key);
  if (existing && inSitemap) {
    existing.in_sitemap = 'yes';
    existing.sitemap_type = sitemapType;
  }
  if (queuedPages.has(key)) return;
  queuedPages.add(key);
  pageQueue.push({ url, discoveredFrom, inSitemap, sitemapType });
}

function extractHtml(html, pageUrl) {
  const tags = [...html.matchAll(/<(a|form|img|source|video|audio|script|link|iframe)\b[^>]*>/gi)];
  const canonicals = [];
  const robots = [];

  for (const match of html.matchAll(/<link\b[^>]*>/gi)) {
    const properties = attrs(match[0]);
    if ((properties.rel || '').toLowerCase().split(/\s+/).includes('canonical') && properties.href) {
      canonicals.push(properties.href);
    }
  }

  for (const match of html.matchAll(/<meta\b[^>]*>/gi)) {
    const properties = attrs(match[0]);
    if ((properties.name || '').toLowerCase() === 'robots') robots.push(properties.content || '');
  }

  for (const match of tags) {
    const tagName = match[1].toLowerCase();
    const properties = attrs(match[0]);

    if ((tagName === 'a' || tagName === 'form') && (properties.href || properties.action)) {
      const target = properties.href || properties.action;
      if (/^(mailto:|tel:|javascript:|data:)/i.test(target)) continue;
      addPage(target, pageUrl, pageUrl.href);
    }

    const resource = properties.src || (
      tagName === 'link' && /(?:stylesheet|icon|preload)/i.test(properties.rel || '') ? properties.href : ''
    );
    if (resource && !/^(data:|blob:)/i.test(resource)) {
      const resourceUrl = normalizeUrl(resource, pageUrl);
      if (resourceUrl) resourceUrls.add(resourceUrl.href);
    }

    if (properties.srcset) {
      for (const candidate of properties.srcset.split(',')) {
        const resourceUrl = normalizeUrl(candidate.trim().split(/\s+/)[0], pageUrl);
        if (resourceUrl) resourceUrls.add(resourceUrl.href);
      }
    }
  }

  return { canonicals, robots };
}

async function fetchManual(url) {
  try {
    return await fetch(url, { redirect: 'manual', signal: AbortSignal.timeout(20000) });
  } catch (error) {
    return { status: 0, headers: new Headers(), error: error.message, text: async () => '' };
  }
}

const indexResponse = await fetchManual(new URL('/sitemap_index.xml', base));
if (indexResponse.status !== 200) {
  throw new Error(`Sitemap index returned ${indexResponse.status}`);
}

const indexXml = await indexResponse.text();
const childSitemaps = [...indexXml.matchAll(/<loc>(.*?)<\/loc>/gsi)].map((match) => decodeEntities(match[1]));

for (const sitemapUrl of childSitemaps) {
  const response = await fetchManual(sitemapUrl);
  if (response.status !== 200) {
    errors.push(`Sitemap ${sitemapUrl} returned ${response.status}`);
    continue;
  }
  const xml = await response.text();
  const type = new URL(sitemapUrl).pathname.replace(/-sitemap\.xml$/, '').replace(/^\//, '');
  const locations = [...xml.matchAll(/<loc>(.*?)<\/loc>/gsi)].map((match) => decodeEntities(match[1]));
  for (const location of locations) {
    sitemapRows.push({ type, url: location });
    addPage(location, base, sitemapUrl, true, type);
  }
}

for (const postType of ['pages', 'posts']) {
  const apiUrl = new URL(`/wp-json/wp/v2/${postType}?per_page=100&status=publish&_fields=link`, base);
  const response = await fetchManual(apiUrl);
  if (response.status !== 200) {
    errors.push(`WordPress REST endpoint ${apiUrl.href} returned ${response.status}`);
    continue;
  }
  const items = await response.json();
  publishedContentCount += items.length;
  for (const item of items) addPage(item.link, base, apiUrl.href);
}

while (pageQueue.length && pageRows.length < maxPages) {
  const item = pageQueue.shift();
  const response = await fetchManual(item.url);
  const contentType = response.headers.get('content-type') || '';
  const location = response.headers.get('location') || '';
  const row = {
    url: item.url.href,
    type: 'page',
    status: response.status,
    location,
    in_sitemap: item.inSitemap ? 'yes' : 'no',
    sitemap_type: item.sitemapType,
    canonical: '',
    robots: '',
    discovered_from: item.discoveredFrom,
    issue: '',
  };

  if (response.status === 200 && /text\/html/i.test(contentType)) {
    const html = await response.text();
    const { canonicals, robots } = extractHtml(html, item.url);
    row.canonical = canonicals.join(' | ');
    row.robots = robots.join(' | ');

    if (item.inSitemap) {
      if (canonicals.length !== 1) {
        row.issue = `Expected one canonical; found ${canonicals.length}`;
      } else {
        const canonicalUrl = normalizeUrl(canonicals[0], item.url);
        if (!canonicalUrl || normalizedPath(canonicalUrl) !== normalizedPath(item.url)) {
          row.issue = `Canonical mismatch: ${canonicals[0]}`;
        }
      }
      if (robots.some((value) => /(?:^|,)\s*noindex\b/i.test(value))) {
        row.issue = [row.issue, 'Sitemap URL is noindex'].filter(Boolean).join('; ');
      }
    }
  } else if (response.status === 200) {
    row.type = 'non-html';
  }

  if (item.inSitemap && response.status !== 200) {
    row.issue = `Sitemap URL returned ${response.status}`;
  } else if (!item.inSitemap && (response.status === 0 || response.status >= 400)) {
    row.issue = `Internal URL returned ${response.status}`;
  } else if (!item.inSitemap && response.status >= 300 && response.status < 400) {
    row.issue = `Internal link uses redirect ${response.status}`;
  }

  if (row.issue) errors.push(`${row.url}: ${row.issue}`);
  pageRows.push(row);
}

if (pageQueue.length) errors.push(`Page crawl exceeded the ${maxPages} URL limit.`);

async function checkResource(url) {
  const response = await fetchManual(url);
  const contentType = response.headers.get('content-type') || '';
  const row = {
    url,
    status: response.status,
    content_type: contentType,
    location: response.headers.get('location') || '',
    issue: '',
  };
  if (response.status === 0 || response.status >= 400) {
    row.issue = `Resource returned ${response.status}`;
    errors.push(`${url}: ${row.issue}`);
  } else if (response.status >= 300 && response.status < 400) {
    row.issue = `Resource uses redirect ${response.status}`;
    errors.push(`${url}: ${row.issue}`);
  }

  if (response.status === 200 && /text\/css/i.test(contentType)) {
    const css = await response.text();
    for (const match of css.matchAll(/url\(\s*(['"]?)(.*?)\1\s*\)/gi)) {
      if (/^(data:|blob:|#)/i.test(match[2])) continue;
      const resourceUrl = normalizeUrl(match[2], new URL(url));
      if (resourceUrl) resourceUrls.add(resourceUrl.href);
    }
  }
  resourceRows.push(row);
}

const checkedResources = new Set();
while (checkedResources.size < resourceUrls.size) {
  const batch = [...resourceUrls]
    .filter((url) => !checkedResources.has(url))
    .sort()
    .slice(0, 12);
  batch.forEach((url) => checkedResources.add(url));
  await Promise.all(batch.map(checkResource));
}

await writeCsv('sitemap-urls.csv', ['type', 'url'], sitemapRows);
await writeCsv(
  'crawl-report.csv',
  ['url', 'type', 'status', 'location', 'in_sitemap', 'sitemap_type', 'canonical', 'robots', 'discovered_from', 'issue'],
  pageRows,
);
await writeCsv('resource-report.csv', ['url', 'status', 'content_type', 'location', 'issue'], resourceRows);

const sitemapCounts = Object.groupBy(sitemapRows, (row) => row.type);
console.log(`SITEMAPS=${childSitemaps.length}`);
console.log(`SITEMAP_URLS=${sitemapRows.length}`);
console.log(`PUBLISHED_POSTS_AND_PAGES=${publishedContentCount}`);
for (const [type, rows] of Object.entries(sitemapCounts)) console.log(`SITEMAP_${type.toUpperCase()}=${rows.length}`);
console.log(`CRAWLED_PAGES=${pageRows.length}`);
console.log(`CRAWLED_RESOURCES=${resourceRows.length}`);
console.log(`ERRORS=${errors.length}`);
for (const error of errors) console.error(`ERROR: ${error}`);

if (errors.length) process.exitCode = 1;
