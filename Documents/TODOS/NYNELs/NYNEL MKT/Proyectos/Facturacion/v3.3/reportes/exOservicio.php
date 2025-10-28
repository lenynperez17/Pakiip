<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['Ventas'] == 1) {
    //Incluímos el archivo oservicio.php
    require('Oservicio.php');

    //Obtenemos los datos de la cabecera de la venta actual
    require_once "../modelos/Ordenservicio.php";
    $oservicio = new Ordenservicio();
    $rsptav = $oservicio->ventacabecera($_GET["id"]);
    $datos = $oservicio->datosemp();
    //Recorremos todos los valores obtenidos
    $regv = $rsptav->fetch_object();

    $datose = $datos->fetch_object();

    $logo = "../files/logo/" . $datose->logo;
    $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

    //Establecemos la configuración de la oservicio
    $pdf = new PDF_Invoice('P', 'mm', 'A4');
    $pdf->AddPage();

    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(10, 10, 10);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true, 10);

    //Enviamos los datos de la empresa al método addSociete de la clase oservicio
    $pdf->addSociete(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección    : ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono     : ") . $datose->telefono1 . " - " . $datose->telefono2 . "\n" . "Email          : " . $datose->correo, $logo, $ext_logo);

    $pdf->numoServicio("$regv->serienumero", "$datose->numero_ruc");

    $pdf->temporaire("");

    // //Enviamos los datos del cliente al método addClientAdresse de la clase oservicio
    $pdf->addProveeAdresse($regv->fechaemision, $regv->fechaentrega, utf8_decode($regv->razon_social), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado);

    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 78,
      "CANTIDAD" => 22,
      "V.U." => 25,
      "P.U." => 20,
      "P.TOTAL" => 22
    );
    $pdf->addCols($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "V.U." => "R",
      "P.U." => "R",
      "P.TOTAL" => "C"
    );
    $pdf->addLineFormat($cols);
    $pdf->addLineFormat($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
    $y = 62;

    //Obtenemos todos los detalles de la venta actual
    $rsptad = $oservicio->ventadetalle($_GET["id"]);

    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo - " . "$regd->descripcion"),
        "CANTIDAD" => "$regd->cantidad",
        "V.U." => "-",
        "P.U." => "$regd->valorcosto",
        "P.TOTAL" => "$regd->totalunitario"
      );
      $size = $pdf->addLine($y, $line);
      $y += $size + 2;
    }

    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->total, "CON"));
    $pdf->addCadreTVAs("" . $con_letra);
    $pdf->observ($regv->serienumero, $regv->estado);

    //Mostramos el impuesto
    $pdf->addTVAs($regv->igv, $regv->total, "S/ ");
    $pdf->addCadreEurosFrancs($regv->subtotal);


    $pdf->Output();

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>