import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const pages = JSON.parse(fs.readFileSync('reports/responsive-ux-audit-20261021/published-pages.json', 'utf8'));
const outputDir = path.resolve('reports/content-compliance-audit-20261021');
fs.mkdirSync(outputDir, { recursive: true });

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});
const context = await browser.newContext();
const page = await context.newPage();
const results = [];

for (const item of pages) {
  const response = await page.goto(item.url, { waitUntil: 'networkidle', timeout: 60_000 });
  const audit = await page.evaluate(() => {
    const visible = (element) => {
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
    };
    const bodyText = document.body.innerText.replace(/\r/g, '').trim();
    const formTexts = [...document.querySelectorAll('form')].map((form) => form.innerText.replace(/\s+/g, ' ').trim());
    const linkedPolicies = [...document.querySelectorAll('a[href]')].filter((link) => /privacy|terms|condition|policy|ferpa/i.test(`${link.textContent} ${link.href}`)).map((link) => ({
      name: (link.textContent || link.getAttribute('aria-label') || '').trim(),
      href: link.href,
    }));
    const cookieControls = [...document.querySelectorAll('[class*="cookie" i], [id*="cookie" i], [class*="consent" i], [id*="consent" i]')]
      .filter(visible)
      .map((element) => ({ tag: element.tagName, id: element.id, classes: element.className, text: element.textContent.trim().slice(0, 300) }));
    return {
      title: document.title,
      robots: document.querySelector('meta[name="robots"]')?.content || '',
      bodyText,
      formTexts,
      linkedPolicies,
      cookieControls,
      h1: [...document.querySelectorAll('h1')].map((h) => h.textContent.trim()),
      addresses: bodyText.match(/\b\d{3,5}\s+West\s+Flagler(?:\s+Street|\s+St\.?)?[^\n]*/gi) || [],
      phones: [...new Set((bodyText.match(/\(?305\)?[\s.-]*443[\s.-]*9170/g) || []))],
      emails: [...new Set((document.body.innerHTML.match(/[A-Z0-9._%+-]+@sabercollege\.edu/gi) || []).map((value) => value.toLowerCase()))],
      suspicious: bodyText.split('\n').map((line) => line.trim()).filter((line) => /lorem ipsum|coming soon|under construction|\bdraft\b|test page|placeholder|guaranteed? (job|employment)|100% placement|VESL|medical assist/i.test(line)).slice(0, 100),
      regulatory: bodyText.split('\n').map((line) => line.trim()).filter((line) => /credit hours?|clock hours?|accredit|licen[cs]|tuition|fee|cost|financial aid|gainful employment|FERPA|privacy|terms|effective date|last updated|revised/i.test(line)).slice(0, 300),
    };
  });
  results.push({ id: item.id, pageTitle: item.title, url: item.url, status: response?.status() || 0, ...audit });
}

const cookies = await context.cookies();
await browser.close();

const summary = {
  pages: results.length,
  non200: results.filter((result) => result.status !== 200).length,
  pagesWithCookieControls: results.filter((result) => result.cookieControls.length).map((result) => result.url),
  pagesWithForms: results.filter((result) => result.formTexts.length).length,
  pagesWithPolicyLinks: results.filter((result) => result.linkedPolicies.length).length,
  noindexPages: results.filter((result) => /noindex/i.test(result.robots)).map((result) => ({ id: result.id, title: result.pageTitle, url: result.url })),
  cookies,
};

fs.writeFileSync(path.join(outputDir, 'site-content-inventory.json'), JSON.stringify({ summary, results }, null, 2));
fs.writeFileSync(path.join(outputDir, 'site-visible-text.txt'), results.map((result) => `\n\n===== ${result.id} | ${result.pageTitle} | ${result.url} =====\n${result.bodyText}`).join(''));
console.log(JSON.stringify(summary, null, 2));
