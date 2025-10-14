<?php
// SEGURIDAD: Cargar helpers de validación y CSRF
require_once "../config/ajax_helper.php";
require_once "../modelos/Persona.php";
$persona = new Persona();

$idpersona = isset($_POST["idpersona"]) ? limpiarCadena($_POST["idpersona"]) : "";
$tipo_persona = isset($_POST["tipo_persona"]) ? limpiarCadena($_POST["tipo_persona"]) : "";
$nombres = isset($_POST["nombres"]) ? limpiarCadena($_POST["nombres"]) : "";
$apellidos = isset($_POST["apellidos"]) ? limpiarCadena($_POST["apellidos"]) : "";
$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
$numero_documento = isset($_POST["numero_documento"]) ? limpiarCadena($_POST["numero_documento"]) : "";
$nruc = isset($_POST["numero_documento3"]) ? limpiarCadena($_POST["numero_documento3"]) : ""; //Viene de nuevo cliente
$razon_social = isset($_POST["razon_social"]) ? limpiarCadena($_POST["razon_social"]) : "";
$nombre_comercial = isset($_POST["nombre_comercial"]) ? limpiarCadena($_POST["nombre_comercial"]) : "";
$domicilio_fiscal = isset($_POST["domicilio_fiscal"]) ? limpiarCadena($_POST["domicilio_fiscal"]) : "";
$departamento = isset($_POST["iddepartamento"]) ? limpiarCadena($_POST["iddepartamento"]) : "";
$ciudad = isset($_POST["idciudad"]) ? limpiarCadena($_POST["idciudad"]) : "";
$distrito = isset($_POST["iddistrito"]) ? limpiarCadena($_POST["iddistrito"]) : "";
$telefono1 = isset($_POST["telefono1"]) ? limpiarCadena($_POST["telefono1"]) : "";
$telefono2 = isset($_POST["telefono2"]) ? limpiarCadena($_POST["telefono2"]) : "";
$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";


$razon_social3 = isset($_POST["razon_social3"]) ? limpiarCadena($_POST["razon_social3"]) : "";
$nombre_comercial3 = isset($_POST["razon_social3"]) ? limpiarCadena($_POST["razon_social3"]) : "";
$domicilio_fiscal3 = isset($_POST["domicilio_fiscal3"]) ? limpiarCadena($_POST["domicilio_fiscal3"]) : "";

