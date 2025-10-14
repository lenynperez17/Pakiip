<?php
require_once "../modelos/Umedida.php";

$umedida = new Umedida();

$idunidadme = isset($_POST["idunidadm"]) ? limpiarCadena($_POST["idunidadm"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$abre = isset($_POST["abre"]) ? limpiarCadena($_POST["abre"]) : "";
$equivalencia = isset($_POST["equivalencia"]) ? limpiarCadena($_POST["equivalencia"]) : "";



switch ($_GET["op"]) {
	case 'guardaryeditar':
	    
		/*$validarUnidadMedida = $umedida->validarUnidadMedida($nombre);
		if ($validarUnidadMedida) {
			echo "Unidad de medida ya registrado";
		} else {
			if (empty($idunidadme)) {
				$rspta = $umedida->insertar($nombre, $abre, $equivalencia);
				echo $rspta ? "Unidad de medida registrada" : "Unidad de medida no se pudo registrar";
			} else {
				$rspta = $umedida->editar($idunidadme, $nombre, $abre, $equivalencia);
				echo $rspta ? "Unidad de medida actualizada" : "Unidad de medida no se pudo actualizar";
			}
		}*/
		
		 // Verifica si el nombre de la unidad ya est¨¢ registrado
    $validarUnidadMedida = $umedida->validarUnidadMedida($nombre);
    if ($validarUnidadMedida && empty($idunidadme)) {
        echo "Unidad de medida ya registrada";
    } else {
        if (empty($idunidadme)) {
            // Insertar nueva unidad de medida
            $rspta = $umedida->insertar($nombre, $abre, $equivalencia);
            echo $rspta ? "Unidad de medida registrada" : "Unidad de medida no se pudo registrar";
        } else {
            // Actualizar unidad de medida existente
            $rspta = $umedida->editar($idunidadme, $nombre, $abre, $equivalencia);
            echo $rspta ? "Unidad de medida actualizada" : "Unidad de medida no se pudo actualizar";
        }
    }

		break;

	case 'desactivar':
		$rspta = $umedida->desactivar($idunidadme);
		echo $rspta ? "Unidad de medida Desactivada" : "Unidad de medida no se puede desactivar";
		break;
		break;

	case 'activar':
		$rspta = $umedida->activar($idunidadme);
		echo $rspta ? "Unidad de medida activada" : "Unidad de medida no se puede activar";
		break;
		break;


	case 'eliminar':
		$rspta = $umedida->eliminar($idunidadme);
		echo $rspta ? "Unidad de medida eliminada" : "Unidad de medida no se puede eliminar";
		break;
		break;

	case 'mostrar':
		//$idum=$_GET['idumedida'];
		$rspta = $umedida->mostrar($idunidadme);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;
		break;

	case 'listar':
		$rspta = $umedida->listar();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
			    
			    "0" => $reg->nombreum,
				"1" => $reg->abre,
				"2" => "$reg->equivalencia",
				"3" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' :
					'<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
				
				"4" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idunidad . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idunidad . ')"><i class="ri-delete-bin-line"></i></button>' :
					'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idunidad . ')"><i class="ri-edit-line"></i></button>' .
					'<button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idunidad . ')"><i class="ri-check-double-line"></i></button>'
				
			);
		}
		$results = array(
			"sEcho" => 1,
			//InformaciÃ³n para el datatables
			"iTotalRecords" => count($data),
			//enviamos el total registros al datatable
			"iTotalDisplayRecords" => count($data),
			//enviamos el total registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;
}
//onclick="eliminar('.$reg->idunidad.')"
?>