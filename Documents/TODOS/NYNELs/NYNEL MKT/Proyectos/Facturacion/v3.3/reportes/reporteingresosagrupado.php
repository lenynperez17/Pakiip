<?php

//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1) 
  session_start();
 
if (!isset($_SESSION["nombre"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
}
else
{
if ($_SESSION['compras']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table4.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Insumos.php";
$insumos = new Insumos();

    $datos = $insumos->datosemp();
    $datose = $datos->fetch_object();



//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
 
//Agregamos la primera página al documento pdf
$pdf->AddPage();

#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(25, 10 , 10); 

#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10);
 
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;

//Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(-20,2,utf8_decode($datose->nombre_comercial),0,0,'C'); 
//$pdf->SetFont('arial','B',8);
//$pdf->Cell(30,10,'REPORTE DE VENTAS AGRUPADO POR DÍA',0,0,'C'); 

$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(160,2,'RUC: '.$datose->numero_ruc,0,0,'C'); 
$pdf->Ln(13);


$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(100,2,utf8_decode('REPORTE DE INGRESOS DEL DÍA'),0,0,'C'); 

$pdf->Ln(5);


//Mostramos la fecha de creación del PDF
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(100, 2, utf8_decode('Fecha: ') . date('d/m/Y'), 0, 0, 'R');
$pdf->Ln(10);    

$pdf->Ln(1);

$pdf->SetFont('COURIER','B',10);
$pdf->Cell(20,20,utf8_decode('CATEGORÍAS: '),0,0,'C',0) ;

		//$pdf->Cell(2,40,utf8_decode('Descripción'),0,0,'C',0) ;  
		//$pdf->Cell(40,40,utf8_decode('Precio'),0,0,'C',0) ;

$pdf->Ln(12);

$rspta = $insumos->categoriaagrupadoingresos($_POST['fechaingreso']);
while($reg0= $rspta->fetch_object())
{
	$pdf->SetFont('COURIER','B',10); 
     $categoria = $reg0->descripcionc;
	 $pdf->Row(array($categoria));

$pdf->SetWidths(array(100,20));
		//$pdf->Ln(1);
//===========================================
$pdf->SetFont('COURIER','B',8);
	 $rspta2 = $insumos->categoriaagrupadoingresosDet($categoria, $_POST['fechaingreso']);
	 $pdf->Row(array(utf8_decode('Descripción'), 'Precio', 'acredor'));

	 while($reg1= $rspta2->fetch_object())
	{
		$pdf->SetFont('COURIER','',8);
		 
		$descri = $reg1->descripcion;
		$acredor = $reg1->acredor;
    	$pre = $reg1->totalgd;
    	$pdf->Row(array($descri, $pre, $acredor));
	}
	

	//=====================================
    	$rspta3 = $insumos->totalxcategoriaingreso($categoria, $_POST['fechaingreso']);
    	while($reg2= $rspta3->fetch_object())
		{
		$pdf->SetFont('COURIER','B',8);
		$totxcat = $reg2->totalxcate;
		$pdf->SetFont('COURIER','B',8);
		$pdf->Row(array(utf8_decode('Total por categoría'),$totxcat));
		$pdf->Row(array('========================================================','======='));
    	}
}

		$rspta4 = $insumos->totalxcategoriageneralIngreso($_POST['fechaingreso']);
    	while($reg3= $rspta4->fetch_object())
		{
		$pdf->SetFont('COURIER','B',10);
		$totalgeneral = $reg3->totalxcategeneral;
		
		$pdf->Row(array('Total general ingresos',$totalgeneral));
    	}




    	$rspta5 = $insumos->totalingresotarjeta($_POST['fechaingreso']);
    	while($reg3= $rspta5->fetch_object())
		{
		$pdf->SetFont('COURIER','B',10);
		$totaltarjeta = $reg3->totaltarje;
		
		$pdf->Row(array('Total tarjeta',$totaltarjeta));
    	}

    	$rspta6 = $insumos->totalingresoefectivototal($_POST['fechaingreso']);
    	while($reg3= $rspta6->fetch_object())
		{
		$pdf->SetFont('COURIER','B',10);
		$efectivot = $reg3->efectivot;
		
		$pdf->Row(array('Total efectivo',$efectivot));
    	}


//Mostramos el documento pdf
$pdf->Output();

}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>