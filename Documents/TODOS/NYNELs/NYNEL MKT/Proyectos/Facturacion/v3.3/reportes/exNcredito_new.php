<?php
/**
 * Reporte PDF: Nota de Crédito Electrónica
 * Compatible con nuevo sistema v3.3
 * Formato idéntico al PDF original de facturas/boletas
 */

// Activar almacenamiento en buffer
ob_start();
if (strlen(session_id()) < 1)
    session_start();

if (!isset($_SESSION["nombre"])) {
    echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
    if ($_SESSION['Ventas'] == 1) {

        // Incluir clase base FPDF
        require('Factura.php');

        // Incluir modelo NotaCredito (nuevo)
        require_once "../modelos/NotaCredito.php";
        $ncredito = new NotaCredito();

        // Obtener tipo de documento afectado
        $tipodoc = $_GET['tipodoc']; // '01' = Factura, '03' = Boleta

        // Obtener datos de la empresa
        $datos = $ncredito->datosemp($_SESSION['idempresa']);
        $datose = $datos->fetch_object();

        // Configurar logo
        $logo = "../files/logo/" . $datose->logo;
        $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

        if ($tipodoc == "01") {
            // ========== NOTA DE CRÉDITO PARA FACTURA ==========

            $rsptav = $ncredito->notacreditocabecera($_GET["id"], $_SESSION['idempresa']);
            $regv = $rsptav->fetch_object();

            // Configurar PDF
            $pdf = new PDF_Invoice('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);

            // ========== PRIMERA COPIA ==========

            // Datos de la empresa
            $pdf->addSocieteNCD(
                utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),
                utf8_decode(htmlspecialchars_decode("Dirección  : ")) . utf8_decode(htmlspecialchars_decode($datose->domicilio_fiscal)) . "\n" .
                utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" .
                "Email        : " . $datose->correo,
                $logo,
                $ext_logo
            );

            // Número de NC y RUC
            $pdf->numNotac("$regv->numeroserienota", "$datose->numero_ruc");

            // Marca de agua si está anulada
            if ($regv->estado == 'Anulado') {
                $pdf->RotatedText($regv->estado, 35, 190, 'ANULADO - DADO DE BAJA', 45);
            }
            $pdf->temporaire("");

            // Datos del cliente y comprobante afectado
            $fecha_nc = date('d-m-Y', strtotime($regv->fecha));
            $fecha_factura = ''; // Necesitamos obtener la fecha de la factura afectada

            $pdf->addClientAdresseNC(
                utf8_decode(htmlspecialchars_decode($regv->razon_social)),
                utf8_decode(htmlspecialchars_decode($regv->domicilio_fiscal)),
                $fecha_nc,
                $regv->serie_numero,
                $fecha_factura,
                utf8_decode(htmlspecialchars_decode($regv->motivonota)),
                utf8_decode($regv->vendedorsitio),
                $regv->numero_documento,
                $regv->tipo_moneda,
                '' // tmonedafac
            );

            // Columnas para detalle
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

            // Posición Y inicial
            $y = 51;

            // Obtener detalle de items
            $rsptad = $ncredito->notacreditodetalle($_GET["id"]);

            while ($regd = $rsptad->fetch_object()) {
                $line = array(
                    "CODIGO" => utf8_decode(htmlspecialchars_decode("$regd->codigo")),
                    "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")) . " - " . utf8_decode("$regd->descripitem"),
                    "CANTIDAD" => "$regd->cantidad",
                    "V.U." => "$regd->valor_unitario",
                    "IMPORTE" => "$regd->valor_venta"
                );
                $size = $pdf->addLine($y, $line);
                $y += $size + 2;
            }

            // Hash del documento (si existe)
            require_once "../modelos/Rutas.php";
            $rutas = new Rutas();
            $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
            $Prutas = $Rrutas->fetch_object();
            $rutafirma = $Prutas->rutafirma;
            $data[0] = "";

            if ($regv->estado == '5') {
                $notaFirm = $datose->numero_ruc . "-07-" . $regv->numeroserienota;
                if (file_exists($rutafirma . $notaFirm . '.xml')) {
                    $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
                    $urn = $sxe->getNamespaces(true);
                    $sxe->registerXPathNamespace('ds', $urn['ds']);
                    $data = $sxe->xpath('//ds:DigestValue');
                }
            }

            // Convertir total en letras
            $viewmon = ($regv->tipo_moneda == 'USD') ? " DOLARES" : " SOLES";
            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total, "CON"));
            $pdf->addCadreTVAsNC($con_letra, $viewmon);

            // Observaciones SUNAT
            $pdf->observSunatNC(
                $regv->numeroserienota,
                $regv->estado,
                $data[0],
                $datose->webconsul,
                $datose->nresolucion
            );

            // Totales
            $pdf->addTVAsNC($regv->sum_igv, $regv->importe_total, "");
            $pdf->addCadreEurosFrancsNC();

            // ========== SEGUNDA COPIA ==========

            $pdf->addSocieteNCD2(
                utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),
                utf8_decode(htmlspecialchars_decode("Dirección  : ")) . utf8_decode(htmlspecialchars_decode($datose->domicilio_fiscal)) . "\n" .
                utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" .
                "Email        : " . $datose->correo,
                $logo,
                $ext_logo
            );

            $pdf->numNotac2("$regv->numeroserienota", "$datose->numero_ruc");
            $pdf->temporaire("");

            $pdf->addClientAdresseNC2(
                utf8_decode(htmlspecialchars_decode($regv->razon_social)),
                utf8_decode(htmlspecialchars_decode($regv->domicilio_fiscal)),
                $fecha_nc,
                $regv->serie_numero,
                $fecha_factura,
                utf8_decode(htmlspecialchars_decode($regv->motivonota)),
                utf8_decode($regv->vendedorsitio),
                $regv->tipo_moneda,
                ''
            );

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

            $y = 200;

            // Detalle segunda copia
            $rsptad = $ncredito->notacreditodetalle($_GET["id"]);
            while ($regd = $rsptad->fetch_object()) {
                $line = array(
                    "CODIGO" => utf8_decode(htmlspecialchars_decode("$regd->codigo")),
                    "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")) . " - " . utf8_decode("$regd->descripitem"),
                    "CANTIDAD" => "$regd->cantidad",
                    "V.U." => "$regd->valor_unitario",
                    "IMPORTE" => "$regd->valor_venta"
                );
                $size = $pdf->addLine2NC($y, $line);
                $y += $size + 2;
            }

            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total, "CON"));
            $pdf->addCadreTVAsNC2($con_letra, $viewmon);

            $pdf->observSunatNC2(
                $regv->numeroserienota,
                $regv->estado,
                $data[0],
                $datose->webconsul,
                $datose->nresolucion
            );

            $pdf->addTVAsNC2($regv->sum_igv, $regv->importe_total, "");
            $pdf->addCadreEurosFrancsNC2();

        } else {
            // ========== NOTA DE CRÉDITO PARA BOLETA ==========

            $rsptavB = $ncredito->notacreditocabecera($_GET["id"], $_SESSION['idempresa']);
            $regv = $rsptavB->fetch_object();

            $pdf = new PDF_Invoice('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);

            // Primera copia
            $pdf->addSocieteNCD(
                utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),
                utf8_decode(htmlspecialchars_decode("Dirección  : ")) . utf8_decode(htmlspecialchars_decode($datose->domicilio_fiscal)) . "\n" .
                utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" .
                "Email        : " . $datose->correo,
                $logo,
                $ext_logo
            );

            $pdf->numNotac("$regv->numeroserienota", "$datose->numero_ruc");
            $pdf->temporaire("");

            $fecha_nc = date('d-m-Y', strtotime($regv->fecha));

            $pdf->addClientAdresseNC(
                utf8_decode(htmlspecialchars_decode($regv->razon_social)),
                utf8_decode(htmlspecialchars_decode($regv->domicilio_fiscal)),
                $fecha_nc,
                $regv->serie_numero,
                '',
                utf8_decode(htmlspecialchars_decode($regv->motivonota)),
                utf8_decode($regv->vendedorsitio),
                $regv->numero_documento
            );

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

            $y = 51;

            $rsptad = $ncredito->notacreditodetalle($_GET["id"]);
            while ($regd = $rsptad->fetch_object()) {
                $line = array(
                    "CODIGO" => utf8_decode(htmlspecialchars_decode("$regd->codigo")),
                    "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")),
                    "CANTIDAD" => "$regd->cantidad",
                    "V.U." => "$regd->valor_unitario",
                    "IMPORTE" => "$regd->valor_venta"
                );
                $size = $pdf->addLine($y, $line);
                $y += $size + 2;
            }

            require_once "../modelos/Rutas.php";
            $rutas = new Rutas();
            $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
            $Prutas = $Rrutas->fetch_object();
            $rutafirma = $Prutas->rutafirma;
            $data[0] = "";

            if ($regv->estado == '5') {
                $notaFirm = $datose->numero_ruc . "-07-" . $regv->numeroserienota;
                if (file_exists($rutafirma . $notaFirm . '.xml')) {
                    $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
                    $urn = $sxe->getNamespaces(true);
                    $sxe->registerXPathNamespace('ds', $urn['ds']);
                    $data = $sxe->xpath('//ds:DigestValue');
                }
            }

            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total, "NUEVOS SOLES"));
            $pdf->addCadreTVAsNC("" . $con_letra);
            $pdf->observSunatNC("$regv->numeroserienota", "$regv->estado", $data[0], $datose->webconsul, $datose->nresolucion);
            $pdf->addTVAsNC($regv->sum_igv, $regv->importe_total, "S/ ");
            $pdf->addCadreEurosFrancsNC();

            // Segunda copia
            $pdf->addSocieteNCD2(
                utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),
                utf8_decode(htmlspecialchars_decode("Dirección  : ")) . utf8_decode(htmlspecialchars_decode($datose->domicilio_fiscal)) . "\n" .
                utf8_decode("Teléfono   : ") . $datose->telefono1 . "\n" .
                "Email        : " . $datose->correo,
                $logo,
                $ext_logo
            );

            $pdf->numNotac2("$regv->numeroserienota", "$datose->numero_ruc");
            $pdf->temporaire("");

            $pdf->addClientAdresseNC2(
                utf8_decode(htmlspecialchars_decode($regv->razon_social)),
                utf8_decode(htmlspecialchars_decode($regv->domicilio_fiscal)),
                $fecha_nc,
                $regv->serie_numero,
                '',
                utf8_decode(htmlspecialchars_decode($regv->motivonota)),
                utf8_decode($regv->vendedorsitio)
            );

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

            $y = 200;

            $rsptad = $ncredito->notacreditodetalle($_GET["id"]);
            while ($regd = $rsptad->fetch_object()) {
                $line = array(
                    "CODIGO" => utf8_decode(htmlspecialchars_decode("$regd->codigo")),
                    "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")),
                    "CANTIDAD" => "$regd->cantidad",
                    "V.U." => "$regd->valor_unitario",
                    "IMPORTE" => "$regd->valor_venta"
                );
                $size = $pdf->addLine2NC($y, $line);
                $y += $size + 2;
            }

            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetras($regv->importe_total, "NUEVOS SOLES"));
            $pdf->addCadreTVAsNC2("" . $con_letra);
            $pdf->observSunatNC2("$regv->numeroserienota", "$regv->estado", $data[0], $datose->webconsul, $datose->nresolucion);
            $pdf->addTVAsNC2($regv->sum_igv, $regv->importe_total, "S/ ");
            $pdf->addCadreEurosFrancsNC2();
        }

        // Salida del PDF
        $pdf->Output($regv->numeroserienota . '.pdf', 'I');

        // Guardar en carpeta si existe ruta configurada
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        if (isset($Prutas->salidafacturas)) {
            $rutasalida = $Prutas->salidafacturas;
            $pdf->Output($rutasalida . $regv->numeroserienota . '.pdf', 'F');
        }

    } else {
        echo 'No tiene permiso para visualizar el reporte';
    }
}
ob_end_flush();
?>
