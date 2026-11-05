import {test, expect, BASE_URL} from '../Support/playwright.js';
test.use({headless: true});

async function conferma(page) {
    const yes = page.getByRole('button', {name: /^Sì,/});
    if (await yes.isVisible()) {
        await yes.click();
        await page.getByRole('button', {name: 'OK', exact: true}).click();
    }
    await page.waitForLoadState('networkidle');
    const esito = page.getByRole('dialog', {name: 'Operazione completata', exact: true});
    if (await esito.isVisible()) await esito.getByRole('button', {name: 'OK', exact: true}).click();
}

async function login(page) {
    await page.goto(`${BASE_URL}/login`);
    await page.locator('[name="login"]').fill('fixture-super_admin');
    await page.locator('[name="password"]').fill('Password-fixture-123!');
    await page.getByRole('button', {name: 'Entra', exact: true}).click();
    const fixture = await (await page.request.get(BASE_URL+'/web-html-fixture.json')).json();
    await page.goto(BASE_URL+'/strutture/seleziona');
    const selection=page.locator('.main-content form[action$="/strutture/'+fixture.A+'/seleziona"]');
    const response=await page.request.post(BASE_URL+'/strutture/'+fixture.A+'/seleziona',{form:{_token:await selection.locator('[name=_token]').inputValue()},maxRedirects:0});
    expect(response.status()).toBe(302);
    await page.goto(BASE_URL+'/web-checkin');
}
for (const width of [1440, 768, 390]) { const saved={};test.describe.serial(`Operatività Web ${width}px`,()=>{test(`Richiesta e comunicazione ${width}px`, async ({page}) => {
    await page.setViewportSize({width, height: 950});
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    page.on('dialog', d => d.accept());
    await login(page);    await page.goto(`${BASE_URL}/web-checkin/nuovo`);
    await page.locator('[name="numero_prenotazione"]').fill(`WEB-OPERATIVO-${width}`);
    await page.locator('[name="nome_referente"]').fill('Referente sintetico');
    await page.locator('[name="email"]').fill('sintetico@example.invalid');
    for (const [name, date] of [['arrivo', '2026-11-10'], ['partenza', '2026-11-13']]) {
        await page.locator(`[name="${name}"]`).evaluate((el, value) => el._flatpickr.setDate(value, true), date);
    }
    await page.locator('[name="numero_adulti"]').fill('1');
    await page.locator('[name="note"]').fill('NOTA-INTERNA-SINTETICA');
    await page.getByRole('button', {name: 'Crea richiesta', exact: true}).click(); await conferma(page);
    await expect(page).toHaveURL(/\/web-checkin$/);
    const row = page.locator('.web-elenco tr').filter({hasText: `WEB-OPERATIVO-${width}`});
    const manage = await row.getByRole('link', {name: 'Modifica', exact: true}).getAttribute('href'); const id = manage.match(/web-checkin\/(\d+)/)[1];
    await page.goto(`${BASE_URL}/web-checkin/${id}/comunicazioni`);
    await page.locator('#invito-canale').selectOption('testo');
    const countBefore = await page.locator('#invito-modello option').count(); expect(countBefore).toBeGreaterThanOrEqual(3);
    await page.locator('#invito-modello').selectOption({label: 'Invito Web Check-in — Famiglia / referente familiare'});
    await expect(page.locator('#invito-corpo')).toHaveValue(/referente familiare/);
    const original = await page.locator('#invito-corpo').inputValue();
    await page.locator('#invito-oggetto').fill(`Invito personalizzato ${width} {{referente}}`);
    await page.locator('#invito-corpo').fill(original + '\nMessaggio personalizzato sintetico.');
    await page.getByRole('button', {name: 'Visualizza testo', exact: true}).click();
    await expect(page.locator('#testo-invito')).toBeVisible();
    await expect(page.locator('#testo-invito')).toHaveValue(/Referente sintetico/);
    await expect(page.locator('#testo-invito')).toHaveValue(/12\/11\/2026 00:00:00/);
    await expect(page.locator('body')).not.toContainText('NOTA-INTERNA-SINTETICA');
    const invitation = await page.locator('#testo-invito').inputValue();
    const publicUrl = invitation.match(/https?:\/\/[^\s]+\/w\/[A-Za-z0-9]+/)[0];
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
    await page.goto(`${BASE_URL}/web-checkin/${id}/comunicazioni`);
    await page.locator('#invito-modello').selectOption({label: 'Invito Web Check-in — Famiglia / referente familiare'});
    await expect(page.locator('#invito-corpo')).toHaveValue(original);
    await page.getByText('Salva o aggiorna un modello',{exact:true}).click();await page.locator('#invito-nome').fill(`Copia sintetica ${width}`);
    await page.getByRole('button', {name: 'Duplica modello', exact: true}).click(); await conferma(page);
    await expect(page.locator('#invito-modello option')).toHaveCount(countBefore + 1);
    Object.assign(saved,{manage,id,publicUrl});expect(errors).toEqual([]);
});
test(`Salvataggio parziale e ripresa ${width}px`,async({page})=>{
    await page.setViewportSize({width,height:950});const errors=[];page.on('pageerror',e=>errors.push(e.message));const {publicUrl}=saved;
    await page.goto(publicUrl);
    const open = page.getByRole('link', {name: /Apri il tuo Web Check-in/});
    if (await open.isVisible()) await open.click();
    await expect(page.locator('#schedina-form')).toBeVisible();
    await expect(page.locator('body')).not.toContainText('NOTA-INTERNA-SINTETICA');
    await page.locator('#schedina-form [name="name"]').fill(`Ospite parziale ${width}`);
    await page.locator('#schedina-form [name="surname"]').fill('Sintetico');
    await page.locator('button[name="save_mode"][value="web"]:visible').first().click();
    await expect(page.locator('#schedina-form')).toHaveCount(0);
    await expect(page.locator('body')).toContainText('Dati parziali salvati');
    await expect(page.locator('body')).toContainText('Dati salvati anche se incompleti');
    await page.goto(publicUrl); if (await open.isVisible()) await open.click();
    await expect(page.locator('#schedina-form [name="name"]')).toHaveValue(`Ospite parziale ${width}`);
    const layoutBefore = await page.evaluate(() => ({stato:document.readyState,larghezza:document.documentElement.scrollWidth}));
    await page.waitForLoadState('load');
    await page.evaluate(async () => { await document.fonts.ready; });
    if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)) {
        console.log('DIAGNOSI_OVERFLOW', JSON.stringify(await page.evaluate(() => [...document.querySelectorAll('body *')].map(el => { const r=el.getBoundingClientRect(); return {tag:el.tagName,id:el.id,css:el.className,left:r.left,right:r.right,width:r.width}; }).filter(r=>r.width>0 && r.right>innerWidth+1).slice(0,40))));
    }
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
    console.log('LAYOUT_PUBBLICO', JSON.stringify({width,prima:layoutBefore,dopo:await page.evaluate(() => ({stato:document.readyState,larghezza:document.documentElement.scrollWidth}))}));
    expect(errors).toEqual([]);
});
test(`Revisione separata ${width}px`,async({page})=>{
    await page.setViewportSize({width,height:950});const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());await login(page);const {manage,id,publicUrl}=saved;
    await page.goto(manage.replace('/modifica','/attivita/ospiti'));
    await page.getByRole('link', {name: 'Correggi dati ospiti', exact: true}).click();
    const sheetUrl = page.url();
    await page.locator('button[name="save_mode"][value="review_web"]:visible').first().click(); await conferma(page);
    await expect(page).toHaveURL(new RegExp('/web-checkin/'+id+'/attivita/ospiti$'));
    await expect(page.locator('body')).toContainText('Da effettuare');
    await page.goto(sheetUrl);
    await expect(page.locator('[name="name"]').first()).toHaveValue(`Ospite parziale ${width}`);
    await page.goto(manage); await expect(page.locator('[name="note"]')).toHaveValue('NOTA-INTERNA-SINTETICA');
    await page.goto(publicUrl);await page.getByRole('link',{name:'Apri il tuo Web Check-in',exact:true}).click(); await expect(page.locator('#schedina-form [name=\"name\"]')).toHaveValue(`Ospite parziale ${width}`);
    expect(errors).toEqual([]);
    console.log(JSON.stringify({width, richiesta: true, modello_originale_preservato: true, duplicazione: true, anteprima: true, parziale_ripreso: true, stessa_scheda: true, note_private: true}));
});

});}
