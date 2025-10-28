<?php
/**
 * HELPER PARA AUTENTICACIÓN DE DOS FACTORES (2FA)
 * Sistema de Facturación v3.3
 * Implementación de TOTP (Time-based One-Time Password) compatible con Google Authenticator
 */

require_once __DIR__ . "/Conexion.php";

/**
 * Genera un secret key aleatorio para TOTP
 * @return string Secret key en base32
 */
function generar2FASecret() {
    // Caracteres válidos en base32
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';

    // Generar 32 caracteres aleatorios
    for ($i = 0; $i < 32; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $secret;
}

/**
 * Genera códigos de respaldo (backup codes)
 * @param int $cantidad Cantidad de códigos a generar (default: 8)
 * @return array Array de códigos de respaldo
 */
function generar2FABackupCodes($cantidad = 8) {
    $codes = [];

    for ($i = 0; $i < $cantidad; $i++) {
        // Generar código de 8 caracteres alfanuméricos
        $code = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sin I, O, 0, 1 para evitar confusión

        for ($j = 0; $j < 8; $j++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Formatear como XXXX-XXXX
        $codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
    }

    return $codes;
}

/**
 * Encripta el secret key para almacenamiento seguro
 * @param string $secret Secret key a encriptar
 * @return string Secret encriptado
 */
function encriptar2FASecret($secret) {
    $key = hash('sha256', getenv('ENCRYPTION_KEY') ?: 'default_key_change_this', true);
    $iv = openssl_random_pseudo_bytes(16);

    $encrypted = openssl_encrypt($secret, 'AES-256-CBC', $key, 0, $iv);

    // Combinar IV + datos encriptados y codificar en base64
    return base64_encode($iv . $encrypted);
}

/**
 * Desencripta el secret key
 * @param string $encrypted Secret encriptado
 * @return string Secret desencriptado
 */
function desencriptar2FASecret($encrypted) {
    $key = hash('sha256', getenv('ENCRYPTION_KEY') ?: 'default_key_change_this', true);
    $data = base64_decode($encrypted);

    $iv = substr($data, 0, 16);
    $encrypted_data = substr($data, 16);

    return openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * Genera código TOTP basado en secret y timestamp
 * @param string $secret Secret key en base32
 * @param int $timestamp Timestamp (default: tiempo actual)
 * @return string Código de 6 dígitos
 */
function generar2FATOTPCode($secret, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }

    // Decodificar secret de base32
    $secret_decoded = base32_decode($secret);

    // Calcular contador basado en tiempo (30 segundos por período)
    $counter = floor($timestamp / 30);

    // Convertir contador a binario de 8 bytes
    $counter_bin = pack('N*', 0) . pack('N*', $counter);

    // HMAC-SHA1
    $hash = hash_hmac('sha1', $counter_bin, $secret_decoded, true);

    // Extraer 4 bytes dinámicamente
    $offset = ord($hash[strlen($hash) - 1]) & 0xf;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % 1000000;

    // Formatear a 6 dígitos con ceros a la izquierda
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

/**
 * Decodifica base32 a binario
 * @param string $input String en base32
 * @return string String binario
 */
function base32_decode($input) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $input = strtoupper($input);
    $output = '';
    $buffer = 0;
    $bitsLeft = 0;

    for ($i = 0; $i < strlen($input); $i++) {
        $val = strpos($chars, $input[$i]);
        if ($val === false) continue;

        $buffer = ($buffer << 5) | $val;
        $bitsLeft += 5;

        if ($bitsLeft >= 8) {
            $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
            $bitsLeft -= 8;
        }
    }

    return $output;
}

/**
 * Verifica un código TOTP
 * @param string $secret Secret key
 * @param string $code Código ingresado por el usuario
 * @param int $window Ventana de tiempo (default: 1 = ±30 segundos)
 * @return bool True si el código es válido
 */
function verificar2FATOTPCode($secret, $code, $window = 1) {
    $timestamp = time();

    // Verificar código actual y códigos adyacentes en la ventana de tiempo
    for ($i = -$window; $i <= $window; $i++) {
        $generated_code = generar2FATOTPCode($secret, $timestamp + ($i * 30));

        if (hash_equals($generated_code, $code)) {
            return true;
        }
    }

    return false;
}

/**
 * Genera URL para QR code compatible con Google Authenticator
 * @param string $secret Secret key
 * @param string $usuario_nombre Nombre del usuario
 * @param string $app_name Nombre de la aplicación (default: "Sistema Facturación")
 * @return string URL otpauth://
 */
function generar2FAQRCodeURL($secret, $usuario_nombre, $app_name = "Sistema Facturación") {
    $usuario_encoded = urlencode($usuario_nombre);
    $app_encoded = urlencode($app_name);

    return "otpauth://totp/{$app_encoded}:{$usuario_encoded}?secret={$secret}&issuer={$app_encoded}";
}

/**
 * Inicializa 2FA para un usuario
 * @param int $idusuario ID del usuario
 * @return array ['secret' => string, 'qr_url' => string, 'backup_codes' => array] o false si falla
 */
function iniciar2FASetup($idusuario) {
    global $conexion;

    try {
        // Generar secret
        $secret = generar2FASecret();
        $secret_encrypted = encriptar2FASecret($secret);

        // Generar códigos de respaldo
        $backup_codes = generar2FABackupCodes();
        $backup_codes_encrypted = encriptar2FASecret(json_encode($backup_codes));

        // Verificar si ya existe registro
        $stmt = $conexion->prepare("SELECT id_2fa FROM user_2fa WHERE idusuario = ?");
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Actualizar registro existente
            $stmt = $conexion->prepare(
                "UPDATE user_2fa SET secret_key = ?, backup_codes = ?, is_enabled = 0 WHERE idusuario = ?"
            );
            $stmt->bind_param("ssi", $secret_encrypted, $backup_codes_encrypted, $idusuario);
        } else {
            // Insertar nuevo registro
            $stmt = $conexion->prepare(
                "INSERT INTO user_2fa (idusuario, secret_key, backup_codes, is_enabled) VALUES (?, ?, ?, 0)"
            );
            $stmt->bind_param("iss", $idusuario, $secret_encrypted, $backup_codes_encrypted);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al guardar configuración 2FA");
        }

        // Registrar evento
        registrar2FALog($idusuario, 'SETUP_INITIATED');

        // Obtener nombre de usuario para QR
        $stmt = $conexion->prepare("SELECT login FROM usuario WHERE idusuario = ?");
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $usuario_data = $stmt->get_result()->fetch_assoc();

        $qr_url = generar2FAQRCodeURL($secret, $usuario_data['login']);

        return [
            'secret' => $secret,
            'qr_url' => $qr_url,
            'backup_codes' => $backup_codes
        ];

    } catch (Exception $e) {
        error_log("Error en iniciar2FASetup: " . $e->getMessage());
        return false;
    }
}

/**
 * Completa la activación de 2FA verificando el primer código
 * @param int $idusuario ID del usuario
 * @param string $code Código TOTP ingresado
 * @return bool True si se activó correctamente
 */
function activar2FA($idusuario, $code) {
    global $conexion;

    try {
        // Obtener secret
        $stmt = $conexion->prepare("SELECT secret_key FROM user_2fa WHERE idusuario = ?");
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        $row = $result->fetch_assoc();
        $secret = desencriptar2FASecret($row['secret_key']);

        // Verificar código
        if (!verificar2FATOTPCode($secret, $code)) {
            return false;
        }

        // Activar 2FA
        $stmt = $conexion->prepare(
            "UPDATE user_2fa SET is_enabled = 1, enabled_at = NOW(), last_verified_at = NOW() WHERE idusuario = ?"
        );
        $stmt->bind_param("i", $idusuario);

        if (!$stmt->execute()) {
            return false;
        }

        // Registrar evento
        registrar2FALog($idusuario, 'SETUP_COMPLETED');

        return true;

    } catch (Exception $e) {
        error_log("Error en activar2FA: " . $e->getMessage());
        return false;
    }
}

/**
 * Desactiva 2FA para un usuario
 * @param int $idusuario ID del usuario
 * @return bool True si se desactivó correctamente
 */
function desactivar2FA($idusuario) {
    global $conexion;

    try {
        $stmt = $conexion->prepare(
            "UPDATE user_2fa SET is_enabled = 0 WHERE idusuario = ?"
        );
        $stmt->bind_param("i", $idusuario);

        if ($stmt->execute()) {
            registrar2FALog($idusuario, 'DISABLED');
            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log("Error en desactivar2FA: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica 2FA durante el login
 * @param int $idusuario ID del usuario
 * @param string $code Código ingresado
 * @return array ['success' => bool, 'message' => string]
 */
function verificar2FALogin($idusuario, $code) {
    global $conexion;

    try {
        // Obtener datos 2FA
        $stmt = $conexion->prepare(
            "SELECT secret_key, backup_codes, is_enabled, failed_attempts, locked_until
             FROM user_2fa WHERE idusuario = ? AND is_enabled = 1"
        );
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => '2FA no configurado'];
        }

        $row = $result->fetch_assoc();

        // Verificar si está bloqueado
        if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
            $segundos_restantes = strtotime($row['locked_until']) - time();
            $minutos_restantes = ceil($segundos_restantes / 60);

            return [
                'success' => false,
                'message' => "Cuenta bloqueada. Intenta nuevamente en {$minutos_restantes} minutos"
            ];
        }

        $secret = desencriptar2FASecret($row['secret_key']);

        // Verificar código TOTP
        if (verificar2FATOTPCode($secret, $code)) {
            // Código válido - resetear intentos fallidos
            $stmt = $conexion->prepare(
                "UPDATE user_2fa SET failed_attempts = 0, locked_until = NULL, last_verified_at = NOW()
                 WHERE idusuario = ?"
            );
            $stmt->bind_param("i", $idusuario);
            $stmt->execute();

            registrar2FALog($idusuario, 'VERIFICATION_SUCCESS');

            return ['success' => true, 'message' => 'Código válido'];
        }

        // Verificar códigos de respaldo
        $backup_codes = json_decode(desencriptar2FASecret($row['backup_codes']), true);

        if (in_array($code, $backup_codes)) {
            // Código de respaldo válido - eliminarlo de la lista
            $backup_codes = array_diff($backup_codes, [$code]);
            $backup_codes_encrypted = encriptar2FASecret(json_encode(array_values($backup_codes)));

            $stmt = $conexion->prepare(
                "UPDATE user_2fa SET backup_codes = ?, failed_attempts = 0, locked_until = NULL, last_verified_at = NOW()
                 WHERE idusuario = ?"
            );
            $stmt->bind_param("si", $backup_codes_encrypted, $idusuario);
            $stmt->execute();

            registrar2FALog($idusuario, 'BACKUP_CODE_USED', ['codes_remaining' => count($backup_codes)]);

            return ['success' => true, 'message' => 'Código de respaldo válido'];
        }

        // Código inválido - incrementar intentos fallidos
        $intentos = $row['failed_attempts'] + 1;
        $locked_until = null;

        if ($intentos >= 5) {
            // Bloquear por 15 minutos
            $locked_until = date('Y-m-d H:i:s', time() + (15 * 60));
            registrar2FALog($idusuario, 'ACCOUNT_LOCKED', ['intentos' => $intentos]);
        }

        $stmt = $conexion->prepare(
            "UPDATE user_2fa SET failed_attempts = ?, locked_until = ? WHERE idusuario = ?"
        );
        $stmt->bind_param("isi", $intentos, $locked_until, $idusuario);
        $stmt->execute();

        registrar2FALog($idusuario, 'VERIFICATION_FAILED', ['intentos' => $intentos]);

        $intentos_restantes = 5 - $intentos;

        if ($intentos >= 5) {
            return ['success' => false, 'message' => 'Cuenta bloqueada por 15 minutos'];
        }

        return [
            'success' => false,
            'message' => "Código inválido. Intentos restantes: {$intentos_restantes}"
        ];

    } catch (Exception $e) {
        error_log("Error en verificar2FALogin: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al verificar código'];
    }
}

/**
 * Verifica si un usuario tiene 2FA activo
 * @param int $idusuario ID del usuario
 * @return bool True si tiene 2FA activo
 */
function tiene2FAActivo($idusuario) {
    global $conexion;

    $stmt = $conexion->prepare("SELECT is_enabled FROM user_2fa WHERE idusuario = ?");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['is_enabled'] == 1;
    }

    return false;
}

/**
 * Registra un evento en el log de 2FA
 * @param int $idusuario ID del usuario
 * @param string $event_type Tipo de evento
 * @param array $details Detalles adicionales (opcional)
 * @return bool True si se registró correctamente
 */
function registrar2FALog($idusuario, $event_type, $details = []) {
    global $conexion;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $details_json = !empty($details) ? json_encode($details) : null;

    $stmt = $conexion->prepare(
        "INSERT INTO user_2fa_log (idusuario, event_type, ip_address, user_agent, details)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $idusuario, $event_type, $ip, $user_agent, $details_json);

    return $stmt->execute();
}
?>
