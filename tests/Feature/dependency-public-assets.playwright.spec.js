import {test,expect,BASE_URL} from '../Support/playwright.js';
test.use({headless:true});
test('Swiper: scorrimento e regressione prototype pollution',async({page})=>{
 await page.goto(`${BASE_URL}/login`);
 await page.addStyleTag({url:`${BASE_URL}/build/libs/swiper/swiper-bundle.min.css`});
 await page.addScriptTag({url:`${BASE_URL}/build/libs/swiper/swiper-bundle.min.js`});
 const result=await page.evaluate(()=>{
  const container=document.createElement('div');container.className='swiper';container.style.cssText='width:400px;height:100px';container.innerHTML='<div class="swiper-wrapper"><div class="swiper-slide">Uno</div><div class="swiper-slide">Due</div><div class="swiper-slide">Tre</div></div>';document.body.append(container);
  const instance=new window.Swiper(container,{slidesPerView:1});instance.slideNext(0);const index=instance.activeIndex;instance.destroy();
  const original=Array.prototype.indexOf;let polluted;
  try{Array.prototype.indexOf=()=>-1;window.Swiper.extendDefaults(JSON.parse('{"__proto__":{"auditPolluted":"si"}}'));polluted=({}).auditPolluted;}finally{Array.prototype.indexOf=original;delete Object.prototype.auditPolluted;}
  return {index,polluted:polluted??null};
 });
 expect(result.index).toBe(1);expect(result.polluted).toBeNull();
});
test('ECharts: grafico e tooltip con etichetta HTML sintetica',async({page})=>{
 await page.goto(`${BASE_URL}/login`);
 await page.addScriptTag({url:`${BASE_URL}/build/libs/echarts/echarts.min.js`});
 const result=await page.evaluate(()=>{
  const container=document.createElement('div');container.style.cssText='width:400px;height:300px';document.body.append(container);
  const chart=window.echarts.init(container,null,{renderer:'svg'});chart.setOption({tooltip:{show:true,trigger:'item',showDelay:0},xAxis:{type:'category',data:['campione']},yAxis:{},series:[{name:'<img src=x onerror="window.auditXss=1">',type:'bar',data:[2]}]});chart.dispatchAction({type:'showTip',seriesIndex:0,dataIndex:0});
  const result={version:window.echarts.version,svg:!!container.querySelector('svg'),unsafe:!!document.querySelector('img[onerror]'),value:chart.getOption().series[0].data[0]};chart.dispose();return result;
 });
 expect(result.version).toBe('6.1.0');expect(result.svg).toBe(true);expect(result.value).toBe(2);expect(result.unsafe).toBe(false);
});
test('Quill: editor, formattazione e incolla senza handler HTML',async({page})=>{
 await page.goto(`${BASE_URL}/login`);
 await page.addStyleTag({url:`${BASE_URL}/build/libs/quill/quill.snow.css`});
 await page.addScriptTag({url:`${BASE_URL}/build/libs/quill/quill.js`});
 const result=await page.evaluate(()=>{
  const container=document.createElement('div');document.body.append(container);const editor=new window.Quill(container,{theme:'snow',modules:{toolbar:[['bold','italic']]}});
  editor.setText('Dato sintetico');editor.formatText(0,4,'bold',true);const text=editor.getText();const bold=editor.getContents().ops[0].attributes.bold;
  editor.clipboard.dangerouslyPasteHTML('<p>Test</p><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" onerror="window.auditXss=1">');
  return {version:window.Quill.version,text,bold,unsafe:!!container.querySelector('[onerror]'),xss:window.auditXss??null};
 });
 expect(result.version).toBe('2.0.3');expect(result.text).toContain('Dato sintetico');expect(result.bold).toBe(true);expect(result.unsafe).toBe(false);expect(result.xss).toBeNull();
});
