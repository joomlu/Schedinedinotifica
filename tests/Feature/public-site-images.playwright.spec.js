import { test, expect, BASE_URL } from '../Support/playwright.js';
for (const width of [1440, 768, 390]) {
  test(`ampliamenti: immagini decodificate e viste a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    const response = await page.goto(`${BASE_URL}/ampliamenti`, { waitUntil: 'load' });
    expect(response.status()).toBe(200);
    const figures = page.locator('main figure'); await expect(figures).toHaveCount(4);
    await figures.locator('img').evaluateAll(images => images.forEach(img => { img.loading = 'eager'; }));
    await expect.poll(() => figures.locator('img').evaluateAll(images => images.every(img => img.complete && img.naturalWidth > 0))).toBe(true);
    for (let i = 0; i < 4; i++) {
      const figure = figures.nth(i); const image = figure.locator('img');
      await image.scrollIntoViewIfNeeded();
      await image.evaluate(async img => { await img.decode(); await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
      await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      await expect(image).toBeInViewport();
      await figure.screenshot({ path: info.outputPath(`ampliamenti-figura-${i + 1}-${width}.png`) });
    }
    await page.evaluate(async () => { scrollTo({ top: 0, behavior: 'instant' }); await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.screenshot({ path: info.outputPath(`ampliamenti-caricate-${width}.png`), fullPage: true });
  });
}
