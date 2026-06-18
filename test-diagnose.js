import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage();

try {
  console.log('🔍 Acessando diagnóstico...\n');
  
  const response = await page.goto('http://192.168.255.5/smcc-whatsapp/public/diagnose-complete.php', {
    waitUntil: 'networkidle',
    timeout: 30000
  });

  const content = await page.content();
  
  // Extract relevant info from HTML
  const text = await page.textContent('body');
  console.log(text);

} catch (error) {
  console.error('Erro:', error.message);
} finally {
  await browser.close();
}
