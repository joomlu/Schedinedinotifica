<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchivosController extends Controller
{
    public function generarArchivoHospedados()
    {
        // Obtén los datos de la base de datos, por ejemplo, de la tabla 'hospedados'
        $hospedados = DB::table('schedina')->get(); // Reemplaza con tu consulta

        // Nombre del archivo
        $fileName = 'istat2022/schedina-'.date('Ymd').'.txt';

        // Inicializar el contenido del archivo
        $contenido = '';

        foreach ($hospedados as $hospedado) {
            // Formatear cada campo según los requisitos del archivo

            // Bloque 1
            $tipoAlojamiento = str_pad($hospedado->relationship, 2, ' ', STR_PAD_RIGHT);
            $fechaLlegada = date('d/m/Y', strtotime($hospedado->arrive));
            $apellido = str_pad($hospedado->surname, 50, ' ', STR_PAD_RIGHT);
            $nombre = str_pad($hospedado->name, 30, ' ', STR_PAD_RIGHT);
            $genero = $hospedado->sex; // 1 o 2
            $fechaNacimiento = date('d/m/Y', strtotime($hospedado->oa_date_nac));
            $municipioNacimiento = str_pad($hospedado->oa_city_nac ?? '', 9, ' ', STR_PAD_RIGHT);
            $provinciaNacimiento = str_pad($hospedado->oa_prov ?? '', 2, ' ', STR_PAD_RIGHT);
            $estadoNacimiento = str_pad($hospedado->oa_country, 9, ' ', STR_PAD_RIGHT);
            $estadoCiudadania = str_pad($hospedado->or_country, 9, ' ', STR_PAD_RIGHT);

            $municipioResidencia = str_pad($hospedado->or_city ?? '', 9, ' ', STR_PAD_RIGHT);
            $provinciaResidencia = str_pad($hospedado->or_prov ?? '', 2, ' ', STR_PAD_RIGHT);
            $estadoResidencia = str_pad($hospedado->or_country, 9, ' ', STR_PAD_RIGHT);
            $direccionResidencial = str_pad($hospedado->or_typeaway.' '.$hospedado->or_address.' '.$hospedado->or_num, 50, ' ', STR_PAD_RIGHT);
            $tipoDocumento = str_pad($hospedado->or_doctype ?? '', 5, ' ', STR_PAD_RIGHT);
            $numeroDocumento = str_pad($hospedado->or_doc ?? '', 20, ' ', STR_PAD_RIGHT);
            $lugarEmisionDocumento = str_pad($hospedado->or_published ?? '', 9, ' ', STR_PAD_RIGHT);

            // Concatenar bloques para cada registro
            $registro = $tipoAlojamiento.$fechaLlegada.$apellido.$nombre.$genero.$fechaNacimiento.
                        $municipioNacimiento.$provinciaNacimiento.$estadoNacimiento.$estadoCiudadania.
                        $municipioResidencia.$provinciaResidencia.$estadoResidencia.$direccionResidencial.
                        $tipoDocumento.$numeroDocumento.$lugarEmisionDocumento;

            // Asegurarse de que cada línea tenga 236 caracteres
            $contenido .= str_pad($registro, 236)."\r\n";
        }

        // Crear el archivo en el disco local
        Storage::disk('local')->put($fileName, $contenido);

        return response()->json([
            'success' => true,
            'message' => "Archivo {$fileName} generado exitosamente.",
        ]);
    }
}
