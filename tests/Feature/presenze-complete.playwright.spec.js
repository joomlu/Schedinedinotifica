import { test, expect, BASE_URL } from '../Support/playwright.js';
for (const width of [1440,768,390]) {
  test(`Presenze: cinque schede, filtri e stampe a ${width}px`, async ({page}, info) => {
    await page.setViewportSize({width,height:1000});
    await page.goto(`${BASE_URL}/login`);
    await page.locator('[name="login"]').fill('presenze-ui');
    await page.locator('[name="password"]').fill('Fixture-presenze-123!');
    await page.getByRole('button',{name:/Accedi|Entra/i}).click();
    await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
    const risposta=page.waitForResponse(r=>r.request().method()==='POST' && new URL(r.url()).pathname==='/login');
    await page.getByRole('button',{name:'OK',exact:true}).click();
    await risposta; await page.waitForLoadState('load');
    await page.goto(`${BASE_URL}/statistica/presenze?anno=2026&mese_da=1&mese_a=1&dal=2026-01-01&al=2026-01-31&giorno_situazione=2026-01-01&occupazione_anno=2026&occupazione_mese=1`);
    await expect(page.locator('#presenze-riepilogo')).toContainText('presenze 7');
    await expect(page.locator('#presenze-riepilogo')).toContainText('saldo iniziale 3');
    await expect(page.locator('#presenze-riepilogo').getByRole('link',{name:'Stampa dettaglio mese'})).toHaveAttribute('href',/dal=2026-01-01.*al=2026-01-31/);
    for(const [tab,label] of [['dettaglio','Dettaglio periodo'],['oggi','Situazione giornaliera'],['movimenti','Arrivi / Partenze'],['occupazione','Occupazione'],['riepilogo','Riepilogo mensile']]) {
      await page.getByRole('tab',{name:label,exact:true}).click();
      await expect(page.locator(`#presenze-${tab}`)).toHaveClass(/show active|active show/);
      if(tab==='occupazione') { await expect(page.locator('#presenze-occupazione')).toContainText('Occupazione posti letto del periodo: 1.1%'); await expect(page.locator('#presenze-occupazione .presenze-month-year')).not.toBeVisible(); }
      await expect(page.locator(`#presenze-${tab}`)).toHaveCSS('opacity','1');
      await page.locator(`#presenze-${tab}`).scrollIntoViewIfNeeded();
      await expect.poll(()=>page.evaluate(()=>document.documentElement.scrollWidth <= innerWidth)).toBe(true);
      await page.screenshot({path:info.outputPath(`presenze-${tab}-${width}.png`),fullPage:true});
    }
    await page.getByRole('tab',{name:'Dettaglio periodo',exact:true}).click();
    await page.locator('#presenze-dettaglio button[type="submit"]').click();
    await expect(page).toHaveURL(/dal=2026-01-01/);
    await expect(page).toHaveURL(/giorno_situazione=2026-01-01/);
    await expect(page.locator('#presenze-dettaglio')).toContainText('7');
    await page.goto(`${BASE_URL}/statistica/presenze/stampa-riepilogo?anno=2026&mese_da=1&mese_a=1`);
    await expect(page.locator('body')).toContainText('presenze 7');
    await page.goto(`${BASE_URL}/statistica/presenze/stampa-dettaglio?dal=2026-01-01&al=2026-01-31`);
    await expect(page.locator('body')).toContainText('Persona sintetica');
    await page.goto(`${BASE_URL}/statistica/presenze?tab=occupazione&occupazione_anno=2024&occupazione_mese=2&dal=2026-06-01&al=2026-09-30&mese_da=6&mese_a=9`);
    await page.getByRole('tab',{name:'Dettaglio periodo',exact:true}).click();
    await expect(page.locator('#presenze-dettaglio')).toContainText('Periodo 01/02/2024 - 29/02/2024');
    await page.getByRole('tab',{name:'Riepilogo mensile',exact:true}).click();
    await expect(page.locator('#presenze-riepilogo')).toContainText('Nessun soggiorno o movimento');
    await page.goto(`${BASE_URL}/statistica/presenze?anno=2024&mese_da=1&mese_a=12`);
    await expect(page.locator('#presenze-riepilogo')).toContainText('Nessun soggiorno o movimento');
    await expect(page.locator('#presenze-riepilogo table')).toHaveCount(0);
  });
}
