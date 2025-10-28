<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['Ventas'] == 1) {
    //Incluímos el archivo Factura.php
    require('Factura.php');





    //Obtenemos los datos de la cabecera de la venta actual
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $rsptav = $factura->ventacabecera($_GET["id"]);
    $datos = $factura->datosemp();
    //Recorremos todos los valores obtenidos
    $regv = $rsptav->fetch_object();
    $datose = $datos->fetch_object();

    $logo = "../files/logo/" . $datose->logo;
    $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

    //Establecemos la configuración de la factura
    $pdf = new PDF_Invoice('P', 'mm', 'A4');
    $pdf->AddPage();

    //Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSociete(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección: ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono: ") . "\n" . "Email : " . $datose->correo, $logo, $ext_logo);

    $pdf->numFactura("$regv->numeracion_08", "$datose->numero_ruc");

    $pdf->temporaire("");

    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse($regv->fecha, utf8_decode($regv->cliente), utf8_decode($regv->direccion), $regv->numero_documento, $regv->estado, utf8_decode($regv->usuario), utf8_decode($regv->guia));

    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 78,
      "CANTIDAD" => 22,
      "V.U." => 25,
      "DSCTO" => 20,
      "SUBTOTAL" => 22
    );
    $pdf->addCols($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "V.U." => "R",
      "DSCTO" => "R",
      "SUBTOTAL" => "C"
    );
    $pdf->addLineFormat($cols);
    $pdf->addLineFormat($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
    $y = 62;

    //Obtenemos todos los detalles de la venta actual
    $rsptad = $factura->ventadetalle($_GET["id"]);

    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo"),
        "CANTIDAD" => "$regd->cantidad_item_12",
        "V.U." => "$regd->valor_uni_item_14",
        "DSCTO" => "0",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size = $pdf->addLine($y, $line);
      $y += $size + 2;
    }
    //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2();
    $Prutas = $Rrutas->fetch_object();
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $data[0] = "";

    //===========PARA EXTRAER EL CODIGO HASH =============================
// if ($regv->estado=='3' || $regv->estado=='4') {
// $facturaFirm=$regv->numero_ruc."-".$regv->tipo_documento_07."-".$regv->numeracion_08;
// $sxe = new SimpleXMLElement($rutafirma.$facturaFirm.'.xml', null, true);
// $urn = $sxe->getNamespaces(true);
// $sxe->registerXPathNamespace('ds', $urn['ds']);
// $data = $sxe->xpath('//ds:DigestValue');

    //==================== PARA IMAGEN DEL CODIGO HASH ================================================
//set it to writable location, a place for temp generated PNG files
    // $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'/generador-qr/temp'.DIRECTORY_SEPARATOR;
    // //html PNG location prefix
    // $PNG_WEB_DIR = 'temp/';
    // include '/generador-qr/phpqrcode.php';    

    // //ofcourse we need rights to create temp dir
    // if (!file_exists($PNG_TEMP_DIR))
    //     mkdir($PNG_TEMP_DIR);
    // $filename = $PNG_TEMP_DIR.'test.png';
    // //processing form input
    // //remember to sanitize user input in real-life solution !!!
    // $dataTxt=$data[0];
    // $errorCorrectionLevel = 'H';    
    // $matrixPointSize = '2';

    // // user data
    //     $filename = $PNG_TEMP_DIR.'test'.md5($dataTxt.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
    //     QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    //     //default data
    //     QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    //    //display generated file
    //     $PNG_WEB_DIR.basename($filename);

    //==================== PARA IMAGEN DEL CODIGO HASH ================================================
//$logoQr = $filename;
//$logoQr = "../files/logo/".$datose->logo;
//$ext_logoQr = substr($filename, strpos($filename,'.'),-4);
//$pdf->ImgQr($logoQr, $ext_logoQr);
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================

    // }
// else
// {
//      $data[0] = "";
// }

    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total_venta_27, "NUEVOS SOLES"));
    $pdf->addCadreTVAs("" . $con_letra);
    $pdf->observSunat($regv->numeracion_08, "", $data[0]);

    //Mostramos el impuesto
    $pdf->addTVAs($regv->sumatoria_igv_22_1, $regv->importe_total_venta_27, "S/ ");
    $pdf->addCadreEurosFrancs($regv->sumatoria_igv_22_1);








    //===============SEGUNDA COPIA DE FACTURA=========================





    //Enviamos los datos de la empresa al método addSociete de la clase Factura
//Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSociete2(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección: ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono: ") . $datose->telefono1 . "\n" . "Email : " . $datose->correo, $logo, $ext_logo);

    //Datos de la empresa
    $pdf->numFactura2("$regv->numeracion_08", "$datose->numero_ruc");

    $pdf->temporaire("");


    ////Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse2($regv->fecha, utf8_decode($regv->cliente), utf8_decode($regv->direccion), $regv->numero_documento, $regv->estado, utf8_decode($regv->usuario), utf8_decode($regv->guia));

    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 78,
      "CANTIDAD" => 22,
      "V.U." => 25,
      "DSCTO" => 20,
      "SUBTOTAL" => 22
    );
    $pdf->addCols2($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "V.U." => "R",
      "DSCTO" => "R",
      "SUBTOTAL" => "C"
    );
    $pdf->addLineFormat2($cols);
    $pdf->addLineFormat2($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
    $y2 = 203; // para el tamaño del cuadro del segundo detalle

    //Obtenemos todos los detalles de la venta actual
    $rsptad = $factura->ventadetalle($_GET["id"]);

    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo"),
        "CANTIDAD" => "$regd->cantidad_item_12",
        "V.U." => "$regd->valor_uni_item_14",
        "DSCTO" => "0",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size2 = $pdf->addLine2($y2, $line);
      $y2 += $size2 + 2;
    }

    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total_venta_27, "NUEVOS SOLES"));
    $pdf->addCadreTVAs2("" . $con_letra);
    $pdf->observSunat2($regv->numeracion_08, "", $data[0]);


    //Mostramos el impuesto
    $pdf->addTVAs2($regv->sumatoria_igv_22_1, $regv->importe_total_venta_27, "S/ ");
    $pdf->addCadreEurosFrancs2($regv->sumatoria_igv_22_1);


    $Factura = $pdf->Output($regv->numeracion_08 . '.pdf', 'I');
    $Factura = $pdf->Output('../facturasPDF/' . $regv->numeracion_08 . '.pdf', 'F');




  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>