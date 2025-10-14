<?php
require_once "../modelos/Venta.php";

$venta = new Venta();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'ListarReporteMes') {

    // Obteniendo los parámetros desde la URL
    $ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y'); // Si no se proporciona, tomamos el año actual
    $mes = isset($_GET['mes']) ? $_GET['mes'] : date('m'); // Si no se proporciona, tomamos el mes actual
    $idempresa = isset($_GET['idempresa']) ? $_GET['idempresa'] : '';
    $tmon = isset($_GET['tmon']) ? $_GET['tmon'] : '';

    $rspta = $venta->regventareporte($ano, $mes, $idempresa, $tmon);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        // Aquí puedes mapear los datos del objeto $reg a la estructura que desees
        $data[] = array(
            "id" => $reg->id,
            "tipodocu" => $reg->tipodocu,
            "fecha" => $reg->fecha,
            "documento" => $reg->documento,
            "subtotal" => $reg->subtotal,
            "igv" => $reg->igv,
            "total" => $reg->total,
            "estado" => $reg->estado,
            "numero_documento" => $reg->numero_documento,
            "razon_social" => $reg->razon_social,
            "icbper" => $reg->icbper,
            "tipofactura" => $reg->tipofactura,
            "tipomoneda" => $reg->tipomoneda,
            "tcambio" => $reg->tcambio,
            "efectivo" => $reg->efectivo,
            "visa" => $reg->visa,
            "yape" => $reg->yape,
            "plin" => $reg->plin,
            "mastercard" => $reg->mastercard,
            "deposito" => $reg->deposito,
            "productos_adquiridos" => $reg->productos_adquiridos
        );
    }

    $results = array("aaData" => $data);

    header('Content-type: application/json');
    echo json_encode($results);
}


if ($action == 'ListarReporteMesyDia') {

    // Obteniendo los parámetros desde la URL
    $ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y'); // Si no se proporciona, tomamos el año actual
    $mes = isset($_GET['mes']) ? $_GET['mes'] : date('m'); // Si no se proporciona, tomamos el mes actual
    $dia = isset($_GET['dia']) ? $_GET['dia'] : date('d');
    $idempresa = isset($_GET['idempresa']) ? $_GET['idempresa'] : '';
    $tmon = isset($_GET['tmon']) ? $_GET['tmon'] : '';

    $rspta = $venta->regventaagruxdia($ano, $mes, $dia, $idempresa, $tmon);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        // Aquí puedes mapear los datos del objeto $reg a la estructura que desees
        $data[] = array(
            "id" => $reg->id,
            "tipodocu" => $reg->tipodocu,
            "fecha" => $reg->fecha,
            "documento" => $reg->documento,
            "subtotal" => $reg->subtotal,
            "igv" => $reg->igv,
            "total" => $reg->total,
            "estado" => $reg->estado,
            "numero_documento" => $reg->numero_documento,
            "razon_social" => $reg->razon_social,
            "icbper" => $reg->icbper,
            "tipofactura" => $reg->tipofactura,
            "tipomoneda" => $reg->tipomoneda,
            "tcambio" => $reg->tcambio,
            "efectivo" => $reg->efectivo,
            "visa" => $reg->visa,
            "yape" => $reg->yape,
            "plin" => $reg->plin,
            "mastercard" => $reg->mastercard,
            "deposito" => $reg->deposito,
            "productos_adquiridos" => $reg->productos_adquiridos
        );
    }

    $results = array("aaData" => $data);

    header('Content-type: application/json');
    echo json_encode($results);
}


