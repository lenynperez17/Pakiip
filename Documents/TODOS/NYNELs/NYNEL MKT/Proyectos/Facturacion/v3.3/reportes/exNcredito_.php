<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar ';
} else {
  if ($_SESSION['Ventas'] == 1) {

    //$tipodoc=$_GET["tipodoc"];

    //Incluímos el archivo Factura.php
    require('Factura.php');

    //Obtenemos los datos de la cabecera de la venta actual
    require_once "../modelos/Notacd.php";
    $ncredito = new Notacd();

    require_once "../modelos/Factura.php";
    $factura = new Factura();

    $tipodoc = $_GET['tipodoc'];


    if ($tipodoc == "01") {

      $rsptav = $ncredito->cabecerancreditoFac($_GET["id"]);
      $datos = $factura->datosemp();
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

      //===================PRIMERA COPIA ================================================================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
      $pdf->addSocieteNCD(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección  : ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" . "Email        : " . $datose->correo, $logo, $ext_logo);
      //
      $pdf->numNotac("$regv->numerncd", "$datose->numero_ruc");

      $pdf->temporaire("");


      //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
      $pdf->addClientAdresseNC(utf8_decode($regv->cliente), utf8_decode($regv->domicilio), $regv->femision, $regv->nfactura, utf8_decode($regv->femisionfac), $regv->observacion, utf8_decode($regv->vendedorsitio));

      //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
      $cols = array(
        "CODIGO" => 23,
        "DESCRIPCION" => 85,
        "CANTIDAD" => 22,
        "V.U." => 22,
        "IMPORTE" => 50
      );
      $pdf->addColsNC($cols);
      $cols = array(
        "CODIGO" => "L",
        "DESCRIPCION" => "L",
        "CANTIDAD" => "L",
        "V.U." => "L",
        "IMPORTE" => "C"
      );
      $pdf->addLineFormat($cols);
      //$pdf->addLineFormat($cols);

      //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
      $y = 51;

      //Obtenemos todos los detalles de la venta actual
      $rsptad = $ncredito->detalleNotacredito($_GET["id"]);

      while ($regd = $rsptad->fetch_object()) {
        $line = array(
          "CODIGO" => utf8_decode("$regd->codigo"),
          "DESCRIPCION" => utf8_decode("$regd->articulo"),
          "CANTIDAD" => "$regd->cantidad",
          "V.U." => "$regd->valor_unitario",
          "IMPORTE" => "$regd->valor_venta"
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
      if ($regv->estado == '5') {
        $notaFirm = $regv->numero_ruc . "-" . $regv->codigo_nota . "-" . $regv->numerncd;
        $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
        $urn = $sxe->getNamespaces(true);
        $sxe->registerXPathNamespace('ds', $urn['ds']);
        $data = $sxe->xpath('//ds:DigestValue');
      } else {
        $data[0] = "";
      }
      //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================

      // Calcular Op. Gravada y Op. Exonerado según tipo de tributo
      $nombretigv = "0.00";
      $nombretexo = "0.00";

      if (isset($regv->nombretrib)) {
          if ($regv->nombretrib == "IGV") {
              $nombretigv = $regv->subtotal ?? "0.00";
              $nombretexo = "0.00";
          } else if ($regv->nombretrib == "EXO") {
              $nombretigv = "0.00";
              $nombretexo = $regv->subtotal ?? "0.00";
          }
      } else {
          // Si no existe nombretrib, asumimos IGV
          $nombretigv = $regv->subtotal ?? ($regv->total - $regv->igv);
          $nombretexo = "0.00";
      }

      //Convertimos el total en letras
      require_once "Letras.php";
      $V = new EnLetras();
      $con_letra = strtoupper($V->ValorEnLetras($regv->total, "NUEVOS SOLES"));
      $pdf->addCadreTVAsNC("" . $con_letra);
      $pdf->observSunatNC("$regv->numerncd", "$regv->estado", $data[0], $datose->webconsul, $datose->nresolucion);

      //Mostramos el impuesto con 9 parámetros modernos
      $pdf->addTVAsNC(
          $regv->total,
          "S/ ",
          $regv->tdescuento ?? "0.00",
          $regv->ipagado ?? "0.00",
          $regv->saldo ?? "0.00",
          $regv->icbper ?? "0.00",
          $regv->igv,
          $nombretigv,
          $nombretexo
      );
      $pdf->addCadreEurosFrancsNC();




    } else { //SI ES BOLETA




      $rsptav = $ncredito->cabecerancreditoBol($_GET["id"]);
      $datos = $factura->datosemp($_SESSION['idempresa']);
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

      //===================PRIMERA COPIA ================================================================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
      $pdf->addSocieteNCD(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección  : ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" . "Email        : " . $datose->correo, $logo, $ext_logo);
      //
      $pdf->numNotac("$regv->numerncd", "$datose->numero_ruc");

      $pdf->temporaire("");


      //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
      $pdf->addClientAdresseNC(utf8_decode($regv->cliente), utf8_decode($regv->domicilio), $regv->femision, $regv->nboleta, utf8_decode($regv->femisionbol), $regv->observacion, utf8_decode($regv->vendedorsitio));

      //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
      $cols = array(
        "CODIGO" => 23,
        "DESCRIPCION" => 85,
        "CANTIDAD" => 22,
        "V.U." => 22,
        "IMPORTE" => 50
      );
      $pdf->addColsNC($cols);
      $cols = array(
        "CODIGO" => "L",
        "DESCRIPCION" => "L",
        "CANTIDAD" => "L",
        "V.U." => "L",
        "IMPORTE" => "C"
      );
      $pdf->addLineFormat($cols);
      //$pdf->addLineFormat($cols);

      //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
      $y = 51;

      //Obtenemos todos los detalles de la venta actual
      $rsptad = $ncredito->detalleNotacredito($_GET["id"]);

      while ($regd = $rsptad->fetch_object()) {
        $line = array(
          "CODIGO" => utf8_decode("$regd->codigo"),
          "DESCRIPCION" => utf8_decode("$regd->articulo"),
          "CANTIDAD" => "$regd->cantidad",
          "V.U." => "$regd->valor_unitario",
          "IMPORTE" => "$regd->valor_venta"
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
      if ($regv->estado == '5') {
        $notaFirm = $regv->numero_ruc . "-" . $regv->codigo_nota . "-" . $regv->numerncd;
        $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
        $urn = $sxe->getNamespaces(true);
        $sxe->registerXPathNamespace('ds', $urn['ds']);
        $data = $sxe->xpath('//ds:DigestValue');
      } else {
        $data[0] = "";
      }
      //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================

      // Calcular Op. Gravada y Op. Exonerado según tipo de tributo (Boleta)
      $nombretigvBol = "0.00";
      $nombretexoBol = "0.00";

      if (isset($regv->nombretrib)) {
          if ($regv->nombretrib == "IGV") {
              $nombretigvBol = $regv->subtotal ?? "0.00";
              $nombretexoBol = "0.00";
          } else if ($regv->nombretrib == "EXO") {
              $nombretigvBol = "0.00";
              $nombretexoBol = $regv->subtotal ?? "0.00";
          }
      } else {
          // Si no existe nombretrib, asumimos IGV
          $nombretigvBol = $regv->subtotal ?? ($regv->total - $regv->igv);
          $nombretexoBol = "0.00";
      }

      //Convertimos el total en letras
      require_once "Letras.php";
      $V = new EnLetras();
      $con_letra = strtoupper($V->ValorEnLetras($regv->total, "NUEVOS SOLES"));
      $pdf->addCadreTVAsNC("" . $con_letra);
      $pdf->observSunatNC("$regv->numerncd", "$regv->estado", $data[0], $datose->webconsul, $datose->nresolucion);

      //Mostramos el impuesto con 9 parámetros modernos (Boleta)
      $pdf->addTVAsNC(
          $regv->total,
          "S/ ",
          $regv->tdescuento ?? "0.00",
          $regv->ipagado ?? "0.00",
          $regv->saldo ?? "0.00",
          $regv->icbper ?? "0.00",
          $regv->igv,
          $nombretigvBol,
          $nombretexoBol
      );
      $pdf->addCadreEurosFrancsNC();



    }
    //===================PRIMERA COPIA ================================================================










    //===============SEGUNDA COPIA DE FACTURA=========================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
    $pdf->addSocieteNCD2(utf8_decode($datose->nombre_comercial), utf8_decode("Dirección  : ") . utf8_decode($datose->domicilio_fiscal) . "\n" . utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" . "Email        : " . $datose->correo, $logo, $ext_logo);
    //
    $pdf->numNotac2("$regv->numerncd", "$datose->numero_ruc");
    $pdf->temporaire("");
    //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
    $pdf->addClientAdresseNC2(utf8_decode($regv->cliente), utf8_decode($regv->domicilio), $regv->femision, $regv->nboleta, utf8_decode($regv->femisionbol), $regv->observacion, utf8_decode($regv->vendedorsitio));
    //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
    $cols = array(
      "CODIGO" => 23,
      "DESCRIPCION" => 85,
      "CANTIDAD" => 22,
      "V.U." => 22,
      "IMPORTE" => 50
    );
    $pdf->addColsNC2($cols);
    $cols = array(
      "CODIGO" => "L",
      "DESCRIPCION" => "L",
      "CANTIDAD" => "L",
      "V.U." => "L",
      "IMPORTE" => "C"
    );
    $pdf->addLineFormat($cols);
    //$pdf->addLineFormat($cols);

    $y = 200; //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
//Obtenemos todos los detalles de la venta actual
    $rsptad = $ncredito->detalleNotacredito($_GET["id"]);
    while ($regd = $rsptad->fetch_object()) {
      $line = array(
        "CODIGO" => utf8_decode("$regd->codigo"),
        "DESCRIPCION" => utf8_decode("$regd->articulo"),
        "CANTIDAD" => "$regd->cantidad",
        "V.U." => "$regd->valor_unitario",
        "IMPORTE" => "$regd->valor_venta"
      );
      $size = $pdf->addLine2NC($y, $line);
      $y += $size + 2;
    }
    //Convertimos el total en letras
    require_once "Letras.php";
    $V = new EnLetras();
    $con_letra = strtoupper($V->ValorEnLetras($regv->total, "NUEVOS SOLES"));
    $pdf->addCadreTVAsNC2("" . $con_letra);
    $pdf->observSunatNC2("$regv->numerncd", "$regv->estado", $data[0], $datose->webconsul, $datose->nresolucion);

    //Mostramos el impuesto con 9 parámetros modernos (segunda copia)
    $pdf->addTVAsNC2(
        $regv->total,
        "S/ ",
        $regv->tdescuento ?? "0.00",
        $regv->ipagado ?? "0.00",
        $regv->saldo ?? "0.00",
        $regv->icbper ?? "0.00",
        $regv->igv,
        $tipodoc == "01" ? $nombretigv : $nombretigvBol,
        $tipodoc == "01" ? $nombretexo : $nombretexoBol
    );
    $pdf->addCadreEurosFrancsNC2();


    //Linea para guardar la notas en la carpeta notasPDF
    $Factura = $pdf->Output($regv->numerncd . '.pdf', 'I');
    $Factura = $pdf->Output('../notasPDF/' . $regv->numerncd . '.pdf', 'F');


    $pdf->Output('Reporte de Venta', 'I');


  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>
