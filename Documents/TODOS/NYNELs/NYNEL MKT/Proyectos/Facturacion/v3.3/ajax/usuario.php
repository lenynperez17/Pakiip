<?php
// NOTA: session_start() ya se ejecuta automáticamente en Conexion.php mediante iniciarSesionSegura()
// No es necesario llamarlo aquí de nuevo
require_once "../modelos/Usuario.php";
$usuario = new Usuario();

$idusuario = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : "";

$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$apellidos = isset($_POST["apellidos"]) ? limpiarCadena($_POST["apellidos"]) : "";
$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
$num_documento = isset($_POST["num_documento"]) ? limpiarCadena($_POST["num_documento"]) : "";
$direccion = isset($_POST["direccion"]) ? limpiarCadena($_POST["direccion"]) : "";
$telefono = isset($_POST["telefono"]) ? limpiarCadena($_POST["telefono"]) : "";
$email = isset($_POST["email"]) ? limpiarCadena($_POST["email"]) : "";
$cargo = isset($_POST["cargo"]) ? limpiarCadena($_POST["cargo"]) : "";
$login = isset($_POST["login"]) ? limpiarCadena($_POST["login"]) : "";
$clave = isset($_POST["clave"]) ? limpiarCadena($_POST["clave"]) : "";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";


