import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test('Artefatto nuovo: CKEditor non usato escluso, asset corretti ancora disponibili',async({page})=>{
 await page.goto(`${BASE_URL}/login`);
 for(const path of ['@ckeditor/ckeditor5-build-classic/build/ckeditor.js']){
  expect((await page.request.get(`${BASE_URL}/build/libs/${path}`)).status()).toBe(404);
 }
 for(const path of ['swiper/swiper-bundle.min.js','quill/quill.js','echarts/echarts.min.js']) expect((await page.request.get(`${BASE_URL}/build/libs/${path}`)).status()).toBe(200);
 await expect(page.getByRole('button',{name:/Entra/i})).toBeVisible();
});
