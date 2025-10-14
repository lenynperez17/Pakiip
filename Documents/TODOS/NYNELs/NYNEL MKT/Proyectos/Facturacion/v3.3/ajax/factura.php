<?php
// SEGURIDAD: Usar sesión segura y helpers de validación
require_once "../config/Conexion.php";
require_once "../config/ajax_helper.php";
iniciarSesionSegura();

require_once "../modelos/Factura.php";
require_once "../modelos/Numeracion.php";
$factura = new Factura();

//Factura
$idfactura = isset($_POST["idfactura"]) ? limpiarCadena($_POST["idfactura"]) : "";
//$idusuario="2";
$idusuario = $_SESSION["idusuario"];
$fecha_emision = isset($_POST["fecha_emision"]) ? limpiarCadena($_POST["fecha_emision"]) : "";
$firma_digital = isset($_POST["firma_digital"]) ? limpiarCadena($_POST["firma_digital"]) : "";
$idempresa = isset($_POST["idempresa"]) ? limpiarCadena($_POST["idempresa"]) : "";
$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
$tipo_documento_dc = isset($_POST["tipo_documento_dc"]) ? limpiarCadena($_POST["tipo_documento_dc"]) : "";
$idserie = isset($_POST["serie"]) ? limpiarCadena($_POST["serie"]) : "";
$SerieReal = isset($_POST["SerieReal"]) ? limpiarCadena($_POST["SerieReal"]) : "";
$numero_factura = isset($_POST["numero_factura"]) ? limpiarCadena($_POST["numero_factura"]) : "";
$idnumeracion = isset($_POST["idnumeracion"]) ? limpiarCadena($_POST["idnumeracion"]) : "";
$numeracion = isset($_POST["numeracion"]) ? limpiarCadena($_POST["numeracion"]) : "";
$idcliente = isset($_POST["idpersona"]) ? limpiarCadena($_POST["idpersona"]) : "";
$total_operaciones_gravadas_codigo = isset($_POST["total_operaciones_gravadas_codigo"]) ? limpiarCadena($_POST["total_operaciones_gravadas_codigo"]) : "";
$total_operaciones_gravadas_monto = isset($_POST["subtotal_factura"]) ? limpiarCadena($_POST["subtotal_factura"]) : "";
$sumatoria_igv_1 = isset($_POST["total_igv"]) ? limpiarCadena($_POST["total_igv"]) : "";
$sumatoria_igv_2 = isset($_POST["total_igv"]) ? limpiarCadena($_POST["total_igv"]) : "";
$total_icbper = isset($_POST["total_icbper"]) ? limpiarCadena($_POST["total_icbper"]) : ""; // NUEVO *BOLSAS TOTAL DE MONTO*
$codigo_tributo_3 = isset($_POST["codigo_tributo_h"]) ? limpiarCadena($_POST["codigo_tributo_h"]) : "";
$nombre_tributo_4 = isset($_POST["nombre_tributo_h"]) ? limpiarCadena($_POST["nombre_tributo_h"]) : "";
$codigo_internacional_5 = isset($_POST["codigo_internacional_5"]) ? limpiarCadena($_POST["codigo_internacional_5"]) : "";
$importe_total_venta = isset($_POST["total_final"]) ? limpiarCadena($_POST["total_final"]) : "";
$tipo_documento_guia = isset($_POST["tipo_documento_guia"]) && $_POST["tipo_documento_guia"] !== "" ? limpiarCadena($_POST["tipo_documento_guia"]) : NULL;
$codigo_leyenda_1 = isset($_POST["codigo_leyenda_1"]) ? limpiarCadena($_POST["codigo_leyenda_1"]) : "";
$descripcion_leyenda_2 = isset($_POST["descripcion_leyenda_2"]) ? limpiarCadena($_POST["descripcion_leyenda_2"]) : "";
$version_ubl = isset($_POST["version_ubl"]) ? limpiarCadena($_POST["version_ubl"]) : "";
$version_estructura = isset($_POST["version_estructura"]) ? limpiarCadena($_POST["version_estructura"]) : "";
$tipo_moneda = isset($_POST["tipo_moneda"]) ? limpiarCadena($_POST["tipo_moneda"]) : "";
$tasa_igv = isset($_POST["tasa_igv"]) ? limpiarCadena($_POST["tasa_igv"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$codigo_precio = isset($_POST["codigo_precio"]) ? limpiarCadena($_POST["codigo_precio"]) : "";
$tipodocuCliente = isset($_POST["tipo_documento_cliente"]) ? limpiarCadena($_POST["tipo_documento_cliente"]) : "";
$rucCliente = isset($_POST["numero_documento2"]) ? limpiarCadena($_POST["numero_documento2"]) : "";
$RazonSocial = isset($_POST["razon_social2"]) ? limpiarCadena($_POST["razon_social2"]) : "";
$numero_guia = isset($_POST["numero_guia"]) ? limpiarCadena($_POST["numero_guia"]) : "";
$hora = isset($_POST["hora"]) ? limpiarCadena($_POST["hora"]) : "";
$guia_remision_29_2 = isset($_POST["guia_remision_29_2"]) && $_POST["guia_remision_29_2"] !== "" ? limpiarCadena($_POST["guia_remision_29_2"]) : NULL;
$vendedorsitio = isset($_POST["vendedorsitio"]) ? limpiarCadena($_POST["vendedorsitio"]) : "";
$email = isset($_POST["correocli"]) ? limpiarCadena($_POST["correocli"]) : "";
$domicilio_fiscal2 = isset($_POST["domicilio_fiscal2"]) ? limpiarCadena($_POST["domicilio_fiscal2"]) : "";
$tdescuento = isset($_POST["total_dcto"]) ? limpiarCadena($_POST["total_dcto"]) : "";
//$nombre_tributo=isset($_POST["nombre_tributo_4_p"])? limpiarCadena($_POST["nombre_tributo_4_p"]):"";

//Datos de tipo de cambio
$tcambio = isset($_POST["tcambio"]) && $_POST["tcambio"] !== "" ? limpiarCadena($_POST["tcambio"]) : NULL;
$fechatc = isset($_POST["fechatc"]) && $_POST["fechatc"] !== "" ? limpiarCadena($_POST["fechatc"]) : NULL;
$compra = isset($_POST["compra"]) ? limpiarCadena($_POST["compra"]) : "";
$venta = isset($_POST["venta"]) ? limpiarCadena($_POST["venta"]) : "";

$idtcambio = isset($_POST["idtcambio"]) ? limpiarCadena($_POST["idtcambio"]) : "";

$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
$idcajai = isset($_POST["idcajai"]) ? limpiarCadena($_POST["idcajai"]) : "";
$idcajas = isset($_POST["idcajas"]) ? limpiarCadena($_POST["idcajas"]) : "";

$fechacaja = isset($_POST["fechacaja"]) ? limpiarCadena($_POST["fechacaja"]) : "";
$montoi = isset($_POST["montoi"]) ? limpiarCadena($_POST["montoi"]) : "";
$montof = isset($_POST["montof"]) ? limpiarCadena($_POST["montof"]) : "";

$concepto = isset($_POST["concepto"]) ? limpiarCadena($_POST["concepto"]) : "";
$monto = isset($_POST["monto"]) ? limpiarCadena($_POST["monto"]) : "";


$ipagado = isset($_POST["ipagado_final"]) && $_POST["ipagado_final"] !== "" ? limpiarCadena($_POST["ipagado_final"]) : NULL;
$saldo = isset($_POST["saldo_final"]) && $_POST["saldo_final"] !== "" ? limpiarCadena($_POST["saldo_final"]) : NULL;
$tipopago = isset($_POST["tipopago"]) ? limpiarCadena($_POST["tipopago"]) : "";
$nroreferencia = isset($_POST["nroreferencia"]) ? limpiarCadena($_POST["nroreferencia"]) : "";

$tipofactura = isset($_POST["tipofactura"]) ? limpiarCadena($_POST["tipofactura"]) : "";

//---- datos del documnto de cobranza ----//
$fedc = isset($_POST["fecemifa"]) ? limpiarCadena($_POST["fecemifa"]) : "";
$SerieRealdc = isset($_POST["SerieRealfactura"]) ? limpiarCadena($_POST["SerieRealfactura"]) : "";
$numero_facturadc = isset($_POST["numero_factura"]) ? limpiarCadena($_POST["numero_factura"]) : "";
$idseriedc = isset($_POST["seriefactura"]) ? limpiarCadena($_POST["seriefactura"]) : "";
$idclientef = isset($_POST["idclientef"]) ? limpiarCadena($_POST["idclientef"]) : "";
$subtotalfactura = isset($_POST["subtotal_factura"]) ? limpiarCadena($_POST["subtotal_factura"]) : "";
$totaligv = isset($_POST["total_igv_factura"]) ? limpiarCadena($_POST["total_igv_factura"]) : "";
$totalfactura = isset($_POST["total_final_factura"]) ? limpiarCadena($_POST["total_final_factura"]) : "";
$tipomonedafactura = isset($_POST["tipo_moneda_factura"]) ? limpiarCadena($_POST["tipo_moneda_factura"]) : "";
$tipodocucli = isset($_POST["tipodocucli"]) ? limpiarCadena($_POST["tipodocucli"]) : "";
$nrodoccli = isset($_POST["numero_documento_factura"]) ? limpiarCadena($_POST["numero_documento_factura"]) : "";
$razonsf = isset($_POST["razon_socialnfactura"]) ? limpiarCadena($_POST["razon_socialnfactura"]) : "";
$horaf = isset($_POST["horaf"]) ? limpiarCadena($_POST["horaf"]) : "";
$correocliente = isset($_POST["correocliente"]) ? limpiarCadena($_POST["correocliente"]) : "";
$domfiscal = isset($_POST["domicilionfactura"]) ? limpiarCadena($_POST["domicilionfactura"]) : "";
$tcambiofactura = isset($_POST["tcambiofactura"]) && $_POST["tcambiofactura"] !== "" ? limpiarCadena($_POST["tcambiofactura"]) : NULL;
$tipopagonfactura = isset($_POST["tipopagonfactura"]) ? limpiarCadena($_POST["tipopagonfactura"]) : "";
$nroreferenciaf = isset($_POST["nroreferenciaf"]) ? limpiarCadena($_POST["nroreferenciaf"]) : "";
$idempresa2 = isset($_POST["idempresa2"]) ? limpiarCadena($_POST["idempresa2"]) : "";

$tipofacturacoti = isset($_POST["tipofacturacoti"]) ? limpiarCadena($_POST["tipofacturacoti"]) : "";


$idcotizacion = isset($_POST["idcotizacion"]) ? limpiarCadena($_POST["idcotizacion"]) : "";

$ccuotas = isset($_POST["ccuotas"]) && $_POST["ccuotas"] !== "" ? limpiarCadena($_POST["ccuotas"]) : NULL;
$fechavecredito = isset($_POST["fechavecredito"]) && $_POST["fechavecredito"] !== "" ? limpiarCadena($_POST["fechavecredito"]) : NULL;
$montocuota = isset($_POST["montocuota"]) && $_POST["montocuota"] !== "" ? limpiarCadena($_POST["montocuota"]) : NULL;

$otroscargos = isset($_POST["otroscargos"]) && $_POST["otroscargos"] !== "" ? limpiarCadena($_POST["otroscargos"]) : NULL;

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

// ============================================================================
// VALIDACIÓN BACKEND CRÍTICA SUNAT: Factura requiere RUC obligatoriamente
// Resolución de Superintendencia N° 097-2012/SUNAT, Art. 6
// Esta validación previene que se emitan facturas a clientes sin RUC
// incluso si se intenta eludir la validación frontend
// ============================================================================
if ($tipo_documento == "01") { // Si es Factura
    // Validar que el cliente tenga RUC (tipo documento = 6)
    if ($tipodocuCliente != "6") {
        echo json_encode([
            "status" => "error",
            "message" => "ERROR DE VALIDACIÓN SUNAT: Las facturas solo pueden emitirse a clientes con RUC. " .
                        "El cliente seleccionado tiene tipo de documento: " . $tipodocuCliente . ". " .
                        "Por favor, seleccione un cliente con RUC o emita una Boleta."
        ]);
        exit();
    }

    // Validar formato de RUC (11 dígitos)
    if (strlen($rucCliente) != 11 || !ctype_digit($rucCliente)) {
        echo json_encode([
            "status" => "error",
            "message" => "ERROR DE VALIDACIÓN: El RUC debe tener exactamente 11 dígitos numéricos. " .
                        "RUC actual: " . $rucCliente
        ]);
        exit();
    }
}
// ============================================================================

switch ($_GET["op"]) {
    case 'guardaryeditarFactura':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        if (empty($idfactura)) {
            $rspta = $factura->insertar(
                $idusuario,
                $fecha_emision,
                $firma_digital,
                $idempresa,
                $tipo_documento,
                $numeracion,
                $idcliente,
                $total_operaciones_gravadas_codigo,
                $total_operaciones_gravadas_monto,
                $sumatoria_igv_1,
                $sumatoria_igv_2,
                $codigo_tributo_3,
                $nombre_tributo_4,
                $codigo_internacional_5,
                $importe_total_venta,
                $tipo_documento_guia,
                $guia_remision_29_2,
                $codigo_leyenda_1,
                $descripcion_leyenda_2,
                $version_ubl,
                $version_estructura,
                $tipo_moneda,
                $tasa_igv,
                $_POST["idarticulo"],
                $_POST["numero_orden_item"],
                $_POST["cantidad"],
                $_POST["codigo_precio"],
                $_POST["pvt"],
                $_POST["igvBD2"],
                $_POST["igvBD2"],
                $_POST["afectacionigv"],
                $_POST["codigotributo"],
                '',
                '',
                $_POST["igvBD"],
                $_POST["valor_unitario"],
                $_POST["subtotalBD"],
                $_POST["codigo"],
                $_POST["unidad_medida"],
                $idserie,
                $SerieReal,
                $numero_factura,
                $tipodocuCliente,
                $rucCliente,
                htmlspecialchars_decode($RazonSocial),
                $hora,
                $_POST["sumadcto"],
                $vendedorsitio,
                htmlspecialchars_decode($email),
                htmlspecialchars_decode($domicilio_fiscal2),
                $_POST["codigotributo"],
                $tdescuento,
                $tcambio,
                $tipopago,
                $nroreferencia,
                $ipagado,
                $vuelto,
                $_POST["descdet"],
                $total_icbper,
                $tipofactura,
                $_POST["cantidadreal"],
                '',
                $ccuotas,
                $fechavecredito,
                $montocuota,
                $otroscargos,
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

            $hora = date("h:i:s");

            if ($rspta) {
                // ========== AUDITORÍA: Registrar creación de factura ==========
                registrarOperacionCreate('factura', $SerieReal . '-' . $numero_factura, [
                    'idcliente' => $idcliente,
                    'razon_social' => $RazonSocial,
                    'ruc_cliente' => $rucCliente,
                    'total_venta' => $importe_total_venta,
                    'subtotal' => $total_operaciones_gravadas_monto,
                    'igv' => $sumatoria_igv_1,
                    'tipo_moneda' => $tipo_moneda,
                    'tipo_pago' => $tipopago,
                    'items' => count($_POST["idarticulo"]),
                    'tipo_factura' => $tipofactura
                ], "Factura {$SerieReal}-{$numero_factura} creada exitosamente por valor de {$tipo_moneda} {$importe_total_venta}");

                echo "Se guardo correctamente";
            } else {
                // ========== AUDITORÍA: Registrar intento fallido ==========
                registrarAuditoria('CREATE', 'factura', [
                    'descripcion' => "Intento fallido de crear factura {$SerieReal}-{$numero_factura}",
                    'resultado' => 'FALLIDO',
                    'codigo_error' => 'ERROR_INSERTAR_FACTURA',
                    'mensaje_error' => 'No se pudo guardar la factura en la base de datos',
                    'metadata' => [
                        'cliente' => $RazonSocial,
                        'total' => $importe_total_venta
                    ]
                ]);

                echo "No se guardo factura";
            }
        } else {
        }

        break;



    case 'guardaryeditarFactura2':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        require_once "../modelos/Persona.php";
        $persona = new Persona();

        if (empty($idfactura)) {


            if ($idcliente == "N") {
                //$tipo_doc_ide="1";
                $rspta = $persona->insertardeFactura($RazonSocial, $tipo_doc_ide, $rucCliente, $domicilio_fiscal);

                $IdC = $persona->mostrarIdFactura();
                //para ultimo registro de cliente
                while ($reg = $IdC->fetch_object()) {
                    $idcl = $reg->idpersona;
                }


                $rspta = $factura->insertar(
                    $idusuario,
                    $fecha_emision,
                    $firma_digital,
                    $idempresa,
                    $tipo_documento,
                    $numeracion,
                    $idcl,
                    $total_operaciones_gravadas_codigo,
                    $total_operaciones_gravadas_monto,
                    $sumatoria_igv_1,
                    $sumatoria_igv_2,
                    $codigo_tributo_3,
                    $nombre_tributo_4,
                    $codigo_internacional_5,
                    $importe_total_venta,
                    $tipo_documento_guia,
                    $guia_remision_29_2,
                    $codigo_leyenda_1,
                    $descripcion_leyenda_2,
                    $version_ubl,
                    $version_estructura,
                    $tipo_moneda,
                    $tasa_igv,
                    $_POST["idarticulo"],
                    $_POST["numero_orden_item_29"],
                    $_POST["cantidad_item_12"],
                    $_POST["codigo_precio_14_1"],
                    $_POST["pvt"],
                    $_POST["igvBD2"],
                    $_POST["igvBD2"],
                    $_POST["afectacionigv"],
                    $_POST["codigotributo"],
                    '',
                    '',
                    $_POST["igvBD"],
                    $_POST["precio_unitario"],
                    $_POST["subtotalBD"],
                    $_POST["codigo"],
                    $_POST["unidad_medida"],
                    $idserie,
                    $SerieReal,
                    $numero_factura,
                    $tipodocuCliente,
                    $rucCliente,
                    htmlspecialchars_decode($RazonSocial),
                    $hora,
                    $_POST["sumadcto"],
                    $vendedorsitio,
                    htmlspecialchars_decode($email),
                    htmlspecialchars_decode($domicilio_fiscal2),
                    $_POST["codigotributo"],
                    $tdescuento,
                    $tcambio,
                    $tipopago,
                    $nroreferencia,
                    $ipagado,
                    $vuelto,
                    $_POST["descdet"],
                    $total_icbper,
                    $tipofactura,
                    $_POST["cantidadreal"],
                    '',
                    $ccuotas,
                    $fechavecredito,
                    $montocuota,
                    $otroscargos,
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

                $hora = date("h:i:s");

                // Capturar y mostrar error de MySQL si falló
                if ($rspta) {
                    echo "Se guardo correctamente";
                } else {
                    $error_mysql = $factura->getLastError() ? " | Error MySQL: " . $factura->getLastError() : "";
                    echo "No se guardo factura" . $error_mysql;
                }

            } else {

                // DEBUG: Log valores antes de insertar
                error_log("DEBUG FACTURA - tipo_documento_guia: " . var_export($tipo_documento_guia, true));
                error_log("DEBUG FACTURA - guia_remision_29_2: " . var_export($guia_remision_29_2, true));

                $rspta = $factura->insertar(
                    $idusuario,
                    $fecha_emision,
                    $firma_digital,
                    $idempresa,
                    $tipo_documento,
                    $numeracion,
                    $idcliente,
                    $total_operaciones_gravadas_codigo,
                    $total_operaciones_gravadas_monto,
                    $sumatoria_igv_1,
                    $sumatoria_igv_2,
                    $codigo_tributo_3,
                    $nombre_tributo_4,
                    $codigo_internacional_5,
                    $importe_total_venta,
                    $tipo_documento_guia,
                    $guia_remision_29_2,
                    $codigo_leyenda_1,
                    $descripcion_leyenda_2,
                    $version_ubl,
                    $version_estructura,
                    $tipo_moneda,
                    $tasa_igv,
                    $_POST["idarticulo"],
                    $_POST["numero_orden_item_29"],
                    $_POST["cantidad_item_12"],
                    $_POST["codigo_precio_14_1"],
                    $_POST["pvt"],
                    $_POST["igvBD2"],
                    $_POST["igvBD2"],
                    $_POST["afectacionigv"],
                    $_POST["codigotributo"],
                    '',
                    '',
                    $_POST["igvBD"],
                    $_POST["precio_unitario"],
                    $_POST["subtotalBD"],
                    $_POST["codigo"],
                    $_POST["unidad_medida"],
                    $idserie,
                    $SerieReal,
                    $numero_factura,
                    $tipodocuCliente,
                    $rucCliente,
                    htmlspecialchars_decode($RazonSocial),
                    $hora,
                    $_POST["sumadcto"],
                    $vendedorsitio,
                    htmlspecialchars_decode($email),
                    htmlspecialchars_decode($domicilio_fiscal2),
                    $_POST["codigotributo"],
                    $tdescuento,
                    $tcambio,
                    $tipopago,
                    $nroreferencia,
                    $ipagado,
                    $vuelto,
                    $_POST["descdet"],
                    $total_icbper,
                    $tipofactura,
                    $_POST["cantidadreal"],
                    '',
                    $ccuotas,
                    $fechavecredito,
                    $montocuota,
                    $otroscargos,
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

                $hora = date("h:i:s");

                // Capturar y mostrar error de MySQL si falló
                if ($rspta) {
                    echo "Se guardo correctamente";
                } else {
                    $error_mysql = $factura->getLastError() ? " | Error MySQL: " . $factura->getLastError() : "";
                    echo "No se guardo factura" . $error_mysql;
                }


            }



        } else {
        }

        break;
    case 'guardaryeditarfacturadc':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        if (empty($idfactura)) {
            $rspta = $factura->insertar(
                $idusuario,
                $fedc,
                '',
                $idempresa2,
                '01',
                '',
                $idclientef,
                '1001',
                $subtotalfactura,
                $totaligv,
                $totaligv,
                '1000',
                'IGV',
                'VAT',
                $totalfactura,
                '-',
                '-',
                '1000',
                '-',
                '2.0',
                '1.0',
                $tipomonedafactura,
                '0.18',
                $_POST["idarticulof"],
                $_POST["norden"],
                $_POST["cantidadf"],
                '01',
                $_POST["valorunitariof"],
                $_POST["igvitem"],
                $_POST["igvitem"],
                $_POST["afeigv3"],
                $_POST["afeigv4"],
                '',
                '',
                $_POST["igvitem"],
                $_POST["preciof"],
                $_POST["valorventaf"],
                $_POST["codigof"],
                $_POST["unidad_medida"],
                $idseriedc,
                $SerieRealdc,
                $numero_facturadc,
                $tipodocucli,
                $nrodoccli,
                htmlspecialchars_decode($razonsf),
                $horaf,
                $_POST["sumadcto"],
                '',
                htmlspecialchars_decode($correocliente),
                htmlspecialchars_decode($domfiscal),
                '1000',
                '',
                $tcambiofactura,
                $tipopago,
                $nroreferenciaf,
                '',
                '',
                $_POST["descdetf"],
                '',
                '',
                '',
                '',
                $ccuotas,
                '',
                '',
                '',
                $tadc,
                $transferencia,
                $_POST["ncuotahiden"],
                $_POST["montocuotacre"],
                $_POST["fechapago"],
                $fedc
            );

            // $hora=date("h:i:s");

            echo $rspta ? "Factura registrada desde documento de cobranza" : "No se pudieron registrar todos los datos de la factura";
        } else {
        }

        break;


    case 'guardaryeditarfacturaCoti':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        if (empty($idfactura)) {
            $rspta = $factura->insertar(
                $idusuario,
                $fedc,
                '',
                $idempresa2,
                '01',
                '',
                $idclientef,
                '1001',
                $subtotalfactura,
                $totaligv,
                $totaligv,
                '1000',
                'IGV',
                'VAT',
                $totalfactura,
                '-',
                '-',
                '1000',
                '-',
                '2.0',
                '1.0',
                $tipomonedafactura,
                '0.18',
                $_POST["idarticulof"],
                $_POST["norden"],
                $_POST["cantidadf"],
                '01',
                $_POST["valorunitariof"],
                $_POST["igvitem"],
                $_POST["igvitem"],
                $_POST["afeigv3"],
                $_POST["afeigv4"],
                '',
                '',
                $_POST["igvitem"],
                $_POST["preciof"],
                $_POST["valorventaf"],
                $_POST["codigof"],
                $_POST["unidad_medida"],
                $idseriedc,
                $SerieRealdc,
                $numero_facturadc,
                $tipodocucli,
                $nrodoccli,
                htmlspecialchars_decode($razonsf),
                $horaf,
                $_POST["sumadcto"],
                '',
                htmlspecialchars_decode($correocliente),
                htmlspecialchars_decode($domfiscal),
                '1000',
                '',
                $tcambiofactura,
                $tipopago,
                $nroreferenciaf,
                '',
                '',
                $_POST["descdetf"],
                '',
                $tipofacturacoti,
                $_POST["cantidadreal"],
                $idcotizacion,
                $ccuotas,
                '',
                '',
                '',
                $tadc,
                $transferencia,
                $_POST["ncuotahiden"],
                $_POST["montocuotacre"],
                $_POST["fechapago"],
                $fedc
            );

            // $hora=date("h:i:s");

            echo $rspta ? "Factura registrada desde cotización" : "No se pudieron registrar todos los datos de la factura";
        } else {
        }

        break;


    case 'guardaryeditarTcambio':

        date_default_timezone_set('America/Lima');
        $hoy = date('d/m/Y');
        //$hoy=date('Y/m/d');

        if (empty($idtcambio)) {
            $rspta = $factura->insertarTc($fechatc, $compra, $venta);
            echo $rspta ? "Tipo de cambio registrado" : "No se pudieron registrar el tipo de cambio";
        } else {
            $rspta = $factura->editarTc($idtcambio, $fechatc, $compra, $venta);
            echo $rspta ? "Tipo de cambio editado" : "No se pudieron editar los datos del tipo de cambio";
        }
        break;


    case 'guardaryeditarCaja':

        date_default_timezone_set('America/Lima');
        $hoy = date('d/m/Y');
        $estado = $_GET['estado'];


        if (empty($idcaja)) {
            $rspta = $factura->insertarCaja($fechacaja, $montoi, $montof, $_SESSION['idempresa']);
            echo $rspta ? "Caja registrada" : "No se pudieron registrar datos de la caja";

        } else {
            if ($estado == 'ABRIR CAJA') {
                $rspta = $factura->editarCaja($idcaja, $fechacaja, $montoi, $montof, '1', $_SESSION['idempresa']);
                echo $rspta ? "Caja abierta" : "No se pudieron editar los datos de la caja";
            } else {
                $rspta = $factura->editarCaja($idcaja, $fechacaja, $montoi, $montof, '0', $_SESSION['idempresa']);
                echo $rspta ? "Caja Cerrada" : "No se pudieron editar los datos de la caja";

            }
        }
        break;


    case 'guardaringreso':

        $rspta = $factura->registraringreso($idcajai, $concepto, $monto);
        echo $rspta ? "Ingreso a caja" : "No se pudieron registrar el ingreso";

        break;

    case 'guardarsalida':

        $rspta = $factura->registrarsalida($idcajas, $concepto, $monto);
        echo $rspta ? "Salida a caja" : "No se pudieron registrar la salida";

        break;


    case 'anular':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $factura->anular($idfactura);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar anulación de factura ==========
            registrarOperacionAnular('factura', $idfactura,
                "Factura #{$idfactura} anulada con reversión de inventario y generación de archivo SUNAT");

            echo "Factura anulada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de anulación ==========
            registrarAuditoria('ANULAR', 'factura', [
                'registro_id' => $idfactura,
                'descripcion' => "Intento fallido de anular factura #{$idfactura}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_ANULAR_FACTURA',
                'mensaje_error' => 'No se pudo anular la factura (posible estado incorrecto o error en BD)'
            ]);

            echo "Factura no se puede anular";
        }
        break;

    case 'enviarcorreo':
        $idf = $_GET['idfact'];
        $correo = $_GET['ema'];
        $rspta = $factura->enviarcorreo($idf, $correo);
        echo $rspta;
        break;


    case 'traercorreocliente':
        $idfacc = $_GET['iddff'];
        $rspta = $factura->traercorreocliente($idfacc);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;



    case 'generarxml':
        $rspta = $factura->generarxml($idfactura, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'regenerarxml':
        $rspta = $factura->regenerarxml($idfactura, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'mostrarxml':
        $rspta = $factura->mostrarxml($idfactura, $_SESSION['idempresa']);

        if ($rspta == "") {
            $rspta = "No se ha creado";
        }
        echo json_encode($rspta);
        break;

    case 'mostrarrpta':
        $rspta = $factura->mostrarrpta($idfactura, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'enviarxmlSUNAT':
        $rspta = $factura->enviarxmlSUNAT($idfactura, $_SESSION['idempresa']);

        // ========== AUDITORÍA: Registrar envío a SUNAT ==========
        // Verificar si el envío fue exitoso analizando la respuesta
        $exitoso = (stripos($rspta, 'aceptad') !== false || stripos($rspta, 'éxito') !== false);

        registrarEnvioSUNAT('factura', $idfactura, $exitoso, $rspta);

        echo $rspta;
        break;


    case 'consultarcdr':
        $rspta = $factura->reconsultarcdr($idfactura, $_SESSION['idempresa']);
        echo $rspta;
        break;


    case 'enviarxmlSUNATbajas':
        $rspta = $factura->enviarxmlSUNATbajas($idfactura, $_SESSION['idempresa']);
        echo $rspta;
        break;


    case 'downFtp':
        $rspta = $factura->downftp($idfactura, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'ftp':
        $rspta = $factura->ftp();
        echo $rspta;
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
        $rspta = $factura->baja($idfactura, $hoy, $com, $hor);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar baja de factura a SUNAT ==========
            registrarAuditoria('BAJA', 'factura', [
                'registro_id' => $idfactura,
                'descripcion' => "Factura #{$idfactura} dada de baja ante SUNAT",
                'metadata' => [
                    'fecha_baja' => $hoy,
                    'hora' => $hor,
                    'comentario' => $com
                ]
            ]);

            echo "La factura esta de baja y anulada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de baja ==========
            registrarAuditoria('BAJA', 'factura', [
                'registro_id' => $idfactura,
                'descripcion' => "Intento fallido de dar de baja factura #{$idfactura}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_BAJA_FACTURA',
                'mensaje_error' => 'No se pudo dar de baja la factura',
                'metadata' => [
                    'comentario_intento' => $com
                ]
            ]);

            echo "Factura no se dar de baja";
        }
        break;

    case 'actualizarNumero':
        require_once "../Modelos/Numeracion.php";
        $numeracion = new Numeracion();

        $num = $_GET['Num'];
        $idnumeracion = $_GET['Idnumeracion'];
        $rspta = $numeracion->UpdateNumeracion($num, $idnumeracion);
        break;

    case 'mostrar':
        $rspta = $factura->mostrar($idfactura);
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

        $rspta = $numeracion->llenarSerieFactura($idusuario);

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idnumeracion . '>' . $reg->serie . '</option>';

        }
        break;


    // Carga de tipos de documentos para venta
    case 'selectDocumento':
        require_once "../modelos/Venta.php";
        $venta = new Venta();

        $rspta = $venta->listarD();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->documento . '>' . $reg->documento . '</option>';

        }
        break;


    //Carga de los últimos numeros de la numeración de acuerdo a la serie seleccionada
    case 'llenarNumero':
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


    case 'listarClientesfactura':
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


    case 'listarArticulosfactura':
        $tmm = $_GET['itm'];
        $tpff = $_GET['tipof'];
        $tipoprecioa = $_GET['tipoprecioaa'];
        $almacen = $_GET['alm'];
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        if ($tmm == '0') {
            $rspta = $articulo->listarActivosVentaumventa($_SESSION['idempresa'], $tpff, $almacen, $tipoprecioa);
            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning btn-sm" onclick="agregarDetalle(0,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\'  , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\',  \'' . $reg->nombreum . '\' ,
                \'' . str_replace("\r\n", " ", $reg->descrip) . '\', \'' . $reg->tipoitem . '\')">
                <span class="fa fa-plus"></span>
                Continuo
                </button>'
                        .
                        '<button class="btn btn-success btn-sm" onclick="agregarDetalle(1,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\'  , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\',  \'' . $reg->nombreum . '\' ,
                \'' . str_replace("\r\n", " ", $reg->descrip) . '\', \'' . $reg->tipoitem . '\'); cerrarModal();">
                <span class="fa fa-outdent"></span>
                individual
                </button>'
                    ,
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->factorconversion,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='60px' width='60px' >" :
                        "<img src='../files/articulos/" . $reg->imagen . "' height='60px' width='60px'>",
                    "7" => ($reg->imagen == "") ? "../files/articulos/simagen.png" :
                        "../files/articulos/" . $reg->imagen,
                    "8" => $reg->idarticulo
                );
            }

        } else {

            $rspta = $articulo->listarActivosVentaumcompra($_SESSION['idempresa'], $tpff, $almacen, $tipoprecioa);
            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning btn-sm" onclick="agregarDetalleItem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\' , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\' , \'' . str_replace("\r\n", " ", $reg->descrip) . '\',  \'' . $reg->factorconversion . '\')">
                <span class="fa fa-plus"></span>
                </button>',
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->stock,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='120' width='120px'>" :
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

    //esto es para el point of sale VENTA POS//       
    case 'listarArticulosfacturaPOS':
        $tmm = $_GET['itm'];
        $tpff = $_GET['tipof'];
        $tipoprecioa = $_GET['tipoprecioaa'];
        $almacen = $_GET['alm'];
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        if ($tmm == '0') {
            $rspta = $articulo->listarActivosVentaumventa($_SESSION['idempresa'], $tpff, $almacen, $tipoprecioa);
            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning btn-sm" onclick="agregarDetalle(0,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\'  , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\',  \'' . $reg->nombreum . '\' ,
                \'' . str_replace("\r\n", " ", $reg->descrip) . '\', \'' . $reg->tipoitem . '\')">
                <span class="fa fa-plus"></span>
                Continuo
                </button>'
                        .
                        '<button class="btn btn-success btn-sm" onclick="agregarDetalle(1,' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\'  , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\',  \'' . $reg->nombreum . '\' ,
                \'' . str_replace("\r\n", " ", $reg->descrip) . '\', \'' . $reg->tipoitem . '\'); cerrarModal();">
                <span class="fa fa-outdent"></span>
                individual
                </button>'
                    ,
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->factorconversion,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='120px' width='60px' >" :
                        "<img src='../files/articulos/" . $reg->imagen . "' height='120px' width='60px'>"
                );
            }

        } else {

            $rspta = $articulo->listarActivosVentaumcompra($_SESSION['idempresa'], $tpff, $almacen, $tipoprecioa);
            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->stock <= $reg->limitestock) ? '<label style="color: red;">Limite stock es: </label>' . '<label style="color: red;">' . $reg->limitestock . '</label>'
                        :
                        '<button class="btn btn-warning btn-sm" onclick="agregarDetalleItem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\' , \'' . $reg->factorconversion . '\' , \'' . $reg->factorc . '\' , \'' . str_replace("\r\n", " ", $reg->descrip) . '\',  \'' . $reg->factorconversion . '\')">
                <span class="fa fa-plus"></span>
                </button>',
                    "1" => $reg->nombre,
                    "2" => $reg->codigo,
                    "3" => $reg->nombreum,
                    "4" => $reg->precio_venta,
                    "5" => $reg->stock,
                    "6" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='120' width='120px'>" :
                        "<img src='../files/articulos/" . $reg->imagen . "' height='120px' width='120px'>"
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

    //cerrar point of sale VENTA POS//

    case 'listarArticulosfacturaItem':
        //$idempresa=$_GET['idempresaA'];
        $tipoprecio = $_GET['tipoprecio'];
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        switch ($tipoprecio) {
            case '1':
                $rspta = $articulo->listarActivosVenta($_SESSION['idempresa']);
                break;
            case '2':
                $rspta = $articulo->listarActivosVenta2($_SESSION['idempresa']);
                break;
            case '3':
                $rspta = $articulo->listarActivosVenta3($_SESSION['idempresa']);
                break;
            default:
                break;
        }
        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => '<button class="btn btn-warning" onclick="agregarDetalleItem(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . htmlspecialchars($reg->nombre) . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->unidad_medida . '\' ,\'' . $reg->precio_unitario . '\',\'' . $reg->cicbper . '\', \'' . $reg->mticbperu . '\')"><span class="fa fa-plus"></span></button>',
                "1" => $reg->nombre,
                "2" => $reg->codigo,
                "3" => $reg->unidad_medida,
                "4" => $reg->precio_venta,
                "5" => number_format($reg->stock, 2),
                "6" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px' >"
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
                "6" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px' >"
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



    case 'listarArticulosNC':
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        $idempresa = $_GET['idempresa'];
        $rspta = $articulo->listarActivosVentaumventa($idempresa);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => '<button class="btn btn-warning" onclick="agregarDetalle(' . $reg->idarticulo . ',\'' . $reg->familia . '\',\'' . $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . $reg->nombre . '\',\'' . $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->unidad_medida . '\' ,\'' . $reg->precio_unitario . '\')"><span class="fa fa-plus"></span></button>',
                "1" => $reg->nombre,
                "2" => $reg->codigo,
                "3" => $reg->unidad_medida,
                "4" => $reg->precio_venta,
                "5" => "<img src='../files/articulos/" . $reg->imagen . "' height='50px' width='50px' >"

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
        $idempr = $_SESSION['idempresa'];
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($idempr);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos0

        $path = $rutaenvio;
        $pathFirma = $rutafirma;
        $pathRpta = $rutarpta;

        // Verificar si los paths son válidos
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
        }

        if (is_dir($pathFirma)) {
            $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        }

        if (is_dir($pathRpta)) {
            $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        }


        // $path  = $rutaenvio;
        // $pathFirma=$rutafirma;
        // $pathRpta  = $rutarpta;
        // // Arreglo con todos los nombres de los archivos
        // $files = array_diff(scandir($path), array('.', '..'));
        // $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        // $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        //=============================================================

        $rspta = $factura->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        $urlT = '../reportes/exTicketFactura.php?id=';
        $urlF = '../reportes/exFactura.php?id=';
        $urlC = '../reportes/exFacturaCompleto.php?id=';

        while ($reg = $rspta->fetch_object()) {
            if ($reg->tipo_documento_07 == 'Ticket') {
                $url = '../reportes/exTicketFactura.php?id=';
            } else {
                $url = '../reportes/exFactura.php?id=';
            }
            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $fileBaja = $reg->numero_ruc . "-RA" . $reg->fechabaja . "-011";
            $rptaSunat = $reg->CodigoRptaSunat;


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
                        $UpSt = $factura->ActualizarEstado($reg->idfactura, $st);
                    }
                }
                $factura->generarxml($reg->idfactura, $_SESSION['idempresa']);
            } elseif ($reg->estado == '4') {
                $factura->enviarxmlSUNAT($reg->idfactura, $_SESSION['idempresa']);
                $st = "5";
                $UpSt = $factura->ActualizarEstado($reg->idfactura, $st);

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
            //$client=substr($reg->cliente,0,10);//oficial
            // Ajustar el nombre del artículo
            // Truncar nombre de artículo si es demasiado largo
            $nombre_articulo_mostrado = (strlen($reg->nombre_articulo) > 10)
                ? substr($reg->nombre_articulo, 0, 10) . '...'
                : $reg->nombre_articulo;

            // Truncar nombre de cliente si es demasiado largo
            $nombre_cliente_mostrado = (strlen($reg->cliente) > 10)
                ? substr($reg->cliente, 0, 10) . '...'
                : $reg->cliente;



            $data[] = array(
                "0" => '
                <div class="btn-group mb-1">
                <div class="dropdown">
                    <!-- Modifica el estilo del botón aquí con `justify-content` y `align-items` -->
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" style="justify-content: center; align-items: center;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" onclick="preticket2(' . $reg->idfactura . ')">Formato Ticket</a>
                        <a class="dropdown-item" onclick="prea4completo2(' . $reg->idfactura . ')">Formato A4</a>
                        <a class="dropdown-item" onclick="baja(' . $reg->idfactura . ')">Dar de baja</a>
                        <a class="dropdown-item" onclick="enviarcorreo(' . $reg->idfactura . ')">Enviar por correo
                        </a>
                    </div>
                </div>
            </div>
            ',

                "1" => $reg->fecha,
                "2" => '<span title="' . $reg->cliente . '">' . $nombre_cliente_mostrado . '</span>',
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_08,
                "5" => $reg->formapago,
                "6" => '<span title="' . $reg->nombre_articulo . '">' . $nombre_articulo_mostrado . '</span>',
                "7" => $reg->importe_total_venta_27,

                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido se genera el xml
                    ? '<i class="fa fa-file-text-o" style="font-size: 14px; color:#BA4A00;"> <span>' . $reg->DetalleSunat . '</span><i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span></i>'

                    : (($reg->estado == '4') ? '<i class="fa fa-thumbs-up" style="font-size: 14px; color:#239B56;"> <span>' . $reg->DetalleSunat . '</span> </i>' //si esta firmado

                        : (($reg->estado == '3') ? '<i class="fa fa-dot-circle-o" style="font-size: 14px; color:#E59866;"> <span>' . $reg->DetalleSunat . '</span></i> ' // si esta de baja

                            : (($reg->estado == '0') ? '<span>' . $reg->DetalleSunat . '</span><br><small style="color:green;"><i style="color:green"class="fa fa-credit-card-alt"></i> Comprobante Afectado</small>' //si esta firmado

                                : (($reg->estado == '5') ? '<div class="boleta-status"><span style="color:#0d6efd;">' . $reg->DetalleSunat . '</span><img style="margin-left:47px; margin:0 auto;" src="../public/images/aceptado.png" width="28px" height="28px" title="Estado de sunat aceptado"></div>' // Si esta aceptado por SUNAT

                                    : '<i class="fa fa-newspaper" style="font-size: 14px; color:#239B56;"> <span>' . $reg->DetalleSunat . '</span></i> ')))),

                //Opciones de envio
                "9" =>
                    '<a style="cursor:pointer;" onclick="mostrarxml(' . $reg->idfactura . ')"><img src="../public/images/xml.png" width="28px" height="28px" title="Descargar XML"></a>',
                "10" =>
                    ' <a style="cursor:pointer;" onclick="mostrarrpta(' . $reg->idfactura . ')"><img src="../public/images/cdr.png" width="28px" height="28px" title="Descargar CDR"></a>',
                "11" =>
                    ' <a style="cursor:pointer;" onclick="prea4completo2(' . $reg->idfactura . ')"><img src="../public/images/pdf.png" width="28px" height="28px" title="Descargar PDF"></a>'

            );
        } //Fin While

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






    case 'listar':
        //$idempr=$_GET['idempresa'];
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos

        $path = $rutaenvio;
        $pathFirma = $rutafirma;
        $pathRpta = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        //=============================================================

        $rspta = $factura->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        $urlT = '../reportes/exTicketFactura.php?id=';
        $urlF = '../reportes/exFactura.php?id=';
        $urlC = '../reportes/exFacturaCompleto.php?id=';

        while ($reg = $rspta->fetch_object()) {
            if ($reg->tipo_documento_07 == 'Ticket') {
                $url = '../reportes/exTicketFactura.php?id=';
            } else {
                $url = '../reportes/exFactura.php?id=';
            }
            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $fileBaja = $reg->numero_ruc . "-RA" . $reg->fechabaja . "-011";
            $rptaSunat = $reg->CodigoRptaSunat;


            $stt = '';
            $vs = '';
            $sunatFirma = '';
            $sunatAceptado = 'Class';

            $estadoenvio = '1';

            $mon = "";
            if ($reg->moneda == "USD") {
                $mon = '<i style="color:green;" data-toggle="tooltip" title="Por T.C. ' . $reg->tcambio . ' = ' . $reg->valordolsol . ' PEN">$</i>';
            }


            $fpago = "";
            if ($reg->formapago == "Credito") {
                $fpago = '<i style="color:red;" data-toggle="tooltip" title="Comprobante al crédito">Cred.</i>';
            }




            //=====================================================================================
            //$client=substr($reg->cliente,0,10);
            $data[] = array(
                "0" =>
                    '<div class="dropdown">
                <button  class="btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                :::
                <span class="caret"></span></button>
                <ul class="dropdown-menu pull-center">

                <li>
                     <a  onclick="baja(' . $reg->idfactura . ')" style="display:' . $vs . ';  color:red;">
                   <i class="fa fa-level-down"  data-toggle="tooltip" title="Dar de baja" onclick=""></i>Dar de baja
                    </a>

                <a  onclick="duplicarf(' . $reg->idfactura . ')"  style="color:green;"  data-toggle="tooltip" title="Duplicar factura" ' . $stt . '">
                  <i  class="fa fa-files-o"></i>
                  Duplicar factura
                  </a>


                  <a  onclick="crearnoti(' . $reg->idfactura . ')">
                  <i  class="fa fa-bell"></i>
                  Crear notificación
                  </a>


                  </li>

                  <li>
                  <a  onclick="prea42copias2(' . $reg->idfactura . ')">
                   <i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir formato 2 copias" onclick=""></i>
                   Imprimir formato 2 copias
                    </a>
                    </i>

                   <li>
                  <a  onclick="preticket2(' . $reg->idfactura . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir Ticket"> </i>
                  Imprimir Ticket
                     </a>
                  </li>


                   <li>
                 <a onclick="prea4completo2(' . $reg->idfactura . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir formato completo"> </i>Imprimir formato completo
                     </a>
                  </li>

                   <li>
                 <a onclick="enviarcorreo(' . $reg->idfactura . ')"><i class="fa  fa-send"  data-toggle="tooltip" title=""> </i>Enviar por correo
                     </a>
                  </li>




                </ul>
                </div>',

                "1" => $reg->fecha,
                "2" => $reg->cliente,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_08,
                "5" => $reg->importe_total_venta_27 . " " . $mon . " " . $fpago,
                "6" => ($reg->tarjetadc == '1') ? '<img src="../files/articulos/tarjetadc.png" width="20px"
                data-toggle="tooltip" title="TARJETA ' . $reg->montotarjetadc . '">' : '',
                "7" => ($reg->transferencia == '1') ? '<img src="../files/articulos/transferencia.png" width="20px" data-toggle="tooltip" title="BANCO ' . $reg->montotransferencia . '">' : '',

                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido
                    ? '<span style="color:#BA4A00;">' . $reg->DetalleSunat . '</span>'
                    : (($reg->estado == '4') ? '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                        : (($reg->estado == '3') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' // si esta de baja

                            : (($reg->estado == '0') ? '<span style="color:#E59866;">c/nota</span>' //si esta firmado

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
                   <a onclick="generarxml(' . $reg->idfactura . ')" ' . $sunatFirma . '="class_a_href"><i class="fa fa-download"  style="color:orange; font-size:18px;" data-toggle="tooltip" title="Generar xml"></i>Generar xml</a>
                  </li>


                  <li>
                    <a onclick="enviarxmlSUNAT(' . $reg->idfactura . ')" ><i class="fa fa-send"  style="color:red; font-size:18px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i>Enviar a SUNAT</a>
                  </li>


                  <li>
                    <a onclick="mostrarxml(' . $reg->idfactura . ')"><i class="fa fa-check" style="color:orange; font-size:18px;"  data-toggle="tooltip" title="Mostrar XML"></i>Mostrar XML</a>
                  </li>

                   <li>
                   <a onclick="mostrarrpta(' . $reg->idfactura . ')"><i class="fa fa-check" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Mostrar respuesta CDR"></i>Mostrar respuesta</a>
                  </li>

                  <li>
                   <a href="https://n9.cl/fo5y" target=_blank >  <img src="../public/images/sunat.png" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Consulta de validez con SUNAT"></i>Consulta de validez</a>
                  </li>


                 <li>
                    <a onclick="consultarcdr(' . $reg->idfactura . ')" ><i class="fa fa-refresh"  style="color:red; font-size:18px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i>Reconsultar a SUNAT</a>
                  </li>

                  <li>
                    <a onclick="cambiartarjetadc(' . $reg->idfactura . ')" ><i class="fa fa-credit-card"></i> Cambiar a tarjeta</a>
                  </li>

                  <li>
                    <a onclick="montotarjetadc(' . $reg->idfactura . ')" ><i class="fa fa-money"></i> Modificar monto tarjeta </a>
                  </li>


                  <li>
                    <a onclick="cambiartransferencia(' . $reg->idfactura . ')" ><i class="fa fa-exchange"></i> Cambiar a transferencia </a>
                  </li>

                  <li>
                    <a onclick="montotransferencia(' . $reg->idfactura . ')" ><i class="fa fa-money"></i> Modificar monto transferencia </a>
                  </li>



                   </ul>
                </div>'
            );
        } //Fin While

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
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos

        $path = $rutaenvio;
        $pathFirma = $rutafirma;
        $pathRpta = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        //=============================================================

        $rspta = $factura->listarValidar($ano, $mes, $dia, $_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        $urlT = '../reportes/exTicketFactura.php?id=';
        $urlF = '../reportes/exFactura.php?id=';
        $urlC = '../reportes/exFacturaCompleto.php?id=';

        while ($reg = $rspta->fetch_object()) {
            if ($reg->tipo_documento_07 == 'Ticket') {
                $url = '../reportes/exTicketFactura.php?id=';
            } else {
                $url = '../reportes/exFactura.php?id=';
            }
            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $fileBaja = $reg->numero_ruc . "-RA" . $reg->fechabaja . "-011";




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
            } else {
                $send = 'none';
            }


            if ($reg->estado == '3') {
                $stt = 'disabled';
                $vs = 'none';
                $sunat = '';
                $sunatFirma = 'class';
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


            $fpago = "";
            if ($reg->formapago == "Credito") {
                $fpago = '<i style="color:red;" data-toggle="tooltip" title="Comprobante al crédito">Cred.</i>';
            }


            //=====================================================================================
            //$client=substr($reg->cliente,0,10);
            $data[] = array(
                "0" =>

                    '
          <input type="hidden" name="idoculto[]" id="idoculto[]" value="' . $reg->idfactura . '">
          <input type="hidden" name="estadoocu[]" id="estadoocu[]" value="' . $reg->estado . '">
        
          <div class="btn-group mb-1">
            <div class="dropdown">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   :::
                </button>
                <div class="dropdown-menu" style="">
                    <a  class="dropdown-item" onclick="baja(' . $reg->idfactura . ')" style="display:' . $vs . ';  color:red;">Dar de baja</a>
                    <a  hidden class="dropdown-item" target="_blank" href="' . $url . $reg->idfactura . '">Imprimir formato 2 copias</a>
                    <a  class="dropdown-item" target="_blank" href="' . $urlT . $reg->idfactura . '">Imprimir Ticket</a>
                    <a class="dropdown-item" target="_blank" href="' . $urlC . $reg->idfactura . '">Imprimir formato completo</a>
                    <a class="dropdown-item" onclick="enviarcorreo(' . $reg->idfactura . ')">Enviar por correo</a>
                </div>
            </div>
        </div>'


                ,

                "1" => $reg->fecha,
                "2" => $reg->cliente,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_08,
                "5" => $reg->importe_total_venta_27 . " " . $mon . " " . $fpago,

                "6" => ($reg->tarjetadc == '1') ? '<img src="../files/articulos/tarjetadc.png" width="20px"
                data-toggle="tooltip" title="TARJETA ' . $reg->montotarjetadc . '">' : '',
                "7" => ($reg->transferencia == '1') ? '<img src="../files/articulos/transferencia.png" width="20px" data-toggle="tooltip" title="BANCO ' . $reg->montotransferencia . '">' : '',

                //Actualizado ===============================================
                "8" => ($reg->estado == '1') //si esta emitido
                    ? '<span style="color:#BA4A00;">' . $reg->DetalleSunat . '</span>'
                    : (($reg->estado == '4') ? '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>' //si esta firmado

                        : (($reg->estado == '3') ? '<span style="color:#E59866;">' . $reg->DetalleSunat . '</span>' // si esta de baja

                            : (($reg->estado == '0') ? '<span style="color:#E59866;">c/nota</span>' //si esta firmado

                                : (($reg->estado == '5') ? '<span style="color:green;">' . $reg->DetalleSunat . '</span>' // Si esta aceptado por SUNAT

                                    : '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>')))),

                //Opciones de envio
                "9" =>

                    ' <div class="btn-group mb-1">
             <div class="dropdown">
                 <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     Opciones
                 </button>
                 <div class="dropdown-menu" style="">
                 <a class="dropdown-item" onclick="regenerarxml(' . $reg->idfactura . ')" >Regenerar xml</a>  
                 <a class="dropdown-item" onclick="enviarxmlSUNATbajas(' . $reg->idfactura . ')">Reenviar xml</a> 
                 <a hidden class="dropdown-item" onclick="generarxml(' . $reg->idfactura . ')" >Generar xml</a>
                <a class="dropdown-item" onclick="mostrarxml(' . $reg->idfactura . ')">Mostrar XML</a>
                <a class="dropdown-item" onclick="enviarxmlSUNAT(' . $reg->idfactura . ')"  ' . $sunatAceptado . '="class_a_href" >Enviar a SUNAT</a>
                <a class="dropdown-item" onclick="mostrarrpta(' . $reg->idfactura . ')">Mostrar respuesta</a>
                <a hidden class="dropdown-item" href="https://n9.cl/fo5y" target=_blank >  <img src="../public/images/sunat.png" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Consulta de validez con SUNAT"></i>Consulta de validez</a>
                <a hidden class="dropdown-item" onclick="consultarcdr(' . $reg->idfactura . ')" >Reconsultar a SUNAT</a>
                <a class="dropdown-item" onclick="cambiartarjetadc(' . $reg->idfactura . ')" > Cambiar a tarjeta</a>   
                <a class="dropdown-item" onclick="montotarjetadc(' . $reg->idfactura . ')" > Modificar monto tarjeta </a>
                <a class="dropdown-item" onclick="cambiartransferencia(' . $reg->idfactura . ')" > Cambiar a transferencia </a>
                <a class="dropdown-item" onclick="montotransferencia(' . $reg->idfactura . ')" > Modificar monto transferencia </a>
                 </div>
             </div>
         </div>'
                ,

                "10" => ($reg->estado == '1' || $reg->estado == '4') ? '<input type="checkbox"  id="chid[]"  name="chid[]">' : '<input type="checkbox"  id="chid[]"  name="chid[]" style="display:none;"> '
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



    case 'autonumeracion':

        $numeracion = new Numeracion();
        $Ser = $_GET['ser'];
        //$idempresa=$_GET['idempresa'];
        $rspta = $numeracion->llenarNumeroFactura($Ser);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->Nnumero;
        }
        break;


    case 'listarClientesfacturaxDoc':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $doc = $_GET['doc'];
        $rspta = $persona->buscarClientexDocFactura($doc);

        echo json_encode($rspta);

        break;


    case 'listarClientesliqui':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $doc = $_GET['doc'];
        $rspta = $persona->buscarClientes($doc);
        echo json_encode($rspta);
        break;



    case 'listarClientesfacturaxDocNuevos':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $rspta = $persona->buscarClientexDocFacturaNuevos();
        echo json_encode($rspta);

        break;

    case 'mostrarultimocomprobante':
        $rspta = $factura->mostrarultimocomprobante($_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'mostrarultimocomprobanteId':
        $rspta = $factura->mostrarultimocomprobanteId($_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'enviarcorreoultimocomprobante':
        $rspta = $factura->enviarUltimoComprobantecorreo($_SESSION['idempresa']);
        echo $rspta;
        break;


    case 'estadoDoc':
        $rspta = $factura->mostrarCabFac();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $archivo = $reg->$reg->ruc . "-" . $reg->tipodoc . "-" . $reg->numerodoc;
        }
        echo json_encode($archivo);
        break;

    case 'listarArticulosfacturaxcodigo':
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        //$idempresa=$_GET['idempresa'];
        $codigob = $_GET['codigob'];
        $tipre = $_GET['tipp'];
        $rspta = $articulo->listarActivosVentaxCodigo($codigob, $tipre);
        echo json_encode($rspta);
        break;

    case 'busquedaPredic':
        require_once "../modelos/Factura.php";
        $factura = new Factura();
        $buscar = $_POST['b'];
        $rspta = $factura->AutocompletarRuc($buscar);
        echo json_encode($rspta);
        break;

    case 'selectNombreCli':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $nombre = $_POST['nombre'];
        $rspta = $persona->listarclienteFact($nombre);

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->razon_social . '</option>';
        }
        break;




    case 'uploadFtp':
        $rspta = $factura->uploadFtp($idfactura);
        echo $rspta;
        break;

    case 'listarDR':

        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        //$idempresa=$_GET['idempresa'];

        $rspta = $factura->listarDR($ano, $mes, $_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->numerofactura,
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
                    ? '<button class="btn btn-warning"  onclick="ConsultaDR(' . $reg->idfactura . ')"> <i class="fa fa-eye" data-toggle="tooltip" title="Ver documento" ></i> </button>' : ''
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

    case 'listarDRdetallado':
        $id = $_GET['idcomp'];
        $idempresa = $_GET['idempresa'];
        //$idcomp = '28';
        $rspta = $factura->listarDRdetallado($id, $idempresa);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => ($reg->codigo_nota == '07')
                    ? '<i style="color:#BA4A00;"  > <span>NOTA DE CRÉDITO</span></i>' : '<i  style="color:#E59866;" > <span>NOTA DE DEBITO</span></i>',
                "1" => $reg->numero,
                "2" => $reg->fecha,
                "3" => $reg->motivo,
                "4" => $reg->subtotal,
                "5" => $reg->igv,
                "6" => $reg->total
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

    case 'selectTributo':
        require_once "../modelos/Factura.php";
        $factura = new Factura();

        $rspta = $factura->tributo();
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->codigo . '>' . $reg->descripcion . '</option>';
        }
        break;





    case 'selectAlmacen':
        require_once "../modelos/Factura.php";
        $factura = new Factura();

        $rspta = $factura->almacenlista();
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idalmacen . '>' . $reg->nombre . '</option>';
        }
        break;






    case 'tcambiodia':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        date_default_timezone_set('America/Lima');
        $hoy = date('Y/m/d');

        $rspta = $consulta->mostrartipocambio($hoy);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->venta;
        }
        break;


    case 'tcambiodiaCompra':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        date_default_timezone_set('America/Lima');
        $hoy = date('Y/m/d');

        $rspta = $consulta->mostrartipocambio($hoy);
        while ($reg = $rspta->fetch_object()) {
            echo $reg->compra;
        }
        break;


    case 'listarcaja':
        $rspta = $factura->listarcaja($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->idcaja,
                "1" => $reg->fecha,
                "2" => $reg->montoi,
                "3" => $reg->montof

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


    case 'listarvalidarcaja':
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];

        $rspta = $factura->listarValidarcaja($ano, $mes, $dia, $_SESSION['idempresa']);
        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->monto,
                "2" => $reg->concepto,
                "3" => ($reg->tipo == 'INGRESO')
                    ? '<i style="color:green;"> <span>INGRESO</span></i>' : '<i  style="color:red;" > <span>SALIDA</span></i>'
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


    case 'selectunidadmedida':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        $rspta = $consulta->selectumedida();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->abre . '>' . $reg->nombreum . '</option>';

        }
        break;


    case 'selectunidadmedidanuevopro':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();

        $rspta = $consulta->selectumedida();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idunidad . '>' . $reg->nombreum . " | " . $reg->abre . '</option>';

        }
        break;


    case 'datostemporizadopr':
        $rspta = $factura->consultatemporizador();
        echo json_encode($rspta);
        break;

    case 'activartempo':
        $st = $_GET['st'];
        $tiempo = $_GET['tiempo'];
        $rspta = $factura->onoffTempo($st, $tiempo);
        echo ($rspta);
        break;

    case 'regenerarxmlEA':
        $ano = $_GET['anO'];
        $mes = $_GET['meS'];
        $dia = $_GET['diA'];
        $idcomprobante = $_GET['idComp'];
        $estadoOcu = $_GET['SToc'];
        $Chfac = $_GET['Ch'];
        $opcii = $_GET['opt'];

        switch ($opcii) {
            case 'firmar':
                $rspta = $factura->solofirma($ano, $mes, $dia, $idcomprobante, $estadoOcu, $Chfac, $_SESSION['idempresa']);
                break;
            case 'fienviar':
                $rspta = $factura->generarxmlEA($ano, $mes, $dia, $idcomprobante, $estadoOcu, $Chfac, $_SESSION['idempresa']);

            default:
                # code...
                break;
        }

        echo json_encode($rspta);
        break;


    case 'tcambiog':
        $tcf = $_GET['feccf'];
        $rspta = $factura->mostrartipocambio($tcf);
        echo json_encode($rspta);
        break;


    case 'consultaRucSunat':
        $token = 'apis-token-1.aTSI1U7KEuT-6bbbCguH-4Y8TI6KS73N';
        $nrucc = $_GET['nroucc'];

        // Iniciar llamada a API
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.apis.net.pe/v1/ruc?numero=' . $nrucc,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Referer: https://apis.net.pe/api-ruc',
                    'Authorization: Bearer' . $token
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        // Datos listos para usar
        $datosRucCli = json_decode($response);
        echo json_encode($datosRucCli);
        break;




    case 'cambiartarjetadc_':
        $opc = $_GET['opcion'];
        $rspta = $factura->cambiartarjetadc($idfactura, $opc);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;

    case 'montotarjetadc_':
        $mto = $_GET['monto'];
        $rspta = $factura->montotarjetadc($idfactura, $mto);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;


    case 'cambiartransferencia':
        $opc = $_GET['opcion'];
        $rspta = $factura->cambiartransferencia($idfactura, $opc);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;

    case 'montotransferencia':
        $mto = $_GET['monto'];
        $rspta = $factura->montotransferencia($idfactura, $mto);
        echo $rspta ? "Cambio realizado correctamente" : "Problemas al cambiar";
        break;


    case 'duplicar':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        $rspta = $factura->duplicar($idfactura);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar duplicación de factura ==========
            registrarAuditoria('DUPLICAR', 'factura', [
                'registro_id' => $idfactura,
                'descripcion' => "Factura #{$idfactura} duplicada exitosamente (nueva factura ID: {$rspta})",
                'metadata' => [
                    'factura_original' => $idfactura,
                    'factura_nueva' => $rspta
                ]
            ]);

            echo "Factura ha sido duplicada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de duplicación ==========
            registrarAuditoria('DUPLICAR', 'factura', [
                'registro_id' => $idfactura,
                'descripcion' => "Intento fallido de duplicar factura #{$idfactura}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_DUPLICAR_FACTURA',
                'mensaje_error' => 'No se pudo duplicar la factura'
            ]);

            echo "Factura no se pudo duplicar";
        }
        break;


    case 'traerclinoti':
        $rspta = $factura->traerclinoti($idfactura);
        echo json_encode($rspta);
        break;


}
?>