import { test, expect, BASE_URL } from '../Support/playwright.js';

for (const width of [1440, 768, 390]) {
  test(`Riepilogo Tassa riconciliato e stampa leggibile a ${width}px`, async ({ page }, testInfo) => {
    test.setTimeout(90000);
    await page.setViewportSize({ width, height: 1000 });
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/Nome di accesso o email/i).fill('tassa-audit');
    await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
    await page.getByRole('button', { name: /Entra/i }).click();
    await expect(page).not.toHaveURL(/\/login$/);
    for (const url of ['/tassa_di_soggiorno?anno_fiscale=2026', '/tassa_di_soggiorno/rapporto?mese=6&anno=2026', '/tassa_di_soggiorno/rapporto/controllo?mese=6&anno=2026']) {
      await page.goto(`${BASE_URL}${url}`);
      await expect(page.locator('body')).toBeVisible();
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true);
      await page.screenshot({ path: testInfo.outputPath(`pagina-${url.includes('controllo') ? 'controllo' : url.includes('rapporto') ? 'rapporto' : 'configurazione'}-${width}.png`), fullPage: true });
    }
    const ids = await (await page.request.get(`${BASE_URL}/tassa-audit-ids.json`)).json();
    await page.goto(`${BASE_URL}/schedine/${ids.schedina}/tassa/anteprima`);
    await expect(page.locator('[data-tassa-totale]')).toHaveAttribute('data-tassa-totale', '3');
    await page.screenshot({ path: testInfo.outputPath(`ricevuta-${width}.png`), fullPage: true });
    const csv = await (await page.request.get(`${BASE_URL}/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026`)).text();
    const rows = csv.split('\n').slice(1).map(line => line.split(';'));
    expect(rows.reduce((n, row) => n + Number(row[6]), 0)).toBe(20);
    expect(rows.filter(row => row[0] === '400').reduce((n, row) => n + Number(row[6]), 0)).toBe(10);
    expect(rows.filter(row => row[0] === '777').reduce((n, row) => n + Number(row[6]), 0)).toBe(8);
    expect(rows.reduce((n, row) => n + Number(row[6]) * Number(row[7]), 0)).toBe(3);
    await page.setViewportSize({ width: 794, height: 1123 });
    await page.addInitScript(() => { window.print = () => {}; });
    await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto/controllo/stampa?mese=6&anno=2026`);
    await page.emulateMedia({ media: 'print' });
    await page.locator('.page').screenshot({ path: testInfo.outputPath(`stampa-${width}.png`) });
    await page.pdf({ path: testInfo.outputPath(`stampa-${width}.pdf`), format: 'A4', printBackground: true, preferCSSPageSize: true });
    expect(await page.locator('.page').evaluate(el => el.scrollWidth <= el.clientWidth + 1)).toBe(true);
    await expect(page.locator('body')).toContainText('Inclusione per data di arrivo');
    await expect(page.locator('body')).not.toContainText('Totale mensile da versare al Comune');
    const esenzioni = page.locator('table').filter({ hasText: 'Motivo esenzione' });
    await expect(esenzioni.locator('tbody tr')).toHaveCount(1);
    await expect(esenzioni.locator('tbody td').nth(0)).toHaveText('400');
    await expect(esenzioni.locator('tbody td').nth(2)).toHaveText('10');
    await expect(esenzioni.locator('tbody td').nth(3)).toHaveText('2');
  });
}
