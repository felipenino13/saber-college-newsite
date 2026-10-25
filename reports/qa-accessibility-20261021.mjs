import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const axeSource = fs.readFileSync(require.resolve('axe-core'), 'utf8');

const outputDir = path.resolve('reports/accessibility-audit-20261021');
fs.mkdirSync(outputDir, { recursive: true });

const pages = [
  ['home', '/'],
  ['programs', '/saber_college_programs/'],
  ['nursing', '/saber_college_programs/professional-nursing-program/'],
  ['pta', '/saber_college_programs/physical-therapist-assistant-program/'],
  ['admissions', '/admissions-and-tuition/'],
  ['apply', '/apply-to-saber-college/'],
  ['accreditation', '/saber-college-accreditation/'],
  ['student-services', '/student-services/'],
  ['blog', '/saber_college_blog/'],
  ['landing-nursing', '/lp/pnp/'],
  ['landing-pta', '/lp/pta/'],
  ['contact', '/about_saber_college/contact/'],
  ['survey', '/student-services/saber-survey/'],
  ['landing-home', '/lp/'],
  ['landing-campus', '/lp/campus-location/'],
  ['design-system', '/saber-design-system-elementor/'],
];

const viewports = [
  ['desktop', { width: 1440, height: 1000 }],
  ['mobile', { width: 390, height: 844 }],
];

function summarizeNode(node) {
  return {
    html: node.html,
    target: node.target,
    failureSummary: node.failureSummary || '',
  };
}

const browser = await chromium.launch({
  headless: true,
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
});

