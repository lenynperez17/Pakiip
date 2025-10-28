<?php
//recibimos los parametros
$ano=$_POST['ano'];
$fechas=$_POST['fechas'];
$codigoInterno=$_POST['codigoInterno'];

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
if ($_SESSION['almacen']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table5.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();
//$rspta = $articulo->kardexArticulo($ano,$fechas,$codigoInterno);
$rspta2 = $articulo->kardexArticulo($ano,$fechas,$codigoInterno);


//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
//Agregamos la primera página al documento pdf
$pdf->AddPage();
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;
 
//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(13,2,18,1,24,2,16,2,18,2,15,5,10,4,25,4,12,4,28));
 
$saldofin=0;
$saldofinal_v=0;
$saldofin2=0; 
$saldofin3=0; 
$valorfin_p=0;
$valorfin4=0;
$saldofin4=0; 
$saldofin5=0;
$saldofinal_nc=0;
$costo2=0;

$saldofinalglobal=0;
$sw=0;

while($reg= $rspta2->fetch_object())
{  
    $fecha = $reg->fecha;
    $tipodoc=$reg->descripcion;
    $docum=$reg->numero_doc;
    $transac=$reg->transaccion;
    $cantidad=$reg->cantidad;
    $costo1=$reg->costo_1;
    $unimed=$reg->unidad_medida;

    $saldoini=$reg->saldo_iniu;
    $valorfinal=$reg->valor_iniu;
    $saldofinal=$reg->saldo_finu;
    $totalventa=$reg->ventast;

    if (is_null($reg)) {
        print("Artículo sin registros");
        $pdf->Row(array('SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','','SIN REGISTROS','', 'SIN REGISTROS','','SIN REGISTROS'));
    }else{
    


if ($transac=="COMPRA") {
    //Calculo para saldo final======================================
    $saldofin = $saldoini + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //$saldofin = $saldofinal + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    //VALFIN+(COSTO*CANTI))/(SALINI+CANTI)
    if ($sw==0){
    $costo2 = round(($valorfinal + ($costo1 * $cantidad)) / ($saldoini + $cantidad + $saldofin3),2);
    }
    else
    {
    $costo2 = round(($vf + ($costo1 * $cantidad)) / ($saldoini + $cantidad + $saldofin3),2);
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p=$saldofin * $costo2 ;
    //Calculo para Valor final======================================

    $saldofin2 = $saldofin2 + $reg->cantidad;
    $vf = $valorfin_p;
    $saldofin3 = $saldofin3 + $reg->cantidad;

    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(utf8_decode($fecha),'',utf8_decode($tipodoc),'',utf8_decode($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode

($unimed),'|',number_format($saldofin,2),'', number_format($costo2,2),'',number_format($valorfin_p,2)));
    $sw=$sw + 1;

    $saldofinalglobal=$saldofin;

//==================================================================================
        
   }else if ($transac=="VENTA") {

    if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal  - $reg->cantidad;// - $saldofin4 ;
    //$saldofin4 =  $saldofin4 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================

    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(utf8_decode($fecha),'',utf8_decode($tipodoc),'',utf8_decode($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode($unimed),'|',number_format($saldofinal_v, 2),'', number_format($costo2,2),'',number_format($valorfin_p,2)));

      $sw=$sw + 1;

    $saldofinalglobal= $saldofinal_v ;


}else if ($transac=="O.SERVI.") {
    if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal - $reg->cantidad;// - $saldofin4 ;
    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================
    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================
    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(utf8_decode($fecha),'',utf8_decode($tipodoc),'',utf8_decode($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode
($unimed),'|',number_format($saldofinal_v, 2),'', number_format($costo2,2),'',number_format($valorfin_p,2)));
    $sw=$sw + 1;
    $saldofinalglobal= $saldofinal_v ;

    
//====================================================================================
    }else if ($transac=="NOTAC" || $transac=="ANULADO" ) {

    //$tipodoc="NOTAC";
    //Calculo para saldo final======================================
    $saldofinal_nc = $saldofinalglobal + $reg->cantidad;// + $saldofin5 ;
    //$saldofin5 =  $saldofin5 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    $costo2 = $costo2;
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_nc * $costo2 ;
    //Calculo para Valor final======================================

    $saldofin5 = $saldofin5 + $reg->cantidad;
    
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(utf8_decode($fecha),'',utf8_decode($tipodoc),'',utf8_decode($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode

($unimed),'|',number_format($saldofinal_nc,2),'', number_format($costo2,2),'', number_format($valorfin_p,2)));
    $saldofinalglobal= $saldofinal_nc;

     }else if ($transac=="COMPRA ANULADA"){

if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal  - $reg->cantidad;// - $saldofin4 ;
    //$saldofin4 =  $saldofin4 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================

    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
    $pdf->SetFont('Arial','',8);
    $pdf->Row(array(($fecha),'',($tipodoc),'',($docum),'',($transac),'',($cantidad),'',($costo1),'',($unimed),'|',($saldofinal_v),'', ($costo2),'',($valorfin_p)));

      $sw=$sw + 1;

    $saldofinalglobal= $saldofinal_v ;
        
    }//Fin IF tipo de transaccion

}// Fin si no hay registros

}//Fin While

//$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(187,10,utf8_decode('---------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ; 
$pdf->Ln(1);    

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