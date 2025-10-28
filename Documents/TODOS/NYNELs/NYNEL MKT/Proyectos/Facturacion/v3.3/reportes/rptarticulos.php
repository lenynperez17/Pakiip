<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1) 
  session_start();
 
if (!isset($_SESSION["almacen"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
}
else
{
if ($_SESSION['almacen']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table_Art.php');

//Incluimos la libreria de codigo de barras 
include 'barcode.php';

 require_once "../modelos/Factura.php";
    $factura= new Factura();
    $datos = $factura->datosemp($_SESSION['idempresa']);
    $datose = $datos->fetch_object();
 

//Comenzamos a crear las filas de los r4gistros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();
$rspta = $articulo->listar($_SESSION['idempresa']);
 

 //Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table_Art();
 $pdf->AddPage();
 #Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(10, 10 , 10); 

#Establecemos el margen inferior: 
//$pdf->SetAutoPageBreak(off, 10);

//Implementamos las celdas de la tabla con los registros a mostrar
//$pdf->SetWidths(array(150,20,30));
  $i=1;
while($reg= $rspta->fetch_object())
{  

    
    $pdf->SetFont('helvetica','',8);
    
    $nombre = $reg->nombre." COD: ".$reg->codigo;;
    $categoria = $reg->familia;
    $precio = $reg->precio;
    $stock = $reg->stock." ".$reg->nombreum;
    $codigo =$reg->codigo;
    $fechavencimiento =$reg->fechavencimiento;

    barcode('codigos/'.$codigo.'.png', $codigo, 25, 'horizontal', 'code128b', true);
    //$pdf->Image('codigos/'.$codigo.'.png',160,$y,0,0,'PNG');
    //$imagen=$pdf->Image('codigos/'.$codigo.'.png', 160, $pdf->GetY(),0,0,'PNG');
    // $pdf->Row(array(utf8_decode($nombre), $precio,$stock,  $imagen . $i));

      $pdf->Cell(120,15,$nombre,0,0,'',0);
      $pdf->Cell(20,15,$precio,0,0,'',0);
      $pdf->Cell(38,15,$stock,0,0,'',0);
      $pdf->Cell(30,15,$fechavencimiento,0,0,'',0);
      //$pdf->Cell( 40, 15, $pdf->Image('codigos/'.$codigo.'.png', $pdf->GetX()+10, $pdf->GetY()+3, 20), 1, 0, 'C', false );
   $pdf->Ln(6);
    $i++;
   
}
 
    

  $pdf->Output();
//Mostramos el documento pdf


}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>