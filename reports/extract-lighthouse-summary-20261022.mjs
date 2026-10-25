import fs from 'node:fs';

const sourcePath = 'C:/Users/Cris/Desktop/download';
const html = fs.readFileSync(sourcePath, 'utf8');
const marker = 'window.__LIGHTHOUSE_JSON__ = ';
const start = html.indexOf(marker);
if (start < 0) throw new Error('No se encontró window.__LIGHTHOUSE_JSON__');
const jsonStart = start + marker.length;
const end = html.indexOf(';</script>', jsonStart);
if (end < 0) throw new Error('No se encontró el cierre del JSON de Lighthouse');
const lhr = JSON.parse(html.slice(jsonStart, end));

const metricIds = [
  'first-contentful-paint',
  'largest-contentful-paint',
  'speed-index',
  'total-blocking-time',
  'cumulative-layout-shift',
  'interaction-to-next-paint',
  'server-response-time',
  'max-potential-fid',
];

const categories = Object.fromEntries(
  Object.entries(lhr.categories).map(([id, category]) => [id, {
    score: Math.round((category.score ?? 0) * 100),
    title: category.title,
  }]),
);

const metrics = Object.fromEntries(metricIds
  .filter((id) => lhr.audits[id])
  .map((id) => [id, {
    score: lhr.audits[id].score,
    displayValue: lhr.audits[id].displayValue,
    numericValue: lhr.audits[id].numericValue,
  }]));

const opportunities = Object.values(lhr.audits)
  .filter((audit) => audit.details?.overallSavingsMs > 0 || audit.details?.overallSavingsBytes > 0)
  .map((audit) => ({
    id: audit.id,
    title: audit.title,
    score: audit.score,
    displayValue: audit.displayValue,
    savingsMs: Math.round(audit.details?.overallSavingsMs || 0),
    savingsBytes: Math.round(audit.details?.overallSavingsBytes || 0),
  }))
  .sort((a, b) => (b.savingsMs - a.savingsMs) || (b.savingsBytes - a.savingsBytes));

const failedAudits = Object.values(lhr.audits)
  .filter((audit) => audit.scoreDisplayMode !== 'notApplicable' && audit.score !== null && audit.score < 1)
  .map((audit) => ({
    id: audit.id,
    title: audit.title,
    score: audit.score,
    displayValue: audit.displayValue,
    description: audit.description,
  }))
  .sort((a, b) => (a.score ?? 1) - (b.score ?? 1));

const diagnostics = {};
for (const id of [
  'largest-contentful-paint-element',
  'lcp-discovery-insight',
  'lcp-breakdown-insight',
  'render-blocking-insight',
  'image-delivery-insight',
  'network-dependency-tree-insight',
  'third-parties-insight',
  'unused-css-rules',
  'unused-javascript',
  'modern-image-formats',
  'uses-optimized-images',
  'uses-responsive-images',
  'uses-long-cache-ttl',
  'uses-text-compression',
  'font-display-insight',
  'dom-size-insight',
  'mainthread-work-breakdown',
  'bootup-time',
  'network-requests',
]) {
  const audit = lhr.audits[id];
  if (!audit) continue;
  diagnostics[id] = {
    title: audit.title,
    score: audit.score,
    displayValue: audit.displayValue,
    numericValue: audit.numericValue,
    details: audit.details,
  };
}

const issueDetails = {};
for (const id of [
  'aria-required-children',
  'link-name',
  'target-size',
  'paste-preventing-inputs',
  'cache-insight',
  'unminified-css',
  'unminified-javascript',
]) {
  const audit = lhr.audits[id];
  if (!audit) continue;
  issueDetails[id] = {
    title: audit.title,
    score: audit.score,
    displayValue: audit.displayValue,
    details: audit.details,
  };
}

const summary = {
  lighthouseVersion: lhr.lighthouseVersion,
  fetchTime: lhr.fetchTime,
  finalUrl: lhr.finalUrl,
  formFactor: lhr.configSettings?.formFactor,
  throttlingMethod: lhr.configSettings?.throttlingMethod,
  categories,
  metrics,
  opportunities,
  failedAudits,
  diagnostics,
  issueDetails,
};

const outPath = 'reports/lighthouse-summary-20261022.json';
fs.writeFileSync(outPath, JSON.stringify(summary, null, 2));
console.log(JSON.stringify({
  outPath,
  lighthouseVersion: summary.lighthouseVersion,
  fetchTime: summary.fetchTime,
  finalUrl: summary.finalUrl,
  formFactor: summary.formFactor,
  categories: summary.categories,
  metrics: summary.metrics,
  opportunities: summary.opportunities,
  diagnostics: Object.keys(diagnostics),
  issueDetails: Object.keys(issueDetails),
}, null, 2));
