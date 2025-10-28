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



            require_once "../modelos/Rutas.php";
            $rutas = new Rutas();
            $Rrutas = $rutas->mostrar2("1");
            $Prutas = $Rrutas->fetch_object();
            $rutalogo = $Prutas->rutalogo;

            //Incluímos la clase Venta
            require_once "../modelos/Notapedido.php";
            require_once "../modelos/Factura.php";
            //Instanciamos a la clase con el objeto venta
            $notapedido = new Notapedido();
            $factura = new Factura();
            $datos = $factura->datosemp($_SESSION['idempresa']);
            //En el objeto $rspta Obtenemos los valores devueltos del método ventacabecera del modelo
            $rspta = $notapedido->ventacabecera($_GET["id"]);
            //Recorremos todos los valores obtenidos
            $reg = $rspta->fetch_object();
            $datose = $datos->fetch_object();

            $logo = "../files\logo\\" . $datose->logo;
            $ext_logo = substr($datose->logo, strpos($datose->logo, '.'), -4);


            ?>
            <!-- <div class="zona_impresion"> -->
            <!-- codigo imprimir -->
            <br>
            <table align="center">
                <tbody>
                    <tr>
                        <td align="center">

                            <img src="<?php echo $logo; ?>" width="50">
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



                    <tr align="center">
                        <td>=================================</td>
                    </tr>

                    <tr>
                        <td align="center">
                            <strong> NOTA DE PEDIDO </br>
                                <?php echo $reg->numeracion_07; ?>
                            </strong>
                        </td>
                    </tr>

                    <tr align="center">
                        <td>=================================</td>
                    </tr>
                </tbody>
            </table>


            <table align="center">
                <tbody>
                    <tr align="center">
                        <td><strong>Cliente:</strong> </td>
                        <td>
                            <?php echo $reg->cliente; ?>
                        </td>
                    </tr>

                    <tr align="center">
                        <td><strong>RUC/DNI:</strong> </td>
                        <td>
                            <?php echo $reg->numero_documento; ?>
                        </td>
                    </tr>

                    <tr align="center">
                        <td><strong>Dirección:</strong> </td>
                        <td>
                            <?php echo $reg->direccion; ?>
                        </td>
                    </tr align="center">

                    <tr align="center">
                        <td><strong>Fecha de emisión:</strong> </td>
                        <td>
                            <?php echo $reg->fecha . " / " . $reg->hora; ?>
                        </td>
                    </tr>

                    <!--  <tr>
        <td ><strong>Fecha de Vcto:</strong>
         <?php echo "."; ?></td>
    </tr> -->

                    <tr align="center">
                        <td><strong>Moneda:</strong></td>
                        <td>
                            SOLES</td>
                    </tr>

                    <tr align="center">
                        <td><strong>Atención:</strong> </td>
                        <td>
                            <?php echo $reg->vendedorsitio; ?>
                        </td>
                    </tr>






                </tbody>
            </table>

            <br>

            <?php
            function shouldDisplayPaymentMethods($record)
            {
                return $record->yape > 0 || $record->visa > 0 || $record->efectivo > 0 || $record->plin > 0 || $record->masterC > 0 || $record->dep > 0;
            }
            ?>

            <table border="0" align="center" width="220px" style="font-size: 4px">

                <?php
                if (shouldDisplayPaymentMethods($reg)) {
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
        
                }
                ?>
            </table>

            <br>
            <!-- Mostramos los detalles de la venta en el documento HTML -->
            <table border="0" align="center" width="220px" style="font-size: 12px">
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

                //===============SEGUNDA COPIA DE BOLETA=========================
                $rsptad = $notapedido->ventadetalle($_GET["id"]);
                $cantidad = 0;
                while ($regd = $rsptad->fetch_object()) {

                    if ($regd->nombretribu == "IGV") {
                        $pv = $regd->precio_uni_item_14_2;
                        $subt = ($reg->adelanto > 0) ? $reg->adelanto : $regd->subtotal;
                    } else {
                        $pv = $regd->precio_uni_item_14_2;
                        $subt = ($reg->adelanto > 0) ? $reg->adelanto : $regd->subtotal;

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



            require_once "Letras.php";
            $V = new EnLetras();

            // Mostrar subtotal o adelanto en las letras
            $montoAMostrar = ($reg->adelanto > 0) ? $reg->adelanto : $reg->subtotal;
            $con_letra = strtolower($V->ValorEnLetras($montoAMostrar, "NUEVOS SOLES"));
            echo "<table border='0'  align='center' width='220px' style='font-size: 12px' >
    <tr><td>-----------------------------------------------------</td></tr>";

            echo "<tr><td></br><strong>Pagaste: </strong>" . $con_letra . "</td></tr></table>";
            ?>



            <!-- Mostramos los totales de la venta en el documento HTML -->

            <table border='0' width='220px' style='font-size: 12px' align="center">
                <tr>
                    <td align='right'><strong>TOTAL ARTÍCULO:
                            <?php echo $reg->subtotal ?>
                        </strong></td>
                </tr>



                <tr>
                    <td colspan="5">&nbsp;</td>
                </tr>


                <?php


                // Mostrar Faltante sólo si hay un monto
                if ($reg->faltante > 0) {
                    echo "<tr>
            <td><strong>Falta pagar :</strong></td>
            <td>" . $reg->faltante . "</td>
          </tr>";
                }

                ?>
                <tr>
                    <td colspan="5">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="5" align="center">
                        <?php echo utf8_decode($datose->nombre_comercial) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">::.GRACIAS POR SU COMPRA.::</td>
                </tr>

            </table>
            <br>
            <!-- <div style="text-align: center;" >
    <img src=<?php echo $logoQr; ?> width="50" height="50"><br>
    <label>Código HASH: <?php echo $reg->hashc;
            ; ?> </label>
    <br>
    <br>

</div> -->
        </body>

        </html>
        <?php
    } else {
        echo 'No tiene permiso para visualizar el reporte';
    }

}
ob_end_flush();
?>