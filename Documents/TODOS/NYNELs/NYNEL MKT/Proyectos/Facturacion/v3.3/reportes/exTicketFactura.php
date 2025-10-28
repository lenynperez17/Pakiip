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

            <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
            <link href="../public/css/ticket.css" rel="stylesheet" type="text/css">
            <meta name="viewport" content="width=device-width, initial" />

        </head>

        <body onload="window.print();">
            <?php

            //Incluímos la clase Venta
            require_once "../modelos/Factura.php";
            $factura = new Factura();


            require_once "../modelos/Rutas.php";
            $rutas = new Rutas();
            $Rrutas = $rutas->mostrar2("1");
            $Prutas = $Rrutas->fetch_object();
            $rutalogo = $Prutas->rutalogo;

            $datos = $factura->datosemp($_SESSION['idempresa']);
            $datose = $datos->fetch_object();
            //En el objeto $rspta Obtenemos los valores devueltos del método ventacabecera del modelo
            $rspta = $factura->ventacabecera($_GET["id"], $_SESSION['idempresa']);
            //$cuotas = $rspta->cuotas;
    
            $logo = "../files/logo/" . $datose->logo;
            $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -5);

            $reg = $rspta->fetch_object();
            $cuotas = explode(",", $reg->cuotas);
            // foreach ($cuotas as $cuota) {
