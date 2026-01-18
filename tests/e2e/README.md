This directory contains a small Puppeteer-based E2E test that verifies the frontend renders error messages as text and does not execute injected HTML/script content.

Run the test locally:

1. Install Node dependencies (once):
   npm ci

2. Run the test:
   npm run test:e2e

What it does:
- Loads `tests/e2e/xss_page.html` (isolated harness that includes `clients.js`).
- Overrides `fetch` to return a malicious message for `/clients/1/files`.
- Triggers the files modal and asserts the injected markup is rendered as plain text and no script executes.

Note: The test requires Chrome/Chromium; Puppeteer will download it when installing dependencies.
