<?php
//recibimos los parametros
$ano = $_POST['ano'];
$mes = $_POST['mes'];
$dia = $_POST['dia'];

//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1)
  session_start();

if (!isset($_SESSION["nombre"])) {
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
} else {
  if ($_SESSION['Ventas'] == 1) {

    //Inlcuímos a la clase PDF_MC_Table
    require('PDF_MC_Table.php');

    //Comenzamos a crear las filas de los registros según la consulta mysql
    require_once "../modelos/Venta.php";
    $venta = new Venta();
    $rspta = $venta->regventareporte($ano, $mes);
    $rspta2 = $venta->regventareportetotales($ano, $mes);



    require_once "../modelos/Compra.php";
    $compra = new Compra();
    $datos = $compra->datosemp();
    $datose = $datos->fetch_object();


    //Instanciamos la clase para generar el documento pdf
    $pdf = new PDF_MC_Table();
    //Agregamos la primera página al documento pdf

    //Cabecera
    $pdf->AddPage();
    //Seteamos el inicio del margen superior en 25 pixeles 
    $y_axis_initial = 25;
    //$pdf->Ln(13);

    //Implementamos las celdas de la tabla con los registros a mostrar
    $pdf->SetWidths(array(6, 20, 15, 25, 60, 15, 15, 15, 25));
    while ($reg0 = $rspta->fetch_object()) {
      $fechae = $reg0->fecha;
      $tipodoc = $reg0->tipodocu;
      $ncomprobante = $reg0->documento;
      $ndocliente = $reg0->numero_documento;
      $cliente = $reg0->razon_social;
      $subtotal = $reg0->subtotal;
      $igv = $reg0->igv;
      $icbper = $reg0->icbper;
      $total = $reg0->total;
      $estado = $reg0->estado;

      switch ($estado) {
        case '3':
          $ndocliente = "DE BAJA";
          $cliente = "DE BAJA";
          $subtotal = '0';
          $igv = '0';
          $total = '0';
          break;
        case '0':
          $ndocliente = "CON NOTA";
          break;
        default:
          # code...
          break;
      }

      switch ($tipodoc) {
        case '01':
          $tipod = "FACTURA";
          break;
        case '03':
          $tipod = "BOLETA";
          break;
        case '07':
          $tipod = utf8_decode("N.CRÉDITO");
          break;
        case '08':
          $tipod = utf8_decode("N.DÉBITO");
          break;

        default:
          # code...
          break;
      }

      //Imprime el detalle de todos los comprobantes
      $pdf->SetFont('Arial', '', 8);
      $pdf->Row(array($fechae, $tipod, $ncomprobante, $ndocliente, utf8_decode($cliente), $subtotal, $igv, $icbper, $total));
    }
    //Imprime el detalle de todos los comprobantes
    $pdf->Ln(1);
    $pdf->SetFont('courier', 'B', 8);
    $pdf->Cell(185, 5, utf8_decode('--------------------------------------------------------------------------------------------------------------------'), 0, 0, 'C', 0);
    $pdf->Ln(5);
    //===================PARA TOTALES================================================
    while ($reg2 = $rspta2->fetch_object()) {
      $stotal = $reg2->subtotal;
      $igv = $reg2->igv;
      $icbper2 = $reg2->icbper;
      $total = $reg2->total;
    }
    //Resumen
    $pdf->SetWidths(array(70, 52, 15, 2, 15, 2, 12, 2, 18));
    $pdf->SetFont('arial', 'B', 8);
    $pdf->Row(array('', utf8_decode('TOTAL S/: '), number_format($stotal, 2), '', number_format($igv, 2), '', number_format($icbper2, 2), '', number_format($total, 2)));

    //===================PARA TOTALES================================================



    // $pdf->Ln(10);
// $pdf->SetWidths(array(50,200));
// $pdf->SetFont('arial','B',12);
// $pdf->Row(array('',utf8_decode('DETALLE DE COMPROBANTES ANULADOS')));
// $pdf->SetFont('Arial','B',8);
// $pdf->Cell(15,20,utf8_decode('DÍA'),0,0,'C',0) ;  
// $pdf->Cell(20,20,utf8_decode('TIP\DOC'),0,0,'C',0) ;  
// $pdf->Cell(20,20,utf8_decode('NÚMERO'),0,0,'C',0) ;  
// $pdf->Cell(25,20,utf8_decode('RUC.'),0,0,'C',0) ;  
// $pdf->Cell(60,20,utf8_decode('CLIENTE.'),0,0,'C',0) ;  
// $pdf->Cell(10,20,utf8_decode('BASE IMP.'),0,0,'C',0) ;  
// $pdf->Cell(25,20,utf8_decode('IGV'),0,0,'C',0) ;  
// $pdf->Cell(17,20,utf8_decode('TOTAL S/'),0,0,'C',0) ;  
// $pdf->Ln(1);
// $pdf->SetFont('Courier','B',8);
// $pdf->Cell(192,24,utf8_decode('-----------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  
// $pdf->Ln(12);
// //==============PARA ANULADOS====================================================
// $y_axis_initial = 25;
// $rspt3 = $venta->regventareporteAnulados($ano,$mes);
// $rspta4 = $venta->regventareportetotalesAnulados($ano,$mes);

    // $pdf->SetWidths(array(15,20,25,25,55,20,18,18));
// while($reg3= $rspt3->fetch_object())
// {  
//     $fechae = $reg3->fecha;
//     $tipodoc = $reg3->tipodocu;
//     $ncomprobante = $reg3->documento;
//     $ndocliente = $reg3->numero_documento;
//     $cliente = $reg3->razon_social;
//     $subtotal = $reg3->subtotal;
//     $igv = $reg3->igv;
//     $total = $reg3->total;
//     $estado=$reg3->estado;

    //     switch ($tipodoc) {
//     case '01':
//         $tipod="FACTURA";
//         break;
//     case '03':
//         $tipod="BOLETA";
//         break;
//     case '07':
//         $tipod=utf8_decode("N.CRÉDITO");
//         break;
//     case '08':
//         $tipod=utf8_decode("N.DÉBITO");
//         break;

    //     default:
//         # code...
//         break;
// }

    // //Imprime el detalle de todos los comprobantes
// $pdf->SetFont('arial','',7);
// $pdf->Row(array($fechae, $tipod, $ncomprobante, $ndocliente, utf8_decode($cliente), $subtotal, $igv, $total));
// }
// //Imprime el detalle de todos los comprobantes
// $pdf->Ln(1);
// $pdf->SetFont('courier','B',8);
// $pdf->Cell(185,5,utf8_decode('--------------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  
// $pdf->Ln(5);
// //===================PARA TOTALES================================================
// while($reg4= $rspta4->fetch_object())
// {  
//     $stotal = $reg4->subtotal;
//     $igv = $reg4->igv;
//     $total = $reg4->total;
// }
// //Resumen
// $pdf->SetWidths(array(80,55,18,2,18,2,18));
// $pdf->SetFont('arial','B',8);
// $pdf->Row(array('',utf8_decode('TOTAL ANULADOS S/: '),number_format($stotal,2),'', number_format($igv,2),'', number_format($total,2)));   






    //Mostramos el documento pdf
    $pdf->Output();

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>