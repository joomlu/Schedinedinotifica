import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
// Runtime dedicato: finestra60secondi nuova, senza azzerare contatori o cambiare il middleware.
test('Limite generale Wi-Fi condiviso300 prima del lookup, anche altre strutture',async({page})=>{
 // 301 richieste HTTP reali nel server PHP seriale: budget della sola prova, nessuna modifica alla soglia300.
 test.setTimeout(90000);
 const r=await (await page.request.get(`${BASE_URL}/web-lifecycle-ids.json`)).json();let limited;for(let i=0;i<301;i++){const response=await page.request.get(`${BASE_URL}/w/invalid-${i}`,{headers:{Accept:'application/json'}});if(response.status()===429){limited=response;break;}expect(response.status()).toBe(404);}expect(limited).toBeTruthy();expect(Number(limited.headers()['retry-after'])).toBeGreaterThan(0);expect((await page.goto(`${BASE_URL}/w/${r.wifi3.short}`)).status()).toBe(429);
});
