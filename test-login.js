import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    console.log('🔄 Accessing login page...');
    const response = await page.goto('http://192.168.255.5/smcc-whatsapp/public/login', {
      waitUntil: 'domcontentloaded',
      timeout: 20000
    });

    console.log('✅ Status:', response.status());
    console.log('✅ URL:', page.url());

    // Check if page loaded
    const title = await page.title();
    console.log('📄 Title:', title);

    // Try to find login form
    const emailField = await page.$('input[name="email"]');
    if (emailField) {
      console.log('✅ Email field found!');
    } else {
      console.log('❌ Email field NOT found');
    }

    // Check for errors
    const errorMsg = await page.$eval('.alert-inset-error', el => el.textContent).catch(() => null);
    if (errorMsg) {
      console.log('⚠️  Error on page:', errorMsg);
    }

  } catch (error) {
    console.error('❌ Error:', error.message);
  } finally {
    await browser.close();
  }
})();
