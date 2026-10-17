import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
for(const width of [1440,768,390]) test(`Nuovo / Lista: selezione pastello come sidebar a ${width}px`,async({page})=>{
 await page.setViewportSize({width,height:900});
 await page.goto(`${BASE_URL}/login`);
 await page.locator('[name="login"]').fill('fixture-super_admin');
 await page.locator('[name="password"]').fill('Password-fixture-123!');
 await page.getByRole('button',{name:'Entra',exact:true}).click();
 await expect(page).not.toHaveURL(/\/login$/);
 for(const path of ['/clienti/nuovo','/clienti?q=VERIFICA-PASTELLO-ZERO']){
  await page.goto(BASE_URL+path);await page.waitForLoadState('networkidle');
  const nav=page.getByRole('navigation',{name:'Nuovo / Lista clienti'});
  const active=nav.locator('[aria-current="page"]');
  const sidebar=page.locator('#sidebarCustomers a.active').first();
  expect(await active.evaluate(e=>getComputedStyle(e).backgroundImage)).toBe(await sidebar.evaluate(e=>getComputedStyle(e).backgroundImage));
  expect(await active.evaluate(e=>getComputedStyle(e).color)).toBe(await sidebar.evaluate(e=>getComputedStyle(e).color));
  for(const a of await nav.getByRole('link').all()){
   const b=await a.boundingBox();expect(b.height).toBeGreaterThanOrEqual(44);expect(b.x).toBeGreaterThanOrEqual(0);expect(b.x+b.width).toBeLessThanOrEqual(width);
  }
 }
});
