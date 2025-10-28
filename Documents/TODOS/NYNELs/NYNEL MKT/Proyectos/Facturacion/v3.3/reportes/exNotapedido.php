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
    require('Notapedido.php');
    //Obtenemos los datos de la cabecera de la venta actual
    require_once "../modelos/Notapedido.php";
    $notapedido = new Notapedido();
    $rsptav = $notapedido->ventacabecera($_GET["id"]);
    $datos = $notapedido->datosemp($_SESSION['idempresa']);


    $rutasalidanota = "";
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2("1");
    $Prutas = $Rrutas->fetch_object();
    $rutasalidanota = $Prutas->salidaboletas;
    $rutalogo = $Prutas->rutalogo;


    //Recorremos todos los valores obtenidos
    $regv = $rsptav->fetch_object();
    $datose = $datos->fetch_object();



    //$logo = "../files/logo/".$datose->logo;
    $logo = $rutalogo . $datose->logo;
    $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

    //Establecemos la configuración de la factura
    $pdf = new PDF_Invoice('P', 'mm', 'A4');
    $pdf->AddPage();

    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(10, 10, 10);

    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true, 10);


    //Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSocietenombre(htmlspecialchars_decode(utf8_decode($datose->nombre_comercial))); //Nuevo

    $pdf->addSociete(
      "",
      utf8_decode("Teléfono  : ") . $datose->telefono1 . " - " . $datose->telefono2 . "\n"
      . "Email  : " . $datose->correo,
      $logo,
      $ext_logo
    );

    $pdf->addSocietedireccion(htmlspecialchars_decode(utf8_decode("Direción: ") . $datose->domicilio_fiscal)); //Nuevo


    $pdf->numBoleta("$regv->numeracion_07", "$datose->numero_ruc");
    //Datos de la empresa

    $pdf->RotatedText($regv->estado, 35, 190, 'ANULADO - DADO DE BAJA', 45);
    $pdf->temporaire("");

    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse(
      $regv->fecha,
      utf8_decode($regv->cliente),
      utf8_decode($regv->direccion),
      $regv->numero_documento,
      $regv->estado,
      utf8_decode($regv->vendedorsitio),
      utf8_decode($regv->guia)
    );

    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 78,
      "CANTIDAD" => 22,
      "P.U." => 25,
      "DSCTO" => 20,
      "SUBTOTAL" => 22
    );
    $pdf->addCols($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "P.U." => "R",
      "DSCTO" => "R",
      "SUBTOTAL" => "C"
    );
    $pdf->addLineFormat($cols);
    $pdf->addLineFormat($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos

    $arrrecibos = "";
    $totalfinal = 0;
    $rsptarecibospend = $notapedido->recibospendientes($_GET["idcliente"]);
    //$regvRP = $rsptarecibospend->fetch_object();
// while ($regvRP = $rsptarecibospend->fetch_object()) {
//     $recibos = $regvRP->numeracion_07;
//     $arrrecibos = $arrrecibos ." , ".$recibos. " de ". $regvRP->total ;  
//     $totalfinal=$totalfinal +  $regvRP->total;
// }



    $y = 62;
    //Obtenemos todos los detalles de la venta actual
    $rsptad = $notapedido->ventadetalle($_GET["id"]);
    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo" . "$regd->descdet"),
        //  ."Lo recibos pendientes son: ".$arrrecibos),
        "CANTIDAD" => "$regd->cantidad_item_12" . " $regd->abre",
        "P.U." => "$regd->precio_uni_item_14_2",
        "DSCTO" => "0",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size = $pdf->addLine($y, $line);
      $y += $size + 2;
    }


    //$arrrecibos = "asdasdas";


    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->totalLetras, "CON"));
    $pdf->addCadreTVAs("" . $con_letra);
    $pdf->observSunat($regv->numeracion_07, $regv->estado, '', $datose->webconsul, $datose->nresolucion, $arrrecibos);

    //Mostramos el impuesto
    $pdf->addTVAs(0, "S/ ", $regv->itotal);
    $pdf->addCadreEurosFrancs();
    // //==================== PARA IMAGEN DEL CODIGO HASH ================================================
