<?php
require_once "../modelos/Consultas.php";

$consultas = new Consultas();


if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}


if ($action == 'ListarDatosUsuarios') {
    $rspta = $consultas->totalAlmacenActiva($nombre, $estado);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        $data[] = array(
            'idalmacen' => $reg->idalmacen,
            'nombre' => $reg->nombre,
            'estado' => $reg->estado
        );
    }
    $results = array(
        "aaData" => $data
    );

    header('Content-type: application/json');
    echo json_encode($results);
}