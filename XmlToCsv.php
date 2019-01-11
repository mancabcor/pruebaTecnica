<?php


class XmlToCsv {

    public function getCsvFromXml($xml_file_path='.\test.xml'){
        if (file_exists($xml_file_path)) {
            $xml = simplexml_load_file($xml_file_path);
            $rows=[];
            $headers=[];


            //Lectura de XML
            foreach ($xml->products as $products) {
                foreach ($products->product as $product) {
                    $row=[];
                    foreach($product->children() as $key=>$attr){
                        $value=html_entity_decode($product->$key);
                        if(!in_array($value, $headers)){
                            $headers[]=$key;//Almacena En un array todos los valores de cabera.
                        }
                        $row[$key]=$value;//Almacena Todas las filas, un array asociativo.
                    }
                    $rows[]=$row;
                }
            }
            $headers=array_unique($headers);//Descarta las cabeceras repetidas


            $file = str_replace('xml', 'csv', $xml_file_path);

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
            exit('Error abriendo el archivo: "'.$xml_file_path.'"');
        }
    }
}
