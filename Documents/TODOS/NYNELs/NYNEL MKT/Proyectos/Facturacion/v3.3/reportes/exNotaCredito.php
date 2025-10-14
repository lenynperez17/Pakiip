<?php
/**
 * Reporte PDF: Nota de Crédito Electrónica
 * Compatible con sistema de facturación v3.3
 * Estándar SUNAT Perú
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

        // Incluir modelo NotaCredito
        require_once "../modelos/NotaCredito.php";
        $notacredito = new NotaCredito();

        // Obtener rutas
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2("1");
        $Prutas = $Rrutas->fetch_object();
        $rutasalidanc = $Prutas->salidafacturas; // Usar misma carpeta que facturas
        $rutalogo = $Prutas->rutalogo;

        // Obtener datos de la NC
        $rsptanc = $notacredito->notacreditocabecera($_GET["id"], $_SESSION['idempresa']);
        $datos = $notacredito->datosemp($_SESSION['idempresa']);

        // Obtener objeto con datos
        $regnc = $rsptanc->fetch_object();
        $datose = $datos->fetch_object();

        // Configurar logo
        $logo = $rutalogo . $datose->logo;
        $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);

        // Crear PDF
        $pdf = new PDF_Invoice('P', 'mm', 'A4');
        $pdf->AddPage();

        // Márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Datos de la empresa
        $pdf->addSocietenombre(htmlspecialchars_decode(utf8_decode($datose->nombre_comercial)), $datose->textolibre);

        $pdf->addSociete(
            utf8_decode("Teléfono: ") . $datose->telefono1 . " - " . $datose->telefono2,
            "Email: " . $datose->correo,
            htmlspecialchars_decode(utf8_decode("Dirección: ") . $datose->domicilio_fiscal),
            $logo,
            $ext_logo
        );

        // Número de NC y RUC
        $pdf->numFactura("$regnc->numeroserienota", "$datose->numero_ruc");

        // Marca de agua si está anulada
        if ($regnc->estado == 'Anulado') {
            $pdf->RotatedText($regnc->estado, 35, 190, 'ANULADO', 45);
        }

        $pdf->temporaire("");

        // Datos del cliente y comprobante afectado
        $fecha_nc = date('Y-m-d', strtotime($regnc->fecha));
        $hora_nc = date('H:i:s', strtotime($regnc->fecha));

        // Construir texto de comprobante afectado
        $tipo_comp_texto = ($regnc->tipo_doc_mod == '01') ? 'FACTURA' : 'BOLETA';
        $comp_afectado_texto = $tipo_comp_texto . ": " . $regnc->serie_numero;

        $pdf->addClientAdresse(
            $fecha_nc . "   /  Hora: " . $hora_nc,
            utf8_decode(htmlspecialchars_decode($regnc->cliente)),
            $regnc->numero_doc_ide,
            utf8_decode(htmlspecialchars_decode($regnc->direccion)),
            $regnc->estado,
            utf8_decode("COMPROBANTE AFECTADO: " . $comp_afectado_texto),
            utf8_decode("MOTIVO: " . $regnc->motivonota),
            "",
            "",
            $regnc->tipo_moneda,
            "",
            ""
        );

        // Columnas para detalle
        $cols = array(
            "CODIGO" => 23,
            "DESCRIPCION" => 78,
            "CANTIDAD" => 22,
            "V.U." => 25,
            "IGV" => 20,
            "SUBTOTAL" => 22
        );
        $pdf->addCols($cols);

        $cols = array(
            "CODIGO" => "L",
            "DESCRIPCION" => "L",
            "CANTIDAD" => "C",
            "V.U." => "R",
            "IGV" => "R",
            "SUBTOTAL" => "C"
        );
        $pdf->addLineFormat($cols);
        $pdf->addLineFormat($cols);

        // Posición Y inicial
        $y = 62;

        // Obtener detalle de items
        $rsptad = $notacredito->notacreditodetalle($_GET["id"]);

        while ($regd = $rsptad->fetch_object()) {
            $line = array(
                "CODIGO" => "$regd->codigo",
                "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")),
                "CANTIDAD" => "$regd->cantidad",
                "V.U." => number_format($regd->valor_unitario, 2),
                "IGV" => number_format($regd->igv, 2),
                "SUBTOTAL" => number_format($regd->valor_venta, 2)
            );
            $size = $pdf->addLine($y, $line);
            $y += $size + 2;
        }

        // Generar QR Code
        $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/generador-qr/temp' . DIRECTORY_SEPARATOR;
        $PNG_WEB_DIR = 'temp/';
        include 'generador-qr/phpqrcode.php';

        if (!file_exists($PNG_TEMP_DIR)) {
            mkdir($PNG_TEMP_DIR);
        }

        // Extraer serie y número
        $parts = explode('-', $regnc->numeroserienota);
        $serie = $parts[0];
        $numero = $parts[1];

        // Datos para QR según formato SUNAT
        $dataTxt = $datose->numero_ruc . "|07|" . $serie . "|" . $numero . "|" .
                   $regnc->sum_igv . "|" . $regnc->importe_total . "|" .
                   $fecha_nc . "|" . $regnc->tipo_doc_ide . "|" . $regnc->numero_doc_ide . "|";

        $errorCorrectionLevel = 'H';
        $matrixPointSize = '2';

        $filename = $PNG_TEMP_DIR . 'nc_' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        $logoQr = $filename;
        $ext_logoQr = substr($filename, strpos($filename, '.'), 0);
        $pdf->ImgQr($logoQr, $ext_logoQr);

        // Convertir total en letras
        $viewmon = ($regnc->tipo_moneda == 'USD') ? " DOLARES" : " SOLES";
        require_once "Letras.php";
        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetras($regnc->importe_total, "CON"));
        $pdf->addCadreTVAs($con_letra, $viewmon);

        // Observaciones SUNAT
        $pdf->observSunat(
            $regnc->numeroserienota,
            $regnc->estado,
            $regnc->hash_cpe,
            $datose->webconsul,
            $datose->nresolucion
        );

        // Totales
        $pdf->addTVAs(
            $regnc->sum_igv,
            $regnc->total_val_venta_og,
            "S/ ",
            "0.00",
            "0.00",
            "0.00",
            $regnc->icbper,
            "0.00"
        );
        $pdf->addCadreEurosFrancs($regnc->sum_igv, "IGV");

        // ========== SEGUNDA COPIA ==========

        $pdf->addSocietenombre2(htmlspecialchars_decode(utf8_decode($datose->nombre_comercial)), $datose->textolibre);

        $pdf->addSociete2(
            utf8_decode("Teléfono: ") . $datose->telefono1 . " - " . $datose->telefono2,
            "Email: " . $datose->correo,
            htmlspecialchars_decode(utf8_decode("Dirección: ") . $datose->domicilio_fiscal),
            $logo,
            $ext_logo
        );

        $pdf->numFactura2("$regnc->numeroserienota", "$datose->numero_ruc");
        $pdf->temporaire("");

        $pdf->addClientAdresse2(
            $fecha_nc . "   /  Hora: " . $hora_nc,
            utf8_decode(htmlspecialchars_decode($regnc->cliente)),
            $regnc->numero_doc_ide,
            utf8_decode(htmlspecialchars_decode($regnc->direccion)),
            $regnc->estado,
            utf8_decode("COMPROBANTE AFECTADO: " . $comp_afectado_texto),
            utf8_decode("MOTIVO: " . $regnc->motivonota),
            "",
            "",
            $regnc->tipo_moneda,
            "",
            ""
        );

        // Columnas segunda copia
        $cols = array(
            "CODIGO" => 23,
            "DESCRIPCION" => 78,
            "CANTIDAD" => 22,
            "V.U." => 25,
            "IGV" => 20,
            "SUBTOTAL" => 22
        );
        $pdf->addCols2($cols);

        $cols = array(
            "CODIGO" => "L",
            "DESCRIPCION" => "L",
            "CANTIDAD" => "C",
            "V.U." => "R",
            "IGV" => "R",
            "SUBTOTAL" => "C"
        );
        $pdf->addLineFormat2($cols);
        $pdf->addLineFormat2($cols);

        $y2 = 206;

        // Detalle segunda copia
        $rsptad = $notacredito->notacreditodetalle($_GET["id"]);

        while ($regd = $rsptad->fetch_object()) {
            $line = array(
                "CODIGO" => "$regd->codigo",
                "DESCRIPCION" => utf8_decode(htmlspecialchars_decode("$regd->articulo")),
                "CANTIDAD" => "$regd->cantidad",
                "V.U." => number_format($regd->valor_unitario, 2),
                "IGV" => number_format($regd->igv, 2),
                "SUBTOTAL" => number_format($regd->valor_venta, 2)
            );
            $size2 = $pdf->addLine2($y2, $line);
            $y2 += $size2 + 2;
        }

        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetras($regnc->importe_total, "CON"));
        $pdf->addCadreTVAs2($con_letra, $viewmon);

        $pdf->observSunat2(
            $regnc->numeroserienota,
            $regnc->estado,
            $regnc->hash_cpe,
            $datose->webconsul,
            $datose->nresolucion
        );

        $pdf->addTVAs2(
            $regnc->sum_igv,
            $regnc->total_val_venta_og,
            "S/ ",
            "0.00",
            "0.00",
            "0.00",
            $regnc->icbper,
            "0.00"
        );
        $pdf->addCadreEurosFrancs2($regnc->sum_igv, "IGV");

        // Salida del PDF
        $pdf->AutoPrint();
        $pdf->Output($regnc->numeroserienota . '.pdf', 'I');
        $pdf->Output($rutasalidanc . $regnc->numeroserienota . '.pdf', 'F');

    } else {
        echo 'No tiene permiso para visualizar el reporte';
    }
}
ob_end_flush();
?>
