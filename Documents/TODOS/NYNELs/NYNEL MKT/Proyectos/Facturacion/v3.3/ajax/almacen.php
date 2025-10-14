<?php
require_once "../modelos/Almacen.php";

$almacen = new Almacen();

$idalmacen = isset($_POST["idalmacen"]) ? filter_var($_POST["idalmacen"], FILTER_SANITIZE_NUMBER_INT) : "";

$nombrea = isset($_POST["nombrea"]) ? filter_var($_POST["nombrea"], FILTER_SANITIZE_STRING) : "";
$descripcion = isset($_POST["descripcion"]) ? filter_var($_POST["descripcion"], FILTER_SANITIZE_STRING) : "";
$estado = isset($_POST["estado"]) ? filter_var($_POST["estado"], FILTER_SANITIZE_STRING) : "";
$direccion = isset($_POST["direccion"]) ? filter_var($_POST["direccion"], FILTER_SANITIZE_STRING) : "";


switch ($_GET["op"]) {


	case 'guardaryeditar':
		$validarAlmacen = $almacen->validarAlmacen($nombrea);
		if ($validarAlmacen) {
			echo "Almacen ya registrado";
		} else {
			if (empty($idalmacen)) {
				$rspta = $almacen->insertaralmacen($nombrea, $direccion, '1');
				echo $rspta ? "Almacen registrado" : "Almacen no se pudo registrar";
			} else {
				$rspta = $almacen->editar($idalmacen, $nombrea, $direccion);
				echo $rspta ? "almacen actualizada" : "almacen no se pudo actualizar";
			}
		}
		break;


	case 'desactivar':
		$rspta = $almacen->desactivar($idalmacen);
		echo $rspta ? "almacen Desactivado" : "almacen no se puede desactivar";
		break;
		break;

	case 'activar':
		$rspta = $almacen->activar($idalmacen);
		echo $rspta ? "almacen habilitado" : "almacen no se puede activar";
		break;
		break;

	case 'mostrar':
		$rspta = $almacen->mostrar($idalmacen);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;
		break;

	case 'listar':
		$rspta = $almacen->listar();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => $reg->nombre,
				"1" => $reg->direccion,
				"2" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' : '<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
				//"3" => ($reg->estado) ? '<button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-info-light" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
					//' <button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-danger-light" onclick="desactivar(' . $reg->idalmacen . ')"><i class="ri-delete-bin-line"></i></button>' :
					//'<button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-info-light" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
					//' <button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-success-light" onclick="activar(' . $reg->idalmacen . ')"><i class="ri-check-double-line"></i></button>'
					
				"3" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idalmacen . ')"><i class="ri-delete-bin-line"></i></button>' :
				'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idalmacen . ')"><i class="ri-check-double-line"></i></button>'
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
?>