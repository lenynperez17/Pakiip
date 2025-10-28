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

    require_once "../modelos/Ventadiaria.php";
    $ventadiaria = new Ventadiaria();



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



    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 6, '', 0, 0, 'C');
    $pdf->Cell(100, 2, utf8_decode('REPORTE DETALLADO GASTOS E INGRESOS POR DÍA'), 0, 0, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('COURIER', 'B', 10);
    $pdf->SetWidths(array(40, 40, 40, 20, 20));
    $pdf->Row(array(utf8_decode('Día'), 'Total gasto', 'Total ingreso', 'Utilidad'));


    $rspta = $insumos->detalladomensual($_POST['mes']);
    while ($reg0 = $rspta->fetch_object()) {
      $fechadia = $reg0->fechadia;
      $totalg = $reg0->tgasto;
      $totali = $reg0->tingreso;
      $tutili = $reg0->utilidad;
      $porce = $reg0->porcentaje;

      $pdf->SetFont('COURIER', '', 8);
      $pdf->Row(array($fechadia, $totalg, $totali, $tutili, $porce));

    }

    $rspta4 = $insumos->detalladomensualtotal($_POST['mes']);
    while ($reg3 = $rspta4->fetch_object()) {
      $pdf->SetFont('COURIER', 'B', 10);
      $ttgasto = $reg3->tgasto;
      $ttingreso = $reg3->tingreso;
      $ttutilidad = $reg3->utilidad;
      //$portt = $reg3->porcentaje;
    }
    $pdf->SetWidths(array(200));
    $pdf->Row(array('_____________________________________________________________________________'));
    $pdf->SetWidths(array(40, 40, 40, 20, 20));
    $pdf->Row(array('Total general', $ttgasto, $ttingreso, $ttutilidad));

    //Mostramos el documento pdf
    $pdf->Output();

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>