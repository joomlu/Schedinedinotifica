import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const width of [1440,768,390]) test(`Lista / Nuovo: selezione pastello conservata a ${width}px`,async({page})=>{
 await page.setViewportSize({width,height:900});
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle'}), page.locator('#accesso').evaluate(form=>form.submit())]);
 await expect(page).not.toHaveURL(/\/login$/);
 await page.goto(`${BASE_URL}/strutture/seleziona`);
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle'}), page.locator('tbody form').first().evaluate(form=>form.submit())]);
 for(const path of ['/clienti/nuovo','/clienti?q=VERIFICA-PASTELLO-ZERO']){
  await page.goto(BASE_URL+path);await page.waitForLoadState('networkidle');
  const nav=page.getByRole('navigation',{name:'Lista / Nuovo clienti'});
  const active=nav.locator('[aria-current="page"]');
  expect(await active.evaluate(e=>getComputedStyle(e).backgroundImage)).toBe('linear-gradient(90deg, rgba(140, 29, 44, 0.12), rgba(140, 29, 44, 0.04))');
  expect(await active.evaluate(e=>getComputedStyle(e).color)).toBe('rgb(116, 24, 38)');
  for(const a of await nav.getByRole('link').all()){
   const b=await a.boundingBox();expect(b.height).toBeGreaterThanOrEqual(44);expect(b.x).toBeGreaterThanOrEqual(0);expect(b.x+b.width).toBeLessThanOrEqual(width);
  }
 }
});
