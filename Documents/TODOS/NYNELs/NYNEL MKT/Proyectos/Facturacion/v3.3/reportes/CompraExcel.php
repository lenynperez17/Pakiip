<?php
	require "../config/Conexion.php";

		
		$idCompra=$_GET["idcompra"];
		

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp();
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
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:F1');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:B2');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C2:E2');

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'COMPRA NR')

            // ->setCellValue('A2', 'EMPRESA')
            // ->setCellValue('B4', 'AÑO')
            // ->setCellValue('B5', 'MES')
            // ->setCellValue('E2', '01:FACTURA/03:BOLETA/07:NOTA DE CRÉDITO/08:NOTA DE DÉBITO')
            // ->setCellValue('G2', 'RUC')

            // ->setCellValue('E4', 'FACTURA')
            // ->setCellValue('E5', 'BOLETA')
            // ->setCellValue('E6', 'NOTA CREDITO')
            // ->setCellValue('E7', 'NOTA DEBITO')

            // ->setCellValue('F4', '01')
            // ->setCellValue('F5', '03')
            // ->setCellValue('F6', '07')
            // ->setCellValue('F7', '08')

            ->setCellValue('C2', $empresa)
            ->setCellValue('H2', $ruc)

           // ->setCellValue('C4', $ano)
           // ->setCellValue('C5', $mes)

            ->setCellValue('A9', 'CODIGO')
            ->setCellValue('B9', 'ARTICULO')
            ->setCellValue('C9', 'NOMBRE')
            ->setCellValue('D9', 'VALOR')
            ->setCellValue('E9', 'CANTIDAD')
            ->setCellValue('F9', 'SUBTOTAL');
            
            
			
// Fuente de la primera fila en negrita

$boldArrayTitulo = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 14),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($boldArrayTitulo);      


$boldArray = 
array('font' => array('bold' => true, 'name' => 'courier new','size' => 12),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

	$objPHPExcel->getActiveSheet()->getStyle('A2:F9')->applyFromArray($boldArray);		

		
//Ancho de las columnas
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);	
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);	
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);	



     $con = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $con, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

	$sql="select 
concat(c.serie,'-' ,
c.numero) as numero, 
date_format(c.fecha, '%d-%m-%Y') as fecha, 
p.razon_social as proveedor, 
u.nombre as usuario, 
ct1.descripcion as tdocumento, 
a.codigo, 
a.nombre, 
dtc.valor_unitario as vunitario, 
dtc.cantidad, 
dtc.subtotal as stotal, 
year(c.fecha) as año,
c.estado,
dtc.descripcion as articulo
from 
compra c inner join detalle_compra_producto dtc on c.idcompra=dtc.idcompra inner join articulo a on dtc.idarticulo=a.idarticulo inner join persona p on c.idproveedor=p.idpersona inner join usuario u on c.idusuario=u.idusuario inner join catalogo1 ct1 on c.tipo_documento=ct1.codigo where c.idcompra='$idCompra'";
	$query=mysqli_query($con, $sql);
	$cel=10;//Numero de fila donde empezara a crear  el reporte

	
	$sumacantidad=0;
	$sumaTotal=0;


	 while ($row=mysqli_fetch_array($query)){
	 	$codigoprove=$row['codigo'];
	 	$descripcion=$row['nombre'];
	 	$vunitario=$row['vunitario'];
	 	$cantidad=$row['cantidad'];
	 	$stotal=$row['stotal'];
	 	$articulo=$row['articulo'];

		
	 		$a="A".$cel;
			$b="B".$cel;
			$c="C".$cel;
			$d="D".$cel;
			$e="E".$cel;
			$f="F".$cel;
		
	// 		// Agregar datos
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($a, $codigoprove)
            ->setCellValue($b, $articulo)
            ->setCellValue($c, $descripcion)
            ->setCellValue($d, $vunitario)
			->setCellValue($e, $cantidad)
			->setCellValue($f, $stotal);
			
	 $cel+=1;
	 $sumacantidad+=$cantidad;
	 $sumaTotal+=$stotal;
	 }

	 $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('D'.$cel, "TOTALES")
                ->setCellValue('E'.$cel, number_format($sumacantidad,2))
                ->setCellValue('F'.$cel, number_format($sumaTotal,2));
                

                $boldArray = 
                array('font' => 
                	array('bold' => true, 'size'=> 14, 'color'=>
                		array('argb' => 'blue')),'alignment' => 
                			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER), 'borders'=>
                				array('allborders'=>array('style'=> PHPExcel_Style_Border::BORDER_THIN,'color'=>array('argb' => 'FFF'))));
	$objPHPExcel->getActiveSheet()->getStyle('D'.$cel.':F'.$cel)->applyFromArray($boldArray);		


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