switch ($_GET["op"]) {
    case 'guardaryeditar':

        if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
            $imagen = $_POST["imagenactual"];
        } else {
            $ext = explode(".", $_FILES["imagen"]["name"]);
            if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png") {
                $imagen = round(microtime(true)) . '.' . end($ext);
                move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/usuarios/" . $imagen);
            }
        }
        if (empty($clave)) {
            // Si la variable $clave está vacía, se obtiene la clave actual del usuario y se usa para actualizar el registro
            $usuario_actual = $usuario->mostrar($idusuario);
            $clavehash = $usuario_actual['clave'];
        } else {
            // Si la variable $clave tiene un valor, hashear con bcrypt (seguro)
            // SEGURIDAD: Usar bcrypt con cost 12 para passwords seguros
            $clavehash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($idusuario)) {
            $rspta = $usuario->insertar($nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clavehash, $imagen, $_POST['permiso'], $_POST['serie'], $_POST['empresa']);
            echo $rspta ? "Usuario registrado" : "No se pudieron registrar todos los datos del usuario";
        } else {
            $rspta = $usuario->editar($idusuario, $nombre, $apellidos, $tipo_documento, $num_documento, $direccion, $telefono, $email, $cargo, $login, $clavehash, $imagen, $_POST['permiso'], $_POST['serie'], $_POST['empresa']);
            echo $rspta ? "Usuario actualizado" : "No se pudieron actualizar todos los datos del usuario";
        }
        break;

    case 'desactivar':
        $rspta = $usuario->desactivar($idusuario);
        echo $rspta ? "Usuario Desactivado" : "Usuario no se puede desactivar";
        break;


    case 'activar':
        $rspta = $usuario->activar($idusuario);
        echo $rspta ? "Usuario activado" : "Usuario no se puede activar";
        break;


    case 'mostrar':
        $rspta = $usuario->mostrar($idusuario);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;


    case 'listar':
        $rspta = $usuario->listar();
        //Vamos a declarar un array

        $data = array();
        while ($reg = $rspta->fetch_object()) {
            // Mapear el valor numérico a su respectiva descripción
            $cargo = '';
            switch ($reg->cargo) {
                case 0:
                    $cargo = "Administrador";
                    break;
                case 1:
                    $cargo = "Ventas";
                    break;
                case 2:
                    $cargo = "Logistica";
                    break;
                case 3:
                    $cargo = "Contabilidad";
                    break;
            }
            $data[] = array(
                "0" => ($reg->condicion) ? '<button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-warning-light" onclick="mostrar(' . $reg->idusuario . ')"><i class="fa fa-pencil"></i></button>' .
                    ' <button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-danger-light" onclick="desactivar(' . $reg->idusuario . ')"><i class="fa fa-close"></i></button>' :
                    '<button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-warning-light" onclick="mostrar(' . $reg->idusuario . ')"><i class="fa fa-pencil"></i></button>' .
                    ' <button class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-primary-light" onclick="activar(' . $reg->idusuario . ')"><i class="fa fa-check"></i></button>',
                "1" => $reg->login,
                "2" => $reg->nombre,
                "3" => $reg->apellidos,
                "4" => $cargo,
                "5" => $reg->tipo_documento,
                "6" => $reg->num_documento,
                "7" => $reg->telefono,
                "8" => $reg->email,
                "9" => "<img src='../files/usuarios/" . $reg->imagen . "' height='50px' width='50px' >",
                "10" => ($reg->condicion) ? '<span class="badge bg-success-transparent">Activado</span>' :
                    '<span class="badge bg-danger-transparent">Inhabilitado</span>'
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

    case 'permisos':
        //Obtenemos todos los permisos de la tabla permisos
        require_once "../modelos/Permiso.php";
        $permiso = new Permiso();
        $rspta = $permiso->listar();

        //Obtener los permisos asignados al usuario
        $id = $_GET['id'];
        $marcados = $usuario->listarmarcados($id);
        //Declaramos el array para almacenar todos los permisos marcados
        $valores = array();

        //Almacenar los permisos asignados al usuario en el array
        while ($per = $marcados->fetch_object()) {
            array_push($valores, $per->idpermiso);
        }

        //Mostramos la lista de permisos en la vista y si están o no marcados
        while ($reg = $rspta->fetch_object()) {
            $sw = in_array($reg->idpermiso, $valores) ? 'checked' : '';
            echo '<li> <input type="checkbox" ' . $sw . '  name="permiso[]" value="' . $reg->idpermiso . '">' . $reg->nombre . '</li>';
        }
        break;

    case 'permisosEmpresa':
        //Obtenemos todos los permisos de la tabla permisos
        require_once "../modelos/Permiso.php";
        $permiso = new Permiso();
        $rspta = $permiso->listarEmpresa();

        //Obtener los permisos asignados al usuario
        $id = $_GET['id'];
        $marcados = $usuario->listarmarcadosEmpresa($id);
        //Declaramos el array para almacenar todos los permisos marcados
        $valores = array();

        //Almacenar los permisos asignados al usuario en el array
        while ($per = $marcados->fetch_object()) {
            array_push($valores, $per->idempresa);
        }

        //Mostramos la lista de permisos en la vista y si están o no marcados
        while ($reg = $rspta->fetch_object()) {
            $sw = in_array($reg->idempresa, $valores) ? 'checked' : '';
            echo '<li> <input type="checkbox" ' . $sw . '  name="empresa[]" value="' . $reg->idempresa . '">' . $reg->nombre_razon_social . '</li>';
        }
        break;


    case 'permisosEmpresaTodos':
        //Obtenemos todos los permisos de la tabla permisos
        require_once "../modelos/Permiso.php";
        $permiso = new Permiso();
        $rspta = $permiso->listarEmpresa();
        $marcados = $usuario->listarmarcadosEmpresaTodos();
        //Declaramos el array para almacenar todos los permisos marcados
        $valores = array();

        //Almacenar los permisos asignados al usuario en el array
        while ($per = $marcados->fetch_object()) {
            array_push($valores, $per->idempresa);
        }

        //Mostramos la lista de permisos en la vista y si están o no marcados
        while ($reg = $rspta->fetch_object()) {
            //$sw=in_array($reg->idempresa,$valores)?'checked':'';
            echo '<li> <input type="checkbox"  name="empresa[]" value="' . $reg->idempresa . '">' . $reg->nombre_razon_social . '</li>';
        }
        break;


    case 'series':
        //Obtenemos todos los permisos de la tabla permisos
        require_once "../modelos/Numeracion.php";
        $numeracion = new Numeracion();
        $rspta = $numeracion->listarSeries();

        //Obtener los permisos asignados al usuario
        $id = $_GET['id'];
        $marcados = $usuario->listarmarcadosNumeracion($id);
        //Declaramos el array para almacenar todos los permisos marcados
        $valores = array();

        //Almacenar los permisos asignados al usuario en el array
        while ($per = $marcados->fetch_object()) {
            array_push($valores, $per->idnumeracion);
        }



        //Mostramos la lista de permisos en la vista y si están o no marcados
        while ($reg = $rspta->fetch_object()) {
            $nombres = "";
            switch ($reg->tipo_documento) {
                case '01':
                    $nombres = "FACTURA";
                    break;
                case '03':
                    $nombres = "BOLETA";
                    break;
                case '07':
                    $nombres = "NOTA DE CRÉDITO";
                    break;
                case '08':
                    $nombres = "NOTA DE DEBITO";
                    break;
                case '09':
                    $nombres = "GUIA REMISION REMITENTE";
                    break;
                case '12':
                    $nombres = "TICKET DE MAQUINA REGISTRADORA";
                    break;
                case '13':
                    $nombres = "DOCUM. EMIT. POR BANC. & SEG.";
                    break;
                case '18':
                    $nombres = "SBS";
                    break;
                case '31':
                    $nombres = "DOC. EMIT. POR AFP";
                    break;
                case '50':
                    $nombres = "NOTA DE PEDIDO";
                    break;
                case '56':
                    $nombres = "GUIA REMISION TRANSPOR.";
                    break;
                case '99':
                    $nombres = "ORDEN DE SERVICIO";
                case '20':
                    $nombres = "COTIZACION";
                    break;
                case '30':
                    $nombres = "DOCUMENTO COBRANZA";
                    break;
                case '90':
                    $nombres = "BOLETAS DE PAGO";
                    break;
                default:
                    # code...
                    break;
            }
            $sw = in_array($reg->idnumeracion, $valores) ? 'checked' : '';
            echo '<li> <input type="checkbox" ' . $sw . ' name="serie[]" value="' . $reg->idnumeracion . '">' . $reg->serie . '-' . $nombres . ' </li>';
        }
        break;


    case 'seriesnuevo':
        //Obtenemos todos los permisos de la tabla permisos
        require_once "../modelos/Numeracion.php";
        $numeracion = new Numeracion();
        $rspta = $numeracion->listarSeriesNuevo();
        //Mostramos la lista de permisos en la vista y si están o no marcados
        while ($reg = $rspta->fetch_object()) {
            $nombres = "";
            switch ($reg->tipo_documento) {
                case '01':
                    $nombres = "FACTURA";
                    break;
                case '03':
                    $nombres = "BOLETA";
                    break;
                case '07':
                    $nombres = "NOTA DE CRÉDITO";
                    break;
                case '08':
                    $nombres = "NOTA DE DEBITO";
                    break;
                case '09':
                    $nombres = "GUIA REMISION REMITENTE";
                    break;
                case '12':
                    $nombres = "TICKET DE MAQUINA REGISTRADORA";
                    break;
                case '13':
                    $nombres = "DOCUM. EMIT. POR BANC. & SEG.";
                    break;
                case '18':
                    $nombres = "SBS";
                    break;
                case '31':
                    $nombres = "DOC. EMIT. POR AFP";
                    break;
                case '50':
                    $nombres = "NOTA DE PEDIDO";
                    break;
                case '56':
                    $nombres = "GUIA REMISION TRANSPOR.";
                    break;
                case '99':
                    $nombres = "ORDEN DE SERVICIO";
                case '20':
                    $nombres = "COTIZACION";
                    break;
                case '30':
                    $nombres = "DOCUMENTO COBRANZA";
                    break;
                case '90':
                    $nombres = "BOLETAS DE PAGO";
                    break;
                default:
                    # code...
                    break;
            }

            echo '<li> <input type="checkbox" name="serie[]" value="' . $reg->idnumeracion . '">' . $reg->serie . '-' . $nombres . ' </li>';
        }
        break;

    case 'verificar':

        $logina = $_POST['logina'];
        $clavea = $_POST['clavea'];
        $empresa = $_POST['empresa'];
        $st = isset($_POST['st']) ? $_POST['st'] : 0;

        // ========== RATE LIMITING - Prevención de ataques de fuerza bruta ==========
        // Generar identificador único basado en IP
        $rate_limit_id = generarIdentificadorRateLimit('login');

        // Verificar límite de intentos: máximo 5 intentos en 15 minutos (900 segundos)
        $rate_limit = rateLimitCheck($rate_limit_id, 5, 900);

        if (!$rate_limit['permitido']) {
            $minutos_espera = ceil($rate_limit['tiempo_espera'] / 60);

            error_log("RATE LIMIT EXCEDIDO en login - IP: " . $_SERVER['REMOTE_ADDR'] .
                      " - Usuario: " . $logina .
                      " - Intentos: " . $rate_limit['intentos'] .
                      " - Tiempo de espera: " . $minutos_espera . " minutos");

            // Redirigir con mensaje de error específico
            header("Location: ../vistas/login.php?error=rate_limit&tiempo=" . $minutos_espera);
            exit();
        }

        // VALIDACIÓN CSRF - Protección contra Cross-Site Request Forgery
        $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

        if (!validarTokenCSRF($csrf_token)) {
            error_log("CSRF Attack detectado en login - IP: " . $_SERVER['REMOTE_ADDR'] . " - Usuario: " . $logina);
            header("Location: ../vistas/login.php?error=csrf");
            exit();
        }

        // DEBUG: Log inicio de verificación
        file_put_contents("/tmp/login_debug.log", date("Y-m-d H:i:s") . " - Inicio verificación - Usuario: $logina\n", FILE_APPEND);

        // Obtener datos del usuario (sin verificar password en SQL - previene SQL injection)
        $rspta = $usuario->verificar($logina);
        $fetch = $rspta->fetch_object();

        // DEBUG: Log resultado de consulta
        file_put_contents("/tmp/login_debug.log", date("Y-m-d H:i:s") . " - Usuario encontrado: " . (isset($fetch) ? "SI" : "NO") . "\n", FILE_APPEND);

        // Verificar si el usuario existe
        if (isset($fetch)) {
            // DEBUG: Log tipo de hash
            file_put_contents("/tmp/login_debug.log", date("Y-m-d H:i:s") . " - Hash inicio: " . substr($fetch->clave, 0, 10) . "\n", FILE_APPEND);
            // Sistema híbrido de verificación de passwords: SHA1/SHA256 → bcrypt
            $password_valido = false;

            // Verificar si el hash en BD es bcrypt (comienza con $2y$) o hash legacy (SHA1/SHA256)
            if (substr($fetch->clave, 0, 4) === '$2y$' || substr($fetch->clave, 0, 4) === '$2a$') {
                // DEBUG: Log antes de verificar bcrypt
                file_put_contents("/tmp/login_debug.log", date("Y-m-d H:i:s") . " - Usando bcrypt para verificar\n", FILE_APPEND);

                // Password ya migrado a bcrypt - verificación segura
                $password_valido = password_verify($clavea, $fetch->clave);

                // DEBUG: Log resultado de verificación
                file_put_contents("/tmp/login_debug.log", date("Y-m-d H:i:s") . " - Password válido (bcrypt): " . ($password_valido ? "SI" : "NO") . "\n", FILE_APPEND);
            } else {
                // Password legacy con SHA1 o SHA256 - verificar y migrar automáticamente
                $hash_legacy_valido = false;
                $tipo_hash = '';

                // Verificar SHA1 (40 caracteres hex)
                if (strlen($fetch->clave) == 40 && sha1($clavea) === $fetch->clave) {
                    $hash_legacy_valido = true;
                    $tipo_hash = 'SHA1';
                }
                // Verificar SHA256 (64 caracteres hex)
                elseif (strlen($fetch->clave) == 64 && hash('sha256', $clavea) === $fetch->clave) {
                    $hash_legacy_valido = true;
                    $tipo_hash = 'SHA256';
                }

                if ($hash_legacy_valido) {
                    $password_valido = true;

                    // Migración automática a bcrypt (transparente para el usuario)
                    $nuevo_hash = password_hash($clavea, PASSWORD_BCRYPT, ['cost' => 12]);
                    $usuario->actualizarPassword($fetch->idusuario, $nuevo_hash);

                    // Log de la migración
                    error_log("Password migrado de {$tipo_hash} a bcrypt para usuario: " . $fetch->login);
                }
            }

            // Si el password es válido, continuar con la creación de sesión
            if ($password_valido) {
                // Actualizar estado del temporizador
                $rspta2 = $usuario->onoffTempo($st);
                $rspta3 = $usuario->consultatemporizador();
                $fetch3 = $rspta3->fetch_object();
            //Declaramos las variables de sesión
            $_SESSION['idusuario'] = $fetch->idusuario;
            $_SESSION['nombre'] = $fetch->nombre;
            $_SESSION['imagen'] = $fetch->imagen;
            $_SESSION['login'] = $fetch->login;

            $_SESSION['empresa'] = $fetch->nombre_razon_social;
            $_SESSION['idempresa'] = $fetch->idempresa;
            $_SESSION['estadotempo'] = $fetch3->estado;
            $_SESSION['estado'] = 'Activo'; // Estado del usuario
            $_SESSION['iva'] = $fetch->igv;

            $_SESSION['ruc'] = $fetch->numero_ruc;
            $_SESSION['nombreemp'] = $fetch->nombre_comercial;
            $_SESSION['domicilio'] = $fetch->domicilio_fiscal;

            //Obtenemos los permisos del usuario
            $marcados = $usuario->listarmarcados($fetch->idusuario);
            $usuario->savedetalsesion($fetch->idusuario);

            //Declaramos el array para almacenar todos los permisos marcados
            $valores = array();

            //Almacenamos los permisos marcados en el array
            while ($per = $marcados->fetch_object()) {
                array_push($valores, $per->idpermiso);
            }

            //Determinamos los accesos del usuario
            in_array(1, $valores) ? $_SESSION['Dashboard'] = 1 : $_SESSION['Dashboard'] = 0;
            in_array(2, $valores) ? $_SESSION['Logistica'] = 1 : $_SESSION['Logistica'] = 0;
            in_array(3, $valores) ? $_SESSION['Ventas'] = 1 : $_SESSION['Ventas'] = 0;
            in_array(4, $valores) ? $_SESSION['Contabilidad'] = 1 : $_SESSION['Contabilidad'] = 0;
            in_array(5, $valores) ? $_SESSION['RRHH'] = 1 : $_SESSION['RRHH'] = 0;
            //in_array(6,$valores)?$_SESSION['consultac']=1:$_SESSION['consultac']=0;
            //in_array(7,$valores)?$_SESSION['consultav']=1:$_SESSION['consultav']=0;
            //===============================================
            in_array(8, $valores) ? $_SESSION['Configuracion'] = 1 : $_SESSION['Configuracion'] = 0;
            //in_array(9, $valores) ? $_SESSION['kardex'] = 1 : $_SESSION['kardex'] = 0;
            //in_array(10, $valores) ? $_SESSION['boletapago'] = 1 : $_SESSION['boletapago'] = 0;

            // DEBUG: Ver qué session_id se creó
            file_put_contents("/tmp/session_debug.log", date("Y-m-d H:i:s") . " - USUARIO.PHP - Login exitoso - Session ID: " . session_id() . "\n", FILE_APPEND);
            file_put_contents("/tmp/session_debug.log", date("Y-m-d H:i:s") . " - USUARIO.PHP - Session nombre: " . $_SESSION['nombre'] . "\n", FILE_APPEND);
            file_put_contents("/tmp/session_debug.log", date("Y-m-d H:i:s") . " - USUARIO.PHP - Session data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

                // Regenerar token CSRF después de login exitoso (prevenir reutilización)
                regenerarTokenCSRF();

                // Resetear contador de Rate Limiting después de login exitoso
                rateLimitReset($rate_limit_id);

                // ========== AUDITORÍA: Registrar login exitoso ==========
                registrarLoginExitoso($fetch->login, $fetch->idusuario, $fetch->nombre);

                // Redireccionar al escritorio
                header("Location: ../vistas/escritorio.php");
                exit();
            } else {
                // Password incorrecto
                error_log("Login fallido - Password incorrecto para usuario: " . $logina);

                // ========== AUDITORÍA: Registrar login fallido ==========
                registrarLoginFallido($logina, 'password_incorrecto');

                header("Location: ../vistas/login.php?error=1");
                exit();
            }
        } else {
            // Usuario no existe
            error_log("Login fallido - Usuario no encontrado: " . $logina);

            // ========== AUDITORÍA: Registrar login fallido ==========
            registrarLoginFallido($logina, 'usuario_no_existe');

            header("Location: ../vistas/login.php?error=1");
            exit();
        }
        break;




    case 'salir':
        //Limpiamos las variables de sesión   
        session_unset();
        //Destruìmos la sesión
        session_destroy();
        //Redireccionamos al login
        header("Location: ../index.php");

        break;



    // case 'cargarbd':
    //     require_once "../modelos/Vendedorsitio.php";
    //     $vendedorsitio = new Vendedorsitio(); 

    //     $rspta = $vendedorsitio->select();
    //     while ($reg = $rspta->fetch_object())
    //             {
    //                 echo '<option value=' . $reg->nombre . '>' . $reg->nombre . '</option>';
    //             }
    // break;
}
?>