<?php 
if (strlen(session_id()) < 1) 
  session_start();
 
require_once "../modelos/Liquidacion.php";

$liquidacion=new Liquidacion();

//Factura
$idliquidacion=isset($_POST["idliquidacion"])? limpiarCadena($_POST["idliquidacion"]):"";
//$idusuario="2";
$idusuario=$_SESSION["idusuario"];
$fecha_emision=isset($_POST["fecha_emision"])? limpiarCadena($_POST["fecha_emision"]):""; 
$firma_digital=isset($_POST["firma_digital"])? limpiarCadena($_POST["firma_digital"]):"";
$idempresa=isset($_POST["idempresa"])? limpiarCadena($_POST["idempresa"]):"";
$tipo_documento=isset($_POST["tipo_documento"])? limpiarCadena($_POST["tipo_documento"]):"";
$tipo_documento_dc=isset($_POST["tipo_documento_dc"])? limpiarCadena($_POST["tipo_documento_dc"]):"";
$idserie=isset($_POST["serie"])? limpiarCadena($_POST["serie"]):"";
$SerieReal=isset($_POST["SerieReal"])? limpiarCadena($_POST["SerieReal"]):"";
$numero_factura=isset($_POST["numero_factura"])? limpiarCadena($_POST["numero_factura"]):"";
$idnumeracion=isset($_POST["idnumeracion"])? limpiarCadena($_POST["idnumeracion"]):"";
$numeracion=isset($_POST["numeracion"])? limpiarCadena($_POST["numeracion"]):"";
$idcliente=isset($_POST["idpersona"])? limpiarCadena($_POST["idpersona"]):"";
$total_operaciones_gravadas_codigo=isset($_POST["total_operaciones_gravadas_codigo"])? limpiarCadena($_POST["total_operaciones_gravadas_codigo"]):"";
$total_operaciones_gravadas_monto=isset($_POST["subtotal_factura"])? limpiarCadena($_POST["subtotal_factura"]):"";
$sumatoria_igv_1=isset($_POST["total_igv"])? limpiarCadena($_POST["total_igv"]):"";
$sumatoria_igv_2=isset($_POST["total_igv"])? limpiarCadena($_POST["total_igv"]):"";
$total_icbper=isset($_POST["total_icbper"])? limpiarCadena($_POST["total_icbper"]):""; // NUEVO *BOLSAS TOTAL DE MONTO*
$codigo_tributo_3=isset($_POST["codigo_tributo_h"])? limpiarCadena($_POST["codigo_tributo_h"]):"";
$nombre_tributo_4=isset($_POST["nombre_tributo_h"])? limpiarCadena($_POST["nombre_tributo_h"]):"";
$codigo_internacional_5=isset($_POST["codigo_internacional_5"])? limpiarCadena($_POST["codigo_internacional_5"]):"";
$importe_total_venta=isset($_POST["total_final"])? limpiarCadena($_POST["total_final"]):"";
$tipo_documento_guia=isset($_POST["tipo_documento_guia"])? limpiarCadena($_POST["tipo_documento_guia"]):"";
$codigo_leyenda_1=isset($_POST["codigo_leyenda_1"])? limpiarCadena($_POST["codigo_leyenda_1"]):"";
$descripcion_leyenda_2=isset($_POST["descripcion_leyenda_2"])? limpiarCadena($_POST["descripcion_leyenda_2"]):"";
$version_ubl=isset($_POST["version_ubl"])? limpiarCadena($_POST["version_ubl"]):"";
$version_estructura=isset($_POST["version_estructura"])? limpiarCadena($_POST["version_estructura"]):"";
$tipo_moneda=isset($_POST["tipo_moneda"])? limpiarCadena($_POST["tipo_moneda"]):"";
$tasa_igv=isset($_POST["tasa_igv"])? limpiarCadena($_POST["tasa_igv"]):"";
$estado=isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";
$codigo_precio=isset($_POST["codigo_precio"])? limpiarCadena($_POST["codigo_precio"]):"";
$tipodocuCliente=isset($_POST["tipo_documento_cliente"])? limpiarCadena($_POST["tipo_documento_cliente"]):"";
$rucCliente=isset($_POST["numero_documento2"])? limpiarCadena($_POST["numero_documento2"]):"";
$RazonSocial=isset($_POST["razon_social2"])? limpiarCadena($_POST["razon_social2"]):"";
$numero_guia=isset($_POST["numero_guia"])? limpiarCadena($_POST["numero_guia"]):"";
$hora=isset($_POST["hora"])? limpiarCadena($_POST["hora"]):"";
$guia_remision_29_2=isset($_POST["guia_remision_29_2"])? limpiarCadena($_POST["guia_remision_29_2"]):"";
$vendedorsitio=isset($_POST["vendedorsitio"])? limpiarCadena($_POST["vendedorsitio"]):"";
$email=isset($_POST["correocli"])? limpiarCadena($_POST["correocli"]):"";
$domicilio_fiscal2=isset($_POST["domicilio_fiscal2"])? limpiarCadena($_POST["domicilio_fiscal2"]):"";
$tdescuento=isset($_POST["total_dcto"])? limpiarCadena($_POST["total_dcto"]):"";
//$nombre_tributo=isset($_POST["nombre_tributo_4_p"])? limpiarCadena($_POST["nombre_tributo_4_p"]):"";

