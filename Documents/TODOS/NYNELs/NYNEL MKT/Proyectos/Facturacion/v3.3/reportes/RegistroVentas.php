<?php
//recibimos los parametros
$ano = $_POST['ano'];
$mes = $_POST['mes'];
$dia = $_POST['dia'];
$tmon = $_POST['tmonedaa'];

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
    $rspta = $venta->regventareporte($ano, $mes, $_SESSION['idempresa'], $tmon);
    $rspta2 = $venta->regventareportetotales($ano, $mes, $_SESSION['idempresa'], $tmon);
    $tproductos = $venta->regventareportetotalesProducto($ano, $mes, $_SESSION['idempresa'], $tmon);
    $tservicios = $venta->regventareportetotalesServicios($ano, $mes, $_SESSION['idempresa'], $tmon);
    $tnotapedido = $venta->regventareportetotalesNotapedido($ano, $mes, $_SESSION['idempresa'], $tmon);
    $tnotacredito = $venta->regventareportetotalesNotacredito($ano, $mes, $_SESSION['idempresa'], $tmon);
    $tnotadebito = $venta->regventareportetotalesNotadebito($ano, $mes, $_SESSION['idempresa'], $tmon);



    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($_SESSION['idempresa']);
    $datose = $datos->fetch_object();


    //Instanciamos la clase para generar el documento pdf
    $pdf = new PDF_MC_Table('L', 'mm', 'A4');

    //Ajustar márgenes (izquierdo, superior, derecho)
    $pdf->SetMargins(8, 10, 8);

    //Cabecera
    $pdf->AddPage();
    //Seteamos el inicio del margen superior en 25 pixeles 
    $y_axis_initial = 25;
    //$pdf->Ln(13);




    //Implementamos las celdas de la tabla con los registros a mostrar
    $pdf->SetWidths(array(6, 12, 16, 22, 42, 55, 8, 8, 8, 8, 8, 8, 18, 14, 14, 23));
    while ($reg0 = $rspta->fetch_object()) {
      $fechae = $reg0->fecha;
      $tipodoc = $reg0->tipodocu;
      $ncomprobante = $reg0->documento;
      $ndocliente = $reg0->numero_documento;
      $cliente = $reg0->razon_social;
      $productosAdquiridos = $reg0->productos_adquiridos;
      $efectivo = $reg0->efectivo;
      $visa = $reg0->visa;
      $yape = $reg0->yape;
      $plin = $reg0->plin;
      $mastercard = $reg0->mastercard;
      $deposito = $reg0->deposito;
      $subtotal = $reg0->subtotal;
      $igv = $reg0->igv;
      $icbper = $reg0->icbper;
      $total = $reg0->total;
      $estado = $reg0->estado;
      $tipoventa = $reg0->tipofactura;
      $moneda = $reg0->tipomoneda;
      $tcambio = $reg0->tcambio;

      $sr = '';
      if ($tipoventa == 'servicios') {
        $sr = ' - SERV';

      }

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
        case '50':
          $tipod = "NOTA PED";
          break;

        default:
          # code...
          break;
      }


      $mon = "";


      $pdf->SetFont('Arial', '', 7);
      switch ($moneda) {
        case 'PEN':
          $pdf->SetTextColor(0, 0, 0);
          $mon = "";

          break;
        case 'USD':
          //$pdf->SetTextColor(144, 34, 7);
          $mon = "USD";

          break;
        default:
          break;
      }



      //Imprime el detalle de todos los comprobantes
      $pdf->SetFont('helvetica', '', 7);
      $pdf->Row(
        array(
          $fechae,
          $tipod,
          $ncomprobante,
          $ndocliente,
          utf8_decode($cliente),
          $productosAdquiridos,
          $efectivo,
          $visa,
          $yape,
          $plin,
          $mastercard,
          $deposito,
          $subtotal,
          $igv,
          $icbper,
          $total . $sr
        )
      );
    }




    $pdf->Ln(1);


    $pdf->Ln(5);
    //===================PARA TOTALES================================================
    while ($reg2 = $rspta2->fetch_object()) {
      $stotal = $reg2->subtotal;
      $igv = $reg2->igv;
      $icbper2 = $reg2->icbper;
      $total = $reg2->total;
    }

    while ($rprodutos = $tproductos->fetch_object()) {
      $stotalp = $rprodutos->subtotal;
      $igvp = $rprodutos->igv;
      $icbper3 = $rprodutos->icbper;
      $totalp = $rprodutos->total;
    }

    while ($rservicios = $tservicios->fetch_object()) {
      $stotals = $rservicios->subtotal;
      $igvs = $rservicios->igv;
      $icbper4 = $rservicios->icbper;
      $totals = $rservicios->total;
    }

    while ($rnotapedido = $tnotapedido->fetch_object()) {
      $stotaln = $rnotapedido->subtotal;
      $igvn = $rnotapedido->igv;
      $icbper5 = $rnotapedido->icbper;
      $totaln = $rnotapedido->total;
    }



    while ($rnotadebito = $tnotadebito->fetch_object()) {
      $stotalnd = $rnotadebito->subtotal;
      $igvnd = $rnotadebito->igv;
      $icbper6 = $rnotadebito->icbper;
      $totalnd = $rnotadebito->total;
    }


    while ($rnotacredito = $tnotacredito->fetch_object()) {
      $stotalnc = $rnotacredito->subtotal;
      $igvnc = $rnotacredito->igv;
      $icbper7 = $rnotacredito->icbper;
      $totalnc = $rnotacredito->total;
    }


    //Resumen
    $pdf->SetWidths(array(65, 50, 14, 2, 14, 2, 11, 2, 17));
    $pdf->SetFont('arial', 'B', 8);


    $pdf->Row(array('', utf8_decode('TOTAL PRODUCTOS: '), number_format($stotalp, 2), '', number_format($igvp, 2), '', number_format($icbper3, 2), '', number_format($totalp, 2)));

    $pdf->Row(array('', utf8_decode('TOTAL SERVICIOS: '), number_format($stotals, 2), '', number_format($igvs, 2), '', number_format($icbper4, 2), '', number_format($totals, 2)));

    $pdf->Row(array('', utf8_decode('TOTAL NOTAS DE PEDIDO: '), number_format($stotaln, 2), '', number_format($igvn, 2), '', number_format($icbper5, 2), '', number_format($totaln, 2)));

    $pdf->Row(array('', utf8_decode('TOTAL NOTAS  DEBITO: '), number_format($stotalnd, 2), '', number_format($igvnd, 2), '', number_format($icbper6, 2), '', number_format($totalnd, 2)));

    $pdf->Row(array('', utf8_decode('TOTAL NOTAS  CREDITO: '), number_format($stotalnc, 2), '', number_format($igvnc, 2), '', number_format($icbper7, 2), '', number_format($totalnc, 2)));

    $pdf->Ln(1);


    $pdf->Ln(5);
    $pdf->SetFont('arial', 'B', 8);
    $pdf->Row(array('', utf8_decode('TOTAL GENERAL: '), number_format($stotal, 2), '', number_format($igv, 2), '', number_format($icbper2, 2), '', number_format($total, 2)));

    //===================PARA TOTALES================================================



    //Mostramos el documento pdf
    $pdf->Output();

  } else {
    echo 'No tiene permiso para visualizar el reporte';
  }

}
ob_end_flush();
?>