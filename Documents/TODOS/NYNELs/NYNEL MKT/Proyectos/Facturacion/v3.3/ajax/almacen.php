<?php
require_once "../modelos/Almacen.php";

$almacen = new Almacen();

$idalmacen = isset($_POST["idalmacen"]) ? filter_var($_POST["idalmacen"], FILTER_SANITIZE_NUMBER_INT) : "";

$nombrea = isset($_POST["nombrea"]) ? filter_var($_POST["nombrea"], FILTER_SANITIZE_STRING) : "";
$descripcion = isset($_POST["descripcion"]) ? filter_var($_POST["descripcion"], FILTER_SANITIZE_STRING) : "";
$estado = isset($_POST["estado"]) ? filter_var($_POST["estado"], FILTER_SANITIZE_STRING) : "";
$direccion = isset($_POST["direccion"]) ? filter_var($_POST["direccion"], FILTER_SANITIZE_STRING) : "";
$telefono = isset($_POST["telefono"]) ? filter_var($_POST["telefono"], FILTER_SANITIZE_STRING) : null;
$email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : null;
$idusuario_responsable = isset($_POST["idusuario_responsable"]) ? filter_var($_POST["idusuario_responsable"], FILTER_SANITIZE_NUMBER_INT) : null;
$tipo_almacen = isset($_POST["tipo_almacen"]) ? filter_var($_POST["tipo_almacen"], FILTER_SANITIZE_STRING) : 'SECUNDARIO';
$capacidad_max = isset($_POST["capacidad_max"]) ? filter_var($_POST["capacidad_max"], FILTER_SANITIZE_NUMBER_INT) : null;
$notas = isset($_POST["notas"]) ? filter_var($_POST["notas"], FILTER_SANITIZE_STRING) : null;


switch ($_GET["op"]) {


	case 'guardaryeditar':
		$validarAlmacen = $almacen->validarAlmacen($nombrea);
		if ($validarAlmacen) {
			echo "Almacen ya registrado";
		} else {
			if (empty($idalmacen)) {
				$rspta = $almacen->insertaralmacen($nombrea, $direccion, '1', $telefono, $email, $idusuario_responsable, $tipo_almacen, $capacidad_max, $notas);
				echo $rspta ? "Almacen registrado" : "Almacen no se pudo registrar";
			} else {
				$rspta = $almacen->editar($idalmacen, $nombrea, $direccion, $telefono, $email, $idusuario_responsable, $tipo_almacen, $capacidad_max, $notas);
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
			// Badge de tipo de almacén
			$tipo_badge = '';
			switch ($reg->tipo_almacen) {
				case 'PRINCIPAL':
					$tipo_badge = '<span class="badge bg-primary-transparent">Principal</span>';
					break;
				case 'TEMPORAL':
					$tipo_badge = '<span class="badge bg-warning-transparent">Temporal</span>';
					break;
				default:
					$tipo_badge = '<span class="badge bg-secondary-transparent">Secundario</span>';
			}

			$data[] = array(
				"0" => $reg->nombre,
				"1" => $reg->direccion,
				"2" => $tipo_badge,
				"3" => $reg->responsable_nombre ? $reg->responsable_nombre : '<span class="text-muted">Sin asignar</span>',
				"4" => number_format($reg->total_productos),
				"5" => 'S/ ' . number_format($reg->valor_inventario, 2),
				"6" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' : '<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
				"7" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idalmacen . ')"><i class="ri-delete-bin-line"></i></button>' :
				'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idalmacen . ')"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idalmacen . ')"><i class="ri-check-double-line"></i></button>',
				"8" => '<button class="btn btn-sm btn-primary" onclick="verProductosAlmacen(' . $reg->idalmacen . ', \'' . addslashes($reg->nombre) . '\')"><i class="ri-archive-line"></i> Ver Productos</button>'
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

	case 'obtenerEstadisticas':
		$rspta = $almacen->obtenerEstadisticas();
		$reg = $rspta->fetch_object();
		echo json_encode($reg);
		break;

	case 'obtenerUsuariosResponsables':
		$rspta = $almacen->obtenerUsuariosResponsables();
		$data = array();
		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"idusuario" => $reg->idusuario,
				"nombre" => $reg->nombre,
				"login" => $reg->login
			);
		}
		echo json_encode($data);
		break;

	case 'selectAlmacenes':
		// Retorna solo almacenes activos para seleccionar en formularios
		global $conexion;
		$sql = "SELECT idalmacen, nombre, direccion
				FROM almacen
				WHERE estado = 1
				ORDER BY tipo_almacen DESC, nombre ASC";
		$resultado = $conexion->query($sql);

		$data = array();
		while ($reg = $resultado->fetch_object()) {
			$data[] = array(
				"idalmacen" => $reg->idalmacen,
				"nombre" => $reg->nombre,
				"direccion" => $reg->direccion
			);
		}
		echo json_encode($data);
		break;

	case 'listarProductosPorAlmacen':
		$idalmacen = isset($_GET["idalmacen"]) ? filter_var($_GET["idalmacen"], FILTER_SANITIZE_NUMBER_INT) : "";

		$rspta = $almacen->listarProductosPorAlmacen($idalmacen);
		$data = array();

		// Verificar si la consulta fue exitosa
		if ($rspta === false) {
			error_log("Error: listarProductosPorAlmacen retornó false para idalmacen=" . $idalmacen);
			echo json_encode(array(
				"sEcho" => 1,
				"iTotalRecords" => 0,
				"iTotalDisplayRecords" => 0,
				"aaData" => array()
			));
			break;
		}

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => $reg->codigo,
				"1" => $reg->nombre,
				"2" => number_format($reg->stock, 2) . ' ' . $reg->unidad_medida,
				"3" => 'S/ ' . number_format($reg->precio_venta, 2),
				"4" => 'S/ ' . number_format($reg->costo_compra, 2),
				"5" => 'S/ ' . number_format($reg->valor_total, 2),
				"6" => '<span class="badge bg-info-transparent">' . $reg->almacen_nombre . '</span><br><small class="text-muted">' . $reg->almacen_direccion . '</small>'
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

	case 'obtenerResumenAlmacen':
		$idalmacen = isset($_GET["idalmacen"]) ? filter_var($_GET["idalmacen"], FILTER_SANITIZE_NUMBER_INT) : "";

		$rspta = $almacen->obtenerResumenAlmacen($idalmacen);
		$reg = $rspta->fetch_object();
		echo json_encode($reg);
		break;
}
?>
