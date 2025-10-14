<?php
require_once "../modelos/Articuloscreditex.php";

$articuloscre=new Articuloscreditex();

switch ($_GET["op"]){

case 'listado':
		$rspta=$articuloscre->listado();
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
 				"0"=>$reg->articulo,
 				"1"=>$reg->nombre,
 				"2"=>$reg->fechacompra,
 				"3"=>$reg->valorunitario,
 				"4"=>$reg->igvunitario,
 				"5"=>$reg->preciounitario
 				
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);
	break;

	case 'clave':
		$cll=$_GET['clave1'];
		$claveacceso="0402";
		if ($cll==$claveacceso) {
			echo json_encode("1");
		}else{
			echo json_encode("0");
		}
		

	}
?>