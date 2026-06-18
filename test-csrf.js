import { chromium } from 'playwright';

async function testLogin(url) {
  console.log(`\n${'='.repeat(60)}`);
  console.log(`Testing: ${url}`);
  console.log('='.repeat(60));

  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Step 1: Get login page and CSRF token
    console.log('\n1️⃣  Getting login page...');
    const response = await page.goto(url, {
      waitUntil: 'domcontentloaded',
      timeout: 20000
    });
    console.log(`   Status: ${response.status()}`);

    // Step 2: Extract CSRF token
    console.log('\n2️⃣  Extracting CSRF token...');
    const csrfToken = await page.$eval('input[name="_token"]', el => el.value).catch(() => null);
    if (csrfToken) {
      console.log(`   ✅ Token found: ${csrfToken.substring(0, 20)}...`);
    } else {
      console.log('   ❌ No CSRF token found!');
    }

    // Step 3: Check cookies
    console.log('\n3️⃣  Checking session cookies...');
    const cookies = await context.cookies();
    const sessionCookie = cookies.find(c => c.name === 'XSRF-TOKEN' || c.name === 'laravel_session');
    if (sessionCookie) {
      console.log(`   ✅ Session cookie found: ${sessionCookie.name}`);
    } else {
      console.log('   ❌ No session cookie!');
      console.log(`   All cookies: ${cookies.map(c => c.name).join(', ')}`);
    }

    // Step 4: Submit login
    if (csrfToken) {
      console.log('\n4️⃣  Submitting login form...');
      await page.fill('input[name="email"]', 'admin@erp.com');
      await page.fill('input[name="password"]', 'password');

      // Log the form data that will be sent
      const formData = new FormData();
      formData.append('email', 'admin@erp.com');
      formData.append('password', 'password');
      formData.append('_token', csrfToken);

      const submitResponse = await page.click('button[type="submit"]');

      // Wait for response
      await page.waitForTimeout(3000);

      const finalUrl = page.url();
      console.log(`   Response URL: ${finalUrl}`);

      // Check response status
      if (finalUrl.includes('dashboard')) {
        console.log('   ✅ LOGIN SUCCESSFUL!');
      } else if (finalUrl.includes('login')) {
        // Check for error
        const errorElement = await page.$('.alert-inset-error');
        if (errorElement) {
          const errorText = await errorElement.textContent();
          console.log(`   ❌ Error: ${errorText.trim()}`);
        } else {
          console.log('   ❌ Login failed (still on login page)');
        }
      }
    }

  } catch (error) {
    console.error(`   ❌ Error: ${error.message}`);
  } finally {
    await browser.close();
  }
}

// Test both URLs
await testLogin('http://192.168.255.5/smcc-whatsapp/public/login');
await testLogin('http://servicos2.santamonica.rec.br/smcc-whatsapp/public/login');

console.log(`\n${'='.repeat(60)}\n`);
