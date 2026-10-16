import fs from 'node:fs';
// Riceve esclusivamente il test già protetto dal supporto ufficiale.
export const withCustomerObservation=base=>base.extend({
 page:async({page,context},use,testInfo)=>{
  const dir=fs.mkdtempSync('/tmp/schedine-mobile-diagnosi-');
  const consoleEvents=[];
  page.on('console',m=>consoleEvents.push({tipo:m.type(),testo:m.text(),posizione:m.location()}));
  page.on('pageerror',e=>consoleEvents.push({tipo:'pageerror',testo:e.message,stack:e.stack}));
  page.on('crash',()=>consoleEvents.push({tipo:'crash',testo:'Chromium ha terminato la pagina'}));
  await context.tracing.start({screenshots:true,snapshots:true,sources:true});
  await context.addInitScript(()=>{
   window.__customerEvents=[];
   const state=()=>({time:performance.now(),scroll:[scrollX,scrollY],body:document.body?.className,focus:document.activeElement?.id,ready:document.readyState,tabs:Array.from(document.querySelectorAll('[data-bs-toggle="pill"]')).map(el=>({id:el.id,classe:el.className,selected:el.getAttribute('aria-selected')})),panes:Array.from(document.querySelectorAll('.tab-pane')).map(el=>({id:el.id,classe:el.className})),bootstrap:window.bootstrap?.Tab?.VERSION});
   for(const type of ['pointerdown','pointerup','mousedown','mouseup','focusin','focusout','click','show.bs.tab','shown.bs.tab','hide.bs.tab','hidden.bs.tab']) document.addEventListener(type,e=>{
    if(!document.querySelector('form.form-steps'))return;
    const row={type,target:e.target?.outerHTML?.slice(0,600),prevented:e.defaultPrevented,point:[e.clientX,e.clientY],state:state()};
    if(type==='pointerdown'&&e.target.closest?.('[data-bs-toggle="pill"]')){
     window.__customerTabPressed=true;
     row.beforeDOM=document.querySelector('form.form-steps').outerHTML;
    }
    if(type==='click'){
     const isTab=!!e.target.closest('[data-bs-toggle="pill"]');
     if(isTab)row.beforeDOM=document.querySelector('form.form-steps').outerHTML;
     // Evita letture geometriche sincrone: non forza il layout tra pressione e rilascio.
     if(isTab||window.__customerTabPressed)setTimeout(()=>window.__customerEvents.push({type:'dopo-click-task',prevented:e.defaultPrevented,state:state(),afterDOM:document.querySelector('form.form-steps')?.outerHTML}),0);
    }
    if(type==='click')window.__customerTabPressed=false;
    window.__customerEvents.push(row);
   },true);
  });
  const retain=data=>fs.appendFileSync(dir+'/documenti.ndjson',JSON.stringify(data)+'\n');
  await page.exposeFunction('retainCustomerObservation',retain);
  await context.addInitScript(()=>{
   addEventListener('pagehide',()=>window.retainCustomerObservation({url:location.href,events:window.__customerEvents||[]}));
  });
  try {await use(page);} finally {
   try {retain(await page.evaluate(()=>({url:location.href,events:window.__customerEvents||[],dom:document.documentElement.outerHTML})));await page.screenshot({path:dir+'/finale.png',fullPage:true});}catch(e){consoleEvents.push({tipo:'raccolta',testo:String(e)});}
   fs.writeFileSync(dir+'/console.json',JSON.stringify(consoleEvents,null,2));
   fs.writeFileSync(dir+'/esito.json',JSON.stringify({titolo:testInfo.title,esito:testInfo.status,atteso:testInfo.expectedStatus},null,2));
   await context.tracing.stop({path:dir+'/trace.zip'});
   console.log('DIAGNOSI_MOBILE='+JSON.stringify({dir,titolo:testInfo.title,esito:testInfo.status}));
  }
 }
});
