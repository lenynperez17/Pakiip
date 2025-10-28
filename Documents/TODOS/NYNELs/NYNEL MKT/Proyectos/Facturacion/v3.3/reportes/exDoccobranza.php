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
    require('Doccobranza.php');

    //Obtenemos los datos de la cabecera de la venta actuall
    require_once "../modelos/Doccobranza.php";
    $doccobranza = new Doccobranza();
    $rsptav = $doccobranza->ventacabecera($_GET["id"], $_SESSION['idempresa']);
    $datos = $doccobranza->datosemp($_SESSION['idempresa']);
    //Recorremos todos los valores obtenidos
    $regv = $rsptav->fetch_object();
    $datose = $datos->fetch_object();
    $logo = "../files/logo/" . $datose->logo;
    $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

    //Establecemos la configuración de la factura
    $pdf = new PDF_Invoice('P', 'mm', 'A4');
    $pdf->AddPage();

    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(10, 10, 10);

    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true, 10);

    //Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSociete(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)), utf8_decode("Dirección    : ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono     : ") . $datose->telefono1 . " - " . $datose->telefono2 . "\n" . "Email          : " . $datose->correo, $logo, $ext_logo);

    $pdf->numdoccobranza("$regv->serienumero", "$datose->numero_ruc");

    $pdf->RotatedText($regv->estado, 35, 190, 'ANULADO', 45);
    $pdf->temporaire("");

    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse($regv->fecha, utf8_decode(htmlspecialchars_decode($regv->cliente)), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, '', $regv->tipodoccobranza, $regv->condicion, $regv->tipo_moneda);


    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 101,
      "CANTIDAD" => 22,
      "PRECIO" => 22,
      "SUBTOTAL" => 22
    );
    $pdf->addCols($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "C",
      "PRECIO" => "C",
      "SUBTOTAL" => "C"
    );
    $pdf->addLineFormat($cols);
    $pdf->addLineFormat($cols);

    //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
    $y = 62;

    //Obtenemos todos los detalles de la venta actual
    $rsptad = $doccobranza->ventadetalle($_GET["id"], $regv->tipodoccobranza);

    while ($regd = $rsptad->fetch_object()) {
      if ($regv->tipodoccobranza == 'producto') {
        $um = $regd->unidad_medida;
      } else {
        $um = '';
      }
      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo") . "\n" . utf8_decode("$regd->descdet"),
        "CANTIDAD" => "$regd->cantidad" . " " . $um,
        "PRECIO" => "$regd->precio",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size = $pdf->addLine($y, $line);
      $y += $size + 2;
    }



    $viewmon = "";
    if ($regv->tipo_moneda == 'USD') {
      $viewmon = " DOLARES";
    } else {
      $viewmon = " SOLES";
    }
    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->total, "CON"));
    $pdf->addCadreTVAs($con_letra, $viewmon);
    $pdf->observSunat($regv->serienumero, $regv->estado, '', $datose->webconsul, $datose->nresolucion);

    //Mostramos el impuesto
    $pdf->addTVAs($regv->igv, $regv->total, $regv->neta, $regv->otros);
    $pdf->addCadreEurosFrancs($regv->igv, '');

    //Linea para guardar la factura en la carpeta facturas PDF
    $Factura = $pdf->Output($regv->serienumero . '.pdf', 'I');

    //$Factura=$pdf->Output('../doccobranzaPDF/'.$regv->serienumero.'.pdf','F');
    $Factura = $pdf->Output('C:/sfs/doccobranzaPDF/' . $regv->serienumero . '.pdf', 'F');

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>