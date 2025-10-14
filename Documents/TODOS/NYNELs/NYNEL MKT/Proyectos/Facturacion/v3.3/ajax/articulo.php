<?php
// SEGURIDAD: Cargar helpers de validación y CSRF
require_once "../config/ajax_helper.php";
require_once "../modelos/Articulo.php";

$articulo = new Articulo();



$idarticulo = isset($_POST["idarticulo"]) ? limpiarCadena($_POST["idarticulo"]) : "";
$idfamilia = isset($_POST["idfamilia"]) ? limpiarCadena($_POST["idfamilia"]) : "";
$codigo_proveedor = isset($_POST["codigo_proveedor"]) ? limpiarCadena($_POST["codigo_proveedor"]) : "";
$idalmacen = isset($_POST["idalmacen"]) ? limpiarCadena($_POST["idalmacen"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$unidad_medida = isset($_POST["unidad_medida"]) ? limpiarCadena($_POST["unidad_medida"]) : "";
$costo_compra = isset($_POST["costo_compra"]) && $_POST["costo_compra"] !== "" ? limpiarCadena($_POST["costo_compra"]) : "0";
$saldo_iniu = isset($_POST["saldo_iniu"]) && $_POST["saldo_iniu"] !== "" ? limpiarCadena($_POST["saldo_iniu"]) : "0";
$valor_iniu = isset($_POST["valor_iniu"]) && $_POST["valor_iniu"] !== "" ? limpiarCadena($_POST["valor_iniu"]) : "0";
$saldo_finu = isset($_POST["saldo_finu"]) && $_POST["saldo_finu"] !== "" ? limpiarCadena($_POST["saldo_finu"]) : "0";
$valor_finu = isset($_POST["valor_finu"]) && $_POST["valor_finu"] !== "" ? limpiarCadena($_POST["valor_finu"]) : "0";
$stock = isset($_POST["stock"]) && $_POST["stock"] !== "" ? limpiarCadena($_POST["stock"]) : "0";
$comprast = isset($_POST["comprast"]) && $_POST["comprast"] !== "" ? limpiarCadena($_POST["comprast"]) : "0";
$ventast = isset($_POST["ventast"]) && $_POST["ventast"] !== "" ? limpiarCadena($_POST["ventast"]) : "0";
$portador = isset($_POST["portador"]) && $_POST["portador"] !== "" ? limpiarCadena($_POST["portador"]) : "0";
$merma = isset($_POST["merma"]) && $_POST["merma"] !== "" ? limpiarCadena($_POST["merma"]) : "0";
$valor_venta = isset($_POST["valor_venta"]) && $_POST["valor_venta"] !== "" ? limpiarCadena($_POST["valor_venta"]) : "0";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
$codigosunat = isset($_POST["codigosunat"]) ? limpiarCadena($_POST["codigosunat"]) : "";
$ccontable = isset($_POST["ccontable"]) ? limpiarCadena($_POST["ccontable"]) : "";
$precio2 = isset($_POST["precio2"]) && $_POST["precio2"] !== "" ? limpiarCadena($_POST["precio2"]) : "0";
$precio3 = isset($_POST["precio3"]) && $_POST["precio3"] !== "" ? limpiarCadena($_POST["precio3"]) : "0";



//Nuevos codigos

$cicbper = isset($_POST["cicbper"]) && $_POST["cicbper"] !== "" ? limpiarCadena($_POST["cicbper"]) : "0";
$nticbperi = isset($_POST["nticbperi"]) && $_POST["nticbperi"] !== "" ? limpiarCadena($_POST["nticbperi"]) : "0";
$ctticbperi = isset($_POST["ctticbperi"]) && $_POST["ctticbperi"] !== "" ? limpiarCadena($_POST["ctticbperi"]) : "0";
$mticbperu = isset($_POST["mticbperu"]) && $_POST["mticbperu"] !== "" ? limpiarCadena($_POST["mticbperu"]) : "0";

//Nuevos codigos





//N-------------------

$codigott = isset($_POST["codigott"]) ? limpiarCadena($_POST["codigott"]) : "";
$desctt = isset($_POST["desctt"]) ? limpiarCadena($_POST["desctt"]) : "";
$codigointtt = isset($_POST["codigointtt"]) ? limpiarCadena($_POST["codigointtt"]) : "";
$nombrett = isset($_POST["nombrett"]) ? limpiarCadena($_POST["nombrett"]) : "";

//N-----------------------





$lote = isset($_POST["lote"]) ? limpiarCadena($_POST["lote"]) : "";

$marca = isset($_POST["marca"]) ? limpiarCadena($_POST["marca"]) : "";

$fechafabricacion = isset($_POST["fechafabricacion"]) && $_POST["fechafabricacion"] !== "" ? limpiarCadena($_POST["fechafabricacion"]) : 'NULL';

$fechavencimiento = isset($_POST["fechavencimiento"]) && $_POST["fechavencimiento"] !== "" ? limpiarCadena($_POST["fechavencimiento"]) : 'NULL';

$procedencia = isset($_POST["procedencia"]) ? limpiarCadena($_POST["procedencia"]) : "";

$fabricante = isset($_POST["fabricante"]) ? limpiarCadena($_POST["fabricante"]) : "";

$registrosanitario = isset($_POST["registrosanitario"]) ? limpiarCadena($_POST["registrosanitario"]) : "";

$fechaingalm = isset($_POST["fechaingalm"]) && $_POST["fechaingalm"] !== "" ? limpiarCadena($_POST["fechaingalm"]) : 'NULL';

$fechafinalma = isset($_POST["fechafinalma"]) && $_POST["fechafinalma"] !== "" ? limpiarCadena($_POST["fechafinalma"]) : 'NULL';

$proveedor = isset($_POST["proveedor"]) ? limpiarCadena($_POST["proveedor"]) : "";

$seriefaccompra = isset($_POST["seriefaccompra"]) ? limpiarCadena($_POST["seriefaccompra"]) : "";

$numerofaccompra = isset($_POST["numerofaccompra"]) ? limpiarCadena($_POST["numerofaccompra"]) : "";

$fechafacturacompra = isset($_POST["fechafacturacompra"]) && $_POST["fechafacturacompra"] !== "" ? limpiarCadena($_POST["fechafacturacompra"]) : 'NULL';



$limitestock = isset($_POST["limitestock"]) ? limpiarCadena($_POST["limitestock"]) : "";

$tipoitem = isset($_POST["tipoitem"]) ? limpiarCadena($_POST["tipoitem"]) : "";
$factorc = isset($_POST["factorc"]) ? limpiarCadena($_POST["factorc"]) : "";
$umedidacompra = isset($_POST["umedidacompra"]) ? limpiarCadena($_POST["umedidacompra"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";


$idalmacennarticulo = isset($_POST["idalmacennarticulo"]) ? limpiarCadena($_POST["idalmacennarticulo"]) : "";
$idfamilianarticulo = isset($_POST["idfamilianarticulo"]) ? limpiarCadena($_POST["idfamilianarticulo"]) : "";
$tipoitemnarticulo = isset($_POST["tipoitemnarticulo"]) ? limpiarCadena($_POST["tipoitemnarticulo"]) : "";
$nombrenarticulo = isset($_POST["nombrenarticulo"]) ? limpiarCadena($_POST["nombrenarticulo"]) : "";
$stocknarticulo = isset($_POST["stocknarticulo"]) ? limpiarCadena($_POST["stocknarticulo"]) : "";
$precioventanarticulo = isset($_POST["precioventanarticulo"]) ? limpiarCadena($_POST["precioventanarticulo"]) : "";
$codigonarticulonarticulo = isset($_POST["codigonarticulonarticulo"]) ? limpiarCadena($_POST["codigonarticulonarticulo"]) : "";
$descripcionnarticulo = isset($_POST["descripcionnarticulo"]) ? limpiarCadena($_POST["descripcionnarticulo"]) : "";
$umedidanp = isset($_POST["umedidanp"]) ? limpiarCadena($_POST["umedidanp"]) : "";


if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = '';
}

if ($action == 'GenerarCodigo') {
	$generatedCode = $articulo->GenerarCodigoCorrelativoAutomatico();

	$results = array(
		"codigo" => $generatedCode
	);

	header('Content-type: application/json');
	echo json_encode($results);
}




require_once "../modelos/Rutas.php";
$rutas = new Rutas();
$Rrutas = $rutas->mostrar2("1");
$Prutas = $Rrutas->fetch_object();
$rutaimagen = $Prutas->rutaarticulos; // ru






switch ($_GET["op"]) {

	case 'guardaryeditar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {

			$imagen = $_POST["imagenactual"];

		} else {
			// SEGURIDAD: Validar imagen con validación fuerte
			$imagen_validada = validarImagenSubida($_FILES['imagen']);

			if ($imagen_validada === false) {
				echo "Error: Imagen inválida. Solo se permiten JPG y PNG de máximo 5MB";
				exit();
			}

			// Usar nombre seguro generado
			$imagen = $imagen_validada['nombre_seguro'];

			// Mover archivo con nombre seguro
			if (!move_uploaded_file($imagen_validada['tmp_name'], $rutaimagen . $imagen)) {
				error_log("Error al mover archivo de imagen: " . $rutaimagen . $imagen);
				echo "Error al guardar la imagen";
				exit();
			}
		}



		if (empty($idarticulo)) {

			$rspta = $articulo->insertar(
				$idalmacen,
				$codigo_proveedor,
				$codigo,
				html_entity_decode($nombre, ENT_QUOTES | ENT_HTML401, 'UTF-8'),
				$idfamilia,
				$unidad_medida,
				$costo_compra,
				$saldo_iniu,
				$valor_iniu,
				$saldo_finu,
				$valor_finu,
				$stock,
				$comprast,
				$ventast,
				$portador,
				$merma,
				$valor_venta,
				$imagen,
				//VA IMAGEN
				$codigosunat,
				$ccontable,
				$precio2,
				$precio3,
				$cicbper,
				$nticbperi,
				$ctticbperi,
				$mticbperu,
				$codigott,
				$desctt,
				$codigointtt,
				$nombrett,
				$lote,
				$marca,
				$fechafabricacion,
				$fechavencimiento,
				$procedencia,
				$fabricante,
				$registrosanitario,
				$fechaingalm,
				$fechafinalma,
				$proveedor,
				$seriefaccompra,
				$numerofaccompra,
				$fechafacturacompra,
				$limitestock,
				$tipoitem,
				$umedidacompra,
				$factorc,
				$descripcion
			);

			if ($rspta) {
				// ========== AUDITORÍA: Registrar creación de artículo ==========
				registrarOperacionCreate('articulo', $codigo, [
					'nombre' => $nombre,
					'codigo_proveedor' => $codigo_proveedor,
					'familia_id' => $idfamilia,
					'almacen_id' => $idalmacen,
					'stock' => $stock,
					'costo_compra' => $costo_compra,
					'valor_venta' => $valor_venta,
					'precio2' => $precio2,
					'precio3' => $precio3,
					'tipo_item' => $tipoitem,
					'marca' => $marca,
					'unidad_medida' => $unidad_medida
				], "Artículo creado: {$nombre} (Código: {$codigo}) - Stock: {$stock}");

				echo "Artículo registrado";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('CREATE', 'articulo', [
					'descripcion' => "Intento fallido de crear artículo: {$nombre}",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_CREAR_ARTICULO',
					'mensaje_error' => 'No se pudo crear el artículo',
					'metadata' => [
						'codigo' => $codigo,
						'nombre' => $nombre
					]
				]);

				echo "Error";
			}

		} else {

			$rspta = $articulo->editar(
				$idarticulo,
				$idalmacen,
				$codigo_proveedor,
				$codigo,
				html_entity_decode($nombre, ENT_QUOTES | ENT_HTML401, 'UTF-8'),
				$idfamilia,
				$unidad_medida,
				$costo_compra,
				$saldo_iniu,
				$valor_iniu,
				$saldo_finu,
				$valor_finu,
				$stock,
				$comprast,
				$ventast,
				$portador,
				$merma,
				$valor_venta,
				$imagen,
				$codigosunat,
				$ccontable,
				$precio2,
				$precio3,
				$cicbper,
				$nticbperi,
				$ctticbperi,
				$mticbperu,
				$codigott,
				$desctt,
				$codigointtt,
				$nombrett,
				$lote,
				$marca,
				$fechafabricacion,
				$fechavencimiento,
				$procedencia,
				$fabricante,
				$registrosanitario,
				$fechaingalm,
				$fechafinalma,
				$proveedor,
				$seriefaccompra,
				$numerofaccompra,
				$fechafacturacompra,
				$limitestock,
				$tipoitem,
				$umedidacompra,
				$factorc,
				$descripcion

			);

			if ($rspta) {
				// ========== AUDITORÍA: Registrar actualización de artículo ==========
				registrarAuditoria('UPDATE', 'articulo', [
					'registro_id' => $idarticulo,
					'descripcion' => "Artículo actualizado: {$nombre} (ID: {$idarticulo})",
					'metadata' => [
						'codigo' => $codigo,
						'stock_nuevo' => $stock,
						'costo_compra' => $costo_compra,
						'valor_venta' => $valor_venta,
						'precio2' => $precio2,
						'precio3' => $precio3
					]
				]);

				echo "Artículo actualizado";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('UPDATE', 'articulo', [
					'registro_id' => $idarticulo,
					'descripcion' => "Intento fallido de actualizar artículo (ID: {$idarticulo})",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_ACTUALIZAR_ARTICULO',
					'mensaje_error' => 'No se pudo actualizar el artículo'
				]);

				echo "Artículo no se pudo actualizar";
			}

		}

		break;

	case 'editarstockarticulo':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		$idarticuloproduct = isset($_POST["idarticuloproduct"]) ? limpiarCadena($_POST["idarticuloproduct"]) : "";
		$stockproduct = isset($_POST["stockproduct"]) ? limpiarCadena($_POST["stockproduct"]) : "";

		// SEGURIDAD: Validar que sean números válidos
		$idarticuloproduct = validarEntero($idarticuloproduct, 1);
		$stockproduct = validarDecimal($stockproduct, 0);

		if ($idarticuloproduct === false || $stockproduct === false) {
			echo "Error: Datos inválidos";
			exit();
		}

		$rspta = $articulo->editarStockArticulo($idarticuloproduct, $stockproduct);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar actualización de stock ==========
			registrarAuditoria('UPDATE', 'articulo', [
				'registro_id' => $idarticuloproduct,
				'descripcion' => "Stock actualizado del artículo ID: {$idarticuloproduct}",
				'metadata' => [
					'stock_nuevo' => $stockproduct,
					'operacion' => 'editar_stock_manual'
				]
			]);

			echo "Stock del artículo actualizado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('UPDATE', 'articulo', [
				'registro_id' => $idarticuloproduct,
				'descripcion' => "Intento fallido de actualizar stock del artículo ID: {$idarticuloproduct}",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_ACTUALIZAR_STOCK',
				'mensaje_error' => 'No se pudo actualizar el stock',
				'metadata' => [
					'stock_intentado' => $stockproduct
				]
			]);

			echo "Stock del artículo no se pudo actualizar";
		}

		break;

	case 'guardarnuevoarticulo':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		if (empty($idarticulo)) {
			$rspta = $articulo->insertar(
				$idalmacennarticulo,
				'',
				$codigonarticulonarticulo,
				html_entity_decode($nombrenarticulo, ENT_QUOTES | ENT_HTML401, 'UTF-8'),
				$idfamilianarticulo,
				$umedidanp,
				'',
				$stocknarticulo,
				'',
				$stocknarticulo,
				'',
				$stocknarticulo,
				'',
				'',
				'',
				'',
				$precioventanarticulo,
				'',
				//VA IMAGEN
				'',
				'',
				$precioventanarticulo,
				$precioventanarticulo,
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'0',
				$tipoitemnarticulo,
				$umedidanp,
				'1',
				$descripcionnarticulo

			);
		}

		if ($rspta) {
			// ========== AUDITORÍA: Registrar creación de nuevo artículo rápido ==========
			registrarOperacionCreate('articulo', $codigonarticulonarticulo, [
				'nombre' => $nombrenarticulo,
				'familia_id' => $idfamilianarticulo,
				'almacen_id' => $idalmacennarticulo,
				'stock' => $stocknarticulo,
				'precio_venta' => $precioventanarticulo,
				'tipo_item' => $tipoitemnarticulo,
				'descripcion' => $descripcionnarticulo,
				'metodo_creacion' => 'guardarnuevoarticulo_rapido'
			], "Nuevo artículo creado rápido: {$nombrenarticulo} (Código: {$codigonarticulonarticulo})");

			echo "Artículo registrado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CREATE', 'articulo', [
				'descripcion' => "Intento fallido de crear artículo rápido: {$nombrenarticulo}",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_CREAR_ARTICULO_RAPIDO',
				'mensaje_error' => 'No se pudo crear el artículo rápido',
				'metadata' => [
					'codigo' => $codigonarticulonarticulo
				]
			]);

			echo "Error";
		}
		break;



	case 'desactivar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar ID
		$idarticulo = validarEntero($idarticulo, 1);
		if ($idarticulo === false) {
			echo "Error: ID de artículo inválido";
			exit();
		}

		$rspta = $articulo->desactivar($idarticulo);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar desactivación de artículo ==========
			registrarAuditoria('CAMBIO_ESTADO', 'articulo', [
				'registro_id' => $idarticulo,
				'descripcion' => "Artículo desactivado (ID: {$idarticulo})",
				'metadata' => [
					'estado_nuevo' => 'Inactivo'
				]
			]);

			echo "Artículo Desactivado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CAMBIO_ESTADO', 'articulo', [
				'registro_id' => $idarticulo,
				'descripcion' => "Intento fallido de desactivar artículo (ID: {$idarticulo})",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_DESACTIVAR_ARTICULO',
				'mensaje_error' => 'No se puede desactivar el artículo'
			]);

			echo "Artículo no se puede desactivar";
		}

		break;





	case 'activar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar ID
		$idarticulo = validarEntero($idarticulo, 1);
		if ($idarticulo === false) {
			echo "Error: ID de artículo inválido";
			exit();
		}

		$rspta = $articulo->activar($idarticulo);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar activación de artículo ==========
			registrarAuditoria('CAMBIO_ESTADO', 'articulo', [
				'registro_id' => $idarticulo,
				'descripcion' => "Artículo activado (ID: {$idarticulo})",
				'metadata' => [
					'estado_nuevo' => 'Activo'
				]
			]);

			echo "Artículo activado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CAMBIO_ESTADO', 'articulo', [
				'registro_id' => $idarticulo,
				'descripcion' => "Intento fallido de activar artículo (ID: {$idarticulo})",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_ACTIVAR_ARTICULO',
				'mensaje_error' => 'No se puede activar el artículo'
			]);

			echo "Artículo no se puede activar";
		}

		break;


	case 'mostrar':
		$rspta = $articulo->mostrar($idarticulo);
		$codigoExistente = $rspta['codigo']; // Obtén el código existente de la consulta

		// Llama a la función con el código existente
		$generatedCode = $articulo->GenerarCodigoCorrelativoAutomatico($codigoExistente);

		// Otros procesos de mostrar...
		echo json_encode($rspta);
		break;






	case 'mostrarequivalencia':



		require_once "../modelos/Almacen.php";

		$almacen = new Almacen();

		$idmm = $_GET['iduni'];

		$rspta = $almacen->selectunidadid($idmm);

		//Codificar el resultado utilizando json

		echo json_encode($rspta);

		break;







	case 'validarcodigo':





		$coddd = $_GET['cdd'];

		$rspta = $articulo->validarcodigo($coddd);

		//Codificar el resultado utilizando json

		echo json_encode($rspta);

		break;





	case 'articuloBusqueda':

		$codigo = $_GET['codigoa'];

		$rspta = $articulo->articuloBusqueda($codigo);

		//Codificar el resultado utilizando json

		echo json_encode($rspta);

		break;


	case 'listar':

		$idempresa = "1";
		$rspta = $articulo->listar($idempresa);
		$url = '../reportes/printbarcode.php?codigopr=';
		//Vamos a declarar un array

		$data = array();



		while ($reg = $rspta->fetch_object()) {
			$data[] = array(

				"0" => $reg->nombre,
 				"1" => $reg->marca,
				"2" => $reg->nombreal,
				"3" => $reg->codigo,
				"4" => $reg->stock,
				"5" => $reg->precio,
				"6" => $reg->costo_compra,
				"7" => ($reg->imagen == "") ? "<img src='../files/articulos/simagen.png' height='60px' width='60px'>" :
					"<img src='$rutaimagen$reg->imagen' height='60px' width='60px'>",
				"8" => ($reg->estado) ? '<span class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-success-light">A</span>
 				' :
					'<span class="label bg-red">I</span>',
				
				"9" => ($reg->estado) ? '<div class="btn-group mb-1">
					<div class="dropdown">
							<button type="button" class="btn btn-wave waves-effect waves-light btn-sm btn-primary-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Opciones
							</button>
							<div class="dropdown-menu" style="">
									<a class="dropdown-item" href="' . $url . $reg->codigo . '&st=' . $reg->st2 . '&pr=' . $reg->precio . '">Código de barra</a>
									<a class="dropdown-item" onclick="mostrar(' . $reg->idarticulo . ')" >Editar artículo</a>
									<a class="dropdown-item" onclick="desactivar(' . $reg->idarticulo . ')" >Desactivar articulo</a>
							</div>
					</div>
		    </div> ' :

					'
								<div class="btn-group mb-1">
									<div class="dropdown">
											<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													Opciones
											</button>
											<div class="dropdown-menu" style="">
													<a class="dropdown-item" href="' . $url . $reg->codigo . '&st=' . $reg->st2 . '&pr=' . $reg->precio . '">Código de barra</a>
													<a class="dropdown-item" onclick="mostrar(' . $reg->idarticulo . ')" >Editar artículo</a>
													<a class="dropdown-item" onclick="activar(' . $reg->idarticulo . ')" >Activar articulo</a>
											</div>
									</div>
						    </div> '
				,
			);

		}

		$results = array(
			"sEcho" => 1,
			//Información para el datatables
			"iTotalRecords" => count($data),
			//enviamos el total registros al datatable
			"iTotalDisplayRecords" => count($data),
			//enviamos el total registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;



	case 'listarservicios':

		$idempresa = "1";
		$rspta = $articulo->listarservicios($idempresa);
		$url = '../reportes/printbarcode.php?codigopr=';
		//Vamos a declarar un array

		$data = array();



		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				

				"0" => $reg->nombre,
				"1" => $reg->nombreal,
				"2" => $reg->codigo,
				//"4"=>$reg->stock,
				"3" => $reg->precio,
				//"6"=>$reg->ccontable ,
				// "7"=>($reg->imagen=="")?"<img src='../files/articulos/simagen.png' height='35px' width='35px'>":
				// "<img src='$rutaimagen$reg->imagen' height='35px' width='35px'>",
				"4" => ($reg->estado) ? '<span class="label bg-green">A</span>
 				' :
					'<span class="label bg-red">I</span>',
					
					"5" => ($reg->estado) ? '<div class="btn-group mb-1">
					<div class="dropdown">
							<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Opciones
							</button>
							<div class="dropdown-menu" style="">
									<a class="dropdown-item" href="' . $url . $reg->codigo . '&st=' . $reg->st2 . '&pr=' . $reg->precio . '">Código de barra</a>
									<a class="dropdown-item" onclick="mostrar(' . $reg->idarticulo . ')" >Editar servicio</a>
									<a class="dropdown-item" onclick="desactivar(' . $reg->idarticulo . ')" >Desactivar servicio</a>
							</div>
					</div>
		    </div>' :

					'
								<div class="btn-group mb-1">
									<div class="dropdown">
											<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													Opciones
											</button>
											<div class="dropdown-menu" style="">
													<a class="dropdown-item" href="' . $url . $reg->codigo . '&st=' . $reg->st2 . '&pr=' . $reg->precio . '">Código de barra</a>
													<a class="dropdown-item" onclick="mostrar(' . $reg->idarticulo . ')" >Editar servicio</a>
													<a class="dropdown-item" onclick="desactivar(' . $reg->idarticulo . ')" >Activar servicio</a>
											</div>
									</div>
						    </div>'
						    ,
			);

		}

		$results = array(
			"sEcho" => 1,
			//Información para el datatables
			"iTotalRecords" => count($data),
			//enviamos el total registros al datatable
			"iTotalDisplayRecords" => count($data),
			//enviamos el total registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;





	case 'inventarioValorizado':

		$rspta = $articulo->listar();

		//Vamos a declarar un array

		$data = array();



		while ($reg = $rspta->fetch_object()) {

			$data[] = array(

				

                

				"0" => $reg->codigo_proveedor,

				"1" => $reg->codigo,

				"2" => $reg->familia,

				"3" => $reg->nombre,

				"4" => $reg->stock,

				"5" => $reg->precio,



				"6" => "<img src='../files/articulos/" . $reg->imagen . "' height='60px' width='60px' >",

				"7" => ($reg->estado) ? '<span class="label bg-green">A</span>' :

					'<span class="label bg-red">I</span>',
					
				"8" => ($reg->estado) ? '<button class="btn btn-warning" onclick="mostrar(' . $reg->idarticulo . ')"> <i class="fa fa-pencil"> </i> </button>' .

					' <button class="btn btn-danger" onclick="desactivar(' . $reg->idarticulo . ')">   <i class="fa fa-close"></i>   </button>' :



					'<button class="btn btn-warning" onclick="mostrar(' . $reg->idarticulo . ')">      <i class="fa fa-pencil"></i> </button>' .

					' <button class="btn btn-primary" onclick="activar(' . $reg->idarticulo . ')">     <i class="fa fa-check"></i>   </button>'

			);

		}

		$results = array(

			"sEcho" => 1,
			//Información para el datatables

			"iTotalRecords" => count($data),
			//enviamos el total registros al datatable

			"iTotalDisplayRecords" => count($data),
			//enviamos el total registros a visualizar

			"aaData" => $data
		);

		echo json_encode($results);



		break;



	case "selectFamilia":
		require_once "../modelos/Familia.php";
		$familia = new Familia();
		$rspta = $familia->select();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idfamilia . '>' . $reg->descripcion . '</option>';
		}
		break;





	case "selectAlmacen":

		require_once "../modelos/Almacen.php";

		$almacen = new Almacen();

		$idempresa = "1";

		$rspta = $almacen->select($idempresa);

		while ($reg = $rspta->fetch_object()) {

			echo '<option value=' . $reg->idalmacen . '>' . $reg->nombre . '</option>';

		}

		break;



	case "selectUnidad":

		require_once "../modelos/Almacen.php";

		$almacen = new Almacen();

		$rspta = $almacen->selectunidad();

		while ($reg = $rspta->fetch_object()) {

			echo '<option value=' . $reg->idunidad . '>' . $reg->nombreum . ' | ' . $reg->abre . '</option>';

		}

		break;



	case 'buscararticulo':

		$key = $_POST['key'];

		$rspta = $articulo->buscararticulo($key);

		echo json_encode($rspta); // ? "Cliente ya existe": "Documento valido";

		break;



	case "comboarticulo":
		$anor = $_GET['anor'];
		$alm = $_GET['aml'];
		$rpta = $articulo->comboarticulo($anor, $alm);
		while ($reg = $rpta->fetch_object()) {
			echo '<option value=' . $reg->codigo . '>' . $reg->codigo . ' | ' . $reg->nombre . '     | Año registro: ' . $reg->anoregistro . '</option>';
		}
		break;



	case "comboarticulomg":
		$alm = $_GET['aml'];
		$anor = $_GET['anor'];
		$rpta = $articulo->comboarticulo($anor, $alm);
		while ($reg = $rpta->fetch_object()) {

			echo '<option value=' . $reg->idarticulo . '>' . $reg->codigo . ' | ' . $reg->nombre . '     | Año registro: ' . $reg->anoregistro . '</option>';

		}

		break;


	case "comboarticulokardex":
		//$anor=$_GET['anor'];
		$rpta = $articulo->comboarticuloKardex();
		while ($reg = $rpta->fetch_object()) {
			echo '<option value=' . $reg->codigo . '>' . $reg->codigo . ' | ' . $reg->nombre . '</option>';
			//echo '<option>any</option>';
		}
		break;



	// case "insertarArticulosMasivo":
	// 	// Recoger los datos del formulario (por ejemplo, desde $_POST)
	// 	$codigo = $_POST['codigo'];
	// 	$familia_descripcion = $_POST['familia_descripcion'];
	// 	$nombre = $_POST['nombre'];
	// 	$marca = $_POST['marca'];
	// 	$descrip = $_POST['descrip'];
	// 	$costo_compra = $_POST['costo_compra'];
	// 	$precio_venta = $_POST['precio_venta'];
	// 	$stock = $_POST['stock'];
	// 	$saldo_iniu = $_POST['saldo_iniu'];
	// 	$valor_iniu = $_POST['valor_iniu'];
	// 	$tipoitem = $_POST['tipoitem'];
	// 	$codigott = $_POST['codigott'];
	// 	$desctt = $_POST['desctt'];
	// 	$codigointtt = $_POST['codigointtt'];
	// 	$nombrett = $_POST['nombrett'];
	// 	$nombre_almacen = $_POST['nombre_almacen'];
	// 	$saldo_finu = $_POST['saldo_finu'];

	// 	// Llama al método del modelo
	// 	$rspta = $articulo->insertarArticulosMasivo(
	// 		$codigo,
	// 		$familia_descripcion,
	// 		$nombre,
	// 		$marca,
	// 		$descrip,
	// 		$costo_compra,
	// 		$precio_venta,
	// 		$stock,
	// 		$saldo_iniu,
	// 		$valor_iniu,
	// 		$tipoitem,
	// 		$codigott,
	// 		$desctt,
	// 		$codigointtt,
	// 		$nombrett,
	// 		$nombre_almacen,
	// 		$saldo_finu
	// 	);

	// 	echo $rspta ? "Artículos insertados exitosamente" : "Error al insertar artículos";
	// 	break;


}

?>