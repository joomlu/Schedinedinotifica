import {test,expect,BASE_URL} from '../Support/playwright.js';
for(const width of [1440,768,390])test(`Tassa informativa e tastiera sezioni ${width}px`,async({page},info)=>{
 await page.setViewportSize({width,height:1000});
 const fixture=await(await page.request.get(BASE_URL+'/web-html-fixture.json')).json();
 expect(fixture.tassa).toMatch(/\/checkin\/[A-Za-z0-9]+$/);const response=await page.goto(fixture.tassa);expect(response.status()).toBe(200);await expect(page).toHaveURL(fixture.tassa);
 const tax=page.locator('[data-web-section="tassa"]');await expect(tax).toBeVisible();
 await page.locator('[data-web-section="base"]').focus();await page.keyboard.press('End');
 await expect(tax).toBeFocused();await expect(tax).toHaveAttribute('aria-selected','true');
 await expect(page.locator('#schedina-step-tassa-pane')).toBeVisible();
 await expect(page.locator('#schedina-form > .tab-content > .tab-pane:visible')).toHaveCount(1);
 await expect(page.getByText('Anteprima informativa della tassa. L’importo sarà verificato dalla reception; questa anteprima non è una ricevuta.',{exact:true})).toBeVisible();
 await expect(page.locator('.web-azioni button[value=web]')).toBeVisible();await expect(page.locator('#web-conferma')).toBeVisible();
 await page.evaluate(async()=>{await document.fonts.ready;});expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBeTruthy();
 await page.screenshot({path:info.outputPath('tassa-'+width+'.png'),fullPage:true});
 await page.keyboard.press('Home');await expect(page.locator('[data-web-section="base"]')).toBeFocused();await expect(page.locator('#schedina-step-base-pane')).toBeVisible();
});
