<?php
	require "../config/Conexion.php";

		
		$ano=$_POST['ano'];
		$mes=$_POST['mes'];
		$tmon=$_POST['tmonedaa'];

		$mesL="";
switch ($mes) {
	case '01':
		$mesL="ENERO";
		break;
	case '02':
		$mesL="FEBRERO";
		break;
	case '03':
		$mesL="MARZO";
		break;
		case '04':
		$mesL="ABRIL";
		break;
		case '05':
		$mesL="MAYO";
		break;
		case '06':
		$mesL="JUNIO";
		break;
		case '07':
		$mesL="JULIO";
		break;
		case '08':
		$mesL="AGOSTO";
		break;
		case '09':
		$mesL="SEPTIEMBRE";
		break;
		case '10':
		$mesL="OCTUBRE";
		break;
		case '11':
		$mesL="NOVIEMBRE";
		break;
		case '12':
		$mesL="DICIEMBRE";
		break;

	default:
		# code...
		break;
}
		

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
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:D2');

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'REPORTE DE VENTAS POR DÍA')

            ->setCellValue('A2', 'EMPRESA')
            ->setCellValue('A3', 'RUC')
            ->setCellValue('A4', 'AÑO')
            ->setCellValue('A5', 'MES')

             ->setCellValue('B2', $empresa)
             ->setCellValue('B3', $ruc)
	        ->setCellValue('B4', $ano)
            ->setCellValue('B5', $mesL)

            ->setCellValue('A6', 'DÍA')
            ->setCellValue('B6', 'VALOR AFECTO')
            ->setCellValue('C6', 'IGV')
            ->setCellValue('D6', 'TOTAL');
			
// Fuente de la primera fila en negrita

$boldArrayTitulo = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 14),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($boldArrayTitulo);      


$boldArray = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 12),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

	$objPHPExcel->getActiveSheet()->getStyle('A2:D6')->applyFromArray($boldArray);		

	
			
//Ancho de las columnas
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);	
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);	
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);	
// $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);	
// $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);	
// $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);		
// $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);		


     $con = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $con, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

	$sql="select date_format(fecha_emision_01, '%d/%m/%y') as fecha, 
        sum(subtotal) as subtotal, 
        sum(igv) as igv, 
        sum(total) as total
            from 
            (select f.fecha_emision_01, f.total_operaciones_gravadas_monto_18_2 as subtotal, f.numeracion_08, f.sumatoria_igv_22_1 as igv, f.importe_total_venta_27 as total, f.estado from factura f inner join empresa e on f.idempresa=e.idempresa where year(f.fecha_emision_01)='$ano'  and month(f.fecha_emision_01)='$mes'  and f.estado in ('5','6','1','4')  and f.tipo_moneda_28='$tmon'
            union all 
           
            select b.fecha_emision_01, b.monto_15_2 as subtotal, b.numeracion_07, b.sumatoria_igv_18_1 as igv, b.importe_total_23 as total, b.estado from boleta b inner join empresa e on b.idempresa=e.idempresa where year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' and b.estado in ('5','6','1','4')  and b.tipo_moneda_24='$tmon'
            union all
           
            select np.fecha_emision_01, np.monto_15_2 as subtotal, np.numeracion_07, np.sumatoria_igv_18_1 as igv, np.importe_total_23 as total, np.estado from notapedido np inner join empresa e on np.idempresa=e.idempresa where year(np.fecha_emision_01)='$ano' and month(np.fecha_emision_01)='$mes' and np.estado in ('1', '5') and np.tipo_moneda_24='$tmon'
            union all 
             select ncd.fecha, if(ncd.codigo_nota='07', ncd.total_val_venta_og *-1, ncd.total_val_venta_og) as subtotal, ncd.numeroserienota, if (ncd.codigo_nota='07', ncd.sum_igv * -1, ncd.sum_igv) as igv, if(ncd.codigo_nota='07',ncd.importe_total * -1, ncd.importe_total) as total, ncd.estado from notacd ncd inner join empresa e on ncd.idempresa=e.idempresa where year(ncd.fecha)='$ano' and month(ncd.fecha)='$mes' and ncd.estado in('5','6') and ncd.tipo_moneda='$tmon' 
             ) 

            as tabla  group by fecha order by fecha";
	$query=mysqli_query($con, $sql);
	$cel=7;//Numero de fila donde empezara a crear  el reporte

	$sumaSubtotal=0;
	$sumaIgv=0;
	$sumaTotal=0;

	


	 while ($row=mysqli_fetch_array($query)){
	 	$fecha=$row['fecha'];
	 	$base=$row['subtotal'];
	 	$igv=$row['igv'];
	 	$total=$row['total'];

	 		$a="A".$cel;
			$b="B".$cel;
			$c="C".$cel;
			$d="D".$cel;
			
	// 		// Agregar datos
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($a, $fecha)
            ->setCellValue($b, number_format($base,2,'.',''))
            ->setCellValue($c, number_format($igv,2,'.',''))
            ->setCellValue($d, number_format($total,2,'.',''));
			

			
	 $cel+=1;
	 $sumaSubtotal+=$base;
	 $sumaIgv+=$igv;
	 $sumaTotal+=$total;
	 }

	 $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$cel, "TOTALES")
                ->setCellValue('B'.$cel, number_format($sumaSubtotal,2,'.',''))
                ->setCellValue('C'.$cel, number_format($sumaIgv,2,'.',''))
                ->setCellValue('D'.$cel, number_format($sumaTotal,2,'.',''));

                $boldArray = 
                array('font' => 
                	array('bold' => true, 'size'=> 14, 'color'=>
                		array('argb' => 'blue')),'alignment' => 
                			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER), 'borders'=>
                				array('allborders'=>array('style'=> PHPExcel_Style_Border::BORDER_THIN,'color'=>array('argb' => 'FFF'))));
	$objPHPExcel->getActiveSheet()->getStyle('A'.$cel.':D'.$cel)->applyFromArray($boldArray);		


/*Fin extracion de datos MYSQL*/
$rango="A6:D6";
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
header('Content-Disposition: attachment;filename="ReportexDia.xls"');
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
$objWriter->save('desktop');
exit;

