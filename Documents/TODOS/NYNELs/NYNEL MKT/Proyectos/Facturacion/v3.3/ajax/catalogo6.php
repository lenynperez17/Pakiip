<?php
require_once "../config/Conexion.php";
require_once "../modelos/Catalogo6.php";

$catalogo6 = new Catalogo6();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$abrev = isset($_POST["abrev"]) ? limpiarCadena($_POST["abrev"]) : "";

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = '';
}

if ($action == 'listar2') {
	$rspta = $catalogo6->listar2();
	$data = array();

	while ($reg = $rspta->fetch_object()) {
		$data[] = array(
			'id' => $reg->id,
			'codigo' => $reg->codigo,
			'documento' => $reg->descripcion,
			'estado' => $reg->estado
		);
	}
	$results = array(
		"aaData" => $data
	);

	header('Content-type: application/json');
	echo json_encode($results);
	exit;
}



if (isset($_GET["op"])) {
switch ($_GET["op"]) {
	case 'guardaryeditar':
		if (empty($id)) {
			$rspta = $catalogo6->insertar($codigo, $descripcion, $abrev);
			echo $rspta ? "Concepto registrado" : "Concepto no se pudo registrar";
		} else {
			$rspta = $catalogo6->editar($id, $codigo, $descripcion, $abrev);
			echo $rspta ? "Concepto actualizado" : "Concepto no se pudo actualizar";
		}
		break;

	case 'desactivar':
		$rspta = $catalogo6->desactivar($id);
		echo $rspta ? "Numeración Desactivada" : "Numeración no se puede desactivar";
		break;
		break;

	case 'activar':
		$rspta = $catalogo6->activar($id);
		echo $rspta ? "Numeración activada" : "Numeración no se puede activar";
		break;
		break;

	case 'mostrar':
		$rspta = $catalogo6->mostrar($id);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		exit;
		break;

	case 'listar':
		$rspta = $catalogo6->listar();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				
				"0" => $reg->codigo,
				"1" => $reg->descripcion,
				"2" => $reg->abrev,
				"3" => ($reg->estado) ? '<span class="badge bg-success-transparent">Activo</span>' :
					'<span class="badge bg-danger-transparent">Inhabilitado</span>',
				"4" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->id . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->id . ')"><i class="ri-delete-bin-line"></i></button>' :
				'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->id . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->id . ')"><i class="ri-check-double-line"></i></button>'
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
		exit;

		break;
} // Cierre del switch
} // Cierre del if (isset($_GET["op"]))
?>