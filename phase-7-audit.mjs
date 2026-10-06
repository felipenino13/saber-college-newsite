import fs from 'node:fs/promises';

function csvCell(value) {
  const text = String(value ?? '');
  return `"${text.replaceAll('"', '""')}"`;
}

function parseCsvLine(line) {
  const values = [];
  let value = '';
  let quoted = false;
  for (let index = 0; index < line.length; index += 1) {
    const character = line[index];
    if (quoted && character === '"' && line[index + 1] === '"') {
      value += '"';
      index += 1;
    } else if (character === '"') {
      quoted = !quoted;
    } else if (character === ',' && !quoted) {
      values.push(value);
      value = '';
    } else {
      value += character;
    }
  }
  values.push(value);
  return values;
}

function attrs(tag) {
  const result = {};
  const expression = /([:\w-]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g;
  for (const match of tag.matchAll(expression)) {
    result[match[1].toLowerCase()] = match[2] ?? match[3] ?? match[4] ?? '';
  }
  return result;
}

function hierarchyIssues(html) {
  const levels = [...html.matchAll(/<h([1-6])\b[^>]*>/gi)].map((match) => Number(match[1]));
  const issues = [];
  for (let index = 1; index < levels.length; index += 1) {
    if (levels[index] > levels[index - 1] + 1) {
      issues.push(`H${levels[index - 1]} to H${levels[index]}`);
    }
  }
  return [...new Set(issues)].join(' | ');
}

const report = await fs.readFile('crawl-report.csv', 'utf8');
const lines = report.trim().split(/\r?\n/);
const headers = parseCsvLine(lines.shift());
const records = lines.map((line) => Object.fromEntries(headers.map((header, index) => [header, parseCsvLine(line)[index]])));
const urls = records.filter((row) => row.status === '200' && row.type === 'page').map((row) => row.url);
const rows = [];
const imageMap = new Map();

for (const url of urls) {
  const response = await fetch(url, { signal: AbortSignal.timeout(20000) });
  const html = await response.text();
  const headings = {};
  for (let level = 1; level <= 6; level += 1) {
    headings[`h${level}`] = [...html.matchAll(new RegExp(`<h${level}\\b`, 'gi'))].length;
  }

  const images = [...html.matchAll(/<img\b[^>]*>/gi)].map((match) => attrs(match[0]));
  const missingAlt = images.filter((image) => !Object.hasOwn(image, 'alt')).length;
  const emptyAlt = images.filter((image) => Object.hasOwn(image, 'alt') && image.alt.trim() === '').length;
  const missingDimensions = images.filter((image) => !image.width || !image.height).length;
  const lazyImages = images.filter((image) => (image.loading || '').toLowerCase() === 'lazy').length;
  const externalHttp = [...new Set(
    [...html.matchAll(/href\s*=\s*["'](http:\/\/[^"']+)/gi)]
      .map((match) => match[1])
      .filter((value) => !/^http:\/\/localhost:8082\/?$/i.test(value) && !/^http:\/\/localhost:8082\//i.test(value)),
  )];
  const mixedHttpResources = [...new Set(
    [...html.matchAll(/src\s*=\s*["'](http:\/\/[^"']+)/gi)]
      .map((match) => match[1])
      .filter((value) => !/^http:\/\/localhost:8082\/?$/i.test(value) && !/^http:\/\/localhost:8082\//i.test(value)),
  )];

  for (const image of images) {
    if (!image.src) continue;
    const imageUrl = new URL(image.src, url).href;
    const current = imageMap.get(imageUrl) || {
      url: imageUrl,
      occurrences: 0,
      pages: new Set(),
      alt_values: new Set(),
      missing_alt_attribute: 0,
      empty_alt: 0,
      missing_dimensions: 0,
      lazy_occurrences: 0,
    };
    current.occurrences += 1;
    current.pages.add(url);
    if (!Object.hasOwn(image, 'alt')) current.missing_alt_attribute += 1;
    else if (!image.alt.trim()) current.empty_alt += 1;
    else current.alt_values.add(image.alt.trim());
    if (!image.width || !image.height) current.missing_dimensions += 1;
    if ((image.loading || '').toLowerCase() === 'lazy') current.lazy_occurrences += 1;
    imageMap.set(imageUrl, current);
  }
  const issues = [];
  if (headings.h1 === 0) issues.push('Missing H1');
  if (headings.h1 > 1) issues.push(`Multiple H1 (${headings.h1})`);
  if (missingAlt) issues.push(`Images missing alt (${missingAlt})`);
  if (externalHttp.length) issues.push(`External HTTP references (${externalHttp.length})`);
  if (mixedHttpResources.length) issues.push(`Mixed HTTP resources (${mixedHttpResources.length})`);

  rows.push({
    url,
    ...headings,
    hierarchy_gaps: hierarchyIssues(html),
    images: images.length,
    missing_alt_attribute: missingAlt,
    empty_alt: emptyAlt,
    missing_dimensions: missingDimensions,
    lazy_images: lazyImages,
    external_http_references: externalHttp.join(' | '),
    mixed_http_resources: mixedHttpResources.join(' | '),
    issue: issues.join('; '),
  });
}

const outputHeaders = [
  'url', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hierarchy_gaps', 'images',
  'missing_alt_attribute', 'empty_alt', 'missing_dimensions', 'lazy_images',
  'external_http_references', 'mixed_http_resources', 'issue',
];
const output = [outputHeaders.map(csvCell).join(',')];
for (const row of rows) output.push(outputHeaders.map((header) => csvCell(row[header])).join(','));
await fs.writeFile('technical-seo-report.csv', `${output.join('\r\n')}\r\n`, 'utf8');

const imageHeaders = [
  'url', 'occurrences', 'page_count', 'alt_values', 'missing_alt_attribute',
  'empty_alt', 'missing_dimensions', 'lazy_occurrences', 'sample_page',
];
const imageRows = [...imageMap.values()].map((image) => ({
  ...image,
  page_count: image.pages.size,
  alt_values: [...image.alt_values].join(' | '),
  sample_page: [...image.pages][0] || '',
}));
const imageOutput = [imageHeaders.map(csvCell).join(',')];
for (const row of imageRows) imageOutput.push(imageHeaders.map((header) => csvCell(row[header])).join(','));
await fs.writeFile('image-seo-report.csv', `${imageOutput.join('\r\n')}\r\n`, 'utf8');

const totals = rows.reduce((result, row) => {
  result.missingH1 += row.h1 === 0 ? 1 : 0;
  result.multipleH1 += row.h1 > 1 ? 1 : 0;
  result.hierarchyGaps += row.hierarchy_gaps ? 1 : 0;
  result.images += row.images;
  result.missingAlt += row.missing_alt_attribute;
  result.emptyAlt += row.empty_alt;
  result.missingDimensions += row.missing_dimensions;
  result.externalHttp += row.external_http_references ? 1 : 0;
  result.mixedHttp += row.mixed_http_resources ? 1 : 0;
  return result;
}, { missingH1: 0, multipleH1: 0, hierarchyGaps: 0, images: 0, missingAlt: 0, emptyAlt: 0, missingDimensions: 0, externalHttp: 0, mixedHttp: 0 });

console.log(`PAGES=${rows.length}`);
for (const [key, value] of Object.entries(totals)) console.log(`${key.toUpperCase()}=${value}`);
