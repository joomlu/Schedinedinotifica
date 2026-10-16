import {test as isolatedTest, expect, BASE_URL} from '../Support/playwright.js';
import {withCustomerObservation} from '../Support/customer-observation.js';
const test=withCustomerObservation(isolatedTest);
test.use({headless:true}); test.setTimeout(180000);
async function login(page) {
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/clienti/nuovo`);
 await page.waitForLoadState('networkidle');
 if(await page.getByRole('button',{name:/Struttura: Hotel Browser(?! B)/}).count()===0){
 await page.getByRole('button',{name:/Struttura:/}).click();
 await page.getByRole('button',{name:'Hotel Browser',exact:true}).click();
 const confirm = page.getByRole('button',{name:'Sì, salva',exact:true});
 if(await confirm.isVisible()) await accept(page);
 await page.waitForLoadState('networkidle');

 }
 await expect(page.getByRole('button',{name:/Struttura: Hotel Browser(?! B)/})).toBeVisible();
}
async function accept(page, label='Sì, salva') {
 await page.getByRole('button',{name:label,exact:true}).click();
 await page.getByRole('button',{name:'OK',exact:true}).click();
 await page.waitForLoadState('networkidle');
 const ok=page.getByRole('button',{name:'OK',exact:true});
 if(await ok.isVisible()) await ok.click();
}
for(const mode of ['smooth-prima','auto-prima','smooth-seconda','auto-seconda']) test(`Causalità scorrimento e clic mobile: ${mode}`,async({page})=>{
 const smooth=mode.startsWith('smooth');
 await page.setViewportSize({width:390,height:900});
 await login(page);
 const observations=[];
 for(let attempt=0;attempt<5;attempt++){
  await page.goto(`${BASE_URL}/clienti/nuovo`);await page.waitForLoadState('networkidle');
  await page.addStyleTag({content:`html { scroll-behavior: ${smooth?'smooth':'auto'} !important; }`});
  expect(await page.evaluate(()=>getComputedStyle(document.documentElement).scrollBehavior)).toBe(smooth?'smooth':'auto');
  // La navigazione ripristina la bozza: torna a Residenza attraverso la UI.
  await page.locator('#steparrow-gen-info-tab').click();
  await expect(page.locator('#steparrow-gen-info')).toBeVisible();
  await page.locator('[name="type_cliente"]').selectOption('Richiesta');
  await page.locator('[name="name"]').fill('Persona');
  await page.locator('[name="surname"]').fill('Sintetica');
  await page.locator('#steparrow-description-info-tab').click({delay:80});
  const observed=await page.evaluate(()=>{
   const events=window.__customerEvents;
   const down=events.findLast(e=>e.type==='pointerdown');
   const up=events.findLast(e=>e.type==='pointerup');
   return {active:document.querySelector('#steparrow-description-info').classList.contains('active'),down:down.target,up:up.target,scrollDown:down.state.scroll[1],scrollUp:up.state.scroll[1],showAfterDown:events.filter(e=>e.type==='show.bs.tab'&&e.state.time>down.state.time).length};
  });
  expect(observed.down).toMatch(/^<button[^>]*id="steparrow-description-info-tab"/);
  if(!observed.active){
   // Il controllo negativo deve provare il meccanismo, non accettare un errore qualunque.
   expect(observed.up).not.toMatch(/^<button[^>]*id="steparrow-description-info-tab"/);
   expect(Math.abs(observed.scrollUp-observed.scrollDown)).toBeGreaterThan(20);
   expect(observed.showAfterDown).toBe(0);
  }
  if(!smooth)await expect(page.locator('#steparrow-description-info')).toBeVisible();
  observations.push(observed);
 }
 // Tutte le cinque prove sono eseguite: nessuna ripetizione automatica per ottenere verde.
 const lost=observations.filter(o=>!o.active).length;
 if(smooth)expect(lost,'Il controcaso fluido deve riprodurre almeno un clic perso').toBeGreaterThan(0);
 else expect(lost,'Ogni clic con auto deve riuscire').toBe(0);
 console.log('CONTROPROVA_SCROLL='+JSON.stringify({mode,tentativi:observations.length,clic_persi:lost,osservazioni:observations.map(({active,scrollDown,scrollUp,showAfterDown})=>({active,scrollDown,scrollUp,showAfterDown}))}));
});
