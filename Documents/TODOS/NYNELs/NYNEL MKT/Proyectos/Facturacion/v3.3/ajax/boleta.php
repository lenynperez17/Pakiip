<?php
// SEGURIDAD: Usar sesión segura y helpers de validación
require_once "../config/Conexion.php";
require_once "../config/ajax_helper.php";
iniciarSesionSegura();

require_once "../modelos/Boleta.php";
require_once "../modelos/Numeracion.php";
$boleta = new Boleta();

require_once "../modelos/Persona.php";
$persona = new Persona();

require_once "../modelos/Usuario.php";
$usuario = new Usuario();


//Factura
$idboleta = isset($_POST["idboleta"]) ? limpiarCadena($_POST["idboleta"]) : "";
//$idusuario="2";
$idusuario = $_SESSION["idusuario"];
$fecha_emision_01 = isset($_POST["fecha_emision_01"]) ? limpiarCadena($_POST["fecha_emision_01"]) : "";
$firma_digital_36 = isset($_POST["firma_digital_36"]) ? limpiarCadena($_POST["firma_digital_36"]) : "";
$idempresa = isset($_POST["idempresa"]) ? limpiarCadena($_POST["idempresa"]) : "";
$tipo_documento_06 = isset($_POST["tipo_documento_06"]) ? limpiarCadena($_POST["tipo_documento_06"]) : "";
$idserie = isset($_POST["serie"]) ? limpiarCadena($_POST["serie"]) : "";
$SerieReal = isset($_POST["SerieReal"]) ? limpiarCadena($_POST["SerieReal"]) : "";
$numero_boleta = isset($_POST["numero_boleta"]) ? limpiarCadena($_POST["numero_boleta"]) : "";
$idnumeracion = isset($_POST["idnumeracion"]) ? limpiarCadena($_POST["idnumeracion"]) : "";
$numeracion_07 = isset($_POST["numeracion_07"]) ? limpiarCadena($_POST["numeracion_07"]) : "";
$monto_15_2 = isset($_POST["subtotal_boleta"]) ? limpiarCadena($_POST["subtotal_boleta"]) : "";
$idcliente = isset($_POST["idcliente"]) ? limpiarCadena($_POST["idcliente"]) : "";
$codigo_tipo_15_1 = isset($_POST["codigo_tipo_15_1"]) ? limpiarCadena($_POST["codigo_tipo_15_1"]) : "";
$sumatoria_igv_18_1 = isset($_POST["total_igv"]) ? limpiarCadena($_POST["total_igv"]) : "";
$sumatoria_igv_18_2 = isset($_POST["total_igv"]) ? limpiarCadena($_POST["total_igv"]) : "";

$total_icbper = isset($_POST["total_icbper"]) ? limpiarCadena($_POST["total_icbper"]) : ""; //NUEVO POR BOLSAS


$codigo_tipo_15_1 = isset($_POST["codigo_tipo_15_1"]) ? limpiarCadena($_POST["codigo_tipo_15_1"]) : "";

$codigo_tributo_18_3 = isset($_POST["codigo_tributo_h"]) ? limpiarCadena($_POST["codigo_tributo_h"]) : "";
$nombre_tributo_18_4 = isset($_POST["nombre_tributo_h"]) ? limpiarCadena($_POST["nombre_tributo_h"]) : "";
$codigo_internacional_18_5 = isset($_POST["codigo_internacional_5"]) ? limpiarCadena($_POST["codigo_internacional_5"]) : "";


