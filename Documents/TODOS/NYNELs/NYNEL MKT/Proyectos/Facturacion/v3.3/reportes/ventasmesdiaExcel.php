<?php
	require "../config/Conexion.php";

		
		$ano=$_POST['ano'];
		$mes=$_POST['mes'];
		//$dia=$_POST['dia'];

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosempExcel();
    $datose = $datos->fetch_object();
		  $ruc=$datose->numero_ruc;
          $empresa=$datose->nombre_razon_social;

 if (PHP_SAPI == 'cli')
 	die('Este ejemplo sólo se puede ejecutar desde un navegador Web');

/** Incluye PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel.php';
//require_once ('PHPExcel.php');
// Crear nuevo objeto PHPExcel
$objPHPExcel = new PHPExcel();

// Propiedades del documento
$objPHPExcel->getProperties()->setCreator("Edu.A")
							 ->setLastModifiedBy("Tecnologos")
							 ->setTitle("Hoja de resumen de ventas por mes")
							 ->setSubject("Hoja de resumen de ventas por mes")
							 ->setDescription("Hoja de resumen de ventas por mes")
							 ->setKeywords("@@")
							 ->setCategory("Hoja de resumen de ventas por mes");



// // Combino las celdas desde A1 hasta E1
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:B2');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C2:E2');

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'REPORTE DE VENTAS MENSUAL')

            ->setCellValue('A2', 'EMPRESA')
            ->setCellValue('B4', 'AÑO')
            ->setCellValue('B5', 'MES')
            ->setCellValue('E2', '01:FACTURA/03:BOLETA/07:NOTA DE CRÉDITO/08:NOTA DE DÉBITO')
            ->setCellValue('G2', 'RUC')

            ->setCellValue('E4', 'FACTURA')
            ->setCellValue('E5', 'BOLETA')
            ->setCellValue('E6', 'NOTA CREDITO')
            ->setCellValue('E7', 'NOTA DEBITO')
            ->setCellValue('E8', 'NOTA DE PEDIDO')

            ->setCellValue('F4', '01')
            ->setCellValue('F5', '03')
            ->setCellValue('F6', '07')
            ->setCellValue('F7', '08')
            ->setCellValue('F8', '50')

            ->setCellValue('C2', $empresa)
            ->setCellValue('H2', $ruc)

            ->setCellValue('C4', $ano)
            ->setCellValue('C5', $mes)

            ->setCellValue('A9', 'DÍA')
            ->setCellValue('B9', 'TIPODOC')
            ->setCellValue('C9', 'NUMERO')
            ->setCellValue('D9', 'RUC')
            ->setCellValue('E9', 'CLIENTE')
            ->setCellValue('F9', 'BASE')
            ->setCellValue('G9', 'IGV')
            ->setCellValue('H9', 'TOTAL');
			
// Fuente de la primera fila en negrita

$boldArrayTitulo = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 14),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($boldArrayTitulo);      


$boldArray = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 12),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

	$objPHPExcel->getActiveSheet()->getStyle('A2:H9')->applyFromArray($boldArray);		

	
			
//Ancho de las columnas
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);	
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);	
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);	
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);	
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);	
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);		
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);		

/*Extraer datos de MYSQL*/
	# conectare la base de datos
    // $con=@mysqli_connect('localhost', 'root', '', 'estrella');
    // if(!$con){
    //     die("imposible conectarse: ".mysqli_error($con));
    // }
    // if (@mysqli_connect_errno()) {
    //     die("Connect failed: ".mysqli_connect_errno()." : ". mysqli_connect_error());
    // }


     $con = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $con, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

	$sql="select 
        idfactura as id,
        tipo_documento_07 as tipodocu, 
        date_format(fecha_emision_01, '%d') as fecha, 
        numeracion_08 as documento,
        subtotal, 
        igv, 
        total, 
        estado, 
        numero_documento, 
        razon_social,
        format(icbper,2) as icbper,
        tipofactura
            from 
            (select f.idfactura, f.tipo_documento_07 ,f.fecha_emision_01, f.total_operaciones_gravadas_monto_18_2 as subtotal, f.numeracion_08, f.sumatoria_igv_22_1 as igv, f.importe_total_venta_27 as total, f.estado, p.numero_documento, p.razon_social, f.icbper,f.tipofactura
            from 
            factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes'  and  p.tipo_persona='CLIENTE' and f.estado in('5','3','0','6','1','4') 
             union all
             
            select  b.idboleta, b.tipo_documento_06 ,b.fecha_emision_01, b.monto_15_2 as subtotal, b.numeracion_07, b.sumatoria_igv_18_1 as igv, b.importe_total_23 as total, b.estado, p.numero_documento , p.razon_social, b.icbper, b.tipoboleta
            from 
            boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa 
            where 
            year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes'  and p.tipo_persona='CLIENTE' and b.estado in('5','3', '0','6','1','4')
             union all
             
             select  np.idboleta, np.tipo_documento_06 ,np.fecha_emision_01, np.monto_15_2 as subtotal, np.numeracion_07, np.sumatoria_igv_18_1 as igv, np.importe_total_23 as total, np.estado, p.numero_documento , p.razon_social, np.icbper, np.tiponota 
             from 
             notapedido np inner join persona p on np.idcliente=p.idpersona inner join empresa e on np.idempresa=e.idempresa 
             where 
             year(np.fecha_emision_01)='$ano' and month(np.fecha_emision_01)='$mes' and p.tipo_persona='CLIENTE' and np.estado in('5','3', '0','6','1','1','4') 

            union all 
             select   ncd.idnota, ncd.codigo_nota, ncd.fecha,  if(ncd.codigo_nota='07', ncd.total_val_venta_og *-1 , ncd.total_val_venta_og) as subtotal, ncd.numeroserienota, if (ncd.codigo_nota='07', ncd.sum_igv * -1, ncd.sum_igv) as igv, if(ncd.codigo_nota='07',ncd.importe_total * -1,ncd.importe_total)  as total, ncd.estado, p.numero_documento , p.razon_social, ncd.icbper, ncd.tiponotacd
             from 
             notacd ncd inner join factura f on ncd.idcomprobante=f.idfactura inner join persona p on  f.idcliente=p.idpersona inner join empresa e on ncd.idempresa=e.idempresa 
             where 
             year(ncd.fecha)='$ano' and month(ncd.fecha)='$mes'  and p.tipo_persona='CLIENTE' and ncd.estado in('5','3','0','1','4') 
             ) 
            as tabla order by fecha, tipodocu, documento asc";
	$query=mysqli_query($con, $sql);
	$cel=10;//Numero de fila donde empezara a crear  el reporte

	$sumaSubtotal=0;
	$sumaIgv=0;
	$sumaExone=0;
	$sumaTotal=0;


	 while ($row=mysqli_fetch_array($query)){
	 	$dia=$row['fecha'];
	 	$tipodoc=$row['tipodocu'];
	 	$numero=$row['documento'];
	 	$ruc=$row['numero_documento'];
	 	$cliente=$row['razon_social'];
	 	$base=$row['subtotal'];
	 	$igv=$row['igv'];
	 	$total=$row['total'];

     $estado=$row['estado'];

	 switch ($estado) {
  case '3':
        $ruc="DE BAJA";
        $cliente="DE BAJA";
        $base = '0';
    $igv = '0';
    $total='0';
    break;
        
  case '0':
        $ruc="CON NOTA";
        break;
  default:
    # code...
    break;

    
}


		//$mes=$row['mes'];
		//$ano=$row['ano'];

		
            // ->setCellValue('D2', $mes)
              //->setCellValue('F2', $ano);
		
	 		$a="A".$cel;
			$b="B".$cel;
			$c="C".$cel;
			$d="D".$cel;
			$e="E".$cel;
			$f="F".$cel;
			$g="G".$cel;
			$h="H".$cel;
	// 		// Agregar datos
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($a, $dia)
            ->setCellValue($b, $tipodoc)
            ->setCellValue($c, $numero)
            ->setCellValue($d, $ruc)
			->setCellValue($e, $cliente)
			->setCellValue($f, $base)
			->setCellValue($g, $igv)
			->setCellValue($h, $total);
			

			
	 $cel+=1;
	 $sumaSubtotal+=$base;
	 $sumaIgv+=$igv;
	// $sumaExone+=$exo;
	 $sumaTotal+=$total;
	 }

	 $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('E'.$cel, "TOTALES")
                ->setCellValue('F'.$cel, number_format($sumaSubtotal,2))
                ->setCellValue('G'.$cel, number_format($sumaIgv,2))
                ->setCellValue('H'.$cel, number_format($sumaTotal,2));

                $boldArray = 
                array('font' => 
                	array('bold' => true, 'size'=> 14, 'color'=>
                		array('argb' => 'blue')),'alignment' => 
                			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER), 'borders'=>
                				array('allborders'=>array('style'=> PHPExcel_Style_Border::BORDER_THIN,'color'=>array('argb' => 'FFF'))));
	$objPHPExcel->getActiveSheet()->getStyle('E'.$cel.':H'.$cel)->applyFromArray($boldArray);		


/*Fin extracion de datos MYSQL*/
$rango="A9:H9";
$styleArray = array('font' => array( 'name' => 'courier new','size' => 10),
'borders'=>array('allborders'=>array('style'=> PHPExcel_Style_Border::BORDER_THIN,'color'=>array('argb' => 'FFF')))
);
$objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($styleArray);
// Cambiar el nombre de hoja de cálculo
$objPHPExcel->getActiveSheet()->setTitle('Reporte del mes');


//Establecer índice de hoja activa a la primera hoja , por lo que Excel abre esto como la primera hoja
$objPHPExcel->setActiveSheetIndex(0);


//Redirigir la salida al navegador web de un cliente ( Excel5 )
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporte.xls"');
header('Cache-Control: max-age=0');
// Si usted está sirviendo a IE 9 , a continuación, puede ser necesaria la siguiente
header('Cache-Control: max-age=1');

// Si usted está sirviendo a IE a través de SSL , a continuación, puede ser necesaria la siguiente
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

