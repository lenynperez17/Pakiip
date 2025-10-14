<?php 
require_once "../modelos/Registroinventario.php";

$registroinv=new Registroinv();

$idregistro=isset($_POST["idregistro"])? limpiarCadena($_POST["idregistro"]):"";
$ano=isset($_POST["ano"])? limpiarCadena($_POST["ano"]):"";
$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
$denominacion=isset($_POST["denominacion"])? limpiarCadena($_POST["denominacion"]):"";
$costoinicial=isset($_POST["costoinicial"])? limpiarCadena($_POST["costoinicial"]):"";
$saldoinicial=isset($_POST["saldoinicial"])? limpiarCadena($_POST["saldoinicial"]):"";
$valorinicial=isset($_POST["valorinicial"])? limpiarCadena($_POST["valorinicial"]):"";
$compras=isset($_POST["compras"])? limpiarCadena($_POST["compras"]):"";
$ventas=isset($_POST["ventas"])? limpiarCadena($_POST["ventas"]):"";
$saldofinal=isset($_POST["saldofinal"])? limpiarCadena($_POST["saldofinal"]):"";
$costo=isset($_POST["costo"])? limpiarCadena($_POST["costo"]):"";
$valorfinal=isset($_POST["valorfinal"])? limpiarCadena($_POST["valorfinal"]):"";



switch ($_GET["op"]){
	case 'guardar':
	if(empty($idregistro))
	{	
			$rspta=$registroinv->insertar($ano, $codigo, $denominacion, $costoinicial, $saldoinicial, $valorinicial, $compras, $ventas, $saldofinal, $costo, $valorfinal);
			echo $rspta ? "Registro correcto" : "No se pudo registrar";
		
	}else{
			$rspta=$registroinv->editar($idregistro, $ano, $codigo, $denominacion, $costoinicial, $saldoinicial, $valorinicial, $compras, $ventas, $saldofinal, $costo, $valorfinal);
			echo $rspta ? "Regsitro actualizado" : "No se pudo actualizar";
		}
	break;

	

	case 'mostrar':
		$rspta=$registroinv->mostrar($idregistro);
 		//Codificar el resultado utilizando json
 		echo json_encode($rspta);
 		break;
	break;

	case 'eliminar':
		$rspta=$registroinv->eliminar($idregistro);
 		
 		echo $rspta? "Registro eliminado": "No se pudo eliminar";
 		break;
	




	case 'listar':
		$rspta=$registroinv->listar();
 		//Vamos a declarar un array
 		$data= Array();
 		$cod="";
 		while ($reg=$rspta->fetch_object()){
 			//$cod=strval($reg->codigo);
 			$data[]=array(
 				
 				
 				"0"=>$reg->ano,
 				"1"=>$reg->codigo,
 				"2"=>$reg->denominacion,
 				"3"=>$reg->costoinicial,
 				"4"=>number_format($reg->saldoinicial,2),
 				"5"=>number_format($reg->valorinicial,2),
 				"6"=>number_format($reg->compras,2),
 				"7"=>number_format($reg->ventas,2),
 				"8"=>number_format($reg->saldofinal,2),
 				"9"=>$reg->costo,
 				"10"=>number_format($reg->valorfinal,2),
 				"11"=>'<div class="botonessed" style="display: block ruby;"><button class="btn btn-warning btn-sm" onclick="mostrar('.$reg->idregistro.')" data-toggle="tooltip" title="Editar" >
 				<i class="fa fa-pencil"></i></button>
 				<button class="btn btn-danger btn-sm" onclick="eliminar('.$reg->idregistro.')" data-toggle="tooltip" title="Eliminar">
 				<i class="fa fa-trash"></i></button></div>'
 				
 			//"11" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idregistro . ')"><i class="ri-edit-line"></i></button>' .
				//' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idregistro . ')"><i class="ri-delete-bin-line"></i></button>' :
				//'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idregistro . ')"><i class="ri-edit-line"></i></button>' .
				//' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idregistro . ')"><i class="ri-check-double-line"></i></button>'
 				
 				
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