if ($action == 'ListarReporteXFecha') {

    // Obteniendo los parámetros desde la URL
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : ''; // Si no se proporciona, tomamos el año actual
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : ''; // Si no se proporciona, tomamos el mes actual
    $idempresa = isset($_GET['idempresa']) ? $_GET['idempresa'] : '';
    $tmon = isset($_GET['tmon']) ? $_GET['tmon'] : '';

    $rspta = $venta->filtrofechaReportes($fecha_desde, $fecha_hasta, $idempresa, $tmon);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        // Aquí puedes mapear los datos del objeto $reg a la estructura que desees
        $data[] = array(
            "id" => $reg->id,
            "tipodocu" => $reg->tipodocu,
            "fecha" => $reg->fecha,
            "documento" => $reg->documento,
            "subtotal" => $reg->subtotal,
            "igv" => $reg->igv,
            "total" => $reg->total,
            "estado" => $reg->estado,
            "numero_documento" => $reg->numero_documento,
            "razon_social" => $reg->razon_social,
            "icbper" => $reg->icbper,
            "tipofactura" => $reg->tipofactura,
            "tipomoneda" => $reg->tipomoneda,
            "tcambio" => $reg->tcambio,
            "efectivo" => $reg->efectivo,
            "visa" => $reg->visa,
            "yape" => $reg->yape,
            "plin" => $reg->plin,
            "mastercard" => $reg->mastercard,
            "deposito" => $reg->deposito,
            "productos_adquiridos" => $reg->productos_adquiridos
        );
    }

    $results = array("aaData" => $data);

    header('Content-type: application/json');
    echo json_encode($results);
}


switch ($_GET["op"]) {

    case 'ListarReporteTributario':

        $FechaDesdeIni = isset($_GET['FechaDesdeIni']) ? $_GET['FechaDesdeIni'] : ''; // Si no se proporciona, tomamos el año actual
        $FechaHastaFin = isset($_GET['FechaHastaFin']) ? $_GET['FechaHastaFin'] : ''; // Si no se proporciona, tomamos el mes actual
        $idempresa = isset($_GET['idempresa']) ? $_GET['idempresa'] : '';
        $tmonedaa = isset($_GET['tmonedaa']) ? $_GET['tmonedaa'] : '';


        $rspta = $venta->reporteBoletayFacturasTributario($FechaDesdeIni, $FechaHastaFin, $idempresa, $tmonedaa);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->{'TIPO CPE'},
                "2" => $reg->{'SERIE Y NUMERO'},
                "3" => $reg->{'RUC/DNI'},
                "4" => $reg->{'RAZON SOCIAL/NOMBRE'},
                "5" => $reg->{'op. gravadas'},
                "6" => $reg->{'op. gratuitas'},
                "7" => $reg->{'op. exoneradas'},
                "8" => $reg->{'op. inafectas'},
                "9" => $reg->{'total dscto'},
                "10" => $reg->igv,
                "11" => $reg->{'imp. bolsa'},
                "12" => $reg->total,
                "13" => $reg->estado,
                "14" => $reg->{'respuesta sunat'},
                "15" => $reg->observacion,
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
}

//reporte tributario 

if ($action == 'hola') {

    // Obteniendo los parámetros desde la URL
    $FechaDesdeIni = isset($_GET['FechaDesdeIni']) ? $_GET['FechaDesdeIni'] : ''; // Si no se proporciona, tomamos el año actual
    $FechaHastaFin = isset($_GET['FechaHastaFin']) ? $_GET['FechaHastaFin'] : ''; // Si no se proporciona, tomamos el mes actual
    $idempresa = isset($_GET['idempresa']) ? $_GET['idempresa'] : '';
    $tmonedaa = isset($_GET['tmonedaa']) ? $_GET['tmonedaa'] : '';

    $rspta = $venta->reporteBoletayFacturasTributario($FechaDesdeIni, $FechaHastaFin, $idempresa, $tmonedaa);
    $data = array();

    while ($reg = $rspta->fetch_object()) {

        // Aquí puedes mapear los datos del objeto $reg a la estructura que desees
        $data[] = array(
            "FECHA" => $reg->fecha,
            "OP_GRAVADAS" => $reg->{'op. gravadas'},
            "OP_GRATUITAS" => $reg->{'op. gratuitas'},
            "OP_EXONERADAS" => $reg->{'op. exoneradas'},
            "OP_INAFECTAS" => $reg->{'op. inafectas'},
            "TOTAL_DSCTO" => $reg->{'total dscto'},
            "IGV" => $reg->igv,
            "IMP_BOLSA" => $reg->{'imp. bolsa'},
            "TOTAL" => $reg->total,
            "ESTADO" => $reg->estado,
            "RESPUESTA_SUNAT" => $reg->{'respuesta sunat'},
            "OBSERVACION" => $reg->observacion,
        );
    }

    $results = array("aaData" => $data);

    header('Content-type: application/json');
    echo json_encode($results);
}