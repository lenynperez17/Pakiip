<?php
//Activamos el almacenamiento en el buffer
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
            <meta charset="UTF-8">
        </head>

        <body onload="window.print();">
            <?php

            //Incluímos la clase Venta
            require_once "../modelos/Factura.php";
            require_once "../modelos/Notacd.php";
            $factura = new Factura();
            $notacd = new Notacd();
            $datos = $factura->datosemp($_SESSION['idempresa']);
            //En el objeto $rspta Obtenemos los valores devueltos del método ventacabecera del modelo
            $rspta = $notacd->cabecerancreditoBol($_GET["id"], $_SESSION['idempresa']);


            $tipodoc = $_GET['tipodoc'];
            if ($tipodoc == "01") {
                $rspta = $notacd->cabecerancreditoFac($_GET["id"], $_SESSION['idempresa']);
            } else {
                $rspta = $notacd->cabecerancreditoBol($_GET["id"], $_SESSION['idempresa']);
            }


            //Recorremos todos los valores obtenidos
            $reg = $rspta->fetch_object();
            $datose = $datos->fetch_object();

            ?>
            <!-- <div class="zona_impresion"> -->
            <!-- codigo imprimir -->
            <br>
            <table border="0" align="center" width="240px">
                <tr>
                    <td align="center">
                        <!-- Mostramos los datos de la empresa en el documento HTML -->
                        .::<strong>
                            <?php echo utf8_decode($datose->nombre_comercial) ?>
                        </strong>::.<br>
                        <strong> R.U.C.
                            <?php echo $datose->numero_ruc; ?>
                        </strong><br>
                        <?php echo utf8_decode($datose->domicilio_fiscal) . ' - ' . $datose->telefono1 . "-" . $datose->telefono2; ?><br>
                        <?php echo utf8_decode(strtolower($datose->correo)); ?><br>
                        <?php echo utf8_decode(strtolower($datose->web)); ?>

                    </td>

                <tr>
                    <td style="text-align: center;">--------------------------------</td>
                </tr>

                <tr>
                    <td align="center">
                        <strong> NOTA DE CRÉDITO ELECTRÓNICA </br>
                            <?php echo $reg->numerncd; ?>
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">--------------------------------</td>
                </tr>
                </tr>

                <tr>
                    <td align="left"><strong>Cliente:</strong> </br>
                        <?php echo $reg->cliente; ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>RUC:</strong> </br>
                        <?php echo $reg->numero_documento; ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>Dirección:</strong> </br>
                        <?php echo strtolower(($reg->domicilio)); ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>Comprobante original:</strong>
                        <?php echo $reg->nboleta; ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>Fecha de emisión:</strong>
                        <?php echo $reg->femision; ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>Fecha comprobante que se modifica:</strong>
                        <?php echo $reg->femisionbol; ?>
                    </td>
                </tr>

                <tr>
                    <td align="left"><strong>Moneda:</strong>
                        SOLES</td>
                </tr>

            </table>

            <br>

            <!-- Mostramos los detalles de la venta en el documento HTML -->
            <table border="0" width="220px" align="center" style="font-size: 14px;">
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

                //======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
                require_once "../modelos/Rutas.php";
                $rutas = new Rutas();
                $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
                $Prutas = $Rrutas->fetch_object();
                $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
                $data[0] = "";

                //===========PARA EXTRAER EL CODIGO HASH =============================
                if ($reg->estado == '5') {
                    $notaFirm = $reg->numero_ruc . "-" . $reg->codigo_nota . "-" . $reg->numerncd;
                    $sxe = new SimpleXMLElement($rutafirma . $notaFirm . '.xml', null, true);
                    $urn = $sxe->getNamespaces(true);
                    $sxe->registerXPathNamespace('ds', $urn['ds']);
                    $data = $sxe->xpath('//ds:DigestValue');
                } else {
                    $data[0] = "";
                }
                //==================== PARA IMAGEN DEL CODIGO HASH ================================================
