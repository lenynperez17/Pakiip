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
		require_once "../modelos/Ventadiaria.php";
		$ventadiaria = new Ventadiaria();

		$datos = $ventadiaria->datosemp();
		$datose = $datos->fetch_object();



		//Instanciamos la clase para generar el documento pdf
		$pdf = new PDF_MC_Table();

		//Agregamos la primera página al documento pdf
		$pdf->AddPage();

		#Establecemos los márgenes izquierda, arriba y derecha: 
		$pdf->SetMargins(25, 10, 10);

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
		$pdf->Ln(13);


		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Cell(40, 6, '', 0, 0, 'C');
		$pdf->Cell(50, 2, utf8_decode('REPORTE DE INGRESOS DEL DÍA'), 0, 0, 'C');

		$pdf->Ln(12);

		$pdf->SetWidths(array(100, 20));
		//$pdf->Ln(1);
//===========================================
		$pdf->SetFont('COURIER', 'B', 10);
		$rspta2 = $ventadiaria->ingresoagrupadoxtipo();
		$pdf->Row(array(utf8_decode('Tipo'), 'Total'));

		while ($reg1 = $rspta2->fetch_object()) {
			$pdf->SetFont('COURIER', '', 8);

			$tip = $reg1->tipo;
			switch ($tip) {
				case 'efectivod':
					$tip = 'EFECTIVO DIA';
					break;

				case 'efectivon':
					$tip = 'EFECTIVO NOCHE';
					break;

				default:
					$tip = 'TARJETA';
					break;
			}

			$tot = $reg1->totali;
			$pdf->Row(array($tip, $tot));
		}
		$pdf->Row(array('========================================================', '=========='));
		$rspta4 = $ventadiaria->ingresoagrupadototal();
		while ($reg3 = $rspta4->fetch_object()) {
			$pdf->SetFont('COURIER', 'B', 10);
			$totalgeneral = $reg3->totalgene;

			$pdf->Row(array('Total efectivo', $totalgeneral));
		}

		$pdf->Ln(5);


		$pdf->Row(array('==============================================', '========'));
		$pdf->Row(array(utf8_decode('Tipo'), 'Total'));
		$rspta5 = $ventadiaria->ingresoagrupadoxtipo2();
		while ($reg4 = $rspta5->fetch_object()) {
			$pdf->SetFont('COURIER', 'B', 10);
			$tot2 = $reg4->totali;
			$tip2 = $reg4->tipo;
			$pdf->SetFont('COURIER', '', 8);
			switch ($tip2) {
				case 'efectivot':
					$tip2 = 'EFECTIVO TOTAL';
					break;

				default:
					$tip2 = 'TARJETA';
					break;
			}
			$pdf->Row(array($tip2, $tot2));
		}
		//Mostramos el documento pdf
		$pdf->Output();

	} else {
		echo 'No tiene permiso para visualizar el reporte';
	}

}
ob_end_flush();
?>