const results = [];
for (const [pageName, pathname] of pages) {
  for (const [viewportName, viewport] of viewports) {
    const context = await browser.newContext({ viewport, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const consoleErrors = [];
    const pageErrors = [];
    page.on('console', (message) => {
      if (message.type() === 'error') consoleErrors.push(message.text());
    });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    const url = `http://localhost:8082${pathname}`;
    const response = await page.goto(url, { waitUntil: 'networkidle', timeout: 60_000 });
    await page.addScriptTag({ content: axeSource });
    const axe = await page.evaluate(async () => window.axe.run(document, {
      runOnly: {
        type: 'tag',
        values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'],
      },
      resultTypes: ['violations', 'incomplete'],
    }));

    const dom = await page.evaluate(() => {
      const headingLevels = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')]
        .filter((heading) => {
          const style = getComputedStyle(heading);
          return style.display !== 'none' && style.visibility !== 'hidden';
        })
        .map((heading) => Number(heading.tagName.slice(1)));
      const headingSkips = [];
      for (let index = 1; index < headingLevels.length; index += 1) {
        if (headingLevels[index] > headingLevels[index - 1] + 1) {
          headingSkips.push([headingLevels[index - 1], headingLevels[index], index]);
        }
      }

      const focusables = [...document.querySelectorAll(
        'a[href],button,input:not([type="hidden"]),select,textarea,[tabindex]:not([tabindex="-1"])'
      )].filter((element) => {
        const style = getComputedStyle(element);
        const rect = element.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
      });
      const unnamed = focusables.filter((element) => {
        const labelledBy = element.getAttribute('aria-labelledby');
        const labelledText = labelledBy
          ? labelledBy.split(/\s+/).map((id) => document.getElementById(id)?.textContent || '').join(' ')
          : '';
        const imageAlt = [...element.querySelectorAll('img')].map((image) => image.alt).join(' ');
        const labelText = element.labels ? [...element.labels].map((label) => label.textContent || '').join(' ') : '';
        const text = [
          element.getAttribute('aria-label'),
          labelledText,
          labelText,
          element.textContent,
          element.getAttribute('title'),
          element.getAttribute('value'),
          imageAlt,
        ].filter(Boolean).join(' ').trim();
        return !text;
      }).map((element) => element.outerHTML.slice(0, 300));

      const missingLabels = [...document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]),select,textarea')]
        .filter((field) => {
          const style = getComputedStyle(field);
          const rect = field.getBoundingClientRect();
          if (field.getAttribute('aria-hidden') === 'true' || style.display === 'none' || style.visibility === 'hidden' || rect.width <= 0 || rect.height <= 0) {
            return false;
          }
          const labels = field.labels ? [...field.labels] : [];
          return !labels.length && !field.getAttribute('aria-label') && !field.getAttribute('aria-labelledby');
        })
        .map((field) => field.outerHTML.slice(0, 300));

      const duplicateIds = [...document.querySelectorAll('[id]')]
        .map((element) => element.id)
        .filter((id, index, ids) => id && ids.indexOf(id) !== index)
        .filter((id, index, ids) => ids.indexOf(id) === index);

      const movingElements = [...document.querySelectorAll('*')].filter((element) => {
        const style = getComputedStyle(element);
        const animation = parseFloat(style.animationDuration) || 0;
        const transition = Math.max(...style.transitionDuration.split(',').map((value) => parseFloat(value) || 0));
        return animation > 0.1 || transition > 0.1;
      }).length;

      return {
        lang: document.documentElement.lang,
        title: document.title,
        mainCount: document.querySelectorAll('main, [role="main"]').length,
        navCount: document.querySelectorAll('nav,[role="navigation"]').length,
        h1Count: document.querySelectorAll('h1').length,
        headingLevels,
        headingSkips,
        imageCount: document.images.length,
        imagesMissingAlt: [...document.images].filter((image) => !image.hasAttribute('alt')).length,
        decorativeImages: [...document.images].filter((image) => image.getAttribute('alt') === '').length,
        focusableCount: focusables.length,
        unnamedFocusable: unnamed,
        positiveTabindex: focusables.filter((element) => Number(element.getAttribute('tabindex')) > 0).length,
        missingLabels,
        duplicateIds,
        dialogs: [...document.querySelectorAll('[role="dialog"],dialog')].map((dialog) => ({
          labelled: Boolean(dialog.getAttribute('aria-label') || dialog.getAttribute('aria-labelledby')),
          hidden: dialog.getAttribute('aria-hidden'),
        })),
        horizontalOverflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        movingElementsWithReducedMotion: movingElements,
      };
    });

    const keyboard = await page.evaluate(() => {
      document.body.focus();
      return true;
    }).then(async () => {
      const visited = [];
      let repeated = 0;
      for (let index = 0; index < 40; index += 1) {
        await page.keyboard.press('Tab');
        const state = await page.evaluate(() => {
          const element = document.activeElement;
          if (!element) return null;
          const style = getComputedStyle(element);
          const rect = element.getBoundingClientRect();
          return {
            tag: element.tagName,
            id: element.id || '',
            text: (element.getAttribute('aria-label') || element.textContent || element.getAttribute('value') || '').trim().slice(0, 80),
            visible: rect.width > 0 && rect.height > 0 && style.visibility !== 'hidden' && style.display !== 'none',
            focusIndicator: (style.outlineStyle !== 'none' && parseFloat(style.outlineWidth) > 0) || style.boxShadow !== 'none',
          };
        });
        if (!state || state.tag === 'BODY' || state.tag === 'HTML') continue;
        const key = `${state.tag}:${state.id}:${state.text}`;
        if (visited.some((item) => item.key === key)) repeated += 1;
        visited.push({ key, ...state });
      }
      return {
        sampled: visited.length,
        invisibleFocusTargets: visited.filter((item) => !item.visible),
        withoutFocusIndicator: visited.filter((item) => !item.focusIndicator),
        repeated,
      };
    });

    const reflowWidth = Math.max(320, Math.floor(viewport.width / 2));
    await page.setViewportSize({ width: reflowWidth, height: Math.max(500, Math.floor(viewport.height / 2)) });
    await page.waitForTimeout(300);
    const reflow = await page.evaluate(() => ({
      overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      viewport: document.documentElement.clientWidth,
    }));

    results.push({
      page: pageName,
      pathname,
      viewport: viewportName,
      status: response?.status() || 0,
      accessibilityScore: axe.violations.length === 0 ? 1 : 0,
      violations: axe.violations.map((violation) => ({
        id: violation.id,
        impact: violation.impact,
        help: violation.help,
        nodes: violation.nodes.map(summarizeNode),
      })),
      incomplete: axe.incomplete.map((item) => ({
        id: item.id,
        impact: item.impact,
        help: item.help,
        nodes: item.nodes.map(summarizeNode),
      })),
      dom,
      keyboard,
      reflow,
      consoleErrors,
      pageErrors,
    });
    await context.close();
  }
}

await browser.close();

const summary = {
  generatedAt: new Date().toISOString(),
  pages: pages.length,
  runs: results.length,
  violationRuns: results.filter((result) => result.violations.length).length,
  violationIds: [...new Set(results.flatMap((result) => result.violations.map((violation) => violation.id)))],
  headingSkipRuns: results.filter((result) => result.dom.headingSkips.length).length,
  missingLabelRuns: results.filter((result) => result.dom.missingLabels.length).length,
  unnamedFocusableRuns: results.filter((result) => result.dom.unnamedFocusable.length).length,
  overflowRuns: results.filter((result) => result.dom.horizontalOverflow > 1).length,
  reflowOverflowRuns: results.filter((result) => result.reflow.overflow > 1).length,
  consoleErrorRuns: results.filter((result) => result.consoleErrors.length).length,
};

const outputName = process.argv[2] || 'axe-and-keyboard-after.json';
fs.writeFileSync(path.join(outputDir, outputName), JSON.stringify({ summary, results }, null, 2));
console.log(JSON.stringify(summary, null, 2));
