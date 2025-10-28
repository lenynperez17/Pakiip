<?php 
	$dni = $_POST['dni'];
	
	if (empty($dni)) {
		echo json_encode(array('error'=>'1'));
	}else{
	  $ch = curl_init('http://aplicaciones007.jne.gob.pe/srop_publico/Consulta/Afiliado/GetNombresCiudadano?DNI='.$dni);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	  $data = curl_exec($ch);
	  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	  curl_close($ch);
		if ($http == 200) {
			$partes = explode("|", $data);
			$datos = array(
					0 => $dni, 
					1 => $partes[0], 
					2 => $partes[1],
					3 => $partes[2],
			);
		    echo json_encode($datos);
		} else {
			echo json_encode(array('error'=>'2'));
		}
	}
?>