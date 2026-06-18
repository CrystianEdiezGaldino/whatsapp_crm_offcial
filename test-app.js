import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    console.log('1. Testing diagnostic endpoint...');
    const diagResponse = await page.goto('http://192.168.255.5/smcc-whatsapp/public/diagnose/db', {
      waitUntil: 'load',
      timeout: 15000
    });

    const diagContent = await page.content();
    console.log('Diagnostic response:', diagContent);

    console.log('\n2. Testing login page...');
    const loginResponse = await page.goto('http://192.168.255.5/smcc-whatsapp/public/login', {
      waitUntil: 'load',
      timeout: 15000
    });

    console.log('Login page status:', loginResponse.status());
    const loginPage = await page.content();

    if (loginPage.includes('Entrar na sua conta')) {
      console.log('✅ Login page loaded successfully!');
    } else {
      console.log('❌ Login page did not load properly');
      console.log(loginPage.substring(0, 500));
    }

    console.log('\n3. Testing login with admin credentials...');
    await page.fill('input[name="email"]', 'admin@erp.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Wait for response
    await page.waitForTimeout(3000);

    const pageUrl = page.url();
    console.log('After login, URL:', pageUrl);

    if (pageUrl.includes('dashboard')) {
      console.log('✅ Login successful! Redirected to dashboard');
    } else if (pageUrl.includes('login')) {
      console.log('❌ Login failed, still on login page');
      const errors = await page.locator('.alert-inset-error').textContent();
      console.log('Error message:', errors);
    }

  } catch (error) {
    console.error('Error:', error.message);
  } finally {
    await browser.close();
  }
})();
