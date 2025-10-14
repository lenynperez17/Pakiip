<?php

require_once "../modelos/Creditospendiente.php";

$creditospendiente = new Creditospendiente();

switch ($_GET["op"]) {

    case 'detallecomprobante':
        $tipoComprobante = isset($_GET["tipoComprobante"]) ? limpiarCadena($_GET["tipoComprobante"]) : "";

        $rspta = $creditospendiente->listarDetalleComprobante($tipoComprobante);

        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "tipo" => $reg->tipo,
                "idcomprobante" => $reg->idcomprobante,
                "idcliente" => $reg->idcliente,
                "cliente" => $reg->cliente,
                "importe_total" => $reg->importe_total,
                "fechavenc" => $reg->fechavenc,
                "ccuotas" => $reg->ccuotas,
                "montocuota" => $reg->montocuota,
                "t_pagado" => $reg->t_pagado,
                "t_restante" => $reg->t_restante

            );
        }
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);

        break;

    case 'listarcuotas':

        $idcomprobante = isset($_GET["idcomprobante"]) ? limpiarCadena($_GET["idcomprobante"]) : "";

        $rspta = $creditospendiente->listarCuotas($idcomprobante);

        $data = array(); // Inicializar un array para almacenar los datos

        while ($reg = $rspta->fetch_object()) {
            $data[] = $reg; // Agregar cada registro al array
        }

        echo json_encode($data); // Convertir el array en formato JSON y devolverlo como respuesta

        break;
}

?>