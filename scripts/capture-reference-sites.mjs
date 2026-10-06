import fs from "node:fs/promises";
import fsSync from "node:fs";
import { createRequire } from "node:module";
import path from "node:path";

const require = createRequire(import.meta.url);
const { chromium } = require("playwright");

const root = path.resolve("referentes", "img");
const onlyArgument = process.argv.find((argument) => argument.startsWith("--only="));
const only = onlyArgument?.slice("--only=".length).toLowerCase();
const onlyTerms = only?.split(",").map((term) => term.trim()).filter(Boolean) ?? [];

const pages = [
  // Home
  ["home", "usahs", "University of St. Augustine for Health Sciences", "https://www.usa.edu/"],
  ["home", "adventhealth-university", "AdventHealth University", "https://www.ahu.edu/"],
  ["home", "johns-hopkins-nursing", "Johns Hopkins School of Nursing", "https://nursing.jhu.edu/"],
  ["home", "miami-sonhs", "University of Miami SONHS", "https://www.sonhs.miami.edu/"],
  ["home", "west-coast-university", "West Coast University", "https://westcoastuniversity.edu/"],
  ["home", "chamberlain", "Chamberlain University", "https://www.chamberlain.edu/"],
  ["home", "duke-nursing", "Duke University School of Nursing", "https://nursing.duke.edu/"],
  ["home", "mgbu", "Mass General Brigham University of Health Professions", "https://www.mgbu.edu/"],
  ["home", "galen", "Galen College of Nursing", "https://galencollege.edu/"],
  ["home", "penn-nursing", "University of Pennsylvania School of Nursing", "https://www.nursing.upenn.edu/"],

  // Program discovery and program landing pages
  ["programas", "usahs-rehabilitative-sciences", "USAHS - Rehabilitative Sciences", "https://www.usa.edu/college-of-rehabilitative-sciences/"],
  ["programas", "adventhealth-programs", "AdventHealth University - Programs", "https://www.ahu.edu/programs"],
  ["programas", "johns-hopkins-programs", "Johns Hopkins Nursing - Programs", "https://nursing.jhu.edu/programs/"],
  ["programas", "west-coast-programs", "West Coast University - Programs", "https://westcoastuniversity.edu/programs"],
  ["programas", "chamberlain-nursing-programs", "Chamberlain - Nursing Programs", "https://www.chamberlain.edu/academics/nursing-school/nursing-programs"],
  ["programas", "mgbu-academics", "MGBU - Academics", "https://www.mgbu.edu/academics"],
  ["programas", "galen-academics", "Galen - Academics", "https://galencollege.edu/academics"],
  ["programas", "ahu-bs-nursing", "AdventHealth University - BS Nursing", "https://www.ahu.edu/programs/bs-nursing"],

  // Admissions and tuition journeys
  ["admisiones", "usahs-admissions", "USAHS - Admissions and Aid", "https://www.usa.edu/admissions-aid/"],
  ["admisiones", "adventhealth-admissions", "AdventHealth University - Admissions", "https://www.ahu.edu/admissions"],
  ["admisiones", "johns-hopkins-admissions", "Johns Hopkins Nursing - Admissions", "https://nursing.jhu.edu/admissions/"],
  ["admisiones", "duke-admissions", "Duke Nursing - Admissions", "https://nursing.duke.edu/academic-programs/admissions"],
  ["admisiones", "penn-admissions", "Penn Nursing - Admissions", "https://www.nursing.upenn.edu/admissions/"],
  ["admisiones", "mgbu-admissions", "MGBU - Admissions", "https://www.mgbu.edu/admissions"],
  ["admisiones", "ahu-bsn-admissions", "AdventHealth University - BS Nursing Admissions", "https://online.ahu.edu/programs/bs-nursing/admissions"],

  // Blog indexes and editorial systems
  ["blog", "usahs-blog", "USAHS - Blog", "https://www.usa.edu/blog/"],
  ["blog", "johns-hopkins-magazine", "Johns Hopkins Nursing Magazine", "https://nursing.jhu.edu/magazine/"],
  ["blog", "chamberlain-blog", "Chamberlain - Blog", "https://www.chamberlain.edu/blog"],
  ["blog", "ahu-blog", "AdventHealth University - Blog", "https://www.ahu.edu/blog?keys=Nursing"],

  // Individual articles
  ["articles", "usahs-miami-dolphins-story", "USAHS - Miami Dolphins student story", "https://www.usa.edu/blog/from-aspiration-to-action-a-students-experience-with-the-miami-dolphins-pt-team/"],
  ["articles", "usahs-miami-clinical-training", "USAHS - Miami clinical training story", "https://www.usa.edu/blog/building-better-clinicians-miami-alumni-strengthen-pt-education-through-clinical-training/"],
  ["articles", "chamberlain-alumni-story", "Chamberlain - Alumni story", "https://www.chamberlain.edu/blog/alumni/moving-through-fear-penelope-deverteuils-journey-from-struggling-nursing-student-to-dnp-leader"],
  ["articles", "jhu-outside-track", "Johns Hopkins Nursing - Student profile", "https://nursing.jhu.edu/magazine/articles/2026/05/outside-track-more-than-words-to-nakeesha-donovan/"],
  ["articles", "ahu-msn-accreditation", "AdventHealth University - Accreditation story", "https://www.ahu.edu/news/ahu-msn-program-earns-accreditation"],

  // Catalogs, manuals and resource hubs
  ["manuales-recursos", "usahs-catalog", "USAHS - University Catalog", "https://catalog.usa.edu/"],
  ["manuales-recursos", "adventhealth-catalog", "AdventHealth University - Academic Catalog", "https://catalog.ahu.edu/"],

  // Legal and disclosure presentation
  ["legales", "usahs-legal-disclosures", "USAHS - Legal and Consumer Disclosures", "https://www.usa.edu/legal/"],
  ["legales", "adventhealth-privacy", "AdventHealth University - Privacy Policy", "https://www.ahu.edu/privacy-policy"],
  ["legales", "ahu-institutional-summaries", "AdventHealth University - Institutional Summaries", "https://www.ahu.edu/accreditation/institutional-summaries"],

  // Conversion pages and primary calls to action
  ["conversion", "usahs-request-information", "USAHS - Request Information", "https://www.usa.edu/request-information/"],
  ["conversion", "west-coast-request-information", "West Coast University - Request Information", "https://westcoastuniversity.edu/get-started/request-info"],
  ["conversion", "johns-hopkins-apply", "Johns Hopkins Nursing - Apply", "https://nursing.jhu.edu/admissions/apply/"],

  // Institutional story, campus and trust
  ["nosotros-campus", "usahs-miami-campus", "USAHS - Miami Campus", "https://www.usa.edu/campus/miami/"],
  ["nosotros-campus", "adventhealth-about", "AdventHealth University - About", "https://www.ahu.edu/about-ahu"],
  ["nosotros-campus", "mgbu-about", "MGBU - About", "https://www.mgbu.edu/about"],
  ["nosotros-campus", "jhu-about", "Johns Hopkins Nursing - About", "https://nursing.jhu.edu/about-us/"],
];

