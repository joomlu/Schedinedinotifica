import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source=readFileSync(new URL('../../resources/js/app.js',import.meta.url),'utf8');
const guard=source.slice(source.indexOf('// Guard DEV:'),source.indexOf('function retrofitDataUi'));
test('La guardia sviluppo conserva matcher, traduzioni, contesto e rifiuti diagnostici Select2',()=>{
  const matcher=()=>({id:1}); const amd={require:()=> 'traduzione sintetica'};const calls=[];const errors=[];
  function select2(...args){calls.push({context:this,args});return this;}
  select2.defaults={defaults:{matcher}};select2.amd=amd;
  const $={fn:{select2}};
  vm.runInNewContext(guard,{$,isDev:()=>true,console:{error:(...args)=>errors.push(args)}});
  assert.notEqual($.fn.select2,select2);
  assert.equal($.fn.select2.defaults.defaults.matcher,matcher);
  assert.equal($.fn.select2.amd,amd);
  const collection={filter:()=>({length:0})};const options={matcher};
  assert.equal($.fn.select2.call(collection,options),collection);
  assert.equal(calls[0].context,collection);assert.equal(calls[0].args[0],options);assert.equal(errors.length,0);
  const forbidden={filter:()=>({length:1,toArray:()=>['elemento sintetico']})};
  $.fn.select2.call(forbidden,'open');assert.equal(errors.length,1);assert.equal(calls[1].args[0],'open');
});