//Datos de tipo de cambio
$tcambio=isset($_POST["tcambio"])? limpiarCadena($_POST["tcambio"]):"";
$fechatc=isset($_POST["fechatc"])? limpiarCadena($_POST["fechatc"]):"";
$compra=isset($_POST["compra"])? limpiarCadena($_POST["compra"]):"";
$venta=isset($_POST["venta"])? limpiarCadena($_POST["venta"]):"";

$idtcambio=isset($_POST["idtcambio"])? limpiarCadena($_POST["idtcambio"]):"";

$idcaja=isset($_POST["idcaja"])? limpiarCadena($_POST["idcaja"]):"";
$idcajai=isset($_POST["idcajai"])? limpiarCadena($_POST["idcajai"]):"";
$idcajas=isset($_POST["idcajas"])? limpiarCadena($_POST["idcajas"]):"";

$fechacaja=isset($_POST["fechacaja"])? limpiarCadena($_POST["fechacaja"]):"";
$montoi=isset($_POST["montoi"])? limpiarCadena($_POST["montoi"]):"";
$montof=isset($_POST["montof"])? limpiarCadena($_POST["montof"]):"";

$concepto=isset($_POST["concepto"])? limpiarCadena($_POST["concepto"]):"";
$monto=isset($_POST["monto"])? limpiarCadena($_POST["monto"]):"";


$ipagado=isset($_POST["ipagado_final"])? limpiarCadena($_POST["ipagado_final"]):"";
$saldo=isset($_POST["saldo_final"])? limpiarCadena($_POST["saldo_final"]):"";
$tipopago=isset($_POST["tipopago"])? limpiarCadena($_POST["tipopago"]):"";
$nroreferencia=isset($_POST["nroreferencia"])? limpiarCadena($_POST["nroreferencia"]):"";

$tipofactura=isset($_POST["tipofactura"])? limpiarCadena($_POST["tipofactura"]):"";

//---- datos del documnto de cobranza ----//
$fedc=isset($_POST["fecemifa"])? limpiarCadena($_POST["fecemifa"]):"";
$SerieRealdc=isset($_POST["SerieRealfactura"])? limpiarCadena($_POST["SerieRealfactura"]):"";
$numero_facturadc=isset($_POST["numero_factura"])? limpiarCadena($_POST["numero_factura"]):"";
$idseriedc=isset($_POST["seriefactura"])? limpiarCadena($_POST["seriefactura"]):"";
$idclientef=isset($_POST["idclientef"])? limpiarCadena($_POST["idclientef"]):"";
$subtotalfactura=isset($_POST["subtotal_factura"])? limpiarCadena($_POST["subtotal_factura"]):"";
$totaligv=isset($_POST["total_igv_factura"])? limpiarCadena($_POST["total_igv_factura"]):"";
$totalfactura=isset($_POST["total_final_factura"])? limpiarCadena($_POST["total_final_factura"]):"";
$tipomonedafactura=isset($_POST["tipo_moneda_factura"])? limpiarCadena($_POST["tipo_moneda_factura"]):"";
$tipodocucli=isset($_POST["tipodocucli"])? limpiarCadena($_POST["tipodocucli"]):"";
$nrodoccli=isset($_POST["numero_documento_factura"])? limpiarCadena($_POST["numero_documento_factura"]):"";
$razonsf=isset($_POST["razon_socialnfactura"])? limpiarCadena($_POST["razon_socialnfactura"]):"";
$horaf=isset($_POST["horaf"])? limpiarCadena($_POST["horaf"]):"";
$correocliente=isset($_POST["correocliente"])? limpiarCadena($_POST["correocliente"]):"";
$domfiscal=isset($_POST["domicilionfactura"])? limpiarCadena($_POST["domicilionfactura"]):"";
$tcambiofactura=isset($_POST["tcambiofactura"])? limpiarCadena($_POST["tcambiofactura"]):"";
$tipopagonfactura=isset($_POST["tipopagonfactura"])? limpiarCadena($_POST["tipopagonfactura"]):"";
$nroreferenciaf=isset($_POST["nroreferenciaf"])? limpiarCadena($_POST["nroreferenciaf"]):"";
$idempresa2=isset($_POST["idempresa2"])? limpiarCadena($_POST["idempresa2"]):"";

$tipofacturacoti=isset($_POST["tipofacturacoti"])? limpiarCadena($_POST["tipofacturacoti"]):"";


$idcotizacion=isset($_POST["idcotizacion"])? limpiarCadena($_POST["idcotizacion"]):"";

