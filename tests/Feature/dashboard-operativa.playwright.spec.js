import {test, expect, BASE_URL} from '../Support/playwright.js';

for (const width of [1440, 768, 390]) {
    test(`Dashboard operativa: priorità e accessi diretti a ${width}px`, async ({page}) => {
        await page.setViewportSize({width, height:1000});
        await page.goto(`${BASE_URL}/login`);
        await page.locator('[name="login"]').fill('calendario-ui');
        await page.locator('[name="password"]').fill('Fixture-calendario-123!');
        await page.getByRole('button', {name:/Accedi|Entra/i}).click();
        await page.getByRole('button', {name:'Sì, salva', exact:true}).click();
        const login = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/login');
        await page.getByRole('button', {name:'OK', exact:true}).click();
        await login;
        await page.goto(`${BASE_URL}/dashboard`);
        const dashboard = page.locator('[data-dashboard-operativo]');
        await expect(dashboard).toBeVisible();
        await expect(dashboard.locator('[data-dashboard-area]')).toHaveCount(5);
        expect(await dashboard.locator('[data-dashboard-area]').evaluateAll(items => items.map(item => item.dataset.dashboardArea)))
            .toEqual(['clienti','schedine','checkin','calendario','notifiche']);
        await expect(dashboard.getByRole('link', {name:'Nuovo cliente',exact:true})).toHaveAttribute('href',`${BASE_URL}/clienti/nuovo`);
        await expect(dashboard.getByRole('link', {name:'Nuova schedina',exact:true})).toHaveAttribute('href',`${BASE_URL}/schedine/nuova`);
        await expect(dashboard.getByRole('link', {name:'Nuovo Web Check-in',exact:true})).toHaveAttribute('href',`${BASE_URL}/web-checkin/nuovo`);
        await expect(dashboard.getByRole('link', {name:'Cerca cliente',exact:true})).toHaveAttribute('href',`${BASE_URL}/clienti`);
        await expect(dashboard.getByRole('link', {name:/Calendario/})).toHaveAttribute('href',`${BASE_URL}/calendario`);
        await expect(dashboard.getByRole('link', {name:/Notifiche/})).toHaveAttribute('href',`${BASE_URL}/notifiche`);
        await expect(dashboard.getByRole('link', {name:/Dati struttura|Configurazioni|Cestino|Statistica|Aiuto/})).toHaveCount(0);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
        for (const [label, path] of [['Nuovo cliente','/clienti/nuovo'],['Nuova schedina','/schedine/nuova'],['Nuovo Web Check-in','/web-checkin/nuovo']]) {
            await page.locator('[data-dashboard-operativo]').getByRole('link', {name:label,exact:true}).click();
            await expect(page).toHaveURL(`${BASE_URL}${path}`);
            await expect(page.locator('form:visible').first()).toBeVisible();
            await page.goto(`${BASE_URL}/dashboard`);
        }
    });
}