$importe_total_23 = isset($_POST["total_final"]) ? limpiarCadena($_POST["total_final"]) : "";
$tipo_documento_25_1 = isset($_POST["tipo_documento_25_1"]) && $_POST["tipo_documento_25_1"] !== "" ? limpiarCadena($_POST["tipo_documento_25_1"]) : NULL;
$guia_remision_25 = isset($_POST["guia_remision_25"]) ? limpiarCadena($_POST["guia_remision_25"]) : "";
$codigo_leyenda_26_1 = isset($_POST["codigo_leyenda_26_1"]) ? limpiarCadena($_POST["codigo_leyenda_26_1"]) : "";
$descripcion_leyenda_26_2 = isset($_POST["descripcion_leyenda_26_2"]) ? limpiarCadena($_POST["descripcion_leyenda_26_2"]) : "";
$version_ubl_37 = isset($_POST["version_ubl_37"]) ? limpiarCadena($_POST["version_ubl_37"]) : "";
$version_estructura_38 = isset($_POST["version_estructura_38"]) ? limpiarCadena($_POST["version_estructura_38"]) : "";
$tipo_moneda_24 = isset($_POST["tipo_moneda_24"]) ? limpiarCadena($_POST["tipo_moneda_24"]) : "";
$tasa_igv = isset($_POST["tasa_igv"]) ? limpiarCadena($_POST["tasa_igv"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$codigo_precio = isset($_POST["codigo_precio"]) ? limpiarCadena($_POST["codigo_precio"]) : "";
$rucCliente = isset($_POST["numero_documento"]) ? limpiarCadena($_POST["numero_documento"]) : "";
$RazonSocial = isset($_POST["razon_social"]) ? limpiarCadena($_POST["razon_social"]) : "";
$tipo_doc_ide = isset($_POST["tipo_doc_ide"]) ? limpiarCadena($_POST["tipo_doc_ide"]) : "";
$domicilio_fiscal = isset($_POST["domicilio_fiscal"]) ? limpiarCadena($_POST["domicilio_fiscal"]) : "";
$hora = isset($_POST["hora"]) ? limpiarCadena($_POST["hora"]) : "";
$vendedorsitio = isset($_POST["vendedorsitio"]) ? limpiarCadena($_POST["vendedorsitio"]) : "";

$tcambio = isset($_POST["tcambio"]) && $_POST["tcambio"] !== "" ? limpiarCadena($_POST["tcambio"]) : NULL;
$tdescuento = isset($_POST["total_dcto"]) ? limpiarCadena($_POST["total_dcto"]) : "";



$ipagado = isset($_POST["ipagado_final"]) && $_POST["ipagado_final"] !== "" ? limpiarCadena($_POST["ipagado_final"]) : NULL;
$saldo = isset($_POST["saldo_final"]) && $_POST["saldo_final"] !== "" ? limpiarCadena($_POST["saldo_final"]) : NULL;
$tipopago = isset($_POST["tipopago"]) ? limpiarCadena($_POST["tipopago"]) : "";
$nroreferencia = isset($_POST["nroreferencia"]) ? limpiarCadena($_POST["nroreferencia"]) : "";
$tipoboleta = isset($_POST["tipoboleta"]) ? limpiarCadena($_POST["tipoboleta"]) : "";

$ccuotas = isset($_POST["ccuotas"]) && $_POST["ccuotas"] !== "" ? limpiarCadena($_POST["ccuotas"]) : NULL;
$fechavecredito = isset($_POST["fechavecredito"]) && $_POST["fechavecredito"] !== "" ? limpiarCadena($_POST["fechavecredito"]) : NULL;
$montocuota = isset($_POST["montocuota"]) && $_POST["montocuota"] !== "" ? limpiarCadena($_POST["montocuota"]) : NULL;

$tadc = isset($_POST["tadc"]) && $_POST["tadc"] !== "" ? limpiarCadena($_POST["tadc"]) : NULL;
$transferencia = isset($_POST["trans"]) && $_POST["trans"] !== "" ? limpiarCadena($_POST["trans"]) : NULL;


$fechavenc = isset($_POST["fechavenc"]) && $_POST["fechavenc"] !== "" ? limpiarCadena($_POST["fechavenc"]) : NULL;

$efectivo = isset($_POST["efectivo"]) && $_POST["efectivo"] !== "" ? limpiarCadena($_POST["efectivo"]) : NULL;
$visa = isset($_POST["visa"]) && $_POST["visa"] !== "" ? limpiarCadena($_POST["visa"]) : NULL;
$yape = isset($_POST["yape"]) && $_POST["yape"] !== "" ? limpiarCadena($_POST["yape"]) : NULL;
$plin = isset($_POST["plin"]) && $_POST["plin"] !== "" ? limpiarCadena($_POST["plin"]) : NULL;
$mastercard = isset($_POST["mastercard"]) && $_POST["mastercard"] !== "" ? limpiarCadena($_POST["mastercard"]) : NULL;
$deposito = isset($_POST["deposito"]) && $_POST["deposito"] !== "" ? limpiarCadena($_POST["deposito"]) : NULL;
$vuelto = isset($_POST["vuelto"]) && $_POST["vuelto"] !== "" ? limpiarCadena($_POST["vuelto"]) : NULL;

switch ($_GET["op"]) {
    case 'guardaryeditarBoleta':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        if (empty($idboleta)) {

            if ($importe_total_23 >= 700) {

                if ($idcliente == "N") {
                    //$tipo_doc_ide="1";
                    $rspta = $persona->insertardeBoleta($RazonSocial, $tipo_doc_ide, $rucCliente, $domicilio_fiscal);

                    $IdC = $persona->mostrarId();
                    //para ultimo registro de cliente
                    while ($reg = $IdC->fetch_object()) {
                        $idcl = $reg->idpersona;
                    }

                    $rspta = $boleta->insertar($idusuario, $fecha_emision_01, $firma_digital_36, $idempresa, $tipo_documento_06, $numeracion_07, $idcl, $codigo_tipo_15_1, $monto_15_2, $sumatoria_igv_18_1, $sumatoria_igv_18_2, $codigo_tributo_18_3, $nombre_tributo_18_4, $codigo_internacional_18_5, $importe_total_23, $codigo_leyenda_26_1, $descripcion_leyenda_26_2, $tipo_documento_25_1, $guia_remision_25, $version_ubl_37, $version_estructura_38, $tipo_moneda_24, $tasa_igv, $_POST["idarticulo"], $_POST["numero_orden_item_29"], $_POST["cantidad_item_12"], $_POST["codigo_precio_14_1"], $_POST["precio_unitario"], $_POST["igvBD"], $_POST["igvBD"], $_POST["afectacionigv"], $_POST["codigotributo"], '', '', $_POST["igvBD2"], $_POST["vvu"], $_POST["subtotalBD"], $_POST["codigo"], $_POST["unidad_medida"], $idserie, $SerieReal, $numero_boleta, $tipo_doc_ide, $rucCliente, html_entity_decode($RazonSocial, ENT_QUOTES | ENT_HTML401, 'UTF-8'), $hora, $_POST["sumadcto"], $vendedorsitio, $tcambio, $tdescuento, $domicilio_fiscal, $tipopago, $nroreferencia, $ipagado, $vuelto, $_POST["descdet"], $total_icbper, $tipoboleta, $_POST["cantidadreal"], $ccuotas, $fechavecredito, $montocuota, $tadc, $transferencia, $_POST["ncuotahiden"], $_POST["montocuotacre"], $_POST["fechapago"], $fechavenc, $efectivo, $visa, $yape, $plin, $mastercard, $deposito);

                    if ($rspta) {
                        // ========== AUDITORÍA: Registrar creación de boleta (>=700, cliente nuevo) ==========
                        registrarOperacionCreate('boleta', $SerieReal . '-' . $numero_boleta, [
                            'idcliente' => $idcl,
                            'razon_social' => $RazonSocial,
                            'ruc_cliente' => $rucCliente,
                            'total_venta' => $importe_total_23,
                            'subtotal' => $monto_15_2,
                            'igv' => $sumatoria_igv_18_1,
                            'tipo_moneda' => $tipo_moneda_24,
                            'tipo_pago' => $tipopago,
                            'items' => count($_POST["idarticulo"]),
                            'tipo_boleta' => $tipoboleta,
                            'cliente_nuevo' => true
                        ], "Boleta {$SerieReal}-{$numero_boleta} creada exitosamente (>=700, cliente nuevo) por valor de {$tipo_moneda_24} {$importe_total_23}");

                        echo "Se guardo boleta correctamente";
                    } else {
                        // ========== AUDITORÍA: Registrar intento fallido ==========
                        $error_mysql = $boleta->getLastError() ? " | Error MySQL: " . $boleta->getLastError() : "";
                        registrarAuditoria('CREATE', 'boleta', [
                            'descripcion' => "Intento fallido de crear boleta {$SerieReal}-{$numero_boleta} (>=700, cliente nuevo)",
                            'resultado' => 'FALLIDO',
                            'codigo_error' => 'ERROR_INSERTAR_BOLETA',
                            'mensaje_error' => 'No se pudo guardar la boleta' . $error_mysql,
                            'metadata' => [
                                'cliente' => $RazonSocial,
                                'total' => $importe_total_23
                            ]
                        ]);

                        echo "No se guardo boleta" . $error_mysql;
                    }
                } else {

                    $rspta = $boleta->insertar($idusuario, $fecha_emision_01, $firma_digital_36, $idempresa, $tipo_documento_06, $numeracion_07, $idcliente, $codigo_tipo_15_1, $monto_15_2, $sumatoria_igv_18_1, $sumatoria_igv_18_2, $codigo_tributo_18_3, $nombre_tributo_18_4, $codigo_internacional_18_5, $importe_total_23, $codigo_leyenda_26_1, $descripcion_leyenda_26_2, $tipo_documento_25_1, $guia_remision_25, $version_ubl_37, $version_estructura_38, $tipo_moneda_24, $tasa_igv, $_POST["idarticulo"], $_POST["numero_orden_item_29"], $_POST["cantidad_item_12"], $_POST["codigo_precio_14_1"], $_POST["precio_unitario"], $_POST["igvBD"], $_POST["igvBD"], $_POST["afectacionigv"], $_POST["codigotributo"], '', '', $_POST["igvBD2"], $_POST["vvu"], $_POST["subtotalBD"], $_POST["codigo"], $_POST["unidad_medida"], $idserie, $SerieReal, $numero_boleta, $tipo_doc_ide, $rucCliente, html_entity_decode($RazonSocial, ENT_QUOTES | ENT_HTML401, 'UTF-8'), $hora, $_POST["sumadcto"], $vendedorsitio, $tcambio, $tdescuento, $domicilio_fiscal, $tipopago, $nroreferencia, $ipagado, $vuelto, $_POST["descdet"], $total_icbper, $tipoboleta, $_POST["cantidadreal"], $ccuotas, $fechavecredito, $montocuota, $tadc, $transferencia, $_POST["ncuotahiden"], $_POST["montocuotacre"], $_POST["fechapago"], $fechavenc, $efectivo, $visa, $yape, $plin, $mastercard, $deposito);

                    if ($rspta) {
                        // ========== AUDITORÍA: Registrar creación de boleta (>=700, cliente existente) ==========
                        registrarOperacionCreate('boleta', $SerieReal . '-' . $numero_boleta, [
                            'idcliente' => $idcliente,
                            'razon_social' => $RazonSocial,
                            'ruc_cliente' => $rucCliente,
                            'total_venta' => $importe_total_23,
                            'subtotal' => $monto_15_2,
                            'igv' => $sumatoria_igv_18_1,
                            'tipo_moneda' => $tipo_moneda_24,
                            'tipo_pago' => $tipopago,
                            'items' => count($_POST["idarticulo"]),
                            'tipo_boleta' => $tipoboleta
                        ], "Boleta {$SerieReal}-{$numero_boleta} creada exitosamente (>=700) por valor de {$tipo_moneda_24} {$importe_total_23}");

                        echo "Se guardo boleta correctamente";
                    } else {
                        // ========== AUDITORÍA: Registrar intento fallido ==========
                        $error_mysql = $boleta->getLastError() ? " | Error MySQL: " . $boleta->getLastError() : "";
                        registrarAuditoria('CREATE', 'boleta', [
                            'descripcion' => "Intento fallido de crear boleta {$SerieReal}-{$numero_boleta} (>=700)",
                            'resultado' => 'FALLIDO',
                            'codigo_error' => 'ERROR_INSERTAR_BOLETA',
                            'mensaje_error' => 'No se pudo guardar la boleta' . $error_mysql,
                            'metadata' => [
                                'cliente' => $RazonSocial,
                                'total' => $importe_total_23
                            ]
                        ]);

                        echo "No se guardo boleta" . $error_mysql;
                    }


                } //FIN DE SEGUNDO IF

            } else //ELSE DE PRIMER IF
            {
                // SI EL TOTAL ES MENOR DE 700

                if ($idcliente == "N") {

                    $rspta = $persona->insertardeBoleta($RazonSocial, $tipo_doc_ide, $rucCliente, $domicilio_fiscal);
                    $IdC = $persona->mostrarId();
                    while ($reg = $IdC->fetch_object()) {
                        $idcl = $reg->idpersona;
                    }
                    $rspta = $boleta->insertar($idusuario, $fecha_emision_01, $firma_digital_36, $idempresa, $tipo_documento_06, $numeracion_07, $idcl, $codigo_tipo_15_1, $monto_15_2, $sumatoria_igv_18_1, $sumatoria_igv_18_2, $codigo_tributo_18_3, $nombre_tributo_18_4, $codigo_internacional_18_5, $importe_total_23, $codigo_leyenda_26_1, $descripcion_leyenda_26_2, $tipo_documento_25_1, $guia_remision_25, $version_ubl_37, $version_estructura_38, $tipo_moneda_24, $tasa_igv, $_POST["idarticulo"], $_POST["numero_orden_item_29"], $_POST["cantidad_item_12"], $_POST["codigo_precio_14_1"], $_POST["precio_unitario"], $_POST["igvBD"], $_POST["igvBD"], $_POST["afectacionigv"], $_POST["codigotributo"], '', '', $_POST["igvBD2"], $_POST["vvu"], $_POST["subtotalBD"], $_POST["codigo"], $_POST["unidad_medida"], $idserie, $SerieReal, $numero_boleta, $tipo_doc_ide, $rucCliente, html_entity_decode($RazonSocial, ENT_QUOTES | ENT_HTML401, 'UTF-8'), $hora, $_POST["sumadcto"], $vendedorsitio, $tcambio, $tdescuento, $domicilio_fiscal, $tipopago, $nroreferencia, $ipagado, $vuelto, $_POST["descdet"], $total_icbper, $tipoboleta, $_POST["cantidadreal"], $ccuotas, $fechavecredito, $montocuota, $tadc, $transferencia, $_POST["ncuotahiden"], $_POST["montocuotacre"], $_POST["fechapago"], $fechavenc, $efectivo, $visa, $yape, $plin, $mastercard, $deposito);

                    if ($rspta) {
                        // ========== AUDITORÍA: Registrar creación de boleta (<700, cliente nuevo) ==========
                        registrarOperacionCreate('boleta', $SerieReal . '-' . $numero_boleta, [
                            'idcliente' => $idcl,
                            'razon_social' => $RazonSocial,
                            'ruc_cliente' => $rucCliente,
                            'total_venta' => $importe_total_23,
                            'subtotal' => $monto_15_2,
                            'igv' => $sumatoria_igv_18_1,
                            'tipo_moneda' => $tipo_moneda_24,
                            'tipo_pago' => $tipopago,
                            'items' => count($_POST["idarticulo"]),
                            'tipo_boleta' => $tipoboleta,
                            'cliente_nuevo' => true
                        ], "Boleta {$SerieReal}-{$numero_boleta} creada exitosamente (<700, cliente nuevo) por valor de {$tipo_moneda_24} {$importe_total_23}");

                        echo "Se guardo boleta correctamente";
                    } else {
                        // ========== AUDITORÍA: Registrar intento fallido ==========
                        $error_mysql = $boleta->getLastError() ? " | Error MySQL: " . $boleta->getLastError() : "";
                        registrarAuditoria('CREATE', 'boleta', [
                            'descripcion' => "Intento fallido de crear boleta {$SerieReal}-{$numero_boleta} (<700, cliente nuevo)",
                            'resultado' => 'FALLIDO',
                            'codigo_error' => 'ERROR_INSERTAR_BOLETA',
                            'mensaje_error' => 'No se pudo guardar la boleta' . $error_mysql,
                            'metadata' => [
                                'cliente' => $RazonSocial,
                                'total' => $importe_total_23
                            ]
                        ]);

                        echo "No se guardo boleta" . $error_mysql;
                    }
                    //


                } else //===========#####################
                {

                    $rspta = $boleta->insertar(
                        $idusuario,
                        $fecha_emision_01,
                        $firma_digital_36,
                        $idempresa,
                        $tipo_documento_06,
                        $numeracion_07,
                        $idcliente,
                        $codigo_tipo_15_1,
                        $monto_15_2,
                        $sumatoria_igv_18_1,
                        $sumatoria_igv_18_2,
                        $codigo_tributo_18_3,
                        $nombre_tributo_18_4,
                        $codigo_internacional_18_5,
                        $importe_total_23,
                        $codigo_leyenda_26_1,
                        $descripcion_leyenda_26_2,
                        $tipo_documento_25_1,
                        $guia_remision_25,
                        $version_ubl_37,
                        $version_estructura_38,
                        $tipo_moneda_24,
                        $tasa_igv,
                        $_POST["idarticulo"],
                        $_POST["numero_orden_item_29"],
                        $_POST["cantidad_item_12"],
                        $_POST["codigo_precio_14_1"],
                        $_POST["precio_unitario"],
                        $_POST["igvBD"],
                        $_POST["igvBD"],
                        $_POST["afectacionigv"],
                        $_POST["codigotributo"],
                        '',
                        '',
                        $_POST["igvBD2"],
                        $_POST["vvu"],
                        $_POST["subtotalBD"],
                        $_POST["codigo"],
                        $_POST["unidad_medida"],
                        $idserie,
                        $SerieReal,
                        $numero_boleta,
                        $tipo_doc_ide,
                        $rucCliente,
                        html_entity_decode($RazonSocial, ENT_QUOTES | ENT_HTML401, 'UTF-8'),
                        $hora,
                        $_POST["sumadcto"],
                        $vendedorsitio,
                        $tcambio,
                        $tdescuento,
                        $domicilio_fiscal,
                        $tipopago,
                        $nroreferencia,
                        $ipagado,
                        $saldo,
                        $_POST["descdet"],
                        $total_icbper,
                        $tipoboleta,
                        $_POST["cantidadreal"],
                        $ccuotas,
                        $fechavecredito,
                        $montocuota,
                        $tadc,
                        $transferencia,
                        $_POST["ncuotahiden"],
                        $_POST["montocuotacre"],
                        $_POST["fechapago"],
                        $fechavenc,
                        $efectivo,
                        $visa,
                        $yape,
                        $plin,
                        $mastercard,
                        $deposito
                    );

                    if ($rspta) {
                        // ========== AUDITORÍA: Registrar creación de boleta (<700, cliente existente) ==========
                        registrarOperacionCreate('boleta', $SerieReal . '-' . $numero_boleta, [
                            'idcliente' => $idcliente,
                            'razon_social' => $RazonSocial,
                            'ruc_cliente' => $rucCliente,
                            'total_venta' => $importe_total_23,
                            'subtotal' => $monto_15_2,
                            'igv' => $sumatoria_igv_18_1,
                            'tipo_moneda' => $tipo_moneda_24,
                            'tipo_pago' => $tipopago,
                            'items' => count($_POST["idarticulo"]),
                            'tipo_boleta' => $tipoboleta
                        ], "Boleta {$SerieReal}-{$numero_boleta} creada exitosamente (<700) por valor de {$tipo_moneda_24} {$importe_total_23}");

                        echo "Se guardo boleta correctamente";
                    } else {
                        // ========== AUDITORÍA: Registrar intento fallido ==========
                        $error_mysql = $boleta->getLastError() ? " | Error MySQL: " . $boleta->getLastError() : "";
                        registrarAuditoria('CREATE', 'boleta', [
                            'descripcion' => "Intento fallido de crear boleta {$SerieReal}-{$numero_boleta} (<700)",
                            'resultado' => 'FALLIDO',
                            'codigo_error' => 'ERROR_INSERTAR_BOLETA',
                            'mensaje_error' => 'No se pudo guardar la boleta' . $error_mysql,
                            'metadata' => [
                                'cliente' => $RazonSocial,
                                'total' => $importe_total_23
                            ]
                        ]);

                        echo "No se guardo boleta" . $error_mysql;
                    }

                }

            }
        } // $######################## FIN DE IF SI ES MAYOR O MENOR A 700
        break;



    case 'mostrarultimocomprobante':
        $rspta = $boleta->mostrarultimocomprobante($_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'mostrarultimocomprobanteId':
        $rspta = $boleta->mostrarultimocomprobanteId($_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'anular':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $boleta->anular($idboleta);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar anulación de boleta ==========
            registrarOperacionAnular('boleta', $idboleta,
                "Boleta #{$idboleta} anulada con reversión de inventario y generación de archivo SUNAT");

            echo "Boleta anulada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de anulación ==========
            registrarAuditoria('ANULAR', 'boleta', [
                'registro_id' => $idboleta,
                'descripcion' => "Intento fallido de anular boleta #{$idboleta}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_ANULAR_BOLETA',
                'mensaje_error' => 'No se pudo anular la boleta'
            ]);

            echo "Boleta no se puede anular";
        }
        break;


    case 'baja':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $com = $_GET['comentario'];
        $hor = $_GET['hora'];
        date_default_timezone_set('America/Lima');
        //$hoy=date('Y/m/d');
        $hoy = date("Y-m-d");
        $rspta = $boleta->baja($idboleta, $hoy, $com, $hor);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar baja de boleta a SUNAT ==========
            registrarAuditoria('BAJA', 'boleta', [
                'registro_id' => $idboleta,
                'descripcion' => "Boleta #{$idboleta} dada de baja ante SUNAT",
                'metadata' => [
                    'fecha_baja' => $hoy,
                    'hora' => $hor,
                    'comentario' => $com
                ]
            ]);

            echo "Boleta de baja";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de baja ==========
            registrarAuditoria('BAJA', 'boleta', [
                'registro_id' => $idboleta,
                'descripcion' => "Intento fallido de dar de baja boleta #{$idboleta}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_BAJA_BOLETA',
                'mensaje_error' => 'No se pudo dar de baja la boleta',
                'metadata' => [
                    'comentario_intento' => $com
                ]
            ]);

            echo "Boleta no se puede dar de baja";
        }
        break;

    case 'actualizarNumero':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        require_once "../Modelos/Numeracion.php";
        $numeracion = new Numeracion();

        $num = $_GET['Num'];
        $idnumeracion = $_GET['Idnumeracion'];
        $rspta = $numeracion->UpdateNumeracion($num, $idnumeracion);
        break;

    case 'mostrar':
        $rspta = $factura->mostrar($idboleta);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;

    case 'listarDetalle':
        //Recibimos el idingreso
        $id = $_GET['id'];

        $rspta = $factura->listarDetalle($id);
        $subt = 0;
        $igv = 0;
        $total = 0;
        echo '
        <thead style="background-color:#A9D0F9">
                                    <th>Artículo</th>
                                    <th>Cantidad</th>
                                    <th>Precio Venta</th>
                                    <th>Subtotal</th>
                                </thead>';

        while ($reg = $rspta->fetch_object()) {
            echo '<tr class="filas"><td>' . $reg->nombre . '</td><td>' . $reg->cantidad_item_12 . '</td><td>' . $reg->valor_uni_item_14 . '</td><td>' . $reg->valor_venta_item_21 . '</td></tr>';
            $subt = $subt + ($reg->valor_venta_item_21);
            $igv = $igv + ($reg->igv_item);
            $total = $subt + $igv;
        }
        echo ' <tfoot>
                                    <th>SUBTOTAL <h4 id="subtotal">S/.' . $subt . '</h4></th>
                                    <th></th>
                                    <th>IGV  <h4 id="subtotal">S/.' . $igv . '</h4></th>
                                    <th></th>
                                    <th>TOTAL  <h4 id="total">S/.' . $total . '</h4></th>
                                    <th></th>
                                    <th></th>
                               </tfoot>

        ';
        break;


    case 'selectCliente':
        require_once "../modelos/Persona.php";
        $persona = new Persona();

        $rspta = $persona->listarC();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->nombre . '</option>';

        }
        break;

    case 'selectClienteDocumento':
        require_once "../modelos/Persona.php";
        $persona = new Persona();

        $rspta = $persona->listarC();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->num_documento . '</option>';

        }
        break;

    case 'selectSerie':
        require_once "../modelos/Numeracion.php";
        $numeracion = new Numeracion();

        $rspta = $numeracion->llenarSerieBoleta($idusuario);

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idnumeracion . '>' . $reg->serie . '</option>';

        }
        break;


    //Carga de los últimos numeros de la numeración de acuerdo a la serie seleccionada
    case 'llenarNumeroFactura':
        $tipoC = $_GET['tipoC'];
        $serieC = $_GET['serieC'];
        $rspta = $venta->sumarC($tipoC, $serieC);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->addnumero;
        }
        break;

    case 'llenarNumeroBoleta':
        $tipoC = $_GET['tipoC'];
        $serieC = $_GET['serieC'];
        $rspta = $venta->sumarC($tipoC, $serieC);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->addnumero;
        }
        break;


    //*-Case para cuando se seleccione o busque numero de documento cliente se carge en
    //en siguiente campo su nombre.-*
    case 'llenarnombrecli':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $numdocu = $_GET['numcli']; //Se recibe de venta.js el parametro-->
        $rspta = $persona->listarcnumdocu($numdocu);
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->nombre . '</option>';
        }


        break;

    //*-Case para cuando se seleccione o busque el nombre del cliente se carge en
    //en siguiente el numero de documento del cliente*
    case 'llenarnumdocucli':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $nomcli = $_GET['nomcli']; //*-Se recibe de venta.js el parametro-*
        $rspta = $persona->listarcnom($nomcli);
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->num_documento . '</option>';
        }
        break;

    case 'llenarIdcliente1':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $numdocu = $_GET['numcli']; //Se recibe de venta.js el parametro-->
        $rspta = $persona->listarcnumdocu($numdocu);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->idpersona;
        }
        break;


    case 'llenarIdcliente2':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $nomcli = $_GET['nomcli']; //Se recibe de venta.js el parametro-->
        $rspta = $persona->listarcnom($nomcli);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->idpersona;
        }
        break;


    case 'listarClientesboleta':
        require_once "../modelos/Persona.php";
        $persona = new Persona();

        $rspta = $persona->listarCliVenta();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => '<button class="btn btn-warning" onclick="agregarCliente(' . $reg->idpersona . ',\'' . $reg->razon_social . '\',\'' . $reg->numero_documento . '\',\'' . $reg->domicilio_fiscal . '\',\'' . $reg->tipo_documento . '\')"><span class="fa fa-plus"></span></button>',
                "1" => $reg->razon_social,
                "2" => $reg->numero_documento,
                "3" => $reg->domicilio_fiscal
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );

        echo json_encode($results);
        break;




    case 'listarArticulosboletaxcodigo':
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        $idempresa = $_GET['idempresa'];
        $codigob = $_GET['codigob'];
        $rspta = $articulo->listarActivosVentaxCodigo($codigob, $idempresa);
        echo json_encode($rspta);
        break;



    case 'listar':
        require_once "../modelos/Rutas.php";
        $numero = 'BKLKASD';
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta ENVIO
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos
        $path = $rutaenvio;
        $path2 = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $files2 = array_diff(scandir($path2), array('.', '..'));

        //=============================================================


        $rspta = $boleta->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {

            $urlT = '../reportes/exTicketBoleta.php?id=';
            $urlB = '../reportes/exBoleta.php?id=';
            $urlC = '../reportes/exBoletaCompleto.php?id=';

            if ($reg->tipo_documento_06 == 'Ticket') {
                $url = '../reportes/exTicket.php?id=';
            } else {
                $url = '../reportes/exBoleta.php?id=';

            }

            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            $rptaSunat = $reg->CodigoRptaSunat;

            //===========================================================================



            $stt = '';
            $sunatFirma = '';
            $sunatAceptado = 'Class';
            if ($reg->estado == '5') { // ACEPTADO PO SUNAT
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = 'class';
            } else if ($reg->estado == '4') {
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = '';
                //$boleta->enviarxmlSUNAT($reg->idboleta);
            } else {
                $send = 'none';
            }
            if ($reg->estado == '3') {
                $stt = 'none';
                $sunat = '';
            }
            if ($reg->estado == '0') {
                $stt = 'none';

                $sunat = '';
            }
            $estadoenvio = '1';

            $mon = "";
            if ($reg->moneda == "USD") {
                $mon = '<i style="color:green;" data-toggle="tooltip" title="Por T.C. ' . $reg->tcambio . ' = ' . $reg->valordolsol . ' PEN">$</i>';
            }


            //=====================================================================================
            $data[] = array(
                "0" =>


                    '<div class="dropdown">
                <button  class="btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                :::
                <span class="caret"></span></button>
                <ul class="dropdown-menu pull-center">

                <li>
                   <a  onclick="baja(' . $reg->idboleta . ')" style="display:' . $stt . ';  color:red;">
                   <i class="fa fa-level-down"  data-toggle="tooltip" title="Dar de baja" ></i>
                      Dar de baja
                    </a>
                </i>


                    <li>
                <a onclick="duplicarb(' . $reg->idboleta . ')"  style="color:green;"  data-toggle="tooltip" title="Duplicar boleta" ' . $stt . '">
                  <i  class="fa fa-files-o"></i>
                  Duplicar boleta
                  </a>

                  </li>



                  <li>
                  <a  onclick="prea42copias2(' . $reg->idboleta . ')">
                   <i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir formato 2 copias" onclick=""></i>
                   Imprimir formato 2 copias
                    </a>

                   <li>
                  <a  onclick="preticket2(' . $reg->idboleta . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir Ticket"> </i>
                  Imprimir Ticket
                     </a>
                  </li>


                   <li>
                 <a onclick="prea4completo2(' . $reg->idboleta . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir formato completo"> </i>Imprimir formato completo
                     </a>
                  </li>



                  <li>
                 <a onclick="enviarcorreo(' . $reg->idboleta . ')"><i class="fa  fa-send"  data-toggle="tooltip" title=""> </i>Enviar por correo
                     </a>
                  </li>



                </ul>
                </div>',

                "1" => $reg->fecha,
                "2" => $reg->cliente,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_07,
                "5" => $reg->importe_total_23 . " " . $mon,
                "6" => ($reg->tarjetadc == '1') ? '<img src="../files/articulos/tarjetadc.png" width="20px"
                data-toggle="tooltip" title="TARJETA ' . $reg->montotarjetadc . '">' : '',
                "7" => ($reg->transferencia == '1') ? '<img src="../files/articulos/transferencia.png" width="20px" data-toggle="tooltip" title="BANCO ' . $reg->montotransferencia . '">' : '',
                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido
                    ? '<span style="color:#BA4A00;">' . $reg->DetalleSunat . '</span>'

                    : (($reg->estado == '4') ? '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                        : (($reg->estado == '3') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' // si esta de baja

                            : (($reg->estado == '0') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                                : (($reg->estado == '5') ? '<span style="color:#145A32;">' . $reg->DetalleSunat . '</span>' // Si esta aceptado por SUNAT

                                    : '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>')))),

                //Opciones de envio
                "9" =>

                    '<div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                Opciones
                <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right">
                  <li>
                   <a onclick="generarxml(' . $reg->idboleta . ')" ><i class="fa fa-download"  style="color:orange; font-size:18px;" data-toggle="tooltip" title="Generar xml"></i>Generar xml</a>
                  </li>


                  <li>
                    <a onclick="enviarxmlSUNAT(' . $reg->idboleta . ')"  ' . $sunatAceptado . '="class_a_href" ><i class="fa fa-send"  style="color:red; font-size:18px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i>Enviar a SUNAT</a>
                  </li>


                  <li>
                    <a onclick="mostrarxml(' . $reg->idboleta . ')"><i class="fa fa-check" style="color:orange; font-size:18px;"  data-toggle="tooltip" title="Mostrar XML"></i>Mostrar XML</a>
                  </li>

                   <li>
                   <a onclick="mostrarrpta(' . $reg->idboleta . ')"><i class="fa fa-check" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Mostrar respuesta CDR"></i>Mostrar respuesta</a>
                  </li>

                  <li>
                   <a href="https://n9.cl/fo5y" target=_blank >  <img src="../public/images/sunat.png" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Consulta de validez con SUNAT"></i>Consulta de validez</a>
                  </li>

                    <li>
                    <a onclick="consultarcdr(' . $reg->idboleta . ')" ><i class="fa fa-refresh"  style="color:red; font-size:18px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i>Reconsultar a SUNAT</a>
                  </li>


                  <li>
                    <a onclick="cambiartarjetadc(' . $reg->idboleta . ')" ><i class="fa fa-credit-card" style="color:blue;"></i> Cambiar a tarjeta</a>
                  </li>

                  <li>
                    <a onclick="montotarjetadc(' . $reg->idboleta . ')" ><i class="fa fa-money" style="color:blue;"></i> Modificar monto tarjeta </a>
                  </li>


                  <li>
                    <a onclick="cambiartransferencia(' . $reg->idboleta . ')" ><i class="fa fa-exchange" style="color:green;"></i> Cambiar a transferencia </a>
                  </li>

                  <li>
                    <a onclick="montotransferencia(' . $reg->idboleta . ')" ><i class="fa fa-money" style="color:green;"></i> Modificar monto transferencia </a>
                  </li>



                   </ul>
                </div>'
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;



    case 'listarValidar':

        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];
        //$idempresa=$_GET['idempresa'];

        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta ENVIO
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos
        $path = $rutaenvio;
        $path2 = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $files2 = array_diff(scandir($path2), array('.', '..'));

        //=============================================================


        $rspta = $boleta->listarValidar($ano, $mes, $dia, $_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {

            $urlT = '../reportes/exTicketBoleta.php?id=';
            $urlB = '../reportes/exBoleta.php?id=';
            $urlC = '../reportes/exBoletaCompleto.php?id=';

            if ($reg->tipo_documento_06 == 'Ticket') {
                $url = '../reportes/exTicket.php?id=';
            } else {
                $url = '../reportes/exBoleta.php?id=';

            }

            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            //===========================================================================



            $stt = '';
            $vs = '';
            $sunatFirma = '';
            $sunatAceptado = 'Class';
            if ($reg->estado == '5') { // ACEPTADO PO SUNAT
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = 'class';
            } else if ($reg->estado == '4') {
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = '';
                //$boleta->enviarxmlSUNAT($reg->idboleta);
            } else {
                $send = 'none';
            }
            if ($reg->estado == '3') {
                $stt = 'none';
                $sunat = '';
                $vs = 'none';
            }
            if ($reg->estado == '0') {
                $stt = 'none';

                $sunat = '';
            }
            $estadoenvio = '1';

            $mon = "";
            if ($reg->moneda == "USD") {
                $mon = '<i style="color:green;" data-toggle="tooltip" title="Por T.C. ' . $reg->tcambio . ' = ' . $reg->valordolsol . ' PEN">$</i>';
            }
            //=====================================================================================

            $data[] = array(
                "0" =>

                    '
                 <input type="hidden" name="idoculto[]" id="idoculto[]" value="' . $reg->idboleta . '">
                 <input type="hidden" name="estadoocu[]" id="estadoocu[]" value="' . $reg->estado . '">
                 <div class="btn-group mb-1">
                 <div class="dropdown">
                     <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        :::
                     </button>
                     <div class="dropdown-menu" style="">
                         <a  class="dropdown-item" onclick="baja(' . $reg->idboleta . ')" style="display:' . $vs . ';  color:red;">Dar de baja</a>
                         <a  class="dropdown-item" target="_blank" href="' . $url . $reg->idboleta . '">Imprimir formato 2 copias</a>
                         <a  class="dropdown-item" target="_blank" href="' . $urlT . $reg->idboleta . '">Imprimir Ticket</a>
                         <a class="dropdown-item" target="_blank" href="' . $urlC . $reg->idboleta . '">Imprimir formato completo</a>
                        
                     </div>
                 </div>
             </div>'

                ,


                "1" => $reg->fecha,
                "2" => $reg->nombres,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_07,
                "5" => $reg->importe_total_23 . " " . $mon,

                "6" => ($reg->tarjetadc == '1') ? '<img src="../files/articulos/tarjetadc.png" width="20px"
                data-toggle="tooltip" title="TARJETA ' . $reg->montotarjetadc . '">' : '',
                "7" => ($reg->transferencia == '1') ? '<img src="../files/articulos/transferencia.png" width="20px" data-toggle="tooltip" title="BANCO ' . $reg->montotransferencia . '">' : '',

                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido
                    ? '<span style="color:#BA4A00;">' . $reg->DetalleSunat . '</span>'

                    : (($reg->estado == '4') ? '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                        : (($reg->estado == '3') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' // si esta de baja

                            : (($reg->estado == '0') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                                : (($reg->estado == '5') ? '<span style="color:#145A32;">' . $reg->DetalleSunat . '</span>' // Si esta aceptado
                                    : '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>')))),

                //Opciones de envio
                "9" =>

                    '<div class="dropdown">
                 <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     Opciones
                 </button>
                 <div class="dropdown-menu" style="">
                 <a class="dropdown-item" onclick="regenerarxml(' . $reg->idboleta . ')" >Regenerar xml</a>  
                 <a class="dropdown-item" onclick="enviarxmlSUNATbajas(' . $reg->idboleta . ')">Reenviar xml</a> 
                 <a hidden class="dropdown-item" onclick="generarxml(' . $reg->idboleta . ')" >Generar xml</a>
                <a class="dropdown-item" onclick="mostrarxml(' . $reg->idboleta . ')">Mostrar XML</a>
                <a class="dropdown-item" onclick="enviarxmlSUNAT(' . $reg->idboleta . ')"  ' . $sunatAceptado . '="class_a_href" >Enviar a SUNAT</a>
                <a class="dropdown-item" onclick="mostrarrpta(' . $reg->idboleta . ')">Mostrar respuesta</a>
                <a hidden class="dropdown-item" href="https://n9.cl/fo5y" target=_blank >  <img src="../public/images/sunat.png" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Consulta de validez con SUNAT"></i>Consulta de validez</a>
                <a hidden class="dropdown-item" onclick="consultarcdr(' . $reg->idboleta . ')" >Reconsultar a SUNAT</a>
                <a class="dropdown-item" onclick="cambiartarjetadc(' . $reg->idboleta . ')" > Cambiar a tarjeta</a>   
                <a class="dropdown-item" onclick="montotarjetadc(' . $reg->idboleta . ')" > Modificar monto tarjeta </a>
                <a class="dropdown-item" onclick="cambiartransferencia(' . $reg->idboleta . ')" > Cambiar a transferencia </a>
                <a class="dropdown-item" onclick="montotransferencia(' . $reg->idboleta . ')" > Modificar monto transferencia </a>
                 </div>
             </div>
         </div>',

                "10" => ($reg->estado == '1' || $reg->estado == '4') ? '<input type="checkbox"  id="chid[]"  name="chid[]">' : '<input type="checkbox"  id="chid[]"  name="chid[]" style="display:none;">'


                //Actualizado ===============================================

            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;



    case 'envioautomatico':
        $idempresa = $_GET['idempresa'];
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($idempresa);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta ENVIO
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos
        $path = $rutaenvio;
        $path2 = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $files2 = array_diff(scandir($path2), array('.', '..'));

        //=============================================================


        $rspta = $boleta->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {

            $urlT = '../reportes/exTicketBoleta.php?id=';
            $urlB = '../reportes/exBoleta.php?id=';
            $urlC = '../reportes/exBoletaCompleto.php?id=';

            if ($reg->tipo_documento_06 == 'Ticket') {
                $url = '../reportes/exTicket.php?id=';
            } else {
                $url = '../reportes/exBoleta.php?id=';

            }

            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_06 . "-" . $reg->numeracion_07;
            $rptaSunat = $reg->CodigoRptaSunat;
            //===========================================================================

            if ($reg->estado == '1') {
                //Validar si existe el archivo firmado
                foreach ($files as $file) {
                    // Divides en dos el nombre de tu archivo utilizando el .
                    $dataSt = explode(".", $file);
                    // Nombre del archivo
                    $fileName = $dataSt[0];
                    $st = "1";
                    // Extensión del archivo
                    $fileExtension = $dataSt[1];
                    if ($archivo == $fileName) {
                        $st = "4";
                        $UpSt = $boleta->ActualizarEstado($reg->idboleta, $st);
                    }
                }
                $boleta->generarxml($reg->idboleta, $_SESSION['idempresa']);
            } elseif ($reg->estado == '4') {
                $boleta->enviarxmlSUNAT($reg->idboleta, $_SESSION['idempresa']);
                $st = "5";
                $UpSt = $boleta->ActualizarEstado($reg->idboleta, $st);

            }



            $stt = '';
            $sunatFirma = '';
            $sunatAceptado = 'Class';


            if ($reg->estado == '5') { // ACEPTADO PO SUNAT
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = 'class';

            } else if ($reg->estado == '4') {
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = '';
                //$boleta->enviarxmlSUNAT($reg->idboleta);
            } else {
                $send = 'none';
            }


            if ($reg->estado == '3') {
                $stt = 'disabled';

                $sunat = '';
            }

            if ($reg->estado == '0') {
                $stt = 'none';

                $sunat = '';
            }

            $estadoenvio = '1';

            //=====================================================================================
            // Ajustar el nombre del artículo
            $nombres_articulos = explode(',', $reg->nombre_articulo); // Convertir la cadena en un array
            $nombre_mostrado = $nombres_articulos[0]; // Tomar solo el primer artículo
            if (count($nombres_articulos) > 1) { // Si hay más de un artículo
                $nombre_mostrado .= '...'; // Añadir puntos suspensivos al nombre
            }

            $data[] = array(
                "0" =>
                    ' <div class="btn-group mb-1">
            <div class="dropdown">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                </button>
                <div class="dropdown-menu" style="">
                    
                    <a  class="dropdown-item" onclick="preticket2(' . $reg->idboleta . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir Ticket"> </i> Formato Ticket</a>
                    <a class="dropdown-item" onclick="prea4completo2(' . $reg->idboleta . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir formato completo"> </i> Formato A4
                    </a>
                    <a  class="dropdown-item" onclick="baja(' . $reg->idboleta . ')" style="display:' . $stt . ';  color:red;"> Dar de baja</a>
                    <a hidden class="dropdown-item" onclick="enviarwhatsapp(' . $reg->idboleta . ')">Enviar por whatsapp</a>
                    <a class="dropdown-item" onclick="enviarcorreo(' . $reg->idboleta . ')">Enviar por correo</a>
                    
                </div>
            </div>
        </div>
        
    
    ',

                "1" => $reg->fecha,
                "2" => $reg->cliente,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_07,
                "5" => $reg->formapago,
                "6" => '<span title="' . $reg->nombre_articulo . '">' . $nombre_mostrado . '</span>',
                "7" => $reg->importe_total_23,

                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido
                    ? '<i class="fa fa-file-text-o" style="font-size: 14px; color:#BA4A00;"> <span>' . $reg->DetalleSunat . '</span><i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span></i>'

                    : (($reg->estado == '4') ? '<span  style="font-size: 14px; color:#239B56;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                        : (($reg->estado == '3') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' // si esta de baja

                            : (($reg->estado == '0') ? '<style="color:#E59866;" span>' . $reg->DetalleSunat . '</span>' //si esta firmado

                                : (($reg->estado == '5') ? '<div class="boleta-status"><span style="color:#0d6efd;">' . $reg->DetalleSunat . '</span><img style="margin-left:47px; margin:0 auto;" src="../public/images/aceptado.png" width="28px" height="28px" title="Estado de sunat aceptado"></div>' // Si esta aceptado por SUNAT

                                    : '<i class="fa fa-newspaper" style="font-size: 14px; color:#239B56;"> <span>' . $reg->DetalleSunat . '</span></i> ')))),

                "9" =>
                    '       
            <a hidden onclick="enviarxmlSUNAT(' . $reg->idboleta . ')"  ' . $sunatAceptado . '="class_a_href" ><i class="fa fa-send"  style="color:red; font-size:18px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i></a>

            <a style="cursor:pointer;" onclick="mostrarxml(' . $reg->idboleta . ')"><img src="../public/images/xml.png" width="28px" height="28px" title="Descargar XML"></a>

           ' //Si esta anulado
                //Actualizado ===============================================
                ,
                "10" =>
                    ' <a style="cursor:pointer;" onclick="mostrarrpta(' . $reg->idboleta . ')"><img src="../public/images/cdr.png" width="28px" height="28px" title="Descargar CDR"></a>',
                "11" =>
                    ' <a style="cursor:pointer;" onclick="prea4completo2(' . $reg->idboleta . ')"><img src="../public/images/pdf.png" width="28px" height="28px" title="Descargar PDF"></a>'
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;






    case 'autonumeracion':

        $numeracion = new Numeracion();
        $Ser = $_GET['ser'];
        $idempresa = $_GET['idempresa'];
        $rspta = $numeracion->llenarNumeroBoleta($Ser, $idempresa);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->Nnumero;
        }
        break;


    case 'listarClientesboletaxDoc':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $doc = $_GET['doc'];
        $rspta = $persona->buscarClientexDocBoleta($doc);

        echo json_encode($rspta);

        break;

    case 'enviarcorreo':
        $idb = $_GET['idbol'];
        $correo = $_GET['ema'];
        $rspta = $boleta->enviarcorreo($idb, $correo);
        echo $rspta;
        break;


    case 'listarDR':

        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        //$idempresa=$_GET['idempresa'];

        $rspta = $boleta->listarDR($ano, $mes, $_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->numeroboleta,
                "2" => $reg->cliente,
                "3" => $reg->ruccliente,
                "4" => $reg->opgravada,
                "5" => $reg->igv,
                "6" => $reg->total,
                "7" => $reg->fechabaja,
                "8" => ($reg->estado == '0')
                    ? '<i style="color:#BA4A00;"  > <span>NOTA</span></i>' : '<i  style="color:#E59866;" > <span>BAJA</span></i>',

                "9" => $reg->vendedorsitio,
                "10" => ($reg->estado == '0')
                    ? '<button class="btn btn-warning"  onclick="ConsultaDR(' . $reg->idboleta . ')"> <i class="fa fa-eye" data-toggle="tooltip" title="Ver documento" ></i> </button>' : ''
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );

        echo json_encode($results);
        break;

    case 'downFtp':
        $rspta = $boleta->downftp($idboleta, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'selectunidadmedida':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        $iiddaa = $_GET['idar'];

        $rspta = $consulta->selectumedidadearticulo($iiddaa);

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->abre . '>' . $reg->nombreum . '</option>';

        }
        break;


    case 'listarArticulosboleta':

        $tipob = $_GET['tb'];
        $tipoprecio = $_GET['tprecio'];
        $tmm = $_GET['itm'];

        $almacen = $_GET['alm'];

        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();



        if ($tmm == '0') {
            $rspta = $articulo->listarActivosVentaumventa($_SESSION['idempresa'], $tipob, $almacen, $tipoprecio);

            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning btn-sm" onclick="agregarDetalle(0,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\' , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\' , \'' . str_replace("\r\n", " ", $reg->descrip) . '\',  \'' . $reg->tipoitem . '\')">
                <span class="fa fa-clone" data-toggle="tooltip" title="Agregar continuo">
                </span>
                Continuo
                </button>'
                        .
                        '<button class="btn btn-success btn-sm" onclick="agregarDetalle(1,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\' , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\' , \'' . str_replace("\r\n", " ", $reg->descrip) . '\',  \'' . $reg->tipoitem . '\'); cerrarModal();">
                <span class="fa fa-outdent" data-toggle="tooltip" title="Agregar separado">
                </span>
                individual
                </button>',
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->factorconversion,
                    //($tipob=="productos")? $reg->factorconversion : $reg->stock,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='60px' width='60px'>" :
                        "<img src='../files/articulos/" . $reg->imagen . "' height='60px' width='60px'>",
                    "7" => ($reg->imagen == "") ? "../files/articulos/simagen.png" :
                        "../files/articulos/" . $reg->imagen,
                    "8" => $reg->idarticulo
                );
            }

        } else {

            $rspta = $articulo->listarActivosVentaumcompra($_SESSION['idempresa'], $tipob, $almacen, $tipoprecio);

            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning" onclick="agregarDetalleItem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\', \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\', \'' . str_replace("\r\n", " ", $reg->descrip) . '\',  \'' . $reg->factorconversion . '\')"><span class="fa fa-plus"></span></button>',
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->stock,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='120px' width='120px'>" :
                        "<img src='../files/articulos/" . $reg->imagen . "' height='120px' width='120px'>",
                    "7" => ($reg->imagen == "") ? "../files/articulos/simagen.png" :
                        "../files/articulos/" . $reg->imagen,
                    "8" => $reg->idarticulo
                );
            }


        }

        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;




    case 'listarArticulosboletaItem':
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        $tipob = $_GET['tb'];

        $rspta = $articulo->listarActivosVentaumventa($_SESSION['idempresa'], "productos");

        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => '<button class="btn btn-warning" onclick="agregarDetalleitem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\')"><span class="fa fa-plus"></span></button>',
                "1" => $reg->nombre,
                "2" => $reg->codigo,
                "3" => $reg->unidad_medida,
                "4" => $reg->precio_venta,
                "5" => number_format($reg->stock, 2),
                "6" => "" //"<img src='../files/articulos/".$reg->imagen."' height='50px' width='50px' >"
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;




    case 'listarArticulosservicio':
        require_once "../modelos/Articulo.php";
        $bienservicio = new Articulo();
        $rspta = $bienservicio->listarActivosVentaSoloServicio($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => '<button class="btn btn-warning" onclick="agregarDetalleItem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->unidad_medida . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\')"><span class="fa fa-plus"></span></button>',
                "1" => $reg->nombre,
                "2" => $reg->codigo,
                "3" => $reg->unidad_medida,
                "4" => $reg->precio_venta,
                "5" => number_format($reg->stock, 2),
                "6" => "" //"<img src='../files/articulos/".$reg->imagen."' height='50px' width='50px' >"
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;


    case 'generarxml':
        $rspta = $boleta->generarxml($idboleta, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'mostrarxml':
        $rspta = $boleta->mostrarxml($idboleta, $_SESSION['idempresa']);

        if ($rspta == "") {
            $rspta = "No se ha creado";
        }
        echo json_encode($rspta);
        break;

    case 'mostrarrpta':
        $rspta = $boleta->mostrarrpta($idboleta, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'enviarxmlSUNAT':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $boleta->enviarxmlSUNAT($idboleta, $_SESSION['idempresa']);

        // ========== AUDITORÍA: Registrar envío a SUNAT ==========
        // Verificar si el envío fue exitoso analizando la respuesta
        $exitoso = (stripos($rspta, 'aceptad') !== false || stripos($rspta, 'éxito') !== false);

        registrarEnvioSUNAT('boleta', $idboleta, $exitoso, $rspta);

        echo $rspta;
        break;


    case 'regenerarxml':
        $rspta = $boleta->regenerarxml($idboleta, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'enviarxmlSUNATbajas':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $boleta->enviarxmlSUNATbajas($idboleta, $_SESSION['idempresa']);
        echo $rspta;
        break;

    case 'regenerarxmlEA':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $ano = $_GET['anO'];
        $mes = $_GET['meS'];
        $dia = $_GET['diA'];
        $idcomprobante = $_GET['idComp'];
        $estadoOcu = $_GET['SToc'];
        $Chfac = $_GET['Ch'];
        $rspta = $boleta->generarxmlEA($ano, $mes, $dia, $idcomprobante, $estadoOcu, $Chfac, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'selectAlmacen':


        $rspta = $boleta->almacenlista();
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idalmacen . '>' . $reg->nombre . '</option>';
        }
        break;

    case 'consultarcdr':
        $rspta = $boleta->reconsultarcdr($idboleta, $_SESSION['idempresa']);
        echo $rspta;
        break;

    case 'tcambiog':
        $tcf = $_GET['feccf'];
        $rspta = $boleta->mostrartipocambio($tcf);
        echo json_encode($rspta);
        break;


    case 'consultaDniSunat':
        $token = 'apis-token-1.aTSI1U7KEuT-6bbbCguH-4Y8TI6KS73N';
        $nndnii = $_GET['nrodni'];

        // Iniciar llamada a API
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.apis.net.pe/v1/dni?numero=' . $nndnii,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Referer: https://apis.net.pe/consulta-dni-api',
                    'Authorization: Bearer' . $token
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        // Datos listos para usar
        $datosDniCli = json_decode($response);
        echo json_encode($datosDniCli);
        break;



    case 'cambiartarjetadc_':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $opc = $_GET['opcion'];
        $rspta = $boleta->cambiartarjetadc($idboleta, $opc);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;

    case 'montotarjetadc_':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $mto = $_GET['monto'];
        $rspta = $boleta->montotarjetadc($idboleta, $mto);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;


    case 'cambiartransferencia':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $opc = $_GET['opcion'];
        $rspta = $boleta->cambiartransferencia($idboleta, $opc);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;

    case 'montotransferencia':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $mto = $_GET['monto'];
        $rspta = $boleta->montotransferencia($idboleta, $mto);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;


    case 'duplicar':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $boleta->duplicar($idboleta);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar duplicación de boleta ==========
            registrarAuditoria('DUPLICAR', 'boleta', [
                'registro_id' => $idboleta,
                'descripcion' => "Boleta #{$idboleta} duplicada exitosamente (nueva boleta ID: {$rspta})",
                'metadata' => [
                    'boleta_original' => $idboleta,
                    'boleta_nueva' => $rspta
                ]
            ]);

            echo "Boleta ha sido duplicada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de duplicación ==========
            registrarAuditoria('DUPLICAR', 'boleta', [
                'registro_id' => $idboleta,
                'descripcion' => "Intento fallido de duplicar boleta #{$idboleta}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_DUPLICAR_BOLETA',
                'mensaje_error' => 'No se pudo duplicar la boleta'
            ]);

            echo "Boleta no se pudo duplicar";
        }
        break;


}


?>