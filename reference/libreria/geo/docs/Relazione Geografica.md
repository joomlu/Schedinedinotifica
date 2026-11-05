# Relazione Geografica

Vedi `src/GeoSelect.js` e il README principale del pacchetto per dipendenze e contratti API.

Esempio (UMD):

```html
<script>
  var geo = new Libreria.GeoSelect({
    nation:'#country', region:'#region', province:'#prov', city:'#city', cap:'#cap',
    preselectItaly:true, filterCapByCity:true, autoSelectUniqueCap:true,
    endpoints:{ nations:'/nations', regions:'/regions', provincesAll:'/provinces-all', citiesByProvince:'/cities-by-province', capByProvince:'/cap-by-province' },
    container: document.getElementById('geo_wrap')
  });
</script>
```
