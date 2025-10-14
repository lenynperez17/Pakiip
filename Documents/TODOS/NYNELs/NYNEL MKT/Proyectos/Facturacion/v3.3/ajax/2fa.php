<?php
/**
 * ENDPOINT AJAX PARA AUTENTICACIÓN DE DOS FACTORES (2FA)
 * Sistema de Facturación v3.3
 */

require_once "../config/Conexion.php";
require_once "../config/ajax_helper.php";
require_once "../config/2fa_helper.php";

iniciarSesionSegura();

// SEGURIDAD: Rate limiting para prevenir ataques de fuerza bruta
rateLimitAjax('2fa', 20, 60); // Máximo 20 requests por minuto

switch ($_GET["op"]) {

    case 'iniciarSetup':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            respuestaJSON(false, "Token de seguridad inválido");
        }

        $idusuario = $_SESSION['idusuario'];

        $setup_data = iniciar2FASetup($idusuario);

        if ($setup_data) {
            // Generar URL para QR code usando servicio externo
            $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($setup_data['qr_url']);

            respuestaJSON(true, "Configuración iniciada", [
                'secret' => $setup_data['secret'],
                'qr_code_url' => $qr_code_url,
                'qr_data_url' => $setup_data['qr_url'],
                'backup_codes' => $setup_data['backup_codes']
            ]);
        } else {
            respuestaJSON(false, "Error al iniciar configuración 2FA");
        }
        break;

    case 'activar':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            respuestaJSON(false, "Token de seguridad inválido");
        }

        $idusuario = $_SESSION['idusuario'];
        $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

        // Validar formato del código (6 dígitos)
        if (!preg_match('/^\d{6}$/', $code)) {
            respuestaJSON(false, "Código inválido. Debe contener 6 dígitos");
        }

        if (activar2FA($idusuario, $code)) {
            // Auditoría
            registrarAuditoria('CONFIG_CHANGE', 'usuario', [
                'registro_id' => $idusuario,
                'descripcion' => "2FA activado para usuario ID: {$idusuario}",
                'metadata' => [
                    'feature' => '2FA',
                    'action' => 'enabled'
                ]
            ]);

            respuestaJSON(true, "2FA activado exitosamente");
        } else {
            respuestaJSON(false, "Código inválido. Verifica el código en tu aplicación de autenticación");
        }
        break;

    case 'desactivar':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            respuestaJSON(false, "Token de seguridad inválido");
        }

        $idusuario = $_SESSION['idusuario'];

        // Requiere confirmación con código actual
        $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

        if (empty($code)) {
            respuestaJSON(false, "Debes ingresar el código actual para desactivar 2FA");
        }

        // Verificar código antes de desactivar
        $verificacion = verificar2FALogin($idusuario, $code);

        if ($verificacion['success']) {
            if (desactivar2FA($idusuario)) {
                // Auditoría
                registrarAuditoria('CONFIG_CHANGE', 'usuario', [
                    'registro_id' => $idusuario,
                    'descripcion' => "2FA desactivado para usuario ID: {$idusuario}",
                    'metadata' => [
                        'feature' => '2FA',
                        'action' => 'disabled'
                    ]
                ]);

                respuestaJSON(true, "2FA desactivado exitosamente");
            } else {
                respuestaJSON(false, "Error al desactivar 2FA");
            }
        } else {
            respuestaJSON(false, $verificacion['message']);
        }
        break;

    case 'verificarLogin':
        // Este caso NO requiere sesión activa (se usa durante el login)

        $idusuario = isset($_POST['idusuario']) ? validarEntero($_POST['idusuario'], 1) : false;
        $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

        if ($idusuario === false) {
            respuestaJSON(false, "ID de usuario inválido");
        }

        // Validar formato del código (6 dígitos o código de respaldo XXXX-XXXX)
        if (!preg_match('/^\d{6}$/', $code) && !preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code)) {
            respuestaJSON(false, "Formato de código inválido");
        }

        $resultado = verificar2FALogin($idusuario, $code);

        if ($resultado['success']) {
            respuestaJSON(true, $resultado['message']);
        } else {
            respuestaJSON(false, $resultado['message']);
        }
        break;

    case 'verificarStatus':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        $idusuario = $_SESSION['idusuario'];

        $tiene_2fa = tiene2FAActivo($idusuario);

        respuestaJSON(true, "Status obtenido", [
            'tiene_2fa' => $tiene_2fa,
            'status' => $tiene_2fa ? 'Activo' : 'Inactivo'
        ]);
        break;

    case 'obtenerBackupCodes':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            respuestaJSON(false, "Token de seguridad inválido");
        }

        $idusuario = $_SESSION['idusuario'];

        // Requiere verificación con código actual
        $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

        if (empty($code)) {
            respuestaJSON(false, "Debes ingresar tu código 2FA actual");
        }

        // Verificar código
        $verificacion = verificar2FALogin($idusuario, $code);

        if (!$verificacion['success']) {
            respuestaJSON(false, $verificacion['message']);
        }

        // Obtener códigos de respaldo
        global $conexion;
        $stmt = $conexion->prepare("SELECT backup_codes FROM user_2fa WHERE idusuario = ?");
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $backup_codes = json_decode(desencriptar2FASecret($row['backup_codes']), true);

            // Auditoría
            registrarAuditoria('CONFIG_CHANGE', 'usuario', [
                'registro_id' => $idusuario,
                'descripcion' => "Códigos de respaldo 2FA visualizados por usuario ID: {$idusuario}",
                'metadata' => [
                    'feature' => '2FA',
                    'action' => 'view_backup_codes'
                ]
            ]);

            respuestaJSON(true, "Códigos de respaldo obtenidos", [
                'backup_codes' => $backup_codes
            ]);
        } else {
            respuestaJSON(false, "No se encontraron códigos de respaldo");
        }
        break;

    case 'regenerarBackupCodes':
        // SEGURIDAD: Validar autenticación
        validarAutenticacion();

        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            respuestaJSON(false, "Token de seguridad inválido");
        }

        $idusuario = $_SESSION['idusuario'];

        // Requiere verificación con código actual
        $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

        if (empty($code)) {
            respuestaJSON(false, "Debes ingresar tu código 2FA actual");
        }

        // Verificar código
        $verificacion = verificar2FALogin($idusuario, $code);

        if (!$verificacion['success']) {
            respuestaJSON(false, $verificacion['message']);
        }

        // Generar nuevos códigos
        $new_backup_codes = generar2FABackupCodes();
        $backup_codes_encrypted = encriptar2FASecret(json_encode($new_backup_codes));

        global $conexion;
        $stmt = $conexion->prepare("UPDATE user_2fa SET backup_codes = ? WHERE idusuario = ?");
        $stmt->bind_param("si", $backup_codes_encrypted, $idusuario);

        if ($stmt->execute()) {
            // Auditoría
            registrarAuditoria('CONFIG_CHANGE', 'usuario', [
                'registro_id' => $idusuario,
                'descripcion' => "Códigos de respaldo 2FA regenerados para usuario ID: {$idusuario}",
                'metadata' => [
                    'feature' => '2FA',
                    'action' => 'regenerate_backup_codes'
                ]
            ]);

            respuestaJSON(true, "Códigos de respaldo regenerados exitosamente", [
                'backup_codes' => $new_backup_codes
            ]);
        } else {
            respuestaJSON(false, "Error al regenerar códigos de respaldo");
        }
        break;

    default:
        respuestaJSON(false, "Operación no válida");
        break;
}
?>
