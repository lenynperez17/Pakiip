<?php 
require_once "../modelos/Numeracion.php";

$numeracion=new Numeracion();

$idnumeracion=isset($_POST["idnumeracion"])? limpiarCadena($_POST["idnumeracion"]):"";
$tipo_documento=isset($_POST["tipo_documento"])? limpiarCadena($_POST["tipo_documento"]):"";
$serie=isset($_POST["serie"])? limpiarCadena($_POST["serie"]):"";
$numero=isset($_POST["numero"])? limpiarCadena($_POST["numero"]):"";


switch ($_GET["op"]){
	case 'guardaryeditar':
		if (empty($idnumeracion)){
			$rspta=$numeracion->insertar($tipo_documento, $serie, $numero );
			echo $rspta ? "Numeración registrada" : "Numeración no se pudo registrar";
		}
		else {
			$rspta=$numeracion->editar($idnumeracion,$tipo_documento, $serie, $numero);
			echo $rspta ? "Numeración actualizada" : "Numeración no se pudo actualizar";
		}
	break;

	case 'desactivar':
		$rspta=$numeracion->desactivar($idnumeracion);
 		echo $rspta ? "Numeración Desactivada" : "Numeración no se puede desactivar";
 		break;
	break;

	case 'activar':
		$rspta=$numeracion->activar($idnumeracion);
 		echo $rspta ? "Numeración activada" : "Numeración no se puede activar";
 		break;
	break;

	case 'eliminar':
		$rspta=$numeracion->eliminar($idnumeracion);
 		echo $rspta ? "Numeración eliminada permanentemente" : "No se puede eliminar. Puede estar siendo usada en documentos.";
 		break;
	break;

	case 'mostrar':
		$rspta=$numeracion->mostrar($idnumeracion);
 		//Codificar el resultado utilizando json
 		echo json_encode($rspta);
 		break;
	break;

	case 'listar':
		$rspta=$numeracion->listar();
 		//Vamos a declarar un array
 		$data= Array();


 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
 				
 				"0"=>$reg->descripcion,
 				"1"=>$reg->serie,
 				"2"=>$reg->numero,
 				"3"=>($reg->estado)?'<span class="badge bg-success-transparent">Activo</span>':
 				'<span class="badge bg-danger-transparent">Inhabilitado</span>',
 				"4" => ($reg->estado) ?
				// BOTONES PARA REGISTROS ACTIVOS
				'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idnumeracion . ')" title="Editar"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idnumeracion . ')" title="Desactivar"><i class="ri-close-circle-line"></i></button>' :
				// BOTONES PARA REGISTROS INHABILITADOS
				'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idnumeracion . ')" title="Editar"><i class="ri-edit-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idnumeracion . ')" title="Activar"><i class="ri-check-double-line"></i></button>' .
				' <button class="btn btn-icon btn-sm btn-dark" onclick="eliminar(' . $reg->idnumeracion . ')" title="Eliminar permanentemente" style="background-color: #8B0000;"><i class="ri-delete-bin-7-line"></i></button>'
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;
}
?>