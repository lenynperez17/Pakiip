<?php
require_once "../modelos/Boleta.php";
$boleta=new Boleta();
	// $nombre_temporal = $_FILES['archivo']['tmp_name'];
	// $nombre = $_FILES['archivo']['name'];
	// move_uploaded_file($nombre_temporal, '../sfs/contingencia/'.$nombre);

$ruta = '../sfs/contingencia/'; //Decalaramos una variable con la ruta en donde almacenaremos los archivos
$mensage = '';//Declaramos una variable mensaje quue almacenara el resultado de las operaciones.
foreach ($_FILES as $key) //Iteramos el arreglo de archivos
{
	if($key['error'] == UPLOAD_ERR_OK )//Si el archivo se paso correctamente Ccontinuamos 
		{
			$NombreOriginal = $key['name'];//Obtenemos el nombre original del archivo
			$temporal = $key['tmp_name']; //Obtenemos la ruta Original del archivo
			$Destino = $ruta.$NombreOriginal;	//Creamos una ruta de destino con la variable ruta y el nombre original del archivo	
			move_uploaded_file($temporal, $Destino); //Movemos el archivo temporal a la ruta especificada		
			$jsonCont = file_get_contents($Destino);
			$content = json_decode($jsonCont, true);
		
			//Leer el archivo

			//var_dump($content);
			//echo  json_encode($content['detalle']);

			$tipoDoc = $content['cabecera']['tipodoc'];
			$serienumero = $content['cabecera']['serienumero'];
			$tipOperacion = $content['cabecera']['tipOperacion'];
			$fecEmision = $content['cabecera']['fecEmision'];
			$horEmision = $content['cabecera']['horEmision'];
			$fecVencimiento = $content['cabecera']['fecVencimiento'];
			$codLocalEmisor = $content['cabecera']['codLocalEmisor'];
			$tipDocUsuario = $content['cabecera']['tipDocUsuario'];
			$numDocUsuario = $content['cabecera']['numDocUsuario'];
			$rznSocialUsuario = $content['cabecera']['rznSocialUsuario'];
			$tipMoneda = $content['cabecera']['tipMoneda'];
			$sumTotTributos = $content['cabecera']['sumTotTributos'];
			$sumTotValVenta = $content['cabecera']['sumTotValVenta'];
			$sumPrecioVenta = $content['cabecera']['sumPrecioVenta'];
			$sumDescTotal = $content['cabecera']['sumDescTotal'];
			$sumOtrosCargos = $content['cabecera']['sumOtrosCargos'];
			$sumTotalAnticipos = $content['cabecera']['sumTotalAnticipos'];
			$sumImpVenta = $content['cabecera']['sumImpVenta'];
			$ublVersionId = $content['cabecera']['ublVersionId'];
			$customizationId = $content['cabecera']['customizationId'];


			$codUnidadMedida = $content['detalle']['codUnidadMedida'];
			$ctdUnidadItem = $content['detalle']['ctdUnidadItem'];
			$codProducto = $content['detalle']['codProducto'];
			$codProductoSUNAT = $content['detalle']['codProductoSUNAT'];
			$desItem = $content['detalle']['desItem'];
			$mtoValorUnitario = $content['detalle']['mtoValorUnitario'];
			$sumTotTributosItem = $content['detalle']['sumTotTributosItem'];
			$codTriIGV = $content['detalle']['codTriIGV'];
			$mtoIgvItem = $content['detalle']['mtoIgvItem'];
			$mtoBaseIgvItem = $content['detalle']['mtoBaseIgvItem'];
			$nomTributoIgvItem = $content['detalle']['nomTributoIgvItem'];
			$codTipTributoIgvItem = $content['detalle']['codTipTributoIgvItem'];
			$tipAfeIGV = $content['detalle']['tipAfeIGV'];
			$porIgvItem = $content['detalle']['porIgvItem'];
			$codTriISC = $content['detalle']['codTriISC'];
			$mtoIscItem = $content['detalle']['mtoIscItem'];
			$mtoBaseIscItem = $content['detalle']['mtoBaseIscItem'];
			$nomTributoIscItem = $content['detalle']['nomTributoIscItem'];
			$codTipTributoIscItem = $content['detalle']['codTipTributoIscItem'];
			$tipSisISC = $content['detalle']['tipSisISC'];
			$porIscItem = $content['detalle']['porIscItem'];
			$codTriOtroItem = $content['detalle']['codTriOtroItem'];
			$mtoTriOtroItem = $content['detalle']['mtoTriOtroItem'];
			$mtoBaseTriOtroItem = $content['detalle']['mtoBaseTriOtroItem'];
			$nomTributoIOtroItem = $content['detalle']['nomTributoIOtroItem'];
			$codTipTributoIOtroItem = $content['detalle']['codTipTributoIOtroItem'];
			$porTriOtroItem = $content['detalle']['porTriOtroItem'];
			$codTriIcbper = $content['detalle']['codTriIcbper'];
			$mtoTriIcbperItem = $content['detalle']['mtoTriIcbperItem'];
			$ctdBolsasTriIcbperItem = $content['detalle']['ctdBolsasTriIcbperItem'];
			$nomTributoIcbperItem = $content['detalle']['nomTributoIcbperItem'];
			$codTipTributoIcbperItem = $content['detalle']['codTipTributoIcbperItem'];
			$mtoTriIcbperUnidad = $content['detalle']['mtoTriIcbperUnidad'];
			$mtoPrecioVentaUnitario = $content['detalle']['mtoPrecioVentaUnitario'];
			$mtoValorVentaItem = $content['detalle']['mtoValorVentaItem'];
			$mtoValorReferencialUnitario = $content['detalle']['mtoValorReferencialUnitario'];


			$codLeyenda = $content['leyendas']['codLeyenda'];
			$desLeyenda = $content['leyendas']['desLeyenda'];


			 $ideTributo = $content['tributos']['ideTributo'];
			$nomTributo = $content['tributos']['nomTributo'];
			$codTipTributo = $content['tributos']['codTipTributo'];
			$mtoBaseImponible = $content['tributos']['mtoBaseImponible'];
			$mtoTributo = $content['tributos']['mtoTributo'];
		 	}
		}


 				$rspta=$boleta->insertarcontingencia(
			 	'1', 
			 	$fecEmision,
			 	$horEmision, 
			 	'', 
			 	'1', 
			 	$tipoDoc, 
			 	$serienumero, 
			 	$numDocUsuario, 
			 	'0101', 
			 	$sumTotValVenta, 
			 	$sumTotTributos, 
			 	$sumTotTributos, 
			 	'1000', 
			 	'IGV', 
			 	'VAT', 
			 	$sumImpVenta, 
			 	$codLeyenda,  
			 	$desLeyenda, 
			 	$tipoDoc, 
			 	'', 
			 	'2.1', 
			 	'2.0', 
			 	'', 
			 	$tipMoneda, 



			 	$codProducto, 
			 	'1', 
			 	$ctdUnidadItem, 
			 	'', 
			 	$mtoPrecioVentaUnitario, 
			 	$mtoIgvItem, 
			 	$mtoIgvItem, 
			 	'10', 
			 	'1000', 
			 	'IGV', 
			 	'VAT', 
			 	$sumImpVenta * 0.18, 
			 	$mtoValorUnitario, 
			 	$mtoValorVentaItem, 
			 	'', 
			 	'', 


			 	'', 
			 	'',
			 	$tipDocUsuario 
			 	);

    echo $rspta;


 
	if ($key['error']=='') //Si no existio ningun error, retornamos un mensaje por cada archivo subido
		{
			$mensage .= '-> Archivo <b>'.$NombreOriginal.$rspta.'</b> Subido correctamente. <br>';
		}
	if ($key['error']!='')//Si existio algún error retornamos un el error por cada archivo.
		{
			$mensage .= '-> No se pudo subir el archivo <b>'.$NombreOriginal.'</b> debido al siguiente Error: n'.$key['error']; 
		}



echo $mensage;// Regresamos los mensajes generados al cliente

?>