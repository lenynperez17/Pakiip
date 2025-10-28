<?php
/**
 * POLÍTICA DE CONTRASEÑAS FUERTE
 * Sistema de Facturación v3.3
 *
 * Implementa validación robusta de contraseñas según estándares NIST SP 800-63B
 * y mejores prácticas de seguridad.
 */

/**
 * Configuración de política de contraseñas
 */
define('PASSWORD_MIN_LENGTH', 12);              // Mínimo 12 caracteres (NIST recomienda 8+)
define('PASSWORD_REQUIRE_UPPERCASE', true);      // Requiere mayúsculas
define('PASSWORD_REQUIRE_LOWERCASE', true);      // Requiere minúsculas
define('PASSWORD_REQUIRE_NUMBERS', true);        // Requiere números
define('PASSWORD_REQUIRE_SYMBOLS', true);        // Requiere símbolos
define('PASSWORD_HISTORY_COUNT', 5);             // No reutilizar últimas 5 contraseñas
define('PASSWORD_EXPIRATION_DAYS', 90);          // Expiración cada 90 días (0 = sin expiración)
define('PASSWORD_MAX_ATTEMPTS_RESET', 3);        // Intentos máximos antes de bloqueo temporal

/**
 * Lista de contraseñas comúnmente usadas (prohibidas)
 * Fuente: https://github.com/danielmiessler/SecLists
 */
$COMMON_PASSWORDS = [
    'password', 'Password123', '12345678', 'qwerty123', 'abc12345',
    'password1', 'Password1', '123456789', 'qwerty12', 'admin123',
    'letmein', 'welcome123', 'monkey123', 'dragon123', 'master123',
    'senha123', 'senha12', 'senha1234', 'Password!', 'Admin123!',
    'factura123', 'sistema123', 'ventas123', 'admin', 'administrador',
    '12341234', '12121212', '11111111', 'qwertyui', 'asdfghjk'
];

/**
 * Valida una contraseña según la política establecida
 *
 * @param string $password Contraseña a validar
 * @param int $idusuario ID del usuario (opcional, para verificar historial)
 * @return array ['valida' => bool, 'errores' => array, 'fortaleza' => string]
 */
function validarPoliticaPassword($password, $idusuario = null) {
    global $COMMON_PASSWORDS;
    $errores = [];
    $fortaleza = 0; // Puntuación de 0-100

    // 1. VALIDAR LONGITUD MÍNIMA
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errores[] = "La contraseña debe tener al menos " . PASSWORD_MIN_LENGTH . " caracteres";
    } else {
        $fortaleza += 20;
    }

    // 2. VALIDAR MAYÚSCULAS
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errores[] = "La contraseña debe contener al menos una letra mayúscula";
    } else {
        $fortaleza += 15;
    }

    // 3. VALIDAR MINÚSCULAS
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errores[] = "La contraseña debe contener al menos una letra minúscula";
    } else {
        $fortaleza += 15;
    }

    // 4. VALIDAR NÚMEROS
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $errores[] = "La contraseña debe contener al menos un número";
    } else {
        $fortaleza += 15;
    }

    // 5. VALIDAR SÍMBOLOS ESPECIALES
    if (PASSWORD_REQUIRE_SYMBOLS && !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errores[] = "La contraseña debe contener al menos un símbolo especial (!@#$%^&*)";
    } else {
        $fortaleza += 15;
    }

    // 6. VERIFICAR CONTRASEÑAS COMUNES (case-insensitive)
    $password_lower = strtolower($password);
    foreach ($COMMON_PASSWORDS as $common) {
        if ($password_lower === strtolower($common) || strpos($password_lower, strtolower($common)) !== false) {
            $errores[] = "La contraseña es demasiado común o predecible";
            $fortaleza = max(0, $fortaleza - 30);
            break;
        }
    }

    // 7. PENALIZAR PATRONES SECUENCIALES
    if (tienePatronesSecuenciales($password)) {
        $errores[] = "La contraseña contiene patrones secuenciales obvios (123, abc, etc.)";
        $fortaleza = max(0, $fortaleza - 20);
    }

    // 8. BONIFICACIÓN POR LONGITUD EXTRA
    if (strlen($password) >= 16) {
        $fortaleza += 10;
    }
    if (strlen($password) >= 20) {
        $fortaleza += 10;
    }

    // 9. VERIFICAR HISTORIAL DE CONTRASEÑAS (si se proporciona idusuario)
    if ($idusuario !== null && PASSWORD_HISTORY_COUNT > 0) {
        if (passwordEnHistorial($password, $idusuario)) {
            $errores[] = "No puedes reutilizar tus últimas " . PASSWORD_HISTORY_COUNT . " contraseñas";
        }
    }

    // Limitar fortaleza a máximo 100
    $fortaleza = min(100, $fortaleza);

    // Clasificar fortaleza
    $nivel_fortaleza = 'Muy débil';
    if ($fortaleza >= 80) {
        $nivel_fortaleza = 'Muy fuerte';
    } elseif ($fortaleza >= 60) {
        $nivel_fortaleza = 'Fuerte';
    } elseif ($fortaleza >= 40) {
        $nivel_fortaleza = 'Media';
    } elseif ($fortaleza >= 20) {
        $nivel_fortaleza = 'Débil';
    }

    return [
        'valida' => empty($errores),
        'errores' => $errores,
        'fortaleza_puntos' => $fortaleza,
        'fortaleza_nivel' => $nivel_fortaleza
    ];
}

