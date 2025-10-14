<link rel="stylesheet" href="carga.css" >
<div class="loader" id="ld" name="ld"></div>




<script type="text/javascript">
$(window).load(function() {
    $(".loader").fadeOut("slow");
});
</script>

<?php

require "../config/Conexion.php";

//conexiones, conexiones everywhere
ini_set('display_errors', 1);
error_reporting(E_ALL);

//$connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME); PHP_5.6

$connect = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);





//=========================================================================
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

//if (!@mysql_connect(DB_HOST, DB_USERNAME , DB_PASSWORD)) PHP 5.6
      if (!@mysqli_connect(DB_HOST, DB_USERNAME , DB_PASSWORD))
    die("No se pudo establecer conexión a la base de datos");
//if (!@mysql_select_db(DB_NAME))
    //die("base de datos no existe");


    if(isset($_POST['submit']))
    {
        //Aquí es donde seleccionamos nuestro csv
         $fname = $_FILES['sel_file']['name'];
         echo 'Cargando nombre del archivo: '.$fname.' <br>';
         $chk_ext = explode(".",$fname);
 
         if(strtolower(end($chk_ext)) == "csv")
         {
             //si es correcto, entonces damos permisos de lectura para subir
             $filename = $_FILES['sel_file']['tmp_name'];
             $handle = fopen($filename, "r");
 
             while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
             {
               //Insertamos los datos con los valores...
                $sql = "insert into articulo 
                (
                idarticulo, 
                idalmacen, 
                codigo_proveedor, 
                codigo, 
                nombre, 
                idfamilia, 
                unidad_medida, 
                costo_compra, 
                saldo_iniu, 
                valor_iniu, 
                saldo_finu, 
                valor_finu, 
                stock, 
                comprast, 
                ventast, 
                portador, 
                merma, 
                precio_venta, 
                imagen, 
                estado, 
                valor_fin_kardex, 
                precio_final_kardex, 
                fecharegistro, 
                codigosunat, 
                ccontable, 
                precio2, 
                precio3,
                costofinal,
                cicbper,
                nticbperi,
                ctticbperi,
                mticbperu,
                codigott,
                desctt,
                codigointtt,
                nombrett,
                lote,
                marca,
                fechafabricacion,
                fechavencimiento ,
                procedencia,
                fabricante,
                registrosanitario,
                fechaingalm,
                fechafinalma,
                proveedor,
                seriefaccompra,
                numerofaccompra,
                fechafacturacompra,
                limitestock,
                tipoitem, 
                umedidacompra,
                factorc,
                descrip
                 ) 

                values 

                (
                '$data[0]', 
                '$data[1]', 
                '$data[2]', 
                '$data[3]', 
                '$data[4]', 
                '$data[5]', 
                '$data[6]', 
                '$data[7]', 
                '$data[8]', 
                '$data[9]', 
                '$data[10]', 
                '$data[11]', 
                '$data[12]', 
                '$data[13]', 
                '$data[14]', 
                '$data[15]', 
                '$data[16]', 
                '$data[17]', 
                '$data[18]', 
                '$data[19]', 
                '$data[20]', 
                '$data[21]', 
                '$data[22]', 
                '$data[23]', 
                '$data[24]', 
                '$data[25]', 
                '$data[26]',
                '$data[27]',
                '$data[28]',
                '$data[29]',
                '$data[30]',
                '$data[31]',
                '$data[32]',
                '$data[33]',
                '$data[34]',
                '$data[35]',
                '$data[36]',
                '$data[37]',
                '$data[38]',
                '$data[39]',
                '$data[40]',
                '$data[41]',
                '$data[42]',
                '$data[43]',
                '$data[44]',
                '$data[45]',
                '$data[46]',
                '$data[47]',
                '$data[48]',
                '$data[49]',
                '$data[50]',
                '$data[51]',
                '$data[52]',
                '$data[53]'
                

            )";
             // mysql_query($sql) or die('Error: '.mysql_error()); PHP 5.6
             $var_resultado = $connect->query($sql);


             }
             //cerramos la lectura del archivo "abrir archivo" con un "cerrar archivo"
             fclose($handle);
             echo "Importación a la tabla artículos, exitosa!";
             echo "<br/><br/>
                  <a href='javascript:history.back(-1);' title='Ir la página anterior'>Volver</a>";
         }
         else
         {
            //si aparece esto es posible que el archivo no tenga el formato adecuado, inclusive cuando es cvs, revisarlo para             
//ver si esta separado por " , "
             echo "Archivo invalido!";
         }
    }

    ?>