// //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/generador-qr/temp' . DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    include 'generador-qr/phpqrcode.php';

    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
      mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR . 'test.png';

    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $dataTxt = $regv->numero_ruc . "|" . $regv->tipo_documento_06 . "|" . $regv->serie . "|" . $regv->numerofac . "|0.00|" . $regv->itotal . "|" . $regv->fecha2 . "|" . $regv->tipo_documento . "|" . $regv->numero_documento . "|";
    ;
    $errorCorrectionLevel = 'H';
    $matrixPointSize = '2';

    // user data
    $filename = $PNG_TEMP_DIR . 'test' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
    QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    //default data
    //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
    //display generated file
    $PNG_WEB_DIR . basename($filename);
    // // //==================== PARA IMAGEN DEL CODIGO HASH ================================================

    $logoQr = $filename;
    //$logoQr = "../files/logo/".$datose->logo;
    $ext_logoQr = substr($filename, strpos($filename, '.'), -4);
    //$pdf->ImgQr($logoQr, $ext_logoQr);

    //===============SEGUNDA COPIA DE BOLETA=========================




    //Enviamos los datos de la empresa al método addSociete de la clase Factura
//Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSocietenombre2(htmlspecialchars_decode(utf8_decode($datose->nombre_comercial))); //Nuevo

    $pdf->addSociete2(
      "",
      utf8_decode("Teléfono  : ") . $datose->telefono1 . " - " . $datose->telefono2 . "\n"
      . "Email  : " . $datose->correo,
      $logo,
      $ext_logo
    );

    $pdf->addSocietedireccion2(htmlspecialchars_decode(utf8_decode("Direción  : ") . $datose->domicilio_fiscal)); //Nuevo

    //Datos de la empresa
    $pdf->numBoleta2("$regv->numeracion_07", "$datose->numero_ruc");

    $pdf->temporaire("");


    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse2($regv->fecha, utf8_decode($regv->cliente), utf8_decode($regv->direccion), $regv->numero_documento, $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));

    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 78,
      "CANTIDAD" => 22,
      "P.U." => 25,
      "DSCTO" => 20,
      "SUBTOTAL" => 22
    );
    $pdf->addCols2($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "P.U." => "R",
      "DSCTO" => "R",
      "SUBTOTAL" => "C"
    );
    $pdf->addLineFormat2($cols);
    $pdf->addLineFormat2($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
    $y2 = 208;

    //Obtenemos todos los detalles de la venta actual
    $rsptad = $notapedido->ventadetalle($_GET["id"]);
    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo" . "$regd->descdet"),
        //  ."Lo recibos pendientes son: ".$arrrecibos),
        "CANTIDAD" => "$regd->cantidad_item_12",
        "P.U." => "$regd->precio_uni_item_14_2",
        "DSCTO" => "0",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size2 = $pdf->addLine2($y2, $line);
      $y2 += $size2 + 2;
    }

    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->totalLetras, "CON"));
    $pdf->addCadreTVAs2("" . $con_letra);
    $pdf->observSunat2($regv->numeracion_07, $regv->estado, '', $datose->webconsul, $datose->nresolucion, $arrrecibos);

    //Mostramos el impuesto
    $pdf->addTVAs2(0, "S/ ", $regv->itotal);
    $pdf->addCadreEurosFrancs2();


    //==========================================================================
    $pdf->AutoPrint();
    $pdf->Output($regv->numeracion_07 . '.pdf', 'I');
    //$Factura=$pdf->Output('../boletasPDF/'.$regv->numeracion_07.'.pdf','F');
    $pdf->Output($rutasalidanota . $regv->numeracion_07 . '.pdf', 'F');

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>