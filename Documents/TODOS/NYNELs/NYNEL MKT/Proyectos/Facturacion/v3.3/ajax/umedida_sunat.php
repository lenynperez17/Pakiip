<?php
require_once "../modelos/UmedidaSunat.php";

$umedida_sunat = new UmedidaSunat();

$idsunat_um = isset($_POST["idsunat_um"]) ? limpiarCadena($_POST["idsunat_um"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$simbolo = isset($_POST["simbolo"]) ? limpiarCadena($_POST["simbolo"]) : "";
$notas = isset($_POST["notas"]) ? limpiarCadena($_POST["notas"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "1";

switch ($_GET["op"]) {
	case 'guardaryeditar':
		// Verificar si el código SUNAT ya está registrado (solo al insertar)
		$validarCodigo = $umedida_sunat->validarCodigo($codigo);
		if ($validarCodigo && empty($idsunat_um)) {
			echo "El código SUNAT '$codigo' ya está registrado";
		} else {
			if (empty($idsunat_um)) {
				// Insertar nueva unidad de medida SUNAT
				$rspta = $umedida_sunat->insertar($codigo, $descripcion, $simbolo, $notas, $estado);
				echo $rspta ? "Unidad de medida SUNAT registrada correctamente" : "Error: No se pudo registrar la unidad de medida SUNAT";
			} else {
				// Actualizar unidad de medida SUNAT existente
				$rspta = $umedida_sunat->editar($idsunat_um, $descripcion, $simbolo, $notas, $estado);
				echo $rspta ? "Unidad de medida SUNAT actualizada correctamente" : "Error: No se pudo actualizar la unidad de medida SUNAT";
			}
		}
		break;

	case 'desactivar':
		$rspta = $umedida_sunat->desactivar($idsunat_um);
		echo $rspta ? "Unidad de medida SUNAT desactivada correctamente" : "Error: No se puede desactivar la unidad de medida SUNAT";
		break;

	case 'activar':
		$rspta = $umedida_sunat->activar($idsunat_um);
		echo $rspta ? "Unidad de medida SUNAT activada correctamente" : "Error: No se puede activar la unidad de medida SUNAT";
		break;

	case 'eliminar':
		$rspta = $umedida_sunat->eliminar($idsunat_um);
		echo $rspta ? "Unidad de medida SUNAT eliminada correctamente" : "Error: No se puede eliminar la unidad de medida SUNAT (puede estar siendo utilizada en compras)";
		break;

	case 'mostrar':
		$rspta = $umedida_sunat->mostrar($idsunat_um);
		// Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;

	case 'listar':
		$rspta = $umedida_sunat->listar();
		// Declarar array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => $reg->codigo,
				"1" => $reg->descripcion,
				"2" => $reg->simbolo ? $reg->simbolo : '<span class="text-muted">-</span>',
				"3" => ($reg->estado)
					? '<span class="badge bg-success"><i class="fa fa-check-circle"></i> Activo</span>'
					: '<span class="badge bg-danger"><i class="fa fa-times-circle"></i> Inactivo</span>',
				"4" => ($reg->estado)
					? '<button class="btn btn-sm btn-info" onclick="mostrar(' . $reg->idsunat_um . ')" title="Editar"><i class="fa fa-edit"></i></button> ' .
					  '<button class="btn btn-sm btn-warning" onclick="desactivar(' . $reg->idsunat_um . ')" title="Desactivar"><i class="fa fa-ban"></i></button>'
					: '<button class="btn btn-sm btn-info" onclick="mostrar(' . $reg->idsunat_um . ')" title="Editar"><i class="fa fa-edit"></i></button> ' .
					  '<button class="btn btn-sm btn-success" onclick="activar(' . $reg->idsunat_um . ')" title="Activar"><i class="fa fa-check"></i></button>'
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

	case 'select':
		// Para usar en selects de formularios
		$rspta = $umedida_sunat->listarActivas();
		echo '<option value="">Seleccione...</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value="' . $reg->codigo . '">' . $reg->codigo . ' - ' . $reg->descripcion . '</option>';
		}
		break;
}
?>
