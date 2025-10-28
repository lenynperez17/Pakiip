<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
	session_start();

if (!isset($_SESSION["boletapago"])) {
	echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
	if ($_SESSION['Ventas'] == 1 || $_SESSION['inventarios'] == 1) {
		//Incluímos el archivo Factura.php
		require('Boletapago.php');
		//Obtenemos los datos de la cabe    cera de la venta actual
		require_once "../modelos/SueldoBoleta.php";
		$boletapa = new BoletaPago();
		require_once "Letras.php";
		$V = new EnLetras();

		// $rutasalidaboletapa="";
// require_once "../modelos/Rutas.php";
//     $rutas = new Rutas();
//     $Rrutas = $rutas->mostrar2("1");
//     $Prutas = $Rrutas->fetch_object();
//     $rutasalidaboletapa=$Prutas->salidaboletapas; 
//     $rutalogo=$Prutas->rutalogo; 


		$rsptav = $boletapa->datosboletapago($_GET["id"], $_SESSION['idempresa']);
		$regv = $rsptav->fetch_object();



		//$logo = $rutalogo.$datose->logo;
//$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);

		//Establecemos la configuración de la factura
		$pdf = new PDF_Invoice('P', 'mm', 'A5');
		$pdf->AddPage();

		#Establecemos los márgenes izquierda, arriba y derecha: 
		$pdf->SetMargins(3.5, 3.5, 3.5);
		#Establecemos el margen inferior: 
		$pdf->SetAutoPageBreak(true, 10);

		$pdf->contenidoboleta(
			$regv->nombre_comercial,
			$regv->domicilio_fiscal,
			$regv->numero_ruc,
			$regv->nroboleta,
			$regv->sueldobruto,
			$regv->horasex,
			$regv->totalhorasEx,
			$regv->horast,
			$regv->asigfam,
			$regv->diast,
			$regv->totaldiast,
			$regv->totalbruto,
			$regv->totaldcto,
			$regv->sueldopagar,
			$regv->nombreSeguro,
			$regv->tiposeguro,
			$regv->aoafp,
			$regv->invsob,
			$regv->comiafp,
			$regv->snp,
			$regv->total5t,
			$regv->taoafp,
			$regv->tinvsob,
			$regv->tcomiafp,
			$regv->tsnp,
			$regv->nombresE,
			$regv->apellidosE,
			$regv->fechaingreso,
			$regv->ocupacion,
			$regv->tiporemuneracion,
			$regv->dni,
			$regv->cusspp,
			$regv->trabNoct,
			$regv->mes,
			$regv->ano,
			$regv->fechapago,
			$regv->totalaportee,
			$con_letra = strtoupper($V->ValorEnLetras($regv->sueldopagar, "CON")),
			$regv->conceptoadicional,
			$regv->importeconcepto,
			$regv->autogenessa
		);





		//==========================================================================
		$pdf->AutoPrint();
		$pdf->Output();
		//$Factura=$pdf->Output('../boletapasPDF/'.$regv->numeracion_07.'.pdf','F');
//$pdf->Output($rutasalidaboletapa.$regv->numeracion_07.'.pdf','F');

	} else {
		echo 'No tiene permiso para visualizar el reporte';
	}

}
ob_end_flush();
?>