switch ($_GET["op"]) {

	case 'guardaryeditar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar email si se proporciona
		if (!empty($email)) {
			$email_validado = validarEmail($email);
			if ($email_validado === false) {
				echo "Error: Email inválido";
				exit();
			}
			$email = $email_validado;
		}

		// SEGURIDAD: Validar número de documento según tipo
		if ($tipo_documento == '1') { // DNI
			$doc_validado = validarDNI($numero_documento);
			if ($doc_validado === false) {
				echo "Error: DNI inválido (debe tener 8 dígitos)";
				exit();
			}
			$numero_documento = $doc_validado;
		} elseif ($tipo_documento == '6') { // RUC
			$doc_validado = validarRUC($numero_documento);
			if ($doc_validado === false) {
				echo "Error: RUC inválido (debe tener 11 dígitos)";
				exit();
			}
			$numero_documento = $doc_validado;
		}

		if (empty($idpersona)) {
			$rspta = $persona->insertar($tipo_persona, htmlspecialchars_decode($nombres), htmlspecialchars_decode($apellidos), $tipo_documento, $numero_documento, htmlspecialchars_decode($razon_social), htmlspecialchars_decode($nombre_comercial), htmlspecialchars_decode($domicilio_fiscal), $departamento, $ciudad, $distrito, $telefono1, $telefono2, htmlspecialchars_decode($email));

			if ($rspta) {
				// ========== AUDITORÍA: Registrar creación de persona ==========
				registrarOperacionCreate('persona', $numero_documento, [
					'tipo_persona' => $tipo_persona,
					'tipo_documento' => $tipo_documento,
					'nombres' => $nombres,
					'apellidos' => $apellidos,
					'razon_social' => $razon_social,
					'nombre_comercial' => $nombre_comercial,
					'telefono1' => $telefono1,
					'email' => $email
				], "{$tipo_persona} creado/a: {$razon_social} (Doc: {$numero_documento})");

				echo "Registro correcto";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('CREATE', 'persona', [
					'descripcion' => "Intento fallido de crear {$tipo_persona}: {$razon_social}",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_CREAR_PERSONA',
					'mensaje_error' => 'No se pudo registrar la persona',
					'metadata' => [
						'tipo_persona' => $tipo_persona,
						'documento' => $numero_documento
					]
				]);

				echo "No se pudo registrar";
			}
		} else {
			$rspta = $persona->editar($idpersona, $tipo_persona, $nombres, $apellidos, $tipo_documento, $numero_documento, $razon_social, $nombre_comercial, $domicilio_fiscal, $departamento, $ciudad, $distrito, $telefono1, $telefono2, $email);

			if ($rspta) {
				// ========== AUDITORÍA: Registrar actualización de persona ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "{$tipo_persona} actualizado/a: {$razon_social} (ID: {$idpersona})",
					'metadata' => [
						'tipo_persona' => $tipo_persona,
						'documento' => $numero_documento,
						'razon_social' => $razon_social,
						'telefono1' => $telefono1,
						'email' => $email
					]
				]);

				echo "Registro actualizado";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "Intento fallido de actualizar {$tipo_persona} (ID: {$idpersona})",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_ACTUALIZAR_PERSONA',
					'mensaje_error' => 'No se pudo actualizar la persona'
				]);

				echo "No se pudo actualizar";
			}
		}
		break;

	case 'guardaryeditarnproveedor':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar RUC
		$doc_validado = validarRUC($numero_documento);
		if ($doc_validado === false) {
			echo "Error: RUC inválido (debe tener 11 dígitos)";
			exit();
		}
		$numero_documento = $doc_validado;

		$rspta = $persona->insertarnproveedor($tipo_persona, $numero_documento, htmlspecialchars_decode($razon_social));

		if ($rspta) {
			// ========== AUDITORÍA: Registrar creación de nuevo proveedor ==========
			registrarOperacionCreate('persona', $numero_documento, [
				'tipo_persona' => $tipo_persona,
				'tipo_documento' => 'RUC',
				'razon_social' => $razon_social,
				'metodo_creacion' => 'insertarnproveedor'
			], "Nuevo proveedor creado: {$razon_social} (RUC: {$numero_documento})");

			echo "Registro correcto";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CREATE', 'persona', [
				'descripcion' => "Intento fallido de crear nuevo proveedor: {$razon_social}",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_CREAR_PROVEEDOR',
				'mensaje_error' => 'No se pudo registrar el proveedor',
				'metadata' => [
					'ruc' => $numero_documento
				]
			]);

			echo "No se pudo registrar";
		}

		break;



	case 'guardaryeditarNcliente':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar email si se proporciona
		if (!empty($email)) {
			$email_validado = validarEmail($email);
			if ($email_validado === false) {
				echo "Error: Email inválido";
				exit();
			}
			$email = $email_validado;
		}

		// SEGURIDAD: Validar documento
		if ($tipo_documento == '1') { // DNI
			$doc_validado = validarDNI($nruc);
			if ($doc_validado === false) {
				echo "Error: DNI inválido";
				exit();
			}
			$nruc = $doc_validado;
		} elseif ($tipo_documento == '6') { // RUC
			$doc_validado = validarRUC($nruc);
			if ($doc_validado === false) {
				echo "Error: RUC inválido";
				exit();
			}
			$nruc = $doc_validado;
		}

		if (empty($idpersona)) {
			$rspta = $persona->insertar($tipo_persona, htmlspecialchars_decode($nombres), htmlspecialchars_decode($apellidos), $tipo_documento, $nruc, htmlspecialchars_decode($razon_social), htmlspecialchars_decode($nombre_comercial), htmlspecialchars_decode($domicilio_fiscal), $departamento, $ciudad, $distrito, $telefono1, $telefono2, htmlspecialchars_decode($email));

			if ($rspta) {
				// ========== AUDITORÍA: Registrar creación de nuevo cliente ==========
				registrarOperacionCreate('persona', $nruc, [
					'tipo_persona' => $tipo_persona,
					'tipo_documento' => $tipo_documento,
					'nombres' => $nombres,
					'apellidos' => $apellidos,
					'razon_social' => $razon_social,
					'telefono1' => $telefono1,
					'email' => $email,
					'metodo_creacion' => 'guardaryeditarNcliente'
				], "Nuevo cliente creado: {$razon_social} (Doc: {$nruc})");

				echo "Registro correcto";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('CREATE', 'persona', [
					'descripcion' => "Intento fallido de crear nuevo cliente: {$razon_social}",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_CREAR_CLIENTE',
					'mensaje_error' => 'No se pudo registrar el cliente',
					'metadata' => [
						'documento' => $nruc
					]
				]);

				echo "No se pudo registrar";
			}
		} else {
			$rspta = $persona->editar($idpersona, $tipo_persona, htmlspecialchars_decode($nombres), htmlspecialchars_decode($apellidos), $tipo_documento, $nruc, htmlspecialchars_decode($razon_social), htmlspecialchars_decode($nombre_comercial), htmlspecialchars_decode($domicilio_fiscal), $departamento, $ciudad, $distrito, $telefono1, $telefono2, htmlspecialchars_decode($email));

			if ($rspta) {
				// ========== AUDITORÍA: Registrar actualización de cliente ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "Cliente actualizado: {$razon_social} (ID: {$idpersona})",
					'metadata' => [
						'tipo_persona' => $tipo_persona,
						'documento' => $nruc,
						'razon_social' => $razon_social,
						'telefono1' => $telefono1
					]
				]);

				echo "Registro actualizado";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "Intento fallido de actualizar cliente (ID: {$idpersona})",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_ACTUALIZAR_CLIENTE',
					'mensaje_error' => 'No se pudo actualizar el cliente'
				]);

				echo "No se pudo actualizar";
			}
		}
	break;



	case 'guardaryeditarNclienteBoleta':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar email si se proporciona
		if (!empty($email)) {
			$email_validado = validarEmail($email);
			if ($email_validado === false) {
				echo "Error: Email inválido";
				exit();
			}
			$email = $email_validado;
		}

		// SEGURIDAD: Validar DNI
		$doc_validado = validarDNI($nruc);
		if ($doc_validado === false) {
			echo "Error: DNI inválido (debe tener 8 dígitos)";
			exit();
		}
		$nruc = $doc_validado;

		if (empty($idpersona)) {
			$rspta = $persona->insertar($tipo_persona, htmlspecialchars_decode($nombre_comercial3), htmlspecialchars_decode($nombre_comercial3), $tipo_documento, $nruc, htmlspecialchars_decode($razon_social3), htmlspecialchars_decode($nombre_comercial3), htmlspecialchars_decode($domicilio_fiscal3), '14', '43', '96', $telefono1, $telefono2, htmlspecialchars_decode($email));

			if ($rspta) {
				// ========== AUDITORÍA: Registrar creación de cliente desde boleta ==========
				registrarOperacionCreate('persona', $nruc, [
					'tipo_persona' => $tipo_persona,
					'tipo_documento' => $tipo_documento,
					'razon_social' => $razon_social3,
					'nombre_comercial' => $nombre_comercial3,
					'domicilio_fiscal' => $domicilio_fiscal3,
					'telefono1' => $telefono1,
					'email' => $email,
					'metodo_creacion' => 'guardaryeditarNclienteBoleta'
				], "Cliente creado desde boleta: {$razon_social3} (DNI: {$nruc})");

				echo "Registro correcto";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('CREATE', 'persona', [
					'descripcion' => "Intento fallido de crear cliente desde boleta: {$razon_social3}",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_CREAR_CLIENTE_BOLETA',
					'mensaje_error' => 'No se pudo registrar el cliente desde boleta',
					'metadata' => [
						'dni' => $nruc
					]
				]);

				echo "No se pudo registrar";
			}
		} else {
			$rspta = $persona->editar($idpersona, $tipo_persona, htmlspecialchars_decode($nombre_comercial3), htmlspecialchars_decode($nombre_comercial3), $tipo_documento, $nruc, htmlspecialchars_decode($razon_social3), htmlspecialchars_decode($nombre_comercial3), htmlspecialchars_decode($domicilio_fiscal3), $departamento, $ciudad, $distrito, $telefono1, $telefono2, htmlspecialchars_decode($email));

			if ($rspta) {
				// ========== AUDITORÍA: Registrar actualización de cliente desde boleta ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "Cliente actualizado desde boleta: {$razon_social3} (ID: {$idpersona})",
					'metadata' => [
						'dni' => $nruc,
						'razon_social' => $razon_social3,
						'origen' => 'boleta'
					]
				]);

				echo "Registro actualizado";
			} else {
				// ========== AUDITORÍA: Registrar intento fallido ==========
				registrarAuditoria('UPDATE', 'persona', [
					'registro_id' => $idpersona,
					'descripcion' => "Intento fallido de actualizar cliente desde boleta (ID: {$idpersona})",
					'resultado' => 'FALLIDO',
					'codigo_error' => 'ERROR_ACTUALIZAR_CLIENTE_BOLETA',
					'mensaje_error' => 'No se pudo actualizar el cliente desde boleta'
				]);

				echo "No se pudo actualizar";
			}
		}
		break;


	case 'eliminar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar ID
		$idpersona = validarEntero($idpersona, 1);
		if ($idpersona === false) {
			echo "Error: ID de persona inválido";
			exit();
		}

		$rspta = $persona->eliminar($idpersona);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar eliminación de persona ==========
			registrarAuditoria('DELETE', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Persona eliminada (ID: {$idpersona})"
			]);

			echo "Persona desactivada";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('DELETE', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Intento fallido de eliminar persona (ID: {$idpersona})",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_ELIMINAR_PERSONA',
				'mensaje_error' => 'No se puede eliminar la persona'
			]);

			echo "Persona no se puede activar";
		}
		break;

	case 'mostrar':
		$rspta = $persona->mostrar($idpersona);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;
	//quitar id persona por validacion
	case 'mostrarClienteVarios':
		$rspta = $persona->mostrarIdVarios($idpersona);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;


	case 'desactivar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar ID
		$idpersona = validarEntero($idpersona, 1);
		if ($idpersona === false) {
			echo "Error: ID de persona inválido";
			exit();
		}

		$rspta = $persona->desactivar($idpersona);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar desactivación de persona ==========
			registrarAuditoria('CAMBIO_ESTADO', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Persona desactivada (ID: {$idpersona})",
				'metadata' => [
					'estado_nuevo' => 'Inactivo'
				]
			]);

			echo "Persona Desactivado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CAMBIO_ESTADO', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Intento fallido de desactivar persona (ID: {$idpersona})",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_DESACTIVAR_PERSONA',
				'mensaje_error' => 'No se puede desactivar la persona'
			]);

			echo "Persona no se puede desactivar";
		}
		break;


	case 'activar':
		// SEGURIDAD: Validar token CSRF
		if (!validarCSRFAjax()) {
			echo "Error: Token de seguridad inválido";
			exit();
		}

		// SEGURIDAD: Validar ID
		$idpersona = validarEntero($idpersona, 1);
		if ($idpersona === false) {
			echo "Error: ID de persona inválido";
			exit();
		}

		$rspta = $persona->activar($idpersona);

		if ($rspta) {
			// ========== AUDITORÍA: Registrar activación de persona ==========
			registrarAuditoria('CAMBIO_ESTADO', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Persona activada (ID: {$idpersona})",
				'metadata' => [
					'estado_nuevo' => 'Activo'
				]
			]);

			echo "Persona activado";
		} else {
			// ========== AUDITORÍA: Registrar intento fallido ==========
			registrarAuditoria('CAMBIO_ESTADO', 'persona', [
				'registro_id' => $idpersona,
				'descripcion' => "Intento fallido de activar persona (ID: {$idpersona})",
				'resultado' => 'FALLIDO',
				'codigo_error' => 'ERROR_ACTIVAR_PERSONA',
				'mensaje_error' => 'No se puede activar la persona'
			]);

			echo "Persona no se puede activar";
		}
		break;


	case 'listarp':
		$rspta = $persona->listarp();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			
			$data[] = array(
			    
				"0" => $reg->razon_social,
				"1" => $reg->numero_documento,
				"2" => $reg->telefono1,
				"3" => $reg->email,
	    		"4" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' : 
	    		    '<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
	    		    
    			"5" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idpersona . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idpersona . ')"><i class="ri-delete-bin-line"></i></button>' :
					'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idpersona . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idpersona . ')"><i class="ri-check-double-line"></i></button>',
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

	case 'listarc':
		$rspta = $persona->listarc();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => htmlspecialchars_decode($reg->razon_social),
				"1" => $reg->descripcion,
				"2" => $reg->numero_documento,
				"3" => $reg->telefono1,
				"4" => $reg->email,
				"5" => ($reg->estado) ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Activo</span>' : '<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Inhabilitado</span>',
				"6" => ($reg->estado) ? '<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idpersona . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-danger" onclick="desactivar(' . $reg->idpersona . ')"><i class="ri-delete-bin-line"></i></button>' :
					'<button class="btn btn-icon btn-sm btn-info" onclick="mostrar(' . $reg->idpersona . ')"><i class="ri-edit-line"></i></button>' .
					' <button class="btn btn-icon btn-sm btn-success" onclick="activar(' . $reg->idpersona . ')"><i class="ri-check-double-line"></i></button>'
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





	// Carga de tipos de documentos para venta
	case 'selectDepartamento':
		require_once "../modelos/Departamento.php";
		$departamento = new Departamento();

		$rspta = $departamento->selectD();
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->iddepartamento . '>' . $reg->nombre . '</option>';
		}
		break;

	// Carga de tipos de documentos para venta
	case 'selectDepartamentoModificar':
		require_once "../modelos/Departamento.php";
		$departamento = new Departamento();

		$id = $_GET['id'];
		$rspta = $departamento->selectID($id);
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->iddepartamento . '>' . $reg->nombre . '</option>';
		}
		break;



	case 'selectCiudad':
		require_once "../modelos/Ciudad.php";
		$ciudad = new Ciudad();

		$id = $_GET['id'];
		$rspta = $ciudad->selectC($id);

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idciudad . '>' . $reg->nombre . '</option>';

		}
		break;

	case 'selectDistrito':
		require_once "../modelos/Distrito.php";
		$distrito = new Distrito();
		$id = $_GET['id'];
		$rspta = $distrito->selectDI($id);

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->iddistrito . '>' . $reg->nombre . '</option>';

		}
		break;


	case 'ValidarCliente':

		$ndocumento = $_GET['ndocumento'];
		$rspta = $persona->validarCliente($ndocumento);
		echo json_encode($rspta); // ? "Cliente ya existe": "Documento valido";
		break;


	case 'ValidarProveedor':

		$ndocumento = $_GET['ndocumento'];
		$rspta = $persona->validarProveedor($ndocumento);
		echo json_encode($rspta); // ? "Cliente ya existe": "Documento valido";
		break;

	case 'selectCliente':
		require_once "../modelos/Persona.php";
		$persona = new Persona();

		$rspta = $persona->listarc();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idpersona . '>' . $reg->numero_documento . '</option>';
		}
		break;


		case 'buscarclienteRuc':
			$key = $_POST['key'];
			$rspta = $persona->buscarCliente($key); // Llama a la función buscarCliente en lugar de buscarclienteRuc
			echo json_encode($rspta);
			break;
		
	case 'buscarclienteDomicilio':
		$key = $_POST['key'];
		$rspta = $persona->buscarclientenombre($key);
		echo json_encode($rspta); // ? "Cliente ya existe": "Documento valido";
		break;


	case 'combocliente':
		$rpta = $persona->combocliente();
		while ($reg = $rpta->fetch_object()) {
			echo '<option value=' . $reg->numero_documento . '>' . $reg->numero_documento . ' | ' . $reg->nombre_comercial . '</option>';
		}
		break;

	case 'comboclientenoti':
		$rpta = $persona->combocliente();
		while ($reg = $rpta->fetch_object()) {
			echo '<option value=' . $reg->idpersona . '>' . $reg->numero_documento . ' | ' . $reg->nombre_comercial . '</option>';
		}
		break;



}
?>