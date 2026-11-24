import {test,expect,BASE_URL} from '../Support/playwright.js';
for(const width of [1440,768,390])test(`Rapporto tassa, filtro Web Check-in e presenze a ${width}px`,async({page})=>{
 test.setTimeout(90000);await page.setViewportSize({width,height:1000});const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto(BASE_URL+'/login');await page.locator('[name=login]').fill('tanggo.igea.demo');await page.locator('[name=password]').fill('Password-Demo-Fixture-123!');await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.locator('#accesso').evaluate(f=>f.submit())]);
 const writes=[];page.on('request',r=>{if(!['GET','HEAD','OPTIONS'].includes(r.method()))writes.push(new URL(r.url()).pathname);});
 await page.goto(BASE_URL+'/tassa_di_soggiorno/rapporto?mese=6&anno=2026');
 const table=page.locator('.tassa-rapporto-table');await expect(table.locator('thead th').nth(9)).toHaveText('Tariffa €');await expect(table.locator('thead th').nth(10)).toHaveText('Tassa €');
 const cells=table.locator('tbody tr').first().locator('td');await expect(cells.nth(9)).toHaveText('1,50');await expect(cells.nth(10)).toHaveText('4,50');
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
 await page.goto(BASE_URL+'/web-checkin?q=PANORAMICA');const form=page.locator('form').filter({has:page.locator('#web-state')});const combo=form.getByRole('combobox');await expect(combo).toBeVisible();
 await combo.click();await page.locator('.select2-container--open .select2-search__field').fill('In compilazione');await page.getByRole('option',{name:'In compilazione',exact:true}).click();
 await expect(page.locator('#web-state')).toHaveValue('in_compilazione');await form.getByRole('button',{name:'Cerca',exact:true}).click();await expect(page).toHaveURL(/stato=in_compilazione/);await expect(page.locator('#web-search')).toHaveValue('PANORAMICA');
 await page.locator('#web-state + .select2 .select2-selection__clear').click();await expect(page.locator('#web-state')).toHaveValue('');await page.keyboard.press('Escape');await form.getByRole('button',{name:'Cerca',exact:true}).click();await expect(page.locator('#web-state')).toHaveValue('');
 for(const value of ['da_inviare','in_compilazione','compilato','convertito']){await page.goto(BASE_URL+'/web-checkin?stato='+value);await expect(page.locator('#web-state')).toHaveValue(value);await expect(page.locator('#web-state + .select2')).toBeVisible();}
 expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
 await page.goto(BASE_URL+'/statistica/presenze?anno=2026&mese_da=6&mese_a=6&dal=2026-06-01&al=2026-06-30&giorno_situazione=2026-06-01&occupazione_anno=2026&occupazione_mese=6');
 await expect(page.locator('#presenze-riepilogo')).toContainText('presenze 3');
 for(const [id,label] of [['dettaglio','Dettaglio periodo'],['oggi','Situazione giornaliera'],['movimenti','Arrivi / Partenze'],['occupazione','Occupazione'],['riepilogo','Riepilogo mensile']]){await page.getByRole('tab',{name:label,exact:true}).click();await expect(page.locator('#presenze-'+id)).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();}
 expect(errors).toEqual([]);expect(writes).toEqual([]);console.log(JSON.stringify({larghezza:width,colonne_tassa:'tariffa poi totale',filtro_interattivo:true,stati:4,presenze_giugno:3,schede_presenze:5,scritture:0,errori_js:0}));
});
