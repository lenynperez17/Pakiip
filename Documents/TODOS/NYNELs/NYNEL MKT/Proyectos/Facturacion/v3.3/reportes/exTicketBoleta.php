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

            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <link href="../public/css/ticket.css" rel="stylesheet" type="text/css">

        </head>

        <body onload="window.print();">
            <?php

            //Incluímos la clase Venta
            require_once "../modelos/Boleta.php";
            require_once "../modelos/Factura.php";
            //Instanciamos a la clase con el objeto venta
            $boleta = new Boleta();
            $factura = new Factura();
            $datos = $factura->datosempImpresiones($_SESSION['idempresa']);
            //$datose = $datos->fetch_object();
    
            //En el objeto $rspta Obtenemos los valores devueltos del método ventacabecera del modelo
            $rspta = $boleta->ventacabecera($_GET["id"], $_SESSION['idempresa']);




            //Recorremos todos los valores obtenidos
            $reg = $rspta->fetch_object();
            $datose = $datos->fetch_object();
            $cuotas = explode(",", $reg->cuotas);

            $logo = "../files\logo\\" . $datose->logo;

            //$logo = $datose->rutalogo.$datose->logo;
    
            $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);


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
            <!-- <div class="zona_impresion"> -->
            <!-- codigo imprimir -->
            <br>
            <table border="0" align="center" width="230px">
                <tbody>
                    <tr>
                        <td align="center">
                            <img src="<?php echo $logo; ?>" width="100">
                        </td>
                    </tr>
                    <tr align="center">
                        <td style="font-size: 14px">
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
                            <strong> BOLETA DE VENTA
                                ELECTRÓNICA <br>
                                <?php echo $reg->numeracion_07; ?>
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <td style="text-align: center;">-------------------------------------------------------</td>
                    </tr>
                </tbody>
            </table>


            <table border="0" align="center" width="230px">
                <tbody>
                    <tr align="left">
                        <td><strong>Cliente:</strong>
                            <?php echo $reg->cliente; ?>
                        </td>
                    </tr>

                    <tr align="left">
                        <td><strong>Documento:</strong> DNI -
                            <?php echo $reg->numero_documento; ?>
                        </td>
                    </tr>

                    <tr align="left">
                        <td><strong>Dirección:</strong>
                            <?php echo $reg->direccion; ?>
                        </td>
                    </tr>

                    <tr align="left">
                        <td><strong>Fecha de emisión:</strong>
                            <?php echo $reg->fecha . " / " . $reg->hora; ?>
                        </td>
                    </tr>

                    <!--  <tr>
        <td ><strong>Fecha de Vcto:</strong> 
         <?php echo "."; ?></td>
    </tr> -->

                    <tr align="left">
                        <td><strong>Moneda:</strong>
                            <?php
                                // Mostrar moneda dinámica según tipo_moneda de la base de datos
                                echo ($reg->tipo_moneda == "PEN") ? "SOLES" :
                                     (($reg->tipo_moneda == "USD") ? "DÓLARES AMERICANOS" :
                                     (($reg->tipo_moneda == "EUR") ? "EUROS" : $reg->tipo_moneda));
                            ?>
                        </td>
                    </tr>

                    <tr align="left">
                        <td><strong>Atención:</strong>
                            <?php echo $reg->vendedorsitio; ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>Tipo de pago:</strong>
                            <?php echo $reg->tipopago; ?>
                        </td>
                    </tr>


                    <tr>
                        <td><strong>Nro referencia:</strong>
                            <?php echo $reg->nroreferencia; ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>Observación:</strong>
                            <?php echo $reg->descripcion_leyenda_26_2; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table border="0" align="center" width="220px" style="font-size: 4px">

                <?php
                if ($reg->tipopago == "Contado") {
                    echo "<tr><td colspan='6'><strong>Métodos de pago:</strong></td></tr>";


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
            <table border="0" align="center" width="220px" style="font-size: 12px">
                <tr>
                    <td colspan="5">-----------------------------------------------------</td>
                </tr>
                <tr>
                    <td>Cant.</td>
                    <td>Producto</td>
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

                if ($reg->estado == '5') {
                    $boletaFirm = $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
                    $sxe = new SimpleXMLElement($rutafirma . $boletaFirm . '.xml', null, true);
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
                if (!file_exists($PNG_TEMP_DIR))
                    mkdir($PNG_TEMP_DIR);
                $filename = $PNG_TEMP_DIR . 'test.png';

                //processing form input
                //remember to sanitize user input in real-life solution !!!
                $dataTxt = $reg->numero_ruc . "|" . $reg->tipo_documento_06 . "|" . $reg->serie . "|" . $reg->numerofac . "|0.00|" . $reg->Itotal . "|" . $reg->fecha2 . "|" . $reg->tipo_documento . "|" . $reg->numero_documento . "|";
                ;
                $errorCorrectionLevel = 'H';
                $matrixPointSize = '2';

                // user data
                $filename = 'generador-qr/temp/test' . md5($dataTxt . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
                QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
                $PNG_WEB_DIR . basename($filename);
                // //==================== PARA IMAGEN DEL CODIGO HASH ================================================
        
                $logoQr = $filename;
                $ext_logoQr = substr($filename, strpos($filename, '.'), -4);

                //===============SEGUNDA COPIA DE BOLETA=========================
                $rsptad = $boleta->ventadetalle($_GET["id"]);
                $cantidad = 0;
                while ($regd = $rsptad->fetch_object()) {

                    if ($regd->nombretribu == "IGV") {
                        $pv = $regd->precio;
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
            </table>
            <?php

            echo "<table border='0'  align='center' width='220px' style='font-size: 12px' >
    <tr><td>-----------------------------------------------------</td></tr>";
            echo "<tr></tr></table>";
            ?>
            <table border='0' width='220px' style='font-size: 12px' align="center">
                <tr>
                    <td colspan='5'><strong>Descuento </strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $reg->tdescuento ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Gravada </strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $nombretigv; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Exonerado </strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $nombretexo; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Op. Inafecto </strong></td>
                    <td>:</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td colspan='5'><strong>ICBPER</strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $reg->icbper ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>I.G.V.</strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $reg->igv ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Imp. Pagado: </strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $reg->ipagado ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'><strong>Vuelto: </strong></td>
                    <td>:</td>
                    <td>
                        <?php echo $reg->saldo ?>
                    </td>
                </tr>

                <!--<tr><td colspan='5'><strong>I.G.V. 18.00 </strong></td><td >:</td><td><?php echo $reg->sumatoria_igv_18_1; ?></td></tr>-->
            </table>


            <!-- Mostramos los totales de la venta en el documento HTML -->

            <table border='0' width='220px' style='font-size: 12px' align="center">
                <tr>
                    <td><strong>Importe a pagar </strong></td>
                    <td>:</td>
                    <td><strong>
                            <?php echo $reg->Itotal ?>
                        </strong></td>
                </tr><br>

                <!--<tr>
      <td colspan="5">&nbsp;</td>
    </tr>-->

                <!--<tr>
      <td colspan="5" align="center"><?php echo utf8_decode($datose->nombre_comercial) ?></td>
    </tr>-->
                <!--<tr>
      <td colspan="5" align="center">::.GRACIAS POR SU COMPRA.::</td>
    </tr>-->

                <?php

                require_once "Letras.php";
                $V = new EnLetras();
                $con_letra = strtoupper($V->ValorEnLetras($reg->Itotal, "NUEVOS SOLES"));

                echo "<table border='0'  align='center' width='220px' style='font-size: 12px' >
    <tr><td>-----------------------------------------------------</td></tr>";
                echo "<tr><td></br><strong>Son: </strong>" . $con_letra . "</td></tr></table>";
                ?>

            </table>
            <br>
            <div style="text-align: center;">
                <img src=<?php echo $logoQr; ?> width="90" height="90"><br>
                <label>
                    <?php echo $reg->hashc;
                    ; ?>
                </label>
                <br>
                <br>
                <label>Representación impresa de la Boleta de<br>Venta Electrónica puede ser consultada<br>en
                    <?php echo utf8_decode(htmlspecialchars_decode($datose->webconsul)) ?>
                </label>
                <br>
                <br>
                <label><strong>::.GRACIAS POR SU COMPRA.::</strong></label>

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