<?php

//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
	session_start();

if (!isset($_SESSION["nombre"])) {
	echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
	if ($_SESSION['Ventas'] == 1) {

		//Inlcuímos a la clase PDF_MC_Table
		require('PDF_MC_Table4.php');

		//Comenzamos a crear las filas de los registros según la consulta mysql
		require_once "../modelos/Insumos.php";
		$insumos = new Insumos();


		$datos = $insumos->datosemp();
		$datose = $datos->fetch_object();



		//Instanciamos la clase para generar el documento pdf
		$pdf = new PDF_MC_Table();

		//Agregamos la primera página al documento pdf
		$pdf->AddPage();

		#Establecemos los márgenes izquierda, arriba y derecha: 
		$pdf->SetMargins(10, 10, 10);

		#Establecemos el margen inferior: 
		$pdf->SetAutoPageBreak(true, 10);

		//Seteamos el inicio del margen superior en 25 pixeles 
		$y_axis_initial = 25;

		//Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
		$pdf->SetFont('Arial', 'B', 8);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(-20, 2, utf8_decode($datose->nombre_comercial), 0, 0, 'C');
		//$pdf->SetFont('arial','B',8);
//$pdf->Cell(30,10,'REPORTE DE VENTAS AGRUPADO POR DÍA',0,0,'C'); 

		$pdf->SetFont('Arial', 'B', 8);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(160, 2, 'RUC: ' . $datose->numero_ruc, 0, 0, 'C');
		$pdf->Ln(8);


		$consu = $insumos->mostrarfechas($_GET['id']);
		while ($resur = $consu->fetch_object()) {
			$fe1 = $resur->fecha1;
			$fe2 = $resur->fecha2;
		}

		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(100, 2, utf8_decode('REPORTE DE GASTOS vs INGRESOS SEMANAL ' . 'DEL ' . $fe1 . ' AL ' . $fe2), 0, 0, 'C');

		$pdf->Ln(5);
		$pdf->SetFont('COURIER', 'B', 10);
		$pdf->SetWidths(array(40, 40, 40, 20, 20));
		$pdf->Row(array(utf8_decode('Día'), 'Total gasto', 'Total ingreso', 'Utilidad', '%'));


		$rspta = $insumos->reporteutilidad($_GET['id']);
		while ($reg0 = $rspta->fetch_object()) {
			$diasem = $reg0->diasemana;
			$totalg = $reg0->tgasto;
			$totali = $reg0->tingreso;
			$tutili = $reg0->utilidad;
			$porce = $reg0->porcentaje;



			$pdf->SetFont('COURIER', '', 8);
			$pdf->Row(array($diasem, $totalg, $totali, $tutili, $porce));

		}

		$rspta4 = $insumos->reporteutilidadtotal($_GET['id']);
		while ($reg3 = $rspta4->fetch_object()) {
			$pdf->SetFont('COURIER', 'B', 10);
			$ttgasto = $reg3->totalgasto;
			$ttingreso = $reg3->totalingreso;
			$ttutilidad = $reg3->totalutilidad;
			//$portt = $reg3->porcentaje;
		}
		$pdf->SetWidths(array(200));
		$pdf->Row(array('_____________________________________________________________________________'));
		$pdf->SetWidths(array(40, 40, 40, 20, 20));
		$pdf->Row(array('Total general', $ttgasto, $ttingreso, $ttutilidad));

		$pdf->Ln(8);
		$pdf->SetFont('Arial', 'B', 10);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(100, 2, utf8_decode('DETALLADO INGRESOS'), 0, 0, 'C');
		$pdf->Ln(5);
		$pdf->SetWidths(array(20, 20, 30, 40, 20));
		$pdf->Row(array(utf8_decode('Fecha'), 'Tipo', utf8_decode('Categoría'), utf8_decode('Descripción'), 'Monto'));

		$detallarpt = $insumos->detalladodatosingresos($_GET['id']);
		while ($reg50 = $detallarpt->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$fechar = $reg50->fechadia;
			$tipod = $reg50->tipodato;
			$categori = $reg50->descripcionc;
			$detaldesc = $reg50->descripcion;
			$ingreso = $reg50->ingreso;

			$pdf->Row(array($fechar, $tipod, $categori, $detaldesc, $ingreso));
		}

		$detallarptT = $insumos->detalladodatosingresostotal($_GET['id']);
		while ($reg50T = $detallarptT->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$totalingresosD = $reg50T->totalingdeta;
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->Row(array('', '', '', '---------------', '---------'));
			$pdf->Row(array('', '', '', 'Total ingresos', $totalingresosD));
		}

		$pdf->Ln(8);
		$pdf->SetFont('Arial', 'B', 10);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(100, 2, utf8_decode('DETALLADO GASTOS'), 0, 0, 'C');
		$pdf->Ln(5);
		$pdf->SetWidths(array(20, 20, 30, 40, 20));
		$pdf->Row(array(utf8_decode('Fecha'), 'Tipo', utf8_decode('Categoría'), utf8_decode('Descripción'), 'Monto'));

		$detallarpt = $insumos->detalladodatosgastos($_GET['id']);
		while ($reg50 = $detallarpt->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$fechar = $reg50->fechadia;
			$tipod = $reg50->tipodato;
			$categori = $reg50->descripcionc;
			$detaldesc = $reg50->descripcion;
			$gasto = $reg50->gasto;

			$pdf->Row(array($fechar, $tipod, $categori, $detaldesc, $gasto));
		}

		$detallarptT = $insumos->detalladodatosgastototal($_GET['id']);
		while ($reg50T = $detallarptT->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$totalgastosD = $reg50T->totalingdeta;
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->Row(array('', '', '', '---------------', '---------'));
			$pdf->Row(array('', '', '', 'Total gastos', $totalgastosD));
		}



		$pdf->Ln(8);
		$pdf->SetFont('Arial', 'B', 10);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(100, 2, utf8_decode('DETALLADO INGRESO TARJETAS'), 0, 0, 'C');
		$pdf->Ln(5);
		$pdf->SetWidths(array(20, 20, 30, 40, 20));
		$pdf->Row(array(utf8_decode('Fecha'), 'Tipo', utf8_decode('Categoría'), utf8_decode('Descripción'), 'Monto'));

		$detallarpt = $insumos->detalladodatosingresotarjetadetalle($_GET['id']);
		while ($reg50 = $detallarpt->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$fechar = $reg50->fechadia;
			$tipod = $reg50->tipodato;
			$categori = $reg50->descripcionc;
			$detaldesc = $reg50->descripcion;
			$totaltarjeta = $reg50->ingreso;

			$pdf->Row(array($fechar, $tipod, $categori, $detaldesc, $totaltarjeta));
		}

		$detallarptT = $insumos->detalladodatosingresostotaltarjeta($_GET['id']);
		while ($reg50T = $detallarptT->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);
			$totalgastosD = $reg50T->totaltarjtotal;
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->Row(array('', '', '', '---------------', '---------'));
			$pdf->Row(array('', '', '', 'Total tarjeta', $totalgastosD));
		}







		//Mostramos el documento pdf
		$pdf->Output();

	} else {
		echo 'No tiene permiso para visualizar el reporte';
	}

}
ob_end_flush();
?>