//set it to writable location, a place for temp generated PNG files
                $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/generador-qr/temp' . DIRECTORY_SEPARATOR;
                //html PNG location prefix
                $PNG_WEB_DIR = 'temp/';
                include 'generador-qr/phpqrcode.php';

                //ofcourse we need rights to create temp dir
                if (!file_exists($PNG_TEMP_DIR)) {
                    mkdir($PNG_TEMP_DIR);
                }
                $filename = $PNG_TEMP_DIR . 'test.png';
                $dataTxt = $reg->numero_ruc . "|" . $reg->codigo_nota . "|" . $reg->serie . "|" . $reg->numeronota . "|" . $reg->igv . "|" . $reg->total . "|" . $reg->femision . "|" . $reg->femision . "|" . $reg->numerncd . "|";

                $errorCorrectionLevel = 'H';
                $matrixPointSize = '2';
                $filename = 'generador-qr/temp/test' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                //default data
                //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
                //display generated file
                $PNG_WEB_DIR . basename($filename);
                // //==================== PARA IMAGEN  ================================================
                $logoQr = $filename;
                $ext_logoQr = substr($filename, strpos($filename, '.'), -4);
                //$pdf->ImgQrN($logoQr, $ext_logoQr);
        
                $tipodoc = $_GET['tipodoc'];


                if ($tipodoc == "03") {
                    $rsptad = $notacd->detalleNotacreditoBol($_GET["id"], $_SESSION['idempresa'], $reg->nboleta);
                    //$rsptad = $notacd->detalleNotacredito($_GET["id"], $_SESSION['idempresa']);
                    $cantidad = 0;
                    while ($regd = $rsptad->fetch_object()) {
                        echo "<tr>";
                        echo "<td>" . $regd->cantidad . "</td>";
                        echo "<td>" . strtolower($regd->articulo) . "</td>";
                        echo "<td>" . $regd->precio_venta . "</td>";
                        echo "<td align='right'>" . $regd->subtotal . "</td>";
                        echo "</tr>";
                        $cantidad += $regd->cantidad;
                    }
                }

                if ($tipodoc == "01") {
                    $rsptad = $notacd->detalleNotacredito($_GET["id"], $_SESSION['idempresa'], $reg->nboleta);
                    //$rsptad = $notacd->detalleNotacredito($_GET["id"], $_SESSION['idempresa']);
                    $cantidad = 0;
                    while ($regd = $rsptad->fetch_object()) {
                        echo "<tr>";
                        echo "<td>" . $regd->cantidad . "</td>";
                        echo "<td>" . strtolower($regd->articulo) . "</td>";
                        echo "<td>" . $regd->precio_venta . "</td>";
                        echo "<td align='right'>" . $regd->subtotal . "</td>";
                        echo "</tr>";
                        $cantidad += $regd->cantidad;
                    }
                }

                ?>
            </table>

            <?php
            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtolower($V->ValorEnLetras($reg->total, "NUEVOS SOLES"));

            echo "<table border='0'  width='230px' align='center' style='font-size: 14px;'' >
    <tr><td>-----------------------------------------------------</td></tr>";
            echo "<tr><td></br><strong>Son: </strong>" . $con_letra . "</td></tr></table>";

            // Calcular Op. Gravada y Op. Exonerado según tipo de tributo
            $nombretigv = "0.00";
            $nombretexo = "0.00";
            $nombreinaf = "0.00";

            if (isset($reg->nombretrib)) {
                if ($reg->nombretrib == "IGV") {
                    $nombretigv = $reg->subtotal ?? "0.00";
                } else if ($reg->nombretrib == "EXO") {
                    $nombretexo = $reg->subtotal ?? "0.00";
                } else if ($reg->nombretrib == "INA") {
                    $nombreinaf = $reg->subtotal ?? "0.00";
                }
            } else {
                // Si no existe nombretrib, asumimos IGV
                $nombretigv = $reg->subtotal ?? ($reg->total - $reg->igv);
            }
            ?>
            <table border='0' width='230px' align="center">
                <tr>
                    <td colspan='5'><strong>Total descuento: </strong></td>
                    <td><?php echo number_format($reg->tdescuento ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. gravada: </strong></td>
                    <td><?php echo number_format($nombretigv, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. exonerado: </strong></td>
                    <td><?php echo number_format($nombretexo, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>OP. inafecto: </strong></td>
                    <td><?php echo number_format($nombreinaf, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>ICBPER: </strong></td>
                    <td><?php echo number_format($reg->icbper ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>I.G.V. 18.00: </strong></td>
                    <td><?php echo number_format($reg->igv, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>IMP. PAGADO: </strong></td>
                    <td><?php echo number_format($reg->ipagado ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td colspan='5'><strong>VUELTO: </strong></td>
                    <td><?php echo number_format($reg->saldo ?? 0, 2) ?></td>
                </tr>
            </table>


            <!-- Mostramos los totales de la venta en el documento HTML -->

            <table border='0' width='230px' align="center" style="font-size: 14px;">
                <tr>
                    <td align='right'><strong>TOTAL:
                            <?php echo $reg->total ?>
                        </strong></td>
                </tr><br>
                <tr>
                    <td><strong>Vendedor:
                            <?php echo $reg->vendedorsitio ?>
                        </strong></td>
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
                        <?php echo utf8_decode($datose->nombre_comercial) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">Lima - Perú</td>
                </tr>

            </table>
            <br>

            <div style="text-align: center;">
                <img src=<?php echo $logoQr; ?> width="150" height="150"><br>
                <label>Cód. HASH:
                    <?php echo $data[0]; ?>
                </label>
            </div>

            <!-- </div> -->
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