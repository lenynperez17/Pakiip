<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Cotizacion
{

    //Implementamos nuestro constructor
    public function __construct()
    {
 

    }


public function insertarTc($fechatc, $compra, $venta)
	{
		global $conexion;

		$sql = "INSERT INTO tcambio (fecha, compra, venta) VALUES (?, ?, ?)";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando INSERT en tcambio: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sdd", $fechatc, $compra, $venta);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para editar registros
	public function editarTc($id, $fechatc, $compra, $venta)
	{
		global $conexion;

		$sql = "UPDATE tcambio SET fecha = ?, compra = ?, venta = ? WHERE idtipocambio = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE en tcambio: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("sddi", $fechatc, $compra, $venta, $id);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

 
 
	//Implementamos un método para insertar registros para cotización
	public function insertar($idempresa, $idusuario, $idcliente, $serienota,
		$moneda, $fechaemision, $hora, $tipocotizacion, $subtotal, $impuesto,
		$total, $observacion, $vendedor, $idarticulo, $codigo, $cantidad,
		$precio_unitario, $numero_cotizacion, $idserie, $descdet, $norden,
		$fechavalidez, $tcambio, $valorventa, $valorunitario, $igvitem, $igventa, $nrofactura)
	{
		global $conexion;
		$sw = true;

		// Iniciar transacción
		mysqli_begin_transaction($conexion);

		try {
			// 1. INSERT principal en cotizacion
			$sql = "INSERT INTO cotizacion (
					idempresa, idusuario, idcliente, serienota, moneda, fechaemision,
					tipocotizacion, subtotal, impuesto, total, observacion, vendedor,
					tipocambio, fechavalidez, nrofactura
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $conexion->prepare($sql);
			if (!$stmt) {
				error_log("Error preparando INSERT en cotizacion: " . $conexion->error);
				throw new Exception("Error al preparar inserción de cotización");
			}

			$fechaemision_completa = $fechaemision . ' ' . $hora;
			$stmt->bind_param("iiissssssssssss",
				$idempresa, $idusuario, $idcliente, $serienota, $moneda,
				$fechaemision_completa, $tipocotizacion, $subtotal, $impuesto,
				$total, $observacion, $vendedor, $tcambio, $fechavalidez, $nrofactura
			);

			if (!$stmt->execute()) {
				error_log("Error ejecutando INSERT en cotizacion: " . $stmt->error);
				throw new Exception("Error al insertar cotización");
			}

			$idcotizacionnew = $stmt->insert_id;
			$stmt->close();

			// 2. INSERT detalles en loop
			$sql_detalle = "INSERT INTO detalle_articulo_cotizacion (
					idcotizacion, iditem, codigo, cantidad, precio, descdet, norden,
					valorventa, valorunitario, igvvalorventa, igvitem
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt_detalle = $conexion->prepare($sql_detalle);
			if (!$stmt_detalle) {
				error_log("Error preparando INSERT en detalle_articulo_cotizacion: " . $conexion->error);
				throw new Exception("Error al preparar inserción de detalles");
			}

			$num_elementos = 0;
			while ($num_elementos < count($idarticulo)) {
				$stmt_detalle->bind_param("iisssssssss",
					$idcotizacionnew,
					$idarticulo[$num_elementos],
					$codigo[$num_elementos],
					$cantidad[$num_elementos],
					$precio_unitario[$num_elementos],
					$descdet[$num_elementos],
					$norden[$num_elementos],
					$valorventa[$num_elementos],
					$valorunitario[$num_elementos],
					$igventa[$num_elementos],
					$igvitem[$num_elementos]
				);

				if (!$stmt_detalle->execute()) {
					error_log("Error ejecutando INSERT detalle cotización: " . $stmt_detalle->error);
					throw new Exception("Error al insertar detalle de cotización");
				}

				$num_elementos++;
			}

			$stmt_detalle->close();

			// 3. UPDATE numeración (fuera del loop)
			$sql_numeracion = "UPDATE numeracion SET numero = ? WHERE idnumeracion = ?";

			$stmt_num = $conexion->prepare($sql_numeracion);
			if (!$stmt_num) {
				error_log("Error preparando UPDATE en numeracion: " . $conexion->error);
				throw new Exception("Error al preparar actualización de numeración");
			}

			$stmt_num->bind_param("si", $numero_cotizacion, $idserie);

			if (!$stmt_num->execute()) {
				error_log("Error ejecutando UPDATE numeracion: " . $stmt_num->error);
				throw new Exception("Error al actualizar numeración");
			}

			$stmt_num->close();

			// 4. INSERT en detalle_usuario_sesion
			$sql_sesion = "INSERT INTO detalle_usuario_sesion
				(idusuario, tcomprobante, idcomprobante, fechahora)
				VALUES (?, 'COTI', ?, NOW())";

			$stmt_sesion = $conexion->prepare($sql_sesion);
			if (!$stmt_sesion) {
				error_log("Error preparando INSERT en detalle_usuario_sesion: " . $conexion->error);
				throw new Exception("Error al preparar inserción de sesión");
			}

			$stmt_sesion->bind_param("ii", $idusuario, $idcotizacionnew);

			if (!$stmt_sesion->execute()) {
				error_log("Error ejecutando INSERT sesión: " . $stmt_sesion->error);
				throw new Exception("Error al insertar sesión de usuario");
			}

			$stmt_sesion->close();

			// Confirmar transacción
			mysqli_commit($conexion);

			return $idcotizacionnew;

		} catch (Exception $e) {
			// Revertir transacción en caso de error
			mysqli_rollback($conexion);
			error_log("Error en transacción de insertar cotización: " . $e->getMessage());
			return false;
		}
	}








	public function mostrarultimocomprobante($idempresa)
	{
		global $conexion;

		$sql = "SELECT numeracion_08
				FROM factura f
				INNER JOIN empresa e ON f.idempresa=e.idempresa
				WHERE e.idempresa = ?
				ORDER BY idfactura DESC
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en mostrarultimocomprobante: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}


public function crearPDF($idfactura, $idempresa)
{
require('Factura.php');
//Obtenemos los datos de la cabecera de la venta actuall
require_once "../modelos/Factura.php";
$factura = new Factura();
$rsptav = $factura->ventacabecera($idfactura, $idempresa);
$datos = $factura->datosemp($idempresa);
//Recorremos todos los valores obtenidos
$regv = $rsptav->fetch_object();
$datose = $datos->fetch_object();
$logo = "../files/logo/".$datose->logo;
$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);
//Establecemos la configuración de la factura
$pdf = new PDF_Invoice( 'P', 'mm',  'A4' );
$pdf->AddPage();
#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(10, 10 , 10); 
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10); 
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección    : ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono     : ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email          : ".$datose->correo, $logo, $ext_logo);
$pdf->numFactura("$regv->numeracion_08" , "$datose->numero_ruc");
$pdf->RotatedText($regv->estado, 35,190,'ANULADO - DADO DE BAJA',45);
$pdf->temporaire( "" );
//Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse( $regv->fecha."   /  Hora: ".$regv->hora,    utf8_decode(htmlspecialchars_decode($regv->cliente)), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia) );
if ($regv->nombretrib=="IGV") {
        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }
//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols); 
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y= 62;
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
 
while ($regd = $rsptad->fetch_object()) {
    if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=> utf8_decode("$regd->articulo"),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size = $pdf->addLine( $y, $line );
            $y   += $size + 2;
}
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $data[0] = "";

//===========PARA EXTRAER EL CODIGO HASH =============================
if ($regv->estado=='5') {
$facturaFirm=$regv->numero_ruc."-".$regv->tipo_documento_07."-".$regv->numeracion_08;
$sxe = new SimpleXMLElement($rutafirma.$facturaFirm.'.xml', null, true);
$urn = $sxe->getNamespaces(true);
$sxe->registerXPathNamespace('ds', $urn['ds']);
$data = $sxe->xpath('//ds:DigestValue');
}
else
{
     $data[0] = "";
}
//==================== PARA IMAGEN DEL CODIGO HASH ================================================
//set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'/generador-qr/temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    include 'generador-qr/phpqrcode.php';    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
$dataTxt=$regv->numero_ruc."|".$regv->tipo_documento_07."|".$regv->serie."|".$regv->numerofac."|".$regv->sumatoria_igv_22_1."|".$regv->importe_total_venta_27."|".$regv->fecha2."|".$regv->tipo_documento."|".$regv->numero_documento."|";
$errorCorrectionLevel = 'H';    
$matrixPointSize = '2';
    // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($dataTxt.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        //default data
        //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
       //display generated file
        $PNG_WEB_DIR.basename($filename);
// //==================== PARA IMAGEN  ================================================
$logoQr = $filename;
//$logoQr = "../files/logo/".$datose->logo;
$ext_logoQr = substr($filename, strpos($filename,'.'),-4);
$pdf->ImgQr($logoQr, $ext_logoQr);
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
//Convertimos el total en letras
require_once "Letras.php";
$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs("".$con_letra);
$pdf->observSunat($regv->numeracion_08,$regv->estado, $data[0], $datose->webconsul,  $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs( $regv->sumatoria_igv_22_1 , $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs($regv->sumatoria_igv_22_1, $regv->nombretrib);
//===============SEGUNDA COPIA DE FACTURA=========================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete2(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección: ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono: ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email : ".$datose->correo, $logo, $ext_logo);
//Datos de la empresa
$pdf->numFactura2("$regv->numeracion_08" , "$datose->numero_ruc" );
$pdf->temporaire( "" );
////Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse2( $regv->fecha."   /  Hora: ".$regv->hora, utf8_decode($regv->cliente), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));
if ($regv->nombretrib=="IGV") {

        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }

//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols2( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat2( $cols);
$pdf->addLineFormat2($cols);
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y2= 208; // para el tamaño del cuadro del segundo detalle
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
while ($regd = $rsptad->fetch_object()) {
  if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=> utf8_decode("$regd->articulo"." - "."$regd->descdet"),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size2 = $pdf->addLine2( $y2, $line );
            $y2   += $size2 + 2;
}

$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs2("".$con_letra);
$pdf->observSunat2($regv->numeracion_08,$regv->estado,$data[0], $datose->webconsul, $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs2( $regv->sumatoria_igv_22_1, $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs2($regv->sumatoria_igv_22_1, $regv->nombretrib);
//Linea para guardar la factura en la carpeta facturas PDF
//$Factura=$pdf->Output($regv->numeracion_08.'.pdf','I');
$Factura=$pdf->Output('../facturasPDF/'.$regv->numeracion_08.'.pdf','F');
}













	//Implementamos un método para dar de baja a cotización
	public function baja($idcotizacion)
	{
		global $conexion;

		$sql = "UPDATE cotizacion SET estado = '3' WHERE idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE estado en cotizacion (baja): " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}

	//Implementamos un método para actualizar estado de factura
	public function ActualizarEstado($idfactura, $st)
	{
		global $conexion;

		$sql = "UPDATE factura SET estado = ? WHERE idfactura = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando UPDATE estado en factura: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("si", $st, $idfactura);

		$resultado = $stmt->execute();
		$stmt->close();

		return $resultado;
	}



	//Implementamos un método para anular la factura
	public function anular($idfactura)
	{
		global $conexion;
		$sw = true;

		// Iniciar transacción
		mysqli_begin_transaction($conexion);

		try {
			// 1. Obtener detalles de la factura a anular
			$sql_detalles = "SELECT idfactura, idarticulo FROM detalle_fac_art WHERE idfactura = ?";

			$stmt_detalles = $conexion->prepare($sql_detalles);
			if (!$stmt_detalles) {
				error_log("Error preparando SELECT detalles factura (anular): " . $conexion->error);
				throw new Exception("Error al obtener detalles de factura");
			}

			$stmt_detalles->bind_param("i", $idfactura);
			$stmt_detalles->execute();

			$resultado = $stmt_detalles->get_result();
			$detalles = $resultado->fetch_all(MYSQLI_ASSOC);  // FIX: fetch_all en lugar de while+for
			$stmt_detalles->close();

			// 2. Preparar statements para UPDATE y INSERT
			$sql_update_articulo = "UPDATE detalle_fac_art de
					INNER JOIN articulo a ON de.idarticulo=a.idarticulo
					SET
						a.saldo_finu = a.saldo_finu + de.cantidad_item_12,
						a.stock = a.stock + de.cantidad_item_12,
						a.ventast = a.ventast - de.cantidad_item_12,
						a.valor_finu = (a.saldo_finu + a.comprast - a.ventast) * a.costo_compra
					WHERE de.idfactura = ? AND de.idarticulo = ?";

			$stmt_update = $conexion->prepare($sql_update_articulo);
			if (!$stmt_update) {
				error_log("Error preparando UPDATE articulo (anular): " . $conexion->error);
				throw new Exception("Error al preparar actualización de artículos");
			}

			$sql_kardex = "INSERT INTO kardex (
					idcomprobante, idarticulo, transaccion, codigo, fecha,
					tipo_documento, numero_doc, cantidad, costo_1, unidad_medida,
					saldo_final, costo_2, valor_final
				) SELECT
					?, a.idarticulo, 'ANULADO', a.codigo, f.fecha_emision_01,
					'01', f.numeracion_08, dtf.cantidad_item_12, dtf.valor_uni_item_14,
					a.unidad_medida, 0, 0, 0
				FROM articulo a
				INNER JOIN detalle_fac_art dtf ON a.idarticulo = dtf.idarticulo
				INNER JOIN factura f ON dtf.idfactura = f.idfactura
				WHERE a.idarticulo = ? AND dtf.idfactura = ?";

			$stmt_kardex = $conexion->prepare($sql_kardex);
			if (!$stmt_kardex) {
				error_log("Error preparando INSERT kardex (anular): " . $conexion->error);
				throw new Exception("Error al preparar inserción en kardex");
			}

			// 3. Procesar cada detalle (FIX: loop correcto sobre array)
			foreach ($detalles as $detalle) {
				$idf = $detalle['idfactura'];
				$ida = $detalle['idarticulo'];

				// UPDATE artículo
				$stmt_update->bind_param("ii", $idf, $ida);
				if (!$stmt_update->execute()) {
					error_log("Error ejecutando UPDATE articulo (anular): " . $stmt_update->error);
					throw new Exception("Error al actualizar stock de artículo");
				}

				// INSERT kardex
				$stmt_kardex->bind_param("iii", $idfactura, $ida, $idf);
				if (!$stmt_kardex->execute()) {
					error_log("Error ejecutando INSERT kardex (anular): " . $stmt_kardex->error);
					throw new Exception("Error al registrar en kardex");
				}
			}

			$stmt_update->close();
			$stmt_kardex->close();

			// 4. UPDATE estado de factura
			$sql_estado = "UPDATE factura SET estado = '0' WHERE idfactura = ?";

			$stmt_estado = $conexion->prepare($sql_estado);
			if (!$stmt_estado) {
				error_log("Error preparando UPDATE estado factura (anular): " . $conexion->error);
				throw new Exception("Error al preparar anulación de factura");
			}

			$stmt_estado->bind_param("i", $idfactura);

			if (!$stmt_estado->execute()) {
				error_log("Error ejecutando UPDATE estado (anular): " . $stmt_estado->error);
				throw new Exception("Error al anular factura");
			}

			$stmt_estado->close();

			// Confirmar transacción
			mysqli_commit($conexion);

			return $sw;

		} catch (Exception $e) {
			// Revertir transacción en caso de error
			mysqli_rollback($conexion);
			error_log("Error en transacción de anular factura: " . $e->getMessage());
			return false;
		}
	}

 
	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idfactura)
	{
		global $conexion;

		$sql = "SELECT
			f.idfactura,
			date(f.fecha_emision_01) as fecha,
			f.idcliente,
			p.razon_social as cliente,
			p.numero_documento,
			p.domicilio_fiscal,
			u.idusuario,
			u.nombre as usuario,
			f.tipo_documento_07,
			f.numeracion_08,
			f.total_operaciones_gravadas_monto_18_2,
			f.sumatoria_igv_22_1,
			f.importe_total_venta_27,
			f.estado
			FROM
			factura f
			INNER JOIN persona p ON f.idcliente=p.idpersona
			INNER JOIN usuario u ON f.idusuario=u.idusuario
			WHERE f.idfactura = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en mostrar (factura): " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idfactura);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}

    
	// Implementar método para enviar correo con factura adjunta
	public function enviarcorreo($idfactura, $idempresa)
	{
		global $conexion;

		require_once "../modelos/Factura.php";
		$factura = new Factura();
		$datos = $factura->correo();
		$correo = $datos->fetch_object();

		// Inclusion de la tabla RUTAS
		require_once "../modelos/Rutas.php";
		$rutas = new Rutas();
		$Rrutas = $rutas->mostrar2($idempresa);
		$Prutas = $Rrutas->fetch_object();
		$rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

		// FIX: Prepared statement para obtener datos de factura
		$sql_sendmail = "SELECT
				f.idfactura,
				p.email,
				p.nombres,
				p.apellidos,
				p.nombre_comercial,
				e.numero_ruc,
				f.tipo_documento_07,
				f.numeracion_08
				FROM
				factura f
				INNER JOIN persona p ON f.idcliente=p.idpersona
				INNER JOIN empresa e ON f.idempresa=e.idempresa
				WHERE f.idfactura = ? AND e.idempresa = ?";

		$stmt = $conexion->prepare($sql_sendmail);
		if (!$stmt) {
			error_log("Error preparando SELECT en enviarcorreo: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ii", $idfactura, $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$datos_envio = $resultado->fetch_all(MYSQLI_ASSOC);  // FIX: Obtener TODOS los registros
		$stmt->close();

		$con = 0;

		// FIX: foreach en lugar de while+for para procesar TODOS los destinatarios
		foreach ($datos_envio as $row) {
			$correocliente = $row["email"];

			// Ruta del directorio donde están los archivos
			$path = $rutafirma;
			$pathFactura = '../facturasPDF/';
			// Arreglo con todos los nombres de los archivos
			$files = array_diff(scandir($path), array('.', '..'));
			$filesFactura = array_diff(scandir($pathFactura), array('.', '..'));

			$factura_nombre = $row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];

			// Validar si existe el archivo XML firmado
			foreach($files as $file){
				$dataSt = explode(".", $file);
				$fileName = $dataSt[0];
				$st = "1";
				$fileExtension = $dataSt[1];

				if($factura_nombre == $fileName){
					$archivoFactura = $fileName;
					break;
				}
			}

			// Validar si existe el archivo PDF
			foreach($filesFactura as $fileFactura){
				$dataStF = explode(".", $fileFactura);
				$fileNameF = $dataStF[0];
				$fileExtensionF = $dataStF[1];

				if($row['numeracion_08'] == $fileNameF){
					$archivoFacturaPDF = $fileNameF;
					break;
				}
			}

			$url = $rutafirma.$archivoFactura.'.xml';
			$fichero = file_get_contents($url);

			$urlFac = '../facturasPDF/'.$archivoFacturaPDF.'.pdf';
			$ficheroFact = file_get_contents($urlFac);

			// FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE
			require '../correo/PHPMailer/class.phpmailer.php';
			require '../correo/PHPMailer/class.smtp.php';
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = $correo->host;
			$mail->SMTPAuth = true;
			$mail->Username = $correo->username;
			$mail->Password = $correo->password;
			$mail->SMTPSecure = $correo->smtpsecure;
			$mail->Port = $correo->port;
			$mail->setFrom($correo->username, utf8_decode($correo->nombre));
			$mail->addReplyTo($correo->username, utf8_decode($correo->nombre));
			$mail->addStringAttachment($fichero, $archivoFactura.'.xml');
			$mail->addStringAttachment($ficheroFact, $archivoFacturaPDF.'.pdf');
			$mail->addAddress($correocliente);

			$message = file_get_contents('../correo/email_template.html');
			$message = str_replace('{{first_name}}', utf8_decode($correo->nombre), utf8_decode($correo->mensaje));
			$message = str_replace('{{message}}', utf8_decode($correo->mensaje), utf8_decode($correo->mensaje));
			$message = str_replace('{{customer_email}}', $correo->username, utf8_decode($correo->mensaje));
			$mail->isHTML(true);

			$mail->Subject = $correo->username;
			$mail->msgHTML($message);

			if(!$mail->send()) {
				echo $mail->ErrorInfo;
			} else {
				echo 'Tu mensaje ha sido enviado';
			}

			$con++;
		}

		// FIX: Prepared statement para registrar log de envío
		$sql_log = "INSERT INTO enviocorreo
				(numero_documento, cliente, correo, comprobante, fecha_envio)
				SELECT
					p.numero_documento,
					p.razon_social,
					p.email,
					f.numeracion_08,
					NOW()
				FROM factura f
				INNER JOIN persona p ON f.idcliente=p.idpersona
				WHERE f.idfactura = ?";

		$stmt_log = $conexion->prepare($sql_log);
		if (!$stmt_log) {
			error_log("Error preparando INSERT log en enviarcorreo: " . $conexion->error);
			return false;
		}

		$stmt_log->bind_param("i", $idfactura);

		if (!$stmt_log->execute()) {
			error_log("Error ejecutando INSERT log enviarcorreo: " . $stmt_log->error);
		}

		$stmt_log->close();

		return true;
	}


    
 
	//Implementar un método para listar los registros
	public function listar($idempresa)
	{
		global $conexion;

		$sql = "SELECT
				c.idcotizacion,
				DATE_FORMAT(c.fechaemision,'%d/%m/%y') as fecha,
				c.idcliente,
				p.razon_social as cliente,
				c.vendedor,
				u.nombre as usuario,
				c.serienota,
				FORMAT(c.total,2) as total,
				c.impuesto,
				c.estado,
				e.numero_ruc,
				p.email,
				c.nrofactura,
				c.moneda
				FROM
				cotizacion c
				INNER JOIN persona p ON c.idcliente=p.idpersona
				INNER JOIN usuario u ON c.idusuario=u.idusuario
				INNER JOIN empresa e ON c.idempresa=e.idempresa
				WHERE e.idempresa = ?
				ORDER BY idcotizacion DESC";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listar: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

     

	public function listarDR($ano, $mes, $idempresa)
	{
		global $conexion;

		$sql = "SELECT
				f.idfactura,
				f.idcliente,
				numeracion_08 as numerofactura,
				DATE_FORMAT(f.fecha_emision_01,'%d/%m/%y') as fecha,
				DATE_FORMAT(f.fecha_baja,'%d/%m/%y') as fechabaja,
				LEFT(p.razon_social,20) as cliente,
				p.numero_documento as ruccliente,
				f.total_operaciones_gravadas_monto_18_2 as opgravada,
				f.sumatoria_igv_22_1 as igv,
				FORMAT(f.importe_total_venta_27,2) as total,
				f.vendedorsitio,
				f.estado
				FROM
				factura f
				INNER JOIN persona p ON f.idcliente=p.idpersona
				INNER JOIN usuario u ON f.idusuario=u.idusuario
				INNER JOIN empresa e ON f.idempresa=e.idempresa
				WHERE YEAR(f.fecha_emision_01) = ? AND MONTH(f.fecha_emision_01) = ?
					AND f.estado IN ('0','3') AND e.idempresa = ?
				ORDER BY idfactura DESC";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarDR: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("iii", $ano, $mes, $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function listarDRdetallado($idcomp, $idempresa)
	{
		global $conexion;

		$sql = "SELECT
				ncd.codigo_nota,
				ncd.numeroserienota as numero,
				f.numeracion_08,
				DATE_FORMAT(ncd.fecha,'%d/%m/%y') as fecha,
				ncd.desc_motivo as motivo,
				ncd.total_val_venta_og as subtotal,
				ncd.sum_igv as igv,
				ncd.importe_total as total
				FROM
				factura f
				INNER JOIN persona p ON f.idcliente=p.idpersona
				INNER JOIN usuario u ON f.idusuario=u.idusuario
				INNER JOIN empresa e ON f.idempresa=e.idempresa
				INNER JOIN notacd ncd ON f.idfactura=ncd.idcomprobante
				WHERE f.idfactura = ? AND e.idempresa = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarDRdetallado: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ii", $idcomp, $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}


	public function ventacabecera($idcotizacion, $idempresa)
	{
		global $conexion;

		$sql = "SELECT
				c.idcotizacion,
				c.idcliente,
				p.razon_social as cliente,
				p.domicilio_fiscal as direccion,
				p.tipo_documento,
				p.numero_documento,
				p.email,
				p.telefono1,
				p.nombre_comercial,
				c.idusuario,
				CONCAT(u.nombre,' ',u.apellidos) as usuario,
				c.serienota,
				DATE_FORMAT(c.fechaemision,'%d-%m-%Y') as fecha,
				DATE_FORMAT(c.fechaemision,'%Y-%m-%d') as fecha2,
				DATE_FORMAT(c.fechaemision, '%H:%i:%s') as hora,
				c.impuesto,
				c.total,
				c.estado,
				e.numero_ruc,
				c.subtotal,
				c.vendedor,
				c.tipocotizacion,
				c.observacion,
				c.moneda,
				c.tipocambio,
				IF(c.moneda='USD', c.tipocambio * c.total, c.total) as conversion,
				c.fechavalidez,
				c.nrofactura
				FROM
				cotizacion c
				INNER JOIN persona p ON c.idcliente=p.idpersona
				INNER JOIN empresa e ON e.idempresa=c.idempresa
				INNER JOIN usuario u ON c.idusuario=u.idusuario
				WHERE c.idcotizacion = ? AND e.idempresa = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en ventacabecera: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ii", $idcotizacion, $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

    

	public function ventadetalle($idcotizacion, $tipocotizacion)
	{
		global $conexion;

		if ($tipocotizacion == 'productos') {
			$sql = "SELECT
					a.nombre as articulo,
					a.codigo,
					FORMAT(dac.cantidad,2) as cantidad,
					dac.precio,
					FORMAT(dac.cantidad * dac.precio,2) as subtotal,
					a.unidad_medida,
					um.nombreum,
					dac.norden
					FROM
					detalle_articulo_cotizacion dac
					INNER JOIN articulo a ON dac.iditem=a.idarticulo
					INNER JOIN umedida um ON a.unidad_medida=um.idunidad
					WHERE dac.idcotizacion = ?";
		} else {
			$sql = "SELECT
					s.descripcion as articulo,
					s.codigo,
					FORMAT(dac.cantidad,2) as cantidad,
					dac.precio,
					FORMAT(dac.cantidad * dac.precio,2) as subtotal,
					dac.norden
					FROM
					detalle_articulo_cotizacion dac
					INNER JOIN servicios_inmuebles s ON dac.iditem=s.id
					WHERE dac.idcotizacion = ?";
		}

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en ventadetalle: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

        public function listarD()
    {
        $sql="select documento from correlativo where documento='factura' or documento='boleta' or documento='nota de credito'or documento='nota de debito' group by documento";
        return ejecutarConsulta($sql);      
    }


	public function listarS($serie)
	{
		global $conexion;

		$sql = "SELECT serie FROM correlativo WHERE documento = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarS: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("s", $serie);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function sumarC($tipo_comprobante, $serie_comprobante)
	{
		global $conexion;

		$sql = "SELECT (numero + 1) as addnumero
				FROM correlativo
				WHERE documento = ? AND serie = ?
				ORDER BY numero DESC
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en sumarC: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("ss", $tipo_comprobante, $serie_comprobante);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

    public function autogenerarN(){

    $sql="select (idfactura + 1) as Nnum from factura order by idfactura desc limit 1";
    return ejecutarConsulta($sql);      

    }

	public function datosemp($idempresa)
	{
		global $conexion;

		$sql = "SELECT * FROM empresa WHERE idempresa = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en datosemp: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

     public function tributo()
    {

    $sql="select * from catalogo5 where estado='1'";
    return ejecutarConsulta($sql);      
    }

    public function afectacionigv()
    {

    $sql="select * from catalogo7";
    return ejecutarConsulta($sql);      
    }

    public function correo()
    {

    $sql="select * from correo";
    return ejecutarConsulta($sql);      
    }


	public function AutocompletarRuc($buscar)
	{
		global $conexion;

		$buscarParam = '%' . $buscar;

		$sql = "SELECT numero_documento, razon_social, domicilio_fiscal
				FROM persona
				WHERE numero_documento LIKE ? AND estado = '1' AND tipo_persona = 'cliente'";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en AutocompletarRuc: " . $conexion->error);
			echo json_encode([]);
			return;
		}

		$stmt->bind_param("s", $buscarParam);
		$stmt->execute();

		$resultado = $stmt->get_result();

		$datos = [];
		if ($resultado->num_rows > 0) {
			while ($fila = $resultado->fetch_array()) {
				$datos[] = $fila['numero_documento'];
			}
		}

		$stmt->close();

		echo json_encode($datos);
	}
    



// public function AutocompletarRuc($buscar){

//   $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
//       mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
//       //Si tenemos un posible error en la conexión lo mostramos
//       if (mysqli_connect_errno())
//       {
//             printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
//             exit();
//       }

//         $sql="select numero_documento, razon_social, domicilio_fiscal from persona where numero_documento like '%$buscar' and estado='1' and tipo_persona='cliente'";

//         $Result=mysqli_query($connect, $sql);

//         if ($Result->num_rows > 0)
//         {
//           while($fila=$result->fecth_array())
//           {
//             $datos[]=$fila['numero_documento'];
//           }
//           echo json_encode($datos);
//         }

//       }


      public function tipodecambio($diaa)
      {

           

      }


	public function editar($idcotizacion)
	{
		global $conexion;

		$sql = "SELECT
				c.idcotizacion, c.idcliente, c.idusuario,
				SUBSTRING(c.serienota,6) as numeroc,
				SUBSTRING(c.serienota,1,4) as serie,
				c.moneda,
				DATE_FORMAT(c.fechaemision, '%Y-%m-%d') as fechaemision,
				c.tipocotizacion, c.subtotal, c.impuesto, c.total,
				c.observacion, c.estado, c.tipocambio,
				DATE_FORMAT(c.fechavalidez, '%Y-%m-%d') as fechavalidez,
				p.numero_documento as ruc, p.nombre_comercial, p.email,
				p.domicilio_fiscal, c.serienota, c.estado
				FROM
				cotizacion c
				INNER JOIN persona p ON c.idcliente=p.idpersona
				INNER JOIN detalle_articulo_cotizacion dc ON dc.idcotizacion=c.idcotizacion
				INNER JOIN articulo a ON dc.iditem=a.idarticulo
				WHERE c.idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en editar (cotizacion): " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}



	public function listarDetallecotizacion($idcotizacion)
	{
		global $conexion;

		$sql = "SELECT
				a.idarticulo,
				a.nombre as narticulo,
				dct.cantidad,
				a.codigo,
				a.unidad_medida,
				dct.precio as precioc,
				dct.valorunitario,
				c.subtotal,
				c.impuesto,
				c.total,
				dct.valorventa,
				dct.norden
				FROM
				detalle_articulo_cotizacion dct
				INNER JOIN articulo a ON dct.iditem=a.idarticulo
				INNER JOIN cotizacion c ON c.idcotizacion=dct.idcotizacion
				WHERE dct.idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarDetallecotizacion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}

	public function listarnumerofilas($idcotizacion)
	{
		global $conexion;

		$sql = "SELECT COUNT(dct.iditem) as cantifilas
				FROM
				detalle_articulo_cotizacion dct
				INNER JOIN articulo a ON dct.iditem=a.idarticulo
				INNER JOIN cotizacion c ON c.idcotizacion=dct.idcotizacion
				WHERE dct.idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarnumerofilas: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}


	//Implementamos un método para editar cotización
	public function editarcotizacion($idcotizacion, $idempresa, $idusuario, $idcliente, $serienota,
		$moneda, $fechaemision, $hora, $tipocotizacion, $subtotal, $impuesto,
		$total, $observacion, $vendedor, $idarticulo, $codigo, $cantidad,
		$precio_unitario, $numero_cotizacion, $idserie, $descdet, $norden,
		$fechavalidez, $tcambio, $valorventa, $valorunitario, $igvitem, $igventa, $estado)
	{
		global $conexion;
		$sw = true;

		// Iniciar transacción
		mysqli_begin_transaction($conexion);

		try {
			// 1. UPDATE cabecera de cotización
			$sql_cabecera = "UPDATE cotizacion SET
					idcliente = ?,
					moneda = ?,
					fechaemision = ?,
					subtotal = ?,
					impuesto = ?,
					total = ?,
					observacion = ?,
					vendedor = ?,
					tipocambio = ?,
					fechavalidez = ?,
					estado = ?
					WHERE idcotizacion = ?";

			$stmt_cabecera = $conexion->prepare($sql_cabecera);
			if (!$stmt_cabecera) {
				error_log("Error preparando UPDATE en cotizacion (editar): " . $conexion->error);
				throw new Exception("Error al preparar actualización de cotización");
			}

			$fechaemision_completa = $fechaemision . ' ' . $hora;
			$stmt_cabecera->bind_param("issssssssssi",
				$idcliente, $moneda, $fechaemision_completa, $subtotal, $impuesto,
				$total, $observacion, $vendedor, $tcambio, $fechavalidez,
				$estado, $idcotizacion
			);

			if (!$stmt_cabecera->execute()) {
				error_log("Error ejecutando UPDATE cotizacion: " . $stmt_cabecera->error);
				throw new Exception("Error al actualizar cotización");
			}

			$stmt_cabecera->close();

			// 2. DELETE detalles anteriores
			$sql_delete = "DELETE FROM detalle_articulo_cotizacion WHERE idcotizacion = ?";

			$stmt_delete = $conexion->prepare($sql_delete);
			if (!$stmt_delete) {
				error_log("Error preparando DELETE en detalle_articulo_cotizacion: " . $conexion->error);
				throw new Exception("Error al preparar eliminación de detalles");
			}

			$stmt_delete->bind_param("i", $idcotizacion);

			if (!$stmt_delete->execute()) {
				error_log("Error ejecutando DELETE detalles: " . $stmt_delete->error);
				throw new Exception("Error al eliminar detalles anteriores");
			}

			$stmt_delete->close();

			// 3. INSERT nuevos detalles en loop
			$sql_detalle = "INSERT INTO detalle_articulo_cotizacion (
					idcotizacion, iditem, codigo, cantidad, precio, descdet, norden,
					valorventa, valorunitario, igvvalorventa, igvitem
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt_detalle = $conexion->prepare($sql_detalle);
			if (!$stmt_detalle) {
				error_log("Error preparando INSERT en detalle_articulo_cotizacion (editar): " . $conexion->error);
				throw new Exception("Error al preparar inserción de detalles");
			}

			$num_elementos = 0;
			while ($num_elementos < count($idarticulo)) {
				$stmt_detalle->bind_param("iisssssssss",
					$idcotizacion,
					$idarticulo[$num_elementos],
					$codigo[$num_elementos],
					$cantidad[$num_elementos],
					$precio_unitario[$num_elementos],
					$descdet[$num_elementos],
					$norden[$num_elementos],
					$valorventa[$num_elementos],
					$valorunitario[$num_elementos],
					$igventa[$num_elementos],
					$igvitem[$num_elementos]
				);

				if (!$stmt_detalle->execute()) {
					error_log("Error ejecutando INSERT detalle (editar): " . $stmt_detalle->error);
					throw new Exception("Error al insertar detalle de cotización");
				}

				$num_elementos++;
			}

			$stmt_detalle->close();

			// Confirmar transacción
			mysqli_commit($conexion);

			return $sw;

		} catch (Exception $e) {
			// Revertir transacción en caso de error
			mysqli_rollback($conexion);
			error_log("Error en transacción de editar cotización: " . $e->getMessage());
			return false;
		}
	}



	public function estado($idcotizacion)
	{
		global $conexion;

		$sql = "SELECT estado FROM cotizacion WHERE idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en estado: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}



	public function traercotizacion($idcotizacionI)
	{
		global $conexion;

		$sql = "SELECT
				DATE_FORMAT(c.fechaemision, '%Y-%m-%d') as fechaemi,
				DATE_FORMAT(c.fechaemision, '%H:%i:%s') as hora,
				c.moneda,
				c.tipocambio,
				p.idpersona,
				p.tipo_documento,
				p.email,
				p.numero_documento as ruc,
				p.nombre_comercial,
				p.domicilio_fiscal,
				c.subtotal as neta,
				c.impuesto as igv,
				c.total,
				c.serienota,
				c.observacion,
				c.tipocotizacion
				FROM
				cotizacion c
				INNER JOIN persona p ON c.idcliente=p.idpersona
				WHERE idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en traercotizacion: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacionI);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}


	public function listarDetalleCoti($idcotizacion)
	{
		global $conexion;

		$sql = "SELECT
				dc.id,
				dc.norden,
				dc.iditem,
				a.idarticulo,
				a.nombre as narticulo,
				dc.descdet,
				dc.cantidad,
				a.codigo,
				a.unidad_medida,
				dc.precio as precioc,
				dc.igvvalorventa,
				dc.igvitem,
				dc.valorunitario,
				dc.valorventa,
				c.subtotal,
				c.impuesto,
				c.total,
				((a.stock/a.factorc) - (a.stock - dc.cantidad) / a.factorc) as cantidadreal,
				um.abre,
				um.nombreum
				FROM
				detalle_articulo_cotizacion dc
				INNER JOIN articulo a ON dc.iditem=a.idarticulo
				INNER JOIN cotizacion c ON dc.idcotizacion=c.idcotizacion
				INNER JOIN umedida um ON um.idunidad=a.unidad_medida
				WHERE dc.idcotizacion = ?";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en listarDetalleCoti: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idcotizacion);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado;
	}


    public function almacenlista()
    {

    $sql="select * from almacen order by idalmacen";
    return ejecutarConsulta($sql);      
    }



	public function mostrarultimocomprobanteId($idempresa)
	{
		global $conexion;

		$sql = "SELECT c.idcotizacion, e.tipoimpresion
				FROM cotizacion c
				INNER JOIN empresa e ON c.idempresa=e.idempresa
				WHERE e.idempresa = ?
				ORDER BY idcotizacion DESC
				LIMIT 1";

		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			error_log("Error preparando SELECT en mostrarultimocomprobanteId: " . $conexion->error);
			return false;
		}

		$stmt->bind_param("i", $idempresa);
		$stmt->execute();

		$resultado = $stmt->get_result();
		$stmt->close();

		return $resultado->fetch_object();
	}


 
       
    }
?>