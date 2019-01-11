<?php


class MergeCsv{

    public function getCsvFromXml($csv_1_file_path='.\csv1.csv', $csv_2_file_path='.\csv2.csv'){
        if (file_exists($csv_1_file_path) && file_exists($csv_2_file_path)) {

            $csv_1= fopen($csv_1_file_path, "r");
            $csv_2= fopen($csv_2_file_path, "r");
            $rows=[];
            $headers_1=[];
            $headers_2=[];
            $headers=[];


            //Lectura del CSV 1
            $headers_1=fgetcsv($csv_1); //Se almacenan en un array los Headers del CSV1
            while (($datos = fgetcsv($csv_1)) !== false) {
                $rows[]= array_combine($headers_1,$datos); //Se crea un array asociativo con las con las cabeceras como key y los valores de fila como value

            }
            fclose($csv_1);


            //Lectura del CSV 2
            $headers_2=fgetcsv($csv_2); //Se almacenan en un array los Headers del CSV1
            while (($datos = fgetcsv($csv_2)) !== false) {
                $rows[]= array_combine($headers_2,$datos); //Se crea un array asociativo con las con las cabeceras como key y los valores de fila como value

            }
            fclose($csv_2);

            $headers=array_unique(array_merge($headers_1,$headers_2));// Suma y Descarta las cabeceras repetidas
            $file = str_replace('.csv', '_merged.csv', $csv_1_file_path);




            //Escritura del archivo mergeado
            if (file_exists($file)) {  //Se comprueba si existe el fichero para sobreescribirlo
                unlink($file);
            }

            $csv = fopen($file, 'w');
            fputcsv($csv, $headers, ';', '"'); //Se inserta la primera fila con las cabeceras
            foreach ($rows as $row) { //Se recorren todas las filas leídas
                $csv_row=[];

                foreach ($headers as $head) {
                    if(isset($row[$head])){ //Si la fila contien un valor se inserta, si no, se inserta un null.
                        $csv_row[]=$row[$head];
                    }else{
                        $csv_row[]=null;
                    }
                }
                fputcsv($csv, $csv_row, ';', '"'); //Se escribe la línea en el Fichero CSV.
            }
            fclose($csv);
            exit('Fichero XML procesado con éxito: "'.$file.'"');
        } else {
            exit('Error abriendo el archivo');
        }
    }

}
