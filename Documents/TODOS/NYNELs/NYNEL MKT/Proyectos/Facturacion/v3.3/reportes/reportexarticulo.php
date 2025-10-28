<?php
//recibimos los parametros
$ano=$_POST['ano'];
$fechas=$_POST['fechas'];
$opcion=$_POST['opcion'];
$codigoInterno=$_POST['codigoInterno'];



//$unimed=$_POST['cbox1'];
$unimed='KG';

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
 
 if (isset($_POST['calcular'])) {

 }else{  // SI ES KARDEX


//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table5.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();


$valoresini = $articulo->saldoinicialV($ano, $codigoInterno, '1');
while($reg= $valoresini->fetch_object())
{
    //$idarticulo=$reg->idarticulo;  
    $costo1=$reg->costoi;
    $saldoini=$reg->saldoi;
    $valori=$reg->valori;
    //$factorc=$reg->factorc;
}


//$rspta = $articulo->kardexArticulo($ano,$fechas,$codigoInterno);
$rspta2 = $articulo->kardexArticulo($ano, $codigoInterno, '1',  $unimed);
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
$idarticulo='';
$costoi=0;
$saldofinalglobal=0;
$factorc=0;
$sw=0;

while($reg= $rspta2->fetch_object())
{  

    $fecha = $reg->fecha;
    $tipodoc=$reg->descripcion;
    $docum=$reg->numero_doc;
    $transac=$reg->transaccion;
    $tcambio=$reg->tcambio;
    $cantidad=$reg->cantidad;
    $idarticulo=$reg->idarticulo;
    $costoi=$reg->costo_compra;
    $costoingreso=$reg->costo_1;
    $costoventa=$reg->costo_1;
    $factorc=$reg->factorc;
    

// if ($reg->moneda=='USD') {
//             $costo1=($costo1 * 1.18) * $tcambio ;

//              }else{ //SI MONEDA ES SOLES
                
//             $costo1=$costo1;
//             }
    
    $unimed=$reg->unidad_medida;

    //$saldoini=$reg->saldo_iniu;
    //$valori=$reg->valor_iniu;
    $valorfinal=$reg->valor_iniu;
    $saldofinal=$reg->saldo_finu;
    $totalventa=$reg->ventast;

    

if ($transac=="COMPRA") {
    //Calculo para saldo final======================================
    $saldofin = $saldoini + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //$saldofin = $saldofinal + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    //VALFIN+(COSTO*CANTI))/(SALINI+CANTI)
     if ($sw==0){
    $costo2 = ($valori + ($costoingreso * $cantidad)) / ($saldoini + $cantidad + $saldofin3);
    }
    else
    {
    $costo2 = ($vf + ($costoingreso * $cantidad)) / ($saldofinal_v + $cantidad + $saldofin3);
    }
    $valorfin_p=$saldofin * number_format($costo2,2) ;

    //$costo2 = $reg->costo_2;
   
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    
    //Calculo para Valor final======================================

    $saldofin2 = $saldofin2 + $reg->cantidad;
    $vf = $valorfin_p;
    $saldofin3 = $saldofin3 + $reg->cantidad;

    $pdf->SetFont('Arial','',7);
     $pdf->Row(array(($fecha),'',($tipodoc),'',($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costoventa,'',utf8_decode($unimed),'|',($saldofin),'', number_format($costo2,2),'',number_format($valorfin_p,2)));
     
    $sw=$sw + 1;
    $saldofinalglobal=$saldofin;

//==================================================================================
        
   }else if ($transac=="VENTA") {

    if ($saldofinalglobal==0) { $saldofinalglobal=$saldoini; }
    
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal  - ($reg->cantidad);// - $saldofin4 ;
    //$saldofin4 =  $saldofin4 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
      if ($costo2==0)
        {
            $costo2=$costo1;
        }else{
            $costo2=$costo2;
        }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v *  number_format($costo2,2) ;
    //Calculo para Valor final======================================

    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
    $pdf->SetFont('Arial','',7);
    $pdf->Row(array(($fecha),'',($tipodoc),'',($docum),'',($transac),'',($cantidad),'',($costoventa),'',($unimed),'|',($saldofinal_v),'', number_format($costo2,2),'', number_format($valorfin_p,2)));

    $sw=$sw + 1;
    $saldofinalglobal= $saldofinal_v;
    $saldofin3=0;


}else if ($transac=="O.SERVI.") {
    if ($saldofinalglobal==0) { $saldofinalglobal=$saldoini; }
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
    $pdf->SetFont('Arial','',7);
    $pdf->Row(array(($fecha),'',($tipodoc),'',($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode($unimed),'|',($saldofinal_v),'', number_format($costo2,2),'',number_format($valorfin_p,2)));
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
    
    $pdf->SetFont('Arial','',7);
    $pdf->Row(array(utf8_decode($fecha),'',utf8_decode($tipodoc),'',utf8_decode($docum),'',utf8_decode($transac),'',utf8_decode($cantidad),'',$costo1,'',utf8_decode($unimed),'|',($saldofinal_nc),'', number_format($costo2,2),'', number_format($valorfin_p,2)));
    $saldofinalglobal= $saldofinal_nc;

    }else if ($transac=="COMPRA ANULADA"){
if ($saldofinalglobal==0) { $saldofinalglobal=$saldoini; }
    
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
    
    $pdf->SetFont('Arial','',7);
    $pdf->Row(array(($fecha),'',($tipodoc),'',($docum),'',($transac),'',($cantidad),'',($costo1),'',($unimed),'|',($saldofinal_v),'', number_format($costo2,2),'',number_format($valorfin_p,2)));

      $sw=$sw + 1;

    $saldofinalglobal= $saldofinal_v ;
    }


} // Fin while

// $rspta3 = $articulo->valoresiniciales($codigoInterno);
//     while($valini= $rspta3->fetch_object())
// {  
//     $idarticulo=$valini->idarticulo;
// }


$pdf->SetFont('courier','B',8);
$pdf->Cell(187,10,utf8_decode('---------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ; 
$pdf->Ln(1);    

//Mostramos el documento pdf
$pdf->Output();


}






}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>