const selectedPages = onlyTerms.length > 0
  ? pages.filter(([category, slug, title]) =>
      onlyTerms.some((term) => `${category}/${slug} ${title}`.toLowerCase().includes(term)),
    )
  : pages;

async function dismissConsent(page) {
  const labels = /^(accept|accept all|allow all|agree|i agree|got it|continue)$/i;
  for (const frame of page.frames()) {
    try {
      const button = frame.getByRole("button", { name: labels }).first();
      if (await button.isVisible({ timeout: 500 })) {
        await button.click({ timeout: 1_500 });
        return;
      }
    } catch {
      // Consent tools often live in cross-origin frames; an inaccessible one is harmless.
    }
  }
}

async function capture(browser, item) {
  const [category, slug, title, url] = item;
  const directory = path.join(root, category);
  const output = path.join(directory, `${slug}.jpg`);
  await fs.mkdir(directory, { recursive: true });

  const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
    deviceScaleFactor: 1,
    colorScheme: "light",
    locale: "en-US",
    userAgent:
      "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 " +
      "(KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36",
  });
  const page = await context.newPage();
  page.setDefaultTimeout(15_000);

  try {
    const response = await page.goto(url, {
      waitUntil: "domcontentloaded",
      timeout: 75_000,
    });

    await page.waitForLoadState("networkidle", { timeout: 12_000 }).catch(() => {});
    await dismissConsent(page);
    const frameTexts = await Promise.all(
      page.frames().map((frame) =>
        frame.locator("body").innerText({ timeout: 2_000 }).catch(() => ""),
      ),
    );
    const pageText = frameTexts.join("\n");
    const pageTitle = await page.title().catch(() => "");
    if (
      /sorry, you have been blocked|confirm you are human|check you're not a robot/i.test(pageText) ||
      /just a moment|security check|attention required/i.test(pageTitle)
    ) {
      throw new Error("The site returned an anti-bot verification page instead of its content.");
    }
    await page.addStyleTag({
      content: `
        *, *::before, *::after {
          animation-duration: 0s !important;
          animation-delay: 0s !important;
          transition-duration: 0s !important;
          caret-color: transparent !important;
        }
      `,
    }).catch(() => {});
    await page.waitForTimeout(1_500);
    const screenshot = await page.screenshot({
      type: "jpeg",
      quality: 82,
      fullPage: true,
    });
    if (screenshot.length < 40_000) {
      throw new Error("The captured page is suspiciously small and is likely a verification screen.");
    }
    await fs.writeFile(output, screenshot);

    return {
      category,
      slug,
      title,
      sourceUrl: url,
      finalUrl: page.url(),
      status: response?.status() ?? null,
      file: path.relative(path.resolve("referentes"), output).replaceAll("\\", "/"),
      capturedAt: new Date().toISOString(),
      result: "ok",
    };
  } catch (error) {
    await fs.rm(output, { force: true }).catch(() => {});
    return {
      category,
      slug,
      title,
      sourceUrl: url,
      finalUrl: page.url(),
      file: path.relative(path.resolve("referentes"), output).replaceAll("\\", "/"),
      capturedAt: new Date().toISOString(),
      result: "error",
      error: error instanceof Error ? error.message : String(error),
    };
  } finally {
    await context.close();
  }
}

