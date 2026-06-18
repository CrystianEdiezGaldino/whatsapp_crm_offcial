import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    console.log('🔄 Accessing login page...');
    await page.goto('http://192.168.255.5/smcc-whatsapp/public/login', {
      waitUntil: 'domcontentloaded',
      timeout: 20000
    });

    console.log('✅ Login page loaded');

    console.log('\n🔄 Filling credentials...');
    await page.fill('input[name="email"]', 'admin@erp.com');
    console.log('   ✅ Email filled');

    await page.fill('input[name="password"]', 'password');
    console.log('   ✅ Password filled');

    console.log('\n🔄 Submitting login...');
    await page.click('button[type="submit"]');

    // Wait for navigation or error
    console.log('⏳ Waiting for response...');
    await page.waitForTimeout(5000);

    const currentUrl = page.url();
    console.log('📍 Current URL:', currentUrl);

    // Check for errors
    const errorElement = await page.$('.alert-inset-error');
    if (errorElement) {
      const errorText = await errorElement.textContent();
      console.log('\n❌ LOGIN FAILED - Error message:');
      console.log('   ', errorText.trim());
    } else if (currentUrl.includes('dashboard')) {
      console.log('\n✅ LOGIN SUCCESSFUL! Redirected to dashboard');
    } else if (currentUrl.includes('login')) {
      console.log('\n⚠️  Still on login page - may be processing...');
      const pageText = await page.textContent('body');
      if (pageText.includes('Credenciais inválidas')) {
        console.log('❌ Invalid credentials');
      }
    }

  } catch (error) {
    console.error('\n❌ Error:', error.message);
    console.error('Stack:', error.stack);
  } finally {
    await browser.close();
  }
})();