//     $datos_cuota = explode("|", $cuota);
//     $ncuota = $datos_cuota[0];
//     $montocuota = $datos_cuota[1];
//     $fechacuota = $datos_cuota[2];
//     //aqui podrias imprimir o utilizar los datos de las cuotas como quieras
// }
            if ($reg->nombretrib == "IGV") {
                $nombretigv = $reg->subtotal;
                $nombretexo = "0.00";
            } else if ($reg->nombretrib == "EXO") {
                $nombretigv = "0.00";
                $nombretexo = $reg->subtotal;
            } else {
                // En caso de otros valores, puedes establecer un valor predeterminado o manejarlo como desees
                $nombretigv = "0.00";
                $nombretexo = "0.00";
            }


            ?>

            <table border="0" align="center" style="font-size: 8px; width: 230px;">

                <tbody>

                    <tr>
                        <td align="center">

                            <img src="<?php echo $logo; ?>" width="70">
                        </td>
                    </tr>

                    <tr align="center">
                        <td style="font-size: 8px">
                            .::<strong>
                                <?php echo utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)) ?>
                            </strong>::.
                        </td>
                    </tr>

                    <tr align="center">
                        <td>
                            <?php echo $datose->textolibre; ?>
                        </td>
                    </tr>

                    <tr align="center">
                        <td>
                            <strong> R.U.C.
                                <?php echo $datose->numero_ruc; ?>
                            </strong>
                        </td>
                    </tr>

                    <tr align="center">
                        <td>
                            <?php echo utf8_decode($datose->domicilio_fiscal) . ' - ' . $datose->telefono1 . "-" . $datose->telefono2; ?>
                        </td>
                    </tr>

                    <tr align="center">
                        <td>
                            <?php echo utf8_decode(strtolower($datose->correo)); ?>
                        </td>
                    </tr>

                    <tr align="center">
                        <td>
                            <?php echo utf8_decode(strtolower($datose->web)); ?>
                        </td>
                    </tr>


                    <tr>
                        <td style="text-align: center;">-------------------------------------------------------</td>
                    </tr>

                    <tr>
                        <td align="center">
                            <strong> FACTURA DE VENTA
                                ELECTRÓNICA <br>
                                <?php echo $reg->numeracion_08; ?>
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <td style="text-align: center;">-------------------------------------------------------</td>
                    </tr>
                </tbody>
            </table>

            <table style="font-size: 8px;" border="0" align="center" width="230px">
                <tbody>
                    <tr>
                        <td align="left"><strong>Cliente: </strong>
                            <?php echo $reg->cliente; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Documento: </strong>RUC -
                            <?php echo $reg->numero_documento; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Dirección: </strong>
                            <?php echo utf8_decode(strtoupper($reg->direccion)); ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Fec. de emision: </strong>
                            <?php echo $reg->fecha; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Fec. de vencimiento: </strong>
                            <?php echo $reg->fechavenc; ?>
                        </td>
                    </tr>


                    <tr>
                        <td align="left"><strong>Moneda: </strong>
                            <?php
                                // Mostrar moneda dinámica según tipo_moneda de la base de datos
                                echo ($reg->tipo_moneda == "PEN") ? "SOLES" :
                                     (($reg->tipo_moneda == "USD") ? "DÓLARES AMERICANOS" :
                                     (($reg->tipo_moneda == "EUR") ? "EUROS" : $reg->tipo_moneda));
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Vendedor: </strong>
                            <?php echo $reg->vendedorsitio; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Tipo de pago: </strong>
                            <?php echo $reg->tipopago; ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="left"><strong>Obs: </strong>
                            <?php echo $reg->descripcion_leyenda_31_2; ?>
                        </td>
                    </tr>


                </tbody>
            </table>

            <table border="0" align="center" width="220px" style="font-size: 4px">

                <?php


                if ($reg->tipopago == "Contado") {

                    //echo "<tr><td colspan='6'><strong>Métodos de pago:</strong></td></tr>";
        

                    echo "<tr>"; // Abrir fila de los nombres de los tipos de pago
        
                    if ($reg->yape > 0) {
                        echo "<td><strong>Yape</strong></td>";
                    }

                    if ($reg->visa > 0) {
                        echo "<td><strong>Visa</strong></td>";
                    }

                    if ($reg->efectivo > 0) {
                        echo "<td><strong>Efectivo</strong></td>";
                    }

                    if ($reg->plin > 0) {
                        echo "<td><strong>Plin</strong></td>";
                    }

                    if ($reg->masterC > 0) {
                        echo "<td><strong>MasterC</strong></td>";
                    }

                    if ($reg->dep > 0) {
                        echo "<td><strong>Dep.</strong></td>";
                    }

                    echo "</tr>"; // Cerrar fila de los nombres
        
                    echo "<tr>"; // Abrir fila de los montos
        
                    if ($reg->yape > 0) {
                        echo "<td> $reg->yape </td>";
                    }

                    if ($reg->visa > 0) {
                        echo "<td> $reg->visa </td>";
                    }

                    if ($reg->efectivo > 0) {
                        echo "<td> $reg->efectivo </td>";
                    }

                    if ($reg->plin > 0) {
                        echo "<td> $reg->plin </td>";
                    }

                    if ($reg->masterC > 0) {
                        echo "<td> $reg->masterC </td>";
                    }

                    if ($reg->dep > 0) {
                        echo "<td> $reg->dep </td>";
                    }

                    echo "</tr>"; // Cerrar fila de los montos
        
                } elseif ($reg->tipopago == "Credito") {
                    echo "<tr><td>";
                    $cuotas = explode(",", $reg->cuotas);
                    echo "<table>";
                    echo "<tr><th>Nro Cuota</th><th>Monto</th><th>Fecha</th></tr>";
                    foreach ($cuotas as $c) {
                        $detalles = explode("|", $c);
                        echo "<tr>";
                        echo "<td>" . $detalles[0] . "</td>";
                        echo "<td>" . $detalles[1] . "</td>";
                        echo "<td>" . $detalles[2] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "</td></tr>";
                }
                ?>
            </table>

            <!-- Mostramos los detalles de la venta en el documento HTML -->
            <table border="0" align="center" width="230px" style="font-size: 8px">
                <tr>
                    <td colspan="5">-----------------------------------------------------</td>
                </tr>
                <tr>
                    <td>Cant.</td>
                    <td align="left">Producto</td>
                    <td>V.u.</td>
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
                //processing form input
                //remember to sanitize user input in real-life solution !!!
                $dataTxt = $reg->numero_ruc . "|" . $reg->tipo_documento_07 . "|" . $reg->serie . "|" . $reg->numerofac . "|" . $reg->sumatoria_igv_22_1 . "|" . $reg->importe_total_venta_27 . "|" . $reg->fecha2 . "|" . $reg->tipo_documento . "|" . $reg->numero_documento . "|";
                $errorCorrectionLevel = 'H';
                $matrixPointSize = '2';
                // user data
                $filename = 'generador-qr/temp/test' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                $PNG_WEB_DIR . basename($filename);
                // //==================== PARA IMAGEN  ================================================
                $logoQr = $filename;
                //$logoQr = "generador-qr/temp/".$datose->logo;
                $ext_logoQr = substr($filename, strpos($filename, '.'), -4);
                //ImgQrT($logoQr, $ext_logoQr);
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
        
                $rsptad = $factura->ventadetalle($_GET["id"]);
                $cantidad = 0;
                while ($regd = $rsptad->fetch_object()) {

                    if ($regd->nombretribu == "IGV") {
                        $pv = $regd->valor_uni_item_14;
                        $subt = $regd->subtotal;
                    } else {
                        $pv = $regd->precio;
                        $subt = $regd->subtotal2;

                    }
                    echo "<tr>";
                    echo "<td>" . $regd->cantidad_item_12 . "</td>";
                    echo "<td>" . strtolower($regd->articulo) . "</td>";
                    echo "<td>" . number_format($pv, 2) . "</td>";
                    echo "<td align='right'>" . $subt . "</td>";
                    echo "</tr>";
                    $cantidad += $regd->cantidad_item_12;
                }
                ?>
                <tr>
                    <td colspan="5">-----------------------------------------------------</td>
                </tr>
            </table>

            <table border='0' width='230px' style='font-size: 8px;' align="center">
                <tr>
                    <td colspan='5'><strong>Descuento</strong></td>
                    <td>:
                        <?php echo $reg->tdescuento ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Gravada</strong></td>
                    <td>:
                        <?php echo $nombretigv; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Exonerado</strong></td>
                    <td>:
                        <?php echo $nombretexo; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Inafecto</strong></td>
                    <td>: 0.00</td>
                </tr>
                <tr>
                    <td colspan='5'><strong>I.G.V.</strong></td>
                    <td>:
                        <?php echo $reg->sumatoria_igv_22_1 ?>
                    </td>
                </tr>
                <!-- <tr><td colspan='5'><strong>Otros</strong></td><td>: <?php echo $reg->otroscargos ?></td></tr> -->

                <tr>
                    <td colspan='5'><strong>Imp. Pagado</strong></td>
                    <td>:
                        <?php echo $reg->ipagado ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Vuelto</strong></td>
                    <td>:
                        <?php echo $reg->saldo ?>
                    </td>
                </tr>

                <tr>
                    <td colspan='5'><strong>Importe a pagar</strong></td>
                    <td>:
                        <?php echo $reg->importe_total_venta_27 ?>
                    </td>
                </tr>

                <tr>

            </table>

            <?php
            require_once "Letras.php";
            $V = new EnLetras();
            $con_letra = strtoupper($V->ValorEnLetras($reg->importe_total_venta_27, "NUEVOS SOLES"));

            echo "<table border='0'  width='220px' style='font-size: 12px' align='center' >
        <tr><td>-----------------------------------------------------</td></tr>";
            echo "<tr><td></br><strong>SON: </strong>" . $con_letra . "</td></tr></table>";
            ?>

            <!-- Mostramos los totales de la venta en el documento HTML -->

            <table border='0' width='230px' style='font-size: 8px' align="center">
                <br>
                <tr>
                    <td align="center"><img src="<?php echo $logoQr; ?>" width="60" height="60"> </td>
                </tr>

                <tr>
                    <td align="center">
                        <?php echo $reg->hashc;
                        ; ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">Representación impresa de la Factura de Venta Electrónica puede ser consultada en
                    <?php echo utf8_decode(strtolower($datose->webconsul)); ?>
                        </td>
                </tr>

                <tr>
                    <td align="center">::.GRACIAS POR SU COMPRA.::</td>
                </tr>

            </table>
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