$ccuotas=isset($_POST["ccuotas"])? limpiarCadena($_POST["ccuotas"]):"";
$fechavecredito=isset($_POST["fechavecredito"])? limpiarCadena($_POST["fechavecredito"]):"";
$montocuota=isset($_POST["montocuota"])? limpiarCadena($_POST["montocuota"]):"";

$otroscargos=isset($_POST["otroscargos"])? limpiarCadena($_POST["otroscargos"]):"";

$tadc=isset($_POST["tadc"])? limpiarCadena($_POST["tadc"]):"";
$transferencia=isset($_POST["trans"])? limpiarCadena($_POST["trans"]):"";


$fechavenc=isset($_POST["fechavenc"])? limpiarCadena($_POST["fechavenc"]):""; 




switch ($_GET["op"]){
    case 'guardaryeditarFactura':

        if (empty($idliquidacion)){
        $rspta=$factura->insertar($idusuario, $fecha_emision, $firma_digital, $idempresa, $tipo_documento, $numeracion, $idcliente, $total_operaciones_gravadas_codigo, $total_operaciones_gravadas_monto, $sumatoria_igv_1, $sumatoria_igv_2, $codigo_tributo_3, $nombre_tributo_4, $codigo_internacional_5, $importe_total_venta, $tipo_documento_guia, $guia_remision_29_2, $codigo_leyenda_1, $descripcion_leyenda_2, $version_ubl, $version_estructura, $tipo_moneda, $tasa_igv, $_POST["idarticulo"], $_POST["numero_orden_item"], $_POST["cantidad"], $_POST["codigo_precio"], $_POST["pvt"], $_POST["igvBD2"], $_POST["igvBD2"], $_POST["afectacionigv"], $_POST["codigotributo"], '', '', $_POST["igvBD"], $_POST["valor_unitario"], $_POST["subtotalBD"], $_POST["codigo"] , $_POST["unidad_medida"], $idserie, $SerieReal, $numero_factura, $tipodocuCliente,  $rucCliente , htmlspecialchars_decode($RazonSocial), $hora, $_POST["sumadcto"], $vendedorsitio, htmlspecialchars_decode($email), htmlspecialchars_decode($domicilio_fiscal2), $_POST["codigotributo"], $tdescuento, $tcambio, $tipopago, $nroreferencia, $ipagado, $saldo, $_POST["descdet"], $total_icbper, $tipofactura, $_POST["cantidadreal"],'', 
            $ccuotas, $fechavecredito, $montocuota, $otroscargos , $tadc, $transferencia, $_POST["ncuotahiden"], $_POST["montocuotacre"], $_POST["fechapago"], $fechavenc);
                
            $hora=date("h:i:s");
                
            echo $rspta ? "Se guardo correctamente" : "No se guardo factura";
        }
        else{
        }
   
    break;



    case 'guardaryeditarfacturadc':

        if (empty($idliquidacion)){
        $rspta=$factura->insertar($idusuario, $fedc, '', $idempresa2, '01', '', $idclientef, '1001', $subtotalfactura, $totaligv, $totaligv, '1000', 'IGV', 'VAT', $totalfactura, '-', '-', '1000', '-', '2.0', '1.0', $tipomonedafactura, '0.18', $_POST["idarticulof"], $_POST["norden"], $_POST["cantidadf"], '01', $_POST["valorunitariof"], $_POST["igvitem"], $_POST["igvitem"], $_POST["afeigv3"], $_POST["afeigv4"], '', '', $_POST["igvitem"], $_POST["preciof"], $_POST["valorventaf"], $_POST["codigof"] , $_POST["unidad_medida"], $idseriedc, $SerieRealdc, $numero_facturadc, $tipodocucli,  $nrodoccli , htmlspecialchars_decode($razonsf), $horaf, $_POST["sumadcto"], '', htmlspecialchars_decode($correocliente), htmlspecialchars_decode($domfiscal), '1000', '', $tcambiofactura, $tipopago, $nroreferenciaf, '', '', $_POST["descdetf"], '', '','','', $ccuotas, '', '', '' , 
            $tadc, $transferencia, $_POST["ncuotahiden"], $_POST["montocuotacre"], $_POST["fechapago"], $fedc);
                
           // $hora=date("h:i:s");
                
            echo $rspta ? "Factura registrada desde documento de cobranza": "No se pudieron registrar todos los datos de la factura";
        }
        else{
        }
   
    break;



        case 'buscarclientepred':
    	$key = $_POST['key'];
		$rspta=$liquidacion->buscarclientepredict($key);
		echo json_encode($rspta); // ? "Cliente ya existe": "Documento valido";
		break;

		   case 'listarClientesliqui':
        require_once "../modelos/Persona.php";
        $persona=new Persona();
        $doc=$_GET['doc'];
        $rspta = $liquidacion->buscarClientes($doc);
        echo json_encode($rspta);
        break;




    
        }
?>