await fs.mkdir(root, { recursive: true });
const browserCandidates = [
  process.env.CHROME_PATH,
  "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe",
].filter(Boolean);
const executablePath = browserCandidates.find((candidate) => fsSync.existsSync(candidate));
let mgbuIp;
try {
  const response = await fetch("https://dns.google/resolve?name=www.mgbu.edu&type=A");
  const dns = await response.json();
  mgbuIp = dns.Answer?.find((answer) => answer.type === 1)?.data;
} catch {
  // Chrome will use the computer's normal DNS resolution when the fallback is unavailable.
}
const browser = await chromium.launch({
  headless: true,
  executablePath,
  args: mgbuIp ? [`--host-resolver-rules=MAP www.mgbu.edu ${mgbuIp}`] : [],
});
const results = [];

try {
  for (const item of selectedPages) {
    const result = await capture(browser, item);
    results.push(result);
    console.log(`${result.result.toUpperCase()} ${result.category}/${result.slug}`);
  }
} finally {
  await browser.close();
}

const manifestPath = path.join(root, "manifest.json");
let manifestResults = results;
if (only) {
  try {
    const previous = JSON.parse(await fs.readFile(manifestPath, "utf8"));
    const refreshed = new Set(results.map((item) => `${item.category}/${item.slug}`));
    manifestResults = [
      ...previous.filter((item) => !refreshed.has(`${item.category}/${item.slug}`)),
      ...results,
    ];
  } catch {
    // A missing or invalid prior manifest simply creates a new filtered manifest.
  }
}
await fs.writeFile(manifestPath, `${JSON.stringify(manifestResults, null, 2)}\n`, "utf8");

const failures = results.filter((result) => result.result !== "ok");
console.log(`Captured ${results.length - failures.length}/${results.length} pages.`);
if (failures.length > 0) {
  console.log(`Failed: ${failures.map((item) => `${item.category}/${item.slug}`).join(", ")}`);
  process.exitCode = 1;
}
