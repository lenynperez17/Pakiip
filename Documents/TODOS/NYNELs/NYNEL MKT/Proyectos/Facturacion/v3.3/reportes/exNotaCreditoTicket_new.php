<?php
/**
 * Reporte TICKET HTML: Nota de Crédito Electrónica
 * Formato de impresión térmica (230px)
 * Compatible con nuevo modelo NotaCredito.php
 */

// Activar almacenamiento en buffer
ob_start();
if (strlen(session_id()) < 1)
    session_start();

if (!isset($_SESSION["nombre"])) {
    echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
    if ($_SESSION['Ventas'] == 1) {
        ?>
        <html>
        <head>
            <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
            <link href="../public/css/ticket.css" rel="stylesheet" type="text/css">
            <meta name="viewport" content="width=device-width, initial" />
        </head>

        <body onload="window.print();">
            <?php
            // Incluir modelo NotaCredito (nuevo)
            require_once "../modelos/NotaCredito.php";
            require_once "../modelos/Factura.php";

            $notacredito = new NotaCredito();
            $factura = new Factura();

            // Obtener datos
            $datos = $notacredito->datosemp($_SESSION['idempresa']);
            $datose = $datos->fetch_object();

            $tipodoc = $_GET['tipodoc']; // '01' = Factura, '03' = Boleta
            $rspta = $notacredito->notacreditocabecera($_GET["id"], $_SESSION['idempresa']);
            $reg = $rspta->fetch_object();

            // Obtener rutas
            require_once "../modelos/Rutas.php";
            $rutas = new Rutas();
            $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
            $Prutas = $Rrutas->fetch_object();
            $rutafirma = $Prutas->rutafirma;
            ?>

            <br>
            <table border="0" align="center" width="230px" style="font-size: 14px;">
                <tr>
                    <td align="center">
                        .::<strong><?php echo utf8_decode($datose->nombre_comercial) ?></strong>::.<br>
                        <strong> R.U.C. <?php echo $datose->numero_ruc; ?></strong><br>
                        <?php echo utf8_decode($datose->domicilio_fiscal) . ' - ' . $datose->telefono1 . "-" . $datose->telefono2; ?><br>
                        <?php echo utf8_decode(strtolower($datose->correo)); ?><br>
                        <?php echo utf8_decode(strtolower($datose->web)); ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">--------------------------------</td>
                </tr>
                <tr>
                    <td align="center">
                        <strong> NOTA DE CRÉDITO ELECTRÓNICA </br>
                            <?php echo $reg->numeroserienota; ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">--------------------------------</td>
                </tr>
                <tr>
                    <td align="left"><strong>Cliente:</strong> </br>
                        <?php echo $reg->razon_social; ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>RUC:</strong> </br>
                        <?php echo $reg->numero_documento; ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Dirección:</strong> </br>
                        <?php echo strtolower($reg->domicilio_fiscal); ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Comprobante original:</strong>
                        <?php echo $reg->serie_numero; ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Fecha de emisión:</strong>
                        <?php echo date('d-m-Y', strtotime($reg->fecha)); ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Fecha comprobante que se modifica:</strong>
                        <?php echo date('d-m-Y', strtotime($reg->fechacomprobante)); ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Moneda:</strong>
                        <?php echo ($reg->tipo_moneda == 'USD') ? 'DÓLARES' : 'SOLES'; ?>
                    </td>
                </tr>
                <tr>
                    <td align="left"><strong>Motivo:</strong> </br>
                        <?php echo utf8_decode($reg->motivonota); ?>
                    </td>
                </tr>
            </table>
            <br>

            <!-- Detalle de productos -->
            <table border="0" width="230px" align="center" style="font-size: 14px;">
                <tr>
                    <td>Cant.</td>
                    <td align="left">Producto</td>
                    <td>P.u.</td>
                    <td>Importe</td>
                </tr>
                <tr>
                    <td colspan="5">-----------------------------------------------------</td>
                </tr>

                <?php
                // Generar QR Code
                $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/generador-qr/temp' . DIRECTORY_SEPARATOR;
                $PNG_WEB_DIR = 'temp/';
                include 'generador-qr/phpqrcode.php';

                if (!file_exists($PNG_TEMP_DIR)) {
                    mkdir($PNG_TEMP_DIR);
                }

                // Extraer serie y número
                $parts = explode('-', $reg->numeroserienota);
                $serie = $parts[0];
                $numero = $parts[1];

                // Datos para QR según formato SUNAT para NC
                $dataTxt = $datose->numero_ruc . "|07|" . $serie . "|" . $numero . "|" .
                           $reg->sum_igv . "|" . $reg->importe_total . "|" .
                           date('Y-m-d', strtotime($reg->fecha)) . "|" . $reg->tipo_documento . "|" .
                           $reg->numero_documento . "|";

                $errorCorrectionLevel = 'H';
                $matrixPointSize = '2';
                $filename = 'generador-qr/temp/nc_' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

                $logoQr = $filename;

                // Obtener hash si existe
                $data[0] = "";
                if ($reg->estado == '5') {
                    $notaFirm = $datose->numero_ruc . "-07-" . $reg->numeroserienota;
                    if (file_exists($rutafirma . $notaFirm . '.xml')) {
                        $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
                        $urn = $sxe->getNamespaces(true);
                        $sxe->registerXPathNamespace('ds', $urn['ds']);
                        $data = $sxe->xpath('//ds:DigestValue');
                    }
                }

                // Obtener detalle de items
                $rsptad = $notacredito->notacreditodetalle($_GET["id"]);
                $cantidad_total = 0;

                while ($regd = $rsptad->fetch_object()) {
                    echo "<tr>";
                    echo "<td>" . $regd->cantidad . "</td>";
                    echo "<td>" . strtolower($regd->articulo) . "</td>";
                    echo "<td>" . number_format($regd->valor_unitario, 2) . "</td>";
                    echo "<td align='right'>" . number_format($regd->valor_venta, 2) . "</td>";
                    echo "</tr>";
                    $cantidad_total += $regd->cantidad;
                }
                ?>
            </table>

            <?php
            // Convertir total en letras
            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtolower($V->ValorEnLetras($reg->importe_total, "NUEVOS SOLES"));

            echo "<table border='0' width='230px' align='center' style='font-size: 14px;'>
            <tr><td>-----------------------------------------------------</td></tr>";
            echo "<tr><td></br><strong>Son: </strong>" . $con_letra . "</td></tr></table>";
            ?>

            <table border='0' width='230px' align="center" style="font-size: 14px;">
                <tr>
                    <td colspan='5'><strong>Total descuento: </strong></td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. gravada: </strong></td>
                    <td><?php echo number_format($reg->total_val_venta_og, 2); ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. exonerado: </strong></td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. inafecto: </strong></td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td colspan='5'><strong>I.G.V. 18.00: </strong></td>
                    <td><?php echo number_format($reg->sum_igv, 2); ?></td>
                </tr>
            </table>

            <!-- Totales -->
            <table border='0' width='230px' align="center" style="font-size: 14px;">
                <tr>
                    <td align='right'><strong>TOTAL: <?php echo number_format($reg->importe_total, 2); ?></strong></td>
                </tr><br>
                <tr>
                    <td><strong>Vendedor: <?php echo $reg->vendedorsitio; ?></strong></td>
                </tr>
                <tr>
                    <td colspan="5">================================</td>
                </tr>
                <tr>
                    <td colspan="5" align="center">
                        <p1>GRACIAS POR SU PREFERENCIA</p1>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">
                        <?php echo utf8_decode($datose->nombre_comercial); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">Lima - Perú</td>
                </tr>
            </table>
            <br>

            <div style="text-align: center;">
                <img src="<?php echo $logoQr; ?>" width="150" height="150"><br>
                <label>Cód. HASH: <?php echo $data[0]; ?></label>
            </div>

            <p>&nbsp;</p>
        </body>
        </html>

        <?php
    } else {
        echo 'No tiene permiso para visualizar el reporte';
    }
}
ob_end_flush();
?>
