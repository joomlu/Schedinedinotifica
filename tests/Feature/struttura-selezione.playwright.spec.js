import {test, expect, BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
async function login(page, username) {
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/Nome di accesso o email/i).fill(username);
  await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
  await page.getByRole('button',{name:/Entra/i}).click();
  await expect(page).not.toHaveURL(/\/login$/);
}
for (const [mode,count] of [['una',1],['multi',2],['vuota',0]]) {
  test(`Selezione proprietario ${mode}: rendering, scelta e persistenza`, async ({page}) => {
    await login(page,`selezione-${mode}`);
    const errors=[];
    page.on('response',r=>{if(r.status()>=500)errors.push(r.url());});
    await page.goto(`${BASE_URL}/strutture/seleziona`);
    await expect(page.getByRole('button',{name:'Seleziona',exact:true})).toHaveCount(count);
    if (!count) await expect(page.getByText('Nessuna struttura disponibile.',{exact:true})).toBeVisible();
    else {
      const ids=await (await page.request.get(`${BASE_URL}/struttura-selezione-ids.json`)).json();
      for (const id of ids[mode].slice().reverse()) {
        await page.locator(`table form[action="${BASE_URL}/strutture/${id}/seleziona"] button`).click();
        await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
        await page.getByRole('button',{name:'OK',exact:true}).click();
        await page.reload();
        await expect(page.locator('tr').filter({has:page.locator(`form[action="${BASE_URL}/strutture/${id}/seleziona"]`)})).toContainText('✓');
        for (const path of ['/', '/schedine', '/tassa_di_soggiorno/rapporto?mese=6&anno=2026']) {
          const response=await page.goto(`${BASE_URL}${path}`);
          expect(response.status()).toBeLessThan(500);
        }
        await page.goto(`${BASE_URL}/strutture/seleziona`);
      }
      await expect(page.locator('table')).not.toContainText(mode==='una'?'Hotel selezione multi':'Hotel selezione una');
    }
    expect(errors).toEqual([]);
  });
}
test('Personale limitato: selezione reindirizzata e ricevuta mobile leggibile', async ({page})=>{
  await login(page,'tassa-anteprima');
  await page.goto(`${BASE_URL}/strutture/seleziona`);
  await expect(page).not.toHaveURL(/\/strutture\/seleziona$/);
  const ids=await (await page.request.get(`${BASE_URL}/tassa-anteprima-ids.json`)).json();
  await page.setViewportSize({width:390,height:844});
  await page.goto(`${BASE_URL}/schedine/${ids.prima}/tassa/anteprima`);
  await expect(page.locator('#ricevuta-tassa-card')).toBeVisible();
  const cell=page.locator('.ricevuta-tabella tbody tr').first().locator('td').nth(3);
  expect((await cell.boundingBox()).width).toBeGreaterThanOrEqual(160);
  await page.locator('.ricevuta-tabella-wrap').evaluate(e=>{e.scrollLeft=e.querySelector('td:nth-child(4)').offsetLeft-e.querySelector('table').offsetLeft;});
  await page.screenshot({path:'/private/tmp/ids-selezione-mobile.png',fullPage:true});
  await page.emulateMedia({media:'print'});
  await page.pdf({path:'/private/tmp/ids-selezione-zero.pdf',format:'A4',printBackground:true});
});
test('Percorso proprietario completo e cambio tenant con documenti fiscali',async({page})=>{
  test.setTimeout(90000);
  await login(page,'selezione-multi');
  const ids=await (await page.request.get(`${BASE_URL}/struttura-selezione-ids.json`)).json();
  const errors=[];page.on('response',r=>{if(r.status()>=500)errors.push(r.url());});
  await page.goto(`${BASE_URL}/strutture/seleziona`);
  await page.locator(`table form[action="${BASE_URL}/strutture/${ids.multi[0]}/seleziona"] button`).click();
  await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  await page.getByRole('link',{name:'Dashboard',exact:true}).click();
  await page.goto(`${BASE_URL}/schedine`);
  await expect(page.locator(`a[href="${BASE_URL}/schedine/${ids.positivo}/tassa/anteprima"]`)).toBeVisible();
  await page.goto(`${BASE_URL}/tassa_di_soggiorno?anno_fiscale=2026`);
  for (const [key,total] of [['zero','0'],['positivo','4.5']]) {
    await page.goto(`${BASE_URL}/schedine/${ids[key]}/tassa/anteprima`);
    await expect(page.locator('[data-tassa-totale]')).toHaveAttribute('data-tassa-totale',total);
    await page.evaluate(()=>{window.print=()=>window.__printed=true;});
    await page.getByRole('button',{name:'Stampa ricevuta',exact:true}).click();
    expect(await page.evaluate(()=>window.__printed)).toBe(true);
    await page.pdf({path:`/private/tmp/ids-selezione-${key}.pdf`,format:'A4',printBackground:true});
  }
  await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto?mese=6&anno=2026`);
  await expect(page.locator('[data-tassa-periodo]')).toHaveAttribute('data-tassa-periodo','4.5');
  const download=page.waitForEvent('download');
  await page.getByRole('button',{name:'Consolida e scarica CSV',exact:true}).click();
  await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  expect((await download).suggestedFilename()).toMatch(/csv$/);
  await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto?mese=6&anno=2026`);
  await page.getByText('Export consolidati e versioni',{exact:true}).click();
  await page.getByRole('link',{name:/versione 1/}).click();
  await expect(page.locator('body')).toContainText('Report storico immutabile');
  await page.goto(`${BASE_URL}/strutture/seleziona`);
  await page.locator(`table form[action="${BASE_URL}/strutture/${ids.multi[1]}/seleziona"] button`).click();
  await page.getByRole('button',{name:'Sì, salva',exact:true}).click();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  await page.goto(`${BASE_URL}/schedine`);
  await expect(page.locator(`a[href="${BASE_URL}/schedine/${ids.positivo}/tassa/anteprima"]`)).toHaveCount(0);
  expect((await page.request.get(`${BASE_URL}/schedine/${ids.positivo}/tassa/anteprima`)).status()).toBe(404);
  await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto?mese=6&anno=2026`);
  await expect(page.getByText('Export consolidati e versioni',{exact:true})).not.toBeVisible();
  expect(errors).toEqual([]);
});
