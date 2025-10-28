<?php
	require "../config/Conexion.php";

		
		$ano=$_POST['ano'];
		
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
            ->setCellValue('A1', 'REPORTE DE VENTAS ANUAL')

            ->setCellValue('A2', 'EMPRESA')
            ->setCellValue('A3', 'RUC')
            ->setCellValue('A4', 'AÑO')
            ->setCellValue('A5', 'MONEDA')
            

             ->setCellValue('B2', $empresa)
             ->setCellValue('B3', $ruc)
	        ->setCellValue('B4', $ano)
	        ->setCellValue('B5', $tmon)
            

            ->setCellValue('A6', 'MES')
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

	$sql="select tmeses.mes, t1.total_base, t1.total_igv, t1.total FROM

 (SELECT 1 as idmes , 'Enero'     as mes UNION
 SELECT 2 as idmes , 'Febrero'    as mes UNION
 SELECT 3 as idmes , 'Marzo'      as mes UNION
 SELECT 4 as idmes , 'Abril'      as mes UNION
 SELECT 5 as idmes , 'Mayo'       as mes UNION
 SELECT 6 as idmes , 'Junio'      as mes UNION
 SELECT 7 as idmes , 'Julio'      as mes UNION
 SELECT 8 as idmes , 'Agosto'     as mes UNION
 SELECT 9 as idmes , 'Septiembre' as mes UNION
 SELECT 10 as idmes, 'Octubre'    as mes UNION
 SELECT 11 as idmes, 'Noviembre'  as mes UNION
 SELECT 12 as idmes, 'Diciembre'  as mes) tmeses

LEFT JOIN

    (SELECT 
    MONTH(fecha_emision_01) mes, 
    SUM(total_operaciones_gravadas_monto_18_2) total_base,
    SUM(sumatoria_igv_22_1) total_igv,
    SUM(importe_total_venta_27) total
    FROM factura 
    WHERE YEAR(fecha_emision_01)='$ano' and tipo_moneda_28='$tmon' 
     group by mes
union all
     SELECT 
     MONTH(fecha_emision_01) mes, 
     SUM(monto_15_2) total_base,
     SUM(sumatoria_igv_18_1) total_igv, 
     SUM(importe_total_23) total  
     FROM boleta 
     WHERE YEAR(fecha_emision_01)='$ano' and tipo_moneda_24='$tmon'
     group by mes
     )t1  ON t1.mes = tmeses.idmes   group by mes order by idmes";

	$query=mysqli_query($con, $sql);
	$cel=7;//Numero de fila donde empezara a crear  el reporte

	$sumaSubtotal=0.00;
	$sumaIgv=0.00;
	$sumaTotal=0.00;

	


	 while ($row=mysqli_fetch_array($query)){
	 	$mes=$row['mes'];
	 	 $base=$row['total_base'];
	 	 $igv=$row['total_igv'];
	 	$total=$row['total'];

	 		$a="A".$cel;
			$b="B".$cel;
			$c="C".$cel;
			$d="D".$cel;
			
	// 		// Agregar datos
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($a, $mes)
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

