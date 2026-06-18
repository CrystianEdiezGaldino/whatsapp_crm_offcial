import { chromium } from 'playwright';

const hostCombinations = [
  { host: '192.168.1.6', name: 'Original (192.168.1.6)' },
  { host: '127.0.0.1', name: 'Localhost (127.0.0.1)' },
  { host: 'localhost', name: 'Localhost hostname' },
  { host: '192.168.255.5', name: 'Same server (192.168.255.5)' },
  { host: 'servicos2.santamonica.rec.br', name: 'Hostname (DNS)' },
];

console.log('🔍 Testing SQL Server connectivity from different possible hosts...\n');
console.log('='.repeat(70));

const browser = await chromium.launch();
const page = await browser.newPage();

for (const combo of hostCombinations) {
  const url = `http://192.168.255.5/smcc-whatsapp/public/test-connect?host=${encodeURIComponent(combo.host)}&port=1433&user=Php&pass=%24%8925%3a7&db=Whatsapp`;

  try {
    console.log(`\n📌 Testing: ${combo.name}`);

    const response = await page.goto(url, {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    if (response.status() === 200) {
      const content = await page.textContent('body');

      if (content.includes('"status":"OK"')) {
        console.log(`   ✅ CONNECTION SUCCESSFUL! SQL Server is at: ${combo.host}`);
      } else if (content.includes('FAILED')) {
        console.log(`   ❌ Connection failed`);
      }
    }
  } catch (error) {
    console.log(`   ⏱️  Timeout or error`);
  }
}

await browser.close();

console.log('\n' + '='.repeat(70));
console.log('\n💡 If one succeeds, update .env: DB_HOST=<that_host>\n');
