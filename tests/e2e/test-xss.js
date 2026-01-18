const puppeteer = require('puppeteer');
const path = require('path');
(async () => {
  const fileUrl = 'file://' + path.resolve(__dirname, 'xss_page.html');
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const page = await browser.newPage();

  // Intercept fetch in the page to return a malicious message for client files endpoint
  await page.evaluateOnNewDocument(() => {
    // preserve original fetch if any
    window.__orig_fetch = window.fetch;
    window.fetch = (input, init) => {
      const url = String(input || '');
      if (url.includes('/clients/1/files')) {
        const body = JSON.stringify({ success: false, message: '<img src=x onerror=window.__XSS=1>' });
        const res = new Response(body, { status: 400, headers: { 'Content-Type': 'application/json' } });
        return Promise.resolve(res);
      }
      return window.__orig_fetch ? window.__orig_fetch(input, init) : Promise.reject(new Error('orig fetch unavailable'));
    };
  });

  await page.goto(fileUrl, { waitUntil: 'networkidle0' });

  // Click the View button which should trigger openFilesModal
  await page.click('button.contact-files');

  // Wait for the error element to appear inside contactFilesList
  await page.waitForSelector('#contactFilesList .text-sm.text-red-600', { timeout: 3000 });

  const errText = await page.$eval('#contactFilesList .text-sm.text-red-600', el => el.textContent.trim());
  const xssFlag = await page.evaluate(() => window.__XSS);

  console.log('Found error text:', errText);
  console.log('Window __XSS flag:', xssFlag);

  if (errText !== '<img src=x onerror=window.__XSS=1>') {
    console.error('Unexpected rendered error text (it should show raw text):', errText);
    await browser.close();
    process.exit(2);
  }

  if (typeof xssFlag !== 'undefined') {
    console.error('XSS executed; window.__XSS is set to:', xssFlag);
    await browser.close();
    process.exit(3);
  }

  console.log('PASS: malicious message did not execute and was rendered as text.');
  await browser.close();
  process.exit(0);
})();