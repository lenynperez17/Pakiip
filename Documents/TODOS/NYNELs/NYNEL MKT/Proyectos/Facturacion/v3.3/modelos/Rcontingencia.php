<?php 
require "../config/Conexion.php";
Class Rcontingencia
{
    //Implementamos nuestro constructor
    public function __construct()
    {
    }
 
    //Implementamos un método para insertar registros para factura
    public function crear($rucemisor, $rf, $fechag, $ncor, $motivoc, $fechacomp, $tipocp, $seriecp, $numerocp, $numeroft, $tipodc, $numerodc, $nombrecli, $totalvvg, $totalvve, $totalvoi, $isc, $igv, $otrosc, $total , $tipocpm, $seriecpm, $numerocpm)
    {

    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2();
    $Prutas = $Rrutas->fetch_object();
    $rutaresumen=$Prutas->rutaresumen; // ruta de la carpeta resumen
    $rutadescargas=$Prutas->rutadescargas; // ruta de la carpeta descargas
    


     $mask = $rutaresumen.'*';
     array_map( "unlink", glob( $mask ) );

        $num_elementos=0;
        $i=0;
        $sw=true;
        while ($num_elementos < count($motivoc))
        {
          for($i=0; $i < count($motivoc); $i++)
          {

      $con=0;
      $motivoc1=array();
      $fechacomp1=array();
      $tipocp1=array();
      $seriecp1=array();
      $numerocp1=array();
      $numeroft1=array();
      $tipodc1=array();
      $numerodc1=array();
      $nombrecli1=array();
      $totalvvg1=array();
      $totalvve1=array();
      $totalvoi1=array();
      $isc1=array();
      $igv1=array();
      $otrosc1=array();
      $total1=array();
      $tipocpm1=array();
      $seriecpm1=array();
      $numerocpm1=array();
      
           $motivoc1[$i]=$motivoc[$num_elementos];
           $fechacomp1[$i]=$fechacomp[$num_elementos];
           $tipocp1[$i]=$tipocp[$num_elementos];
           $seriecp1[$i]=$seriecp[$num_elementos];
           $numerocp1[$i]=$numerocp[$num_elementos];
           $numeroft1[$i]=$numeroft[$num_elementos];
           $tipodc1[$i]=$tipodc[$num_elementos];
           $numerodc1[$i]=$numerodc[$num_elementos];
           $nombrecli1[$i]=$nombrecli[$num_elementos];
           $totalvvg1[$i]=$totalvvg[$num_elementos];
           $totalvve1[$i]=$totalvve[$num_elementos];
           $totalvoi1[$i]=$totalvoi[$num_elementos];
           $isc1[$i]=$isc[$num_elementos];
           $igv1[$i]=$igv[$num_elementos];
           $otrosc1[$i]=$otrosc[$num_elementos];
           $total1[$i]=$total[$num_elementos];
           $tipocpm1[$i]=$tipocpm[$num_elementos];
           $seriecpm1[$i]=$seriecpm[$num_elementos];
           $numerocpm1[$i]=$numerocpm[$num_elementos];

$path=$rutaresumen.$rucemisor."-".$rf."-".$fechag."-".$ncor.".txt";
$handle=fopen($path, "a");
fwrite($handle, $motivoc1[$i]."|".$fechacomp1[$i]."|".$tipocp1[$i]."|".$seriecp1[$i]."|".$numerocp1[$i]."|".$numeroft1[$i]."|".$tipodc1[$i]."|".$numerodc1[$i]."|".$nombrecli1[$i]."|".$totalvvg1[$i] ."|".$totalvve1[$i] ."|".$totalvoi1[$i] ."|".$isc1[$i] ."|".$igv1[$i] ."|".$otrosc1[$i] ."|".$total1[$i] ."|".$tipocpm1[$i] ."|".$seriecpm1[$i] ."|".$numerocpm1[$i]."|\r\n"); fclose($handle);
    $i=$i+1;
    }
    $num_elementos=$num_elementos + 1;
  }



// //==========================COMPRESION===================================
//            /* primero creamos la función que hace la magia ===========================
//            * esta funcion recorre carpetas y subcarpetas
//            * añadiendo todo archivo que encuentre a su paso
//            * recibe el directorio y el zip a utilizar 
//            */
//           //if (!function_exists("agregar_zip")){
//           function agregar_zip($dir, $zip) {

//             //verificamos si $dir es un directorio
//             if (is_dir($dir)) {
//               //abrimos el directorio y lo asignamos a $da
//               if ($da = opendir($dir)) {
//                 //leemos del directorio hasta que termine
//                 while (($archivo = readdir($da)) !== false) {
//                   /*Si es un directorio imprimimos la ruta
//                    * y llamamos recursivamente esta función
//                    * para que verifique dentro del nuevo directorio
//                    * por mas directorios o archivos
//                    */
//                   if (is_dir($dir . $archivo) && $archivo != "." && $archivo != "..") {
//                     echo "<strong>Creando directorio: $dir$archivo</strong><br/>";
//                     agregar_zip($dir . $archivo . "/", $zip);
//                     /*si encuentra un archivo imprimimos la ruta donde se encuentra
//                      * y agregamos el archivo al zip junto con su ruta 
//                      */
//                   } elseif (is_file($dir . $archivo) && $archivo != "." && $archivo != "..") {
//                     echo "Agregando archivo: $dir$archivo <br/>";
//                     $zip->addFile($dir . $archivo, $dir . $archivo);
//                   }
//                 }
//                 //cerramos el directorio abierto en el momento
//                 closedir($da);
//               }
//             }
//           }//fin de la función =================================================
//        // }
        
//         //creamos una instancia de ZipArchive
//         $zip = new ZipArchive();
//         /*directorio a comprimir
//          * la barra inclinada al final es importante
//          * la ruta debe ser relativa no absoluta
//          */
//         $dir = $rutaresumen;
//         //ruta donde guardar los archivos zip, ya debe existir
//         $rutaFinal = $rutadescargas;

//         if(!file_exists($rutaFinal)){
//           mkdir($rutaFinal);
//         }
//         $archivoZip = $rucemisor."-".$rf."-".$fechag."-".$ncor.".zip";
//         if ($zip->open($archivoZip, ZIPARCHIVE::CREATE) === true) {

//           agregar_zip($dir, $zip);
//           $zip->close();
//           //Muevo el archivo a una ruta
//           //donde no se mezcle los zip con los demas archivos
//           rename($archivoZip, "$rutaFinal/$archivoZip");
         
//           //Hasta aqui el archivo zip ya esta creado
//           //Verifico si el archivo ha sido creado
//           if (file_exists($rutaFinal. "/" . $archivoZip)) {
//             echo "Proceso Finalizado!! <br/><br/>
//                         Descargar: <a href='$rutaFinal/$archivoZip'>$archivoZip</a>";
//           } else {
//             echo "Error, archivo zip no ha sido creado!!";
//           }
          
        
//           }
            //$mask = "../sfs/BAJA/*";
            //array_map( "unlink", glob( $mask ) );

        //Fin de compresion de archivos cab y det            
        exec ("explorer.exe  ".$rutaresumen); 
          ?>
               <script>
                      alert("Revizar carpeta descargas");
                      //window.setTimeout("history.back(-1)", 500);
               </script>

          <?php
          return $sw;

    }
}

?>