import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage();

try {
  console.log('🔍 Fetching database diagnostics...\n');

  const response = await page.goto('http://192.168.255.5/smcc-whatsapp/public/diagnose/db', {
    waitUntil: 'networkidle',
    timeout: 30000
  });

  console.log('Status:', response.status());
  console.log('\n' + '='.repeat(70));

  const content = await page.textContent('body');
  const jsonData = JSON.parse(content);

  console.log(JSON.stringify(jsonData, null, 2));

  console.log('\n' + '='.repeat(70));
  console.log('\n📋 Summary:');
  console.log('────────────────────────────────────────────────────────────────────');

  if (jsonData.socket?.status === 'FAILED') {
    console.log('❌ Socket: Cannot reach SQL Server on port 1433');
    console.log('   → SQL Server is down, firewall blocks it, or IP/port is wrong');
  } else {
    console.log('✅ Socket: Port 1433 is reachable');
  }

  if (jsonData.php_extensions?.sqlsrv === 'MISSING') {
    console.log('❌ PHP sqlsrv driver: NOT INSTALLED');
    console.log('   → Install: sudo pecl install sqlsrv pdo_sqlsrv');
  } else {
    console.log('✅ PHP sqlsrv driver: Installed');
  }

  if (jsonData.pdo?.status === 'FAILED') {
    console.log('❌ PDO Connection: Failed');
    console.log('   Error:', jsonData.pdo.error);
  } else if (jsonData.pdo?.status === 'OK') {
    console.log('✅ PDO Connection: Successful');
  }

} catch (error) {
  console.error('Error:', error.message);
} finally {
  await browser.close();
}
