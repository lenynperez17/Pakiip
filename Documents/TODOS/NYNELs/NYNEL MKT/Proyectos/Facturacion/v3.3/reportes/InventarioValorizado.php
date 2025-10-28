<?php
//recibimos los parametros
$entrada="";
$entrada=$_POST['entrada'];

$ano=$_POST['ano'];

    $codigo = "";
    $nombre = "";
    $saldoini = "";
    $totalc = "";
    $totalv = "";
    $saldofin = "";
    $costoc = "";
    $valorfin = "";


 
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
if ($_SESSION['kardex']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table3.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();
$rspta = $articulo->inventariovalorizado($ano);
$rspta2 = $articulo->totalinventariovalorizado($ano);
//$rspta3 = $articulo->inventariovalorizadoxcodigo($entrada);
//$rspta4 = $articulo->inventariovalorizadoxcodigo($entrada);

    

//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
//Agregamos la primera página al documento pdf
$pdf->AddPage();
 
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;
 


 
if ($entrada==""){

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(15,40,22,22,22,22,14,2,22));
while($reg= $rspta->fetch_object())
{  
    $codigo = $reg->codigo;
    $nombre=$reg->denominacion;
    $saldoini=$reg->saldoinicial;
    $comprastt=$reg->compras;
    $ventast=$reg->ventas;
    $saldofin=$reg->saldofinal;
    $costoc=$reg->costo;
    $valorfin=$reg->valorfinal;
    
     
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(utf8_decode($codigo),utf8_decode($nombre),utf8_decode($saldoini),utf8_decode($comprastt),utf8_decode($ventast),utf8_decode($saldofin),utf8_decode($costoc),'',utf8_decode($valorfin)));
}

$pdf->Ln(5);
//$pdf->SetFont('courier','B',8);
$pdf->Cell(185,5,utf8_decode('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),0,0,'C',0) ; 
$pdf->Ln(5);

while($reg2= $rspta2->fetch_object())
{  
    $totalsi = $reg2->saldoinicial;
    $totalcom = $reg2->compras;
    $totalven = $reg2->ventas;
    $totalsf = $reg2->saldofinal;
    $totalvf = $reg2->valorfinal;
}

//Resumen
$pdf->SetWidths(array(15,40,22,22,22,22,14,22));
$pdf->SetFont('Arial','B',8);
$pdf->Row(array('',utf8_decode('TOTAL S/: '),$totalsi,$totalcom,$totalven,$totalsf,'==>',$totalvf));

//$pdf->SetFont('courier','B',8);
$pdf->Cell(185,5,utf8_decode('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),0,0,'C',0) ; 

}
else
{

$pdf->SetWidths(array(15,40,22,22,22,22,14,2,22));
while($reg5= $rspta3->fetch_object())
{  
    $codigo = $reg5->codigo;
    $nombre=$reg5->denominacion;
    $saldoini=$reg5->saldoinicial;
    $comprastt=$reg5->compras;
    $ventast=$reg5->ventas;
    $saldofin=$reg5->saldofinal;
    $costoc=$reg5->costo;
    $valorfin=$reg5->valorfinal;
    
     
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array($codigo, $nombre, $saldoini, $comprast, $ventast, $saldofin, $costoc,'', $valorfin));
}


$pdf->Ln(5);
//$pdf->SetFont('courier','B',8);
$pdf->Cell(185,5,utf8_decode('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),0,0,'C',0) ; 
$pdf->Ln(5);

    $totalsi = 0;
    $totalcom = 0;
    $totalven = 0;
    $totalsf = 0;
    $totalvf = 0;


while($reg3= $rspta4->fetch_object())
{  
    $totalsi = $reg3->saldoinicial;
    $totalcom = $reg3->compras;
    $totalven = $reg3->ventas;
    $totalsf = $reg3->saldofinal;
    $totalvf = $reg3->valorfinal;
}

//Resumen
$pdf->SetWidths(array(15,40,22,22,22,22,14,22));
$pdf->SetFont('arial','B',8);
$pdf->Row(array('',utf8_decode('TOTAL S/: '), $totalsi, $totalcom, $totalven, $totalsf,'==>', $totalvf));

//$pdf->SetFont('courier','B',8);
$pdf->Cell(185,5,utf8_decode('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),0,0,'C',0) ; 

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