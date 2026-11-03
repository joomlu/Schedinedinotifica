import {test, expect, BASE_URL} from '../Support/playwright.js';

async function confirmDelete(page, trigger, path) {
    await trigger.click();
    await page.getByRole('button', {name: 'Sì, elimina', exact: true}).click();
    const response = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === path);
    await page.getByRole('button', {name: 'OK', exact: true}).click();
    expect((await response).status()).toBe(302);
    await page.waitForLoadState('load');
    await expect(page.getByRole('heading', {name: 'Operazione completata', exact: true})).toBeVisible();
    await page.getByRole('button', {name: 'OK', exact: true}).click();
}

for (const width of [1440, 768, 390]) {
    test(`Cestino: dettaglio, selezione e svuotamento; calendario con avvisi a ${width}px`, async ({page}) => {
        await page.setViewportSize({width, height: 1000});
        const errors = [];
        page.on('pageerror', e => errors.push(e.message));
        await page.goto(`${BASE_URL}/login`);
        await page.locator('[name="login"]').fill(`cestino-ui-${width}`);
        await page.locator('[name="password"]').fill('Fixture-cestino-123!');
        await page.getByRole('button', {name: /Accedi|Entra/i}).click();
        await page.getByRole('button', {name: 'Sì, salva', exact: true}).click();
        const login = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/login');
        await page.getByRole('button', {name: 'OK', exact: true}).click();
        await login;
        await page.goto(`${BASE_URL}/cestino?per_page=25`);
        await expect(page.locator('.cestino-selezione')).toHaveCount(13);
        const first = page.locator('#cestinoTable .data-row').first();
        await first.getByRole('button', {name: /Visualizza dettaglio/}).click();
        const modal = page.locator('.modal.show');
        await expect(modal).toContainText('Dati dell’elemento eliminato');
        await expect(modal.locator('pre')).toHaveCount(0);
        await expect(modal).toContainText('Nome');
        await modal.getByRole('button', {name: 'Chiudi', exact: true}).last().click();
        await page.locator('.cestino-selezione').nth(0).check();
        await page.locator('.cestino-selezione').nth(1).check();
        await expect(page.getByRole('button', {name: 'Elimina selezionati (2)', exact: true})).toBeEnabled();
        await confirmDelete(page, page.getByRole('button', {name: 'Elimina selezionati (2)', exact: true}), '/cestino/selezionati');
        await expect(page.locator('.cestino-selezione')).toHaveCount(11);
        await confirmDelete(page, page.getByRole('button', {name: 'Svuota cestino (11)', exact: true}), '/cestino/svuota');
        await expect(page.locator('.cestino-selezione')).toHaveCount(0);
        await expect(page.getByRole('button', {name: 'Svuota cestino (0)', exact: true})).toBeDisabled();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        await page.goto(`${BASE_URL}/calendario?mese=2026-10&giorno=2026-10-30&vista=day`);
        for (const color of ['danger', 'warning', 'success']) await expect(page.locator(`.calendar-entry[data-calendar-alert="${color}"]`).first()).toBeVisible();
        await expect(page.getByText('Scaduta', {exact:true})).toBeVisible();
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        expect(errors).toEqual([]);
    });
}
