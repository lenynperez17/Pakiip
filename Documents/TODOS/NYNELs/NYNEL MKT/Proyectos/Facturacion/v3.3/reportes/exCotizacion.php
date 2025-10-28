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
    require('Cotizacion.php');

    //Obtenemos los datos de la cabecera de la venta actuall
    require_once "../modelos/Cotizacion.php";
    $cotizacion = new Cotizacion();
    $rsptav = $cotizacion->ventacabecera($_GET["id"], $_SESSION['idempresa']);
    $datos = $cotizacion->datosemp($_SESSION['idempresa']);
    //Recorremos todos los valores obtenidos
    $regv = $rsptav->fetch_object();

    $datose = $datos->fetch_object();

    $logo = "../files/logo/" . $datose->logo;
    $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

    // $logo = "";
// $ext_logo = "";

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

    $pdf->addSocietedireccion(htmlspecialchars_decode(utf8_decode("Direción  : ") . $datose->domicilio_fiscal)); //Nuevo

    $pdf->numCotizacion("$regv->serienota", "$datose->numero_ruc");

    $pdf->RotatedText($regv->estado, 35, 190, 'ANULADO', 45);
    $pdf->temporaire("");

    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresse($regv->fecha . "   /  Hora: " . $regv->hora . "          Fecha Validez        :    " . $regv->fechavalidez, utf8_decode(htmlspecialchars_decode($regv->cliente)), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedor), $regv->tipocotizacion, utf8_decode($regv->moneda), $regv->nrofactura);


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
    $rsptad = $cotizacion->ventadetalle($_GET["id"], $regv->tipocotizacion);



    while ($regd = $rsptad->fetch_object()) {

      // if ($regv->tipocotizacion=='producto') {
      // $um=$regd->nombreum;
      //        }else{
      // $um='';
      //        }

      $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo"),
        "CANTIDAD" => "$regd->cantidad" . " " . $regd->nombreum,
        "PRECIO" => "$regd->precio",
        "SUBTOTAL" => "$regd->subtotal"
      );
      $size = $pdf->addLine($y, $line);
      $y += $size + 2;
    }


    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->total, "CON"));
    $pdf->addCadreTVAs("" . $con_letra);
    $pdf->observSunat($regv->serienota, $regv->estado, $regv->observacion, $datose->webconsul, $datose->nresolucion);


    $pdf->numerocuentas($datose->banco1, $datose->cuenta1, $datose->banco2, $datose->cuenta2, $datose->banco3, $datose->cuenta3, $datose->banco4, $datose->cuenta4, $datose->cuentacci1, $datose->cuentacci2, $datose->cuentacci3, $datose->cuentacci4);


    //Mostramos el impuesto
    $pdf->addTVAs($regv->impuesto, $regv->total, "S/ ", 0);
    $pdf->addCadreEurosFrancs($regv->impuesto, '', $regv->moneda);
    $pdf->tipocambio($regv->tipocambio, $regv->conversion);


    $pdf->AutoPrint();
    //Linea para guardar la factura en la carpeta facturas PDF
    $Factura = $pdf->Output($regv->serienota . '.pdf', 'I');
    //$Factura=$pdf->Output('../cotizacionPDF/'.$regv->serienota.'.pdf','F');
    $Factura = $pdf->Output('C:/sfs/cotizacionPDF/' . $regv->serienota . '.pdf', 'F');

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>