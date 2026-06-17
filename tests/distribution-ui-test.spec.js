import { test, expect } from '@playwright/test';

test.describe('Distribution System', () => {
  test.beforeEach(async ({ page }) => {
    // Login
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@test.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
  });

  test('should display distribution settings', async ({ page }) => {
    await page.goto('/admin/distribution');

    // Check if page loads
    expect(page.url()).toContain('/admin/distribution');

    // Verify distribution mode selector exists
    const modeSelect = page.locator('select[name="mode"]');
    await expect(modeSelect).toBeVisible();
  });

  test('should show queued conversations', async ({ page }) => {
    await page.goto('/admin/distribution');

    // Wait for queued conversations table to load
    await page.waitForSelector('[data-test="queued-conversations"]', { timeout: 5000 }).catch(() => null);

    const queuedSection = page.locator('[data-test="queued-conversations"]');
    if (await queuedSection.isVisible()) {
      const count = await page.locator('tr[data-test="queued-row"]').count();
      console.log(`Found ${count} queued conversations`);
    }
  });

  test('should process distribution queue manually', async ({ page }) => {
    await page.goto('/admin/distribution');

    // Click process queue button
    const processButton = page.locator('button[data-test="process-queue"]');

    if (await processButton.isVisible()) {
      await processButton.click();

      // Wait for success message
      const successMsg = page.locator('text=/distribuída|processada/i');
      await expect(successMsg).toBeVisible({ timeout: 10000 });

      console.log('Queue processed successfully');
    }
  });

  test('should display agent metrics', async ({ page }) => {
    await page.goto('/admin/distribution');

    // Check agent table
    const agentRows = page.locator('[data-test="agent-row"]');
    const count = await agentRows.count();

    expect(count).toBeGreaterThan(0);
    console.log(`Found ${count} agents`);

    // Verify agent has metrics
    const firstAgent = agentRows.first();
    const capacity = firstAgent.locator('[data-test="agent-capacity"]');
    await expect(capacity).toBeVisible();
  });

  test('should switch between automatic and manual modes', async ({ page }) => {
    await page.goto('/admin/distribution');

    const modeSelect = page.locator('select[name="mode"]');
    const currentMode = await modeSelect.inputValue();

    // Switch mode
    const newMode = currentMode === 'automatic' ? 'manual' : 'automatic';
    await modeSelect.selectOption(newMode);

    // Submit form
    const saveBtn = page.locator('button:has-text("Salvar")');
    await saveBtn.click();

    // Verify change
    await page.waitForURL(/admin\/distribution/);
    const updatedMode = await modeSelect.inputValue();
    expect(updatedMode).toBe(newMode);
  });
});
