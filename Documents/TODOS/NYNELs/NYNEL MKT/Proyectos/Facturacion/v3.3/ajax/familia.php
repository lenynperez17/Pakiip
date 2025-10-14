<?php
require_once "../modelos/Familia.php";

$familia = new Familia();

$idfamilia = isset($_POST["idfamilia"]) ? limpiarCadena($_POST["idfamilia"]) : "";
$idalmacen = isset($_POST["idalmacen"]) ? limpiarCadena($_POST["idalmacen"]) : "";
$nombrea = isset($_POST["nombrea"]) ? limpiarCadena($_POST["nombrea"]) : "";
$nombrec = isset($_POST["nombrec"]) ? limpiarCadena($_POST["nombrec"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$direc = isset($_POST["direc"]) ? limpiarCadena($_POST["direc"]) : "";
$idempresa = isset($_POST["idempresa2"]) ? limpiarCadena($_POST["idempresa2"]) : "";


$nombreu = isset($_POST["nombreu"]) ? limpiarCadena($_POST["nombreu"]) : "";
$abre = isset($_POST["abre"]) ? limpiarCadena($_POST["abre"]) : "";
$equivalencia = isset($_POST["equivalencia2"]) ? limpiarCadena($_POST["equivalencia2"]) : "";



switch ($_GET["op"]) {

	case 'guardaryeditar':
		$validarCategoria = $familia->validarCategoria($nombrec);
		if ($validarCategoria) {
			echo "Categoría ya registrada";
		} else {
			if (empty($idfamilia)) {
				$rspta = $familia->insertarCategoria($nombrec);
				echo $rspta ? "Categoría registrada" : "Categoría no se pudo registrar";
			} else {
				$rspta = $familia->editar($idfamilia, $nombrec);
				echo $rspta ? "Categoría actualizada" : "Categoría no se pudo actualizar";
			}
		}
		break;


	case 'guardaryeditaralmacen':
		if (empty($idalmacen)) {
			$rspta = $familia->insertaralmacen($nombrea, $direc, $idempresa);
			echo $rspta ? "Almacen registrado" : "Almacen no se pudo registrar";
		} else {
			$rspta = $familia->editar($idalmacen, $nombrea);
			echo $rspta ? "Familia actualizada" : "Familia no se pudo actualizar";
		}
		break;

	case 'guardaryeditarUmedida':
		if (empty($idfamilia)) {
			$rspta = $familia->insertaraunidad($nombreu, $abre, $equivalencia);
			echo $rspta ? "Unidad registrada" : "Unidad no se pudo registrar";
		} else {
			$rspta = $familia->editar($idfamilia, $nombre);
			echo $rspta ? "Unidad actualizada" : "Unidad no se pudo actualizar";
		}
		break;

	case 'desactivar':
		$rspta = $familia->desactivar($idfamilia);
		echo $rspta ? "Categoria Desactivada" : "Categoria no se puede desactivar";
		break;
		break;

	case 'activar':
		$rspta = $familia->activar($idfamilia);
		echo $rspta ? "Categoria activada" : "Categoria no se puede activar";
		break;
		break;

	case 'mostrar':
		$rspta = $familia->mostrar($idfamilia);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;
		break;

	case 'listar':
		$rspta = $familia->listar();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
			    
			    "0" => $reg->descripcion,
			    "1" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' :
					'<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
				
				"2" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idfamilia . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idfamilia . ')"><i class="ri-delete-bin-line"></i></button>' :
					'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idfamilia . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idfamilia . ')"><i class="ri-check-double-line"></i></button>'
                
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