/**
 * Detecta patrones secuenciales obvios en la contraseña
 *
 * @param string $password Contraseña a analizar
 * @return bool True si contiene patrones secuenciales
 */
function tienePatronesSecuenciales($password) {
    $password_lower = strtolower($password);

    $patrones = [
        '123', '234', '345', '456', '567', '678', '789', '890',
        'abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij',
        'qwe', 'wer', 'ert', 'rty', 'tyu', 'yui', 'uio', 'iop',
        'asd', 'sdf', 'dfg', 'fgh', 'ghj', 'hjk', 'jkl',
        'zxc', 'xcv', 'cvb', 'vbn', 'bnm'
    ];

    foreach ($patrones as $patron) {
        if (strpos($password_lower, $patron) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Verifica si una contraseña está en el historial del usuario
 *
 * @param string $password Contraseña a verificar
 * @param int $idusuario ID del usuario
 * @return bool True si la contraseña ya fue usada
 */
function passwordEnHistorial($password, $idusuario) {
    global $conexion;

    // Obtener historial de contraseñas del usuario
    $sql = "SELECT password_hash
            FROM usuario_password_history
            WHERE idusuario = ?
            ORDER BY fecha_cambio DESC
            LIMIT ?";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar consulta de historial: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("ii", $idusuario, PASSWORD_HISTORY_COUNT);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar contra cada hash del historial
    while ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            return true; // Contraseña ya fue usada
        }
    }

    return false;
}

/**
 * Registra una contraseña en el historial del usuario
 *
 * @param int $idusuario ID del usuario
 * @param string $password_hash Hash bcrypt de la contraseña
 * @return bool True si se registró correctamente
 */
function registrarPasswordEnHistorial($idusuario, $password_hash) {
    global $conexion;

    $sql = "INSERT INTO usuario_password_history (idusuario, password_hash, fecha_cambio)
            VALUES (?, ?, NOW())";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar consulta de historial: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("is", $idusuario, $password_hash);
    $resultado = $stmt->execute();

    if ($resultado) {
        // Limpiar historial antiguo (mantener solo las últimas N contraseñas)
        limpiarHistorialAntiguo($idusuario);
    }

    return $resultado;
}

/**
 * Limpia el historial de contraseñas antiguas del usuario
 *
 * @param int $idusuario ID del usuario
 * @return bool True si se limpió correctamente
 */
function limpiarHistorialAntiguo($idusuario) {
    global $conexion;

    $sql = "DELETE FROM usuario_password_history
            WHERE idusuario = ?
            AND id_history NOT IN (
                SELECT id_history FROM (
                    SELECT id_history
                    FROM usuario_password_history
                    WHERE idusuario = ?
                    ORDER BY fecha_cambio DESC
                    LIMIT ?
                ) AS recent
            )";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error al limpiar historial: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("iii", $idusuario, $idusuario, PASSWORD_HISTORY_COUNT);
    return $stmt->execute();
}

/**
 * Verifica si la contraseña del usuario ha expirado
 *
 * @param int $idusuario ID del usuario
 * @return array ['expirada' => bool, 'dias_restantes' => int]
 */
function verificarExpiracionPassword($idusuario) {
    global $conexion;

    if (PASSWORD_EXPIRATION_DAYS == 0) {
        return ['expirada' => false, 'dias_restantes' => null];
    }

    $sql = "SELECT DATEDIFF(NOW(), password_changed_at) AS dias_desde_cambio
            FROM usuario
            WHERE idusuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $dias_desde_cambio = $row['dias_desde_cambio'] ?? 0;
    $dias_restantes = PASSWORD_EXPIRATION_DAYS - $dias_desde_cambio;

    return [
        'expirada' => $dias_desde_cambio >= PASSWORD_EXPIRATION_DAYS,
        'dias_restantes' => max(0, $dias_restantes),
        'dias_desde_cambio' => $dias_desde_cambio
    ];
}

/**
 * Genera una contraseña aleatoria segura que cumple con la política
 *
 * @param int $length Longitud de la contraseña (default: PASSWORD_MIN_LENGTH)
 * @return string Contraseña generada
 */
function generarPasswordSeguro($length = PASSWORD_MIN_LENGTH) {
    $length = max($length, PASSWORD_MIN_LENGTH);

    $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $minusculas = 'abcdefghijklmnopqrstuvwxyz';
    $numeros = '0123456789';
    $simbolos = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    $password = '';

    // Garantizar al menos un carácter de cada tipo requerido
    if (PASSWORD_REQUIRE_UPPERCASE) {
        $password .= $mayusculas[random_int(0, strlen($mayusculas) - 1)];
    }
    if (PASSWORD_REQUIRE_LOWERCASE) {
        $password .= $minusculas[random_int(0, strlen($minusculas) - 1)];
    }
    if (PASSWORD_REQUIRE_NUMBERS) {
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
    }
    if (PASSWORD_REQUIRE_SYMBOLS) {
        $password .= $simbolos[random_int(0, strlen($simbolos) - 1)];
    }

    // Completar el resto con caracteres aleatorios de todos los conjuntos
    $todos_chars = $mayusculas . $minusculas . $numeros . $simbolos;
    $remaining = $length - strlen($password);

    for ($i = 0; $i < $remaining; $i++) {
        $password .= $todos_chars[random_int(0, strlen($todos_chars) - 1)];
    }

    // Mezclar caracteres
    $password = str_shuffle($password);

    return $password;
}

/**
 * Retorna un array con los requisitos de la política para mostrar al usuario
 *
 * @return array Requisitos de contraseña
 */
function obtenerRequisitosPassword() {
    $requisitos = [];

    $requisitos[] = "Mínimo " . PASSWORD_MIN_LENGTH . " caracteres";

    if (PASSWORD_REQUIRE_UPPERCASE) {
        $requisitos[] = "Al menos una letra mayúscula (A-Z)";
    }
    if (PASSWORD_REQUIRE_LOWERCASE) {
        $requisitos[] = "Al menos una letra minúscula (a-z)";
    }
    if (PASSWORD_REQUIRE_NUMBERS) {
        $requisitos[] = "Al menos un número (0-9)";
    }
    if (PASSWORD_REQUIRE_SYMBOLS) {
        $requisitos[] = "Al menos un símbolo especial (!@#$%^&*)";
    }
    if (PASSWORD_HISTORY_COUNT > 0) {
        $requisitos[] = "No reutilizar las últimas " . PASSWORD_HISTORY_COUNT . " contraseñas";
    }
    if (PASSWORD_EXPIRATION_DAYS > 0) {
        $requisitos[] = "Se debe cambiar cada " . PASSWORD_EXPIRATION_DAYS . " días";
    }

    $requisitos[] = "No usar contraseñas comunes o predecibles";
    $requisitos[] = "Evitar patrones secuenciales (123, abc, etc.)";

    return $requisitos;
}
?>
