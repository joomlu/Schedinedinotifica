<?php
require __DIR__.'/seed-schedina-flow.php';
$chunk=fn($type,$data)=>pack('N',strlen($data)).$type.$data.pack('N',crc32($type.$data));
foreach([[$s,35,'#345678'],[$b,170,'#8c3d57']]as[$hotel,$red,$color]){
 $png="\x89PNG\r\n\x1a\n".$chunk('IHDR',pack('NNCCCCC',220,70,8,2,0,0,0));
 $row="\x00".str_repeat(pack('CCC',$red,85,120),220);$png.=$chunk('IDAT',gzcompress(str_repeat($row,70))).$chunk('IEND','');
 $path='uploads/loghi/html-browser-'.$hotel->id.'.png';\Illuminate\Support\Facades\Storage::disk('public')->put($path,$png);
 $hotel->forceFill(['logo'=>'storage/'.$path,'web_checkin_colore'=>$color])->save();
}
file_put_contents(public_path('web-html-fixture.json'),json_encode(['A'=>$s->id,'B'=>$b->id,'logoA'=>$s->logo,'logoB'=>$b->logo]));
