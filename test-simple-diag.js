import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage();

try {
  console.log('🔍 Testando diagnóstico simples...\n');
  
  const response = await page.goto('http://192.168.255.5/smcc-whatsapp/public/diag-simple.php', {
    waitUntil: 'load',
    timeout: 15000
  });

  const content = await page.textContent('body');
  const json = JSON.parse(content);
  
  console.log(JSON.stringify(json, null, 2));
  
} catch (error) {
  console.error('Erro:', error.message);
} finally {
  await browser.close();
}
