<?php
/**
 * HELPER GENÉRICO DE ENCRIPTACIÓN DE DATOS SENSIBLES
 * Sistema de Facturación v3.3
 *
 * Proporciona funciones de encriptación/desencriptación AES-256-CBC para datos sensibles
 * como números de tarjetas, cuentas bancarias, información confidencial, etc.
 *
 * IMPORTANTE: Este helper es complementario al sistema de contraseñas (bcrypt) y 2FA.
 * - Contraseñas: Usar password_hash() con bcrypt (NO este helper)
 * - Datos 2FA: Usar encriptar2FASecret() en 2fa_helper.php
 * - Otros datos sensibles: Usar funciones de este archivo
 */

/**
 * Obtiene la clave de encriptación del entorno
 * @return string Clave derivada de 32 bytes
 */
function obtenerClaveEncriptacion() {
    $encryption_key = getenv('ENCRYPTION_KEY') ?: 'default_key_CHANGE_THIS_IN_PRODUCTION';

    // Derivar clave de 32 bytes con SHA-256
    return hash('sha256', $encryption_key, true);
}

/**
 * Encripta un dato sensible usando AES-256-CBC
 *
 * @param string $dato_plano Dato a encriptar (texto plano)
 * @return string|false Dato encriptado en base64 (IV + datos) o false si falla
 *
 * @example
 * $numero_tarjeta = "4111111111111111";
 * $tarjeta_encriptada = encriptarDato($numero_tarjeta);
 * // Guardar $tarjeta_encriptada en BD
 */
function encriptarDato($dato_plano) {
    if (empty($dato_plano)) {
        return false;
    }

    try {
        $key = obtenerClaveEncriptacion();

        // Generar IV aleatorio de 16 bytes para AES-256-CBC
        $iv = openssl_random_pseudo_bytes(16);

        // Encriptar dato
        $encrypted = openssl_encrypt($dato_plano, 'AES-256-CBC', $key, 0, $iv);

        if ($encrypted === false) {
            error_log("Error al encriptar dato");
            return false;
        }

        // Combinar IV + datos encriptados y codificar en base64
        return base64_encode($iv . $encrypted);

    } catch (Exception $e) {
        error_log("Error en encriptarDato: " . $e->getMessage());
        return false;
    }
}

/**
 * Desencripta un dato previamente encriptado con encriptarDato()
 *
 * @param string $dato_encriptado Dato encriptado en base64
 * @return string|false Dato desencriptado (texto plano) o false si falla
 *
 * @example
 * $tarjeta_encriptada = "..."; // De la BD
 * $numero_tarjeta = desencriptarDato($tarjeta_encriptada);
 * // Usar $numero_tarjeta (texto plano)
 */
function desencriptarDato($dato_encriptado) {
    if (empty($dato_encriptado)) {
        return false;
    }

    try {
        $key = obtenerClaveEncriptacion();

        // Decodificar de base64
        $data = base64_decode($dato_encriptado);

        if ($data === false) {
            error_log("Error al decodificar dato encriptado");
            return false;
        }

        // Extraer IV (primeros 16 bytes) y datos encriptados (resto)
        $iv = substr($data, 0, 16);
        $encrypted_data = substr($data, 16);

        // Desencriptar
        $decrypted = openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);

        if ($decrypted === false) {
            error_log("Error al desencriptar dato");
            return false;
        }

        return $decrypted;

    } catch (Exception $e) {
        error_log("Error en desencriptarDato: " . $e->getMessage());
        return false;
    }
}

/**
 * Encripta un array de datos y retorna array encriptado
 * Útil para encriptar múltiples campos a la vez
 *
 * @param array $datos Array asociativo con datos a encriptar
 * @return array Array con mismas keys pero valores encriptados
 *
 * @example
 * $datos_sensibles = [
 *     'numero_tarjeta' => '4111111111111111',
 *     'cvv' => '123',
 *     'cuenta_bancaria' => '191-123456789'
 * ];
 * $datos_encriptados = encriptarArray($datos_sensibles);
 */
function encriptarArray($datos) {
    $resultado = [];

    foreach ($datos as $key => $valor) {
        $resultado[$key] = encriptarDato($valor);

        // Si algún campo falla, retornar false
        if ($resultado[$key] === false) {
            error_log("Error al encriptar campo: {$key}");
            return false;
        }
    }

    return $resultado;
}

/**
 * Desencripta un array de datos previamente encriptado
 *
 * @param array $datos_encriptados Array con valores encriptados
 * @return array Array con mismas keys pero valores desencriptados
 */
function desencriptarArray($datos_encriptados) {
    $resultado = [];

    foreach ($datos_encriptados as $key => $valor) {
        $resultado[$key] = desencriptarDato($valor);

        // Si algún campo falla, retornar false
        if ($resultado[$key] === false) {
            error_log("Error al desencriptar campo: {$key}");
            return false;
        }
    }

    return $resultado;
}

/**
 * Enmascara datos sensibles para mostrar en logs o UI
 * Útil para mostrar solo los últimos 4 dígitos de tarjetas, etc.
 *
 * @param string $dato Dato a enmascarar
 * @param int $visible_chars Cantidad de caracteres finales visibles (default: 4)
 * @param string $mask_char Carácter para enmascarar (default: *)
 * @return string Dato enmascarado
 *
 * @example
 * echo enmascararDato("4111111111111111", 4); // ************1111
 * echo enmascararDato("juan@email.com", 6, 'X'); // XXXXXXXXil.com
 */
function enmascararDato($dato, $visible_chars = 4, $mask_char = '*') {
    $length = strlen($dato);

    if ($length <= $visible_chars) {
        return str_repeat($mask_char, $length);
    }

    $masked_length = $length - $visible_chars;
    $visible_part = substr($dato, -$visible_chars);

    return str_repeat($mask_char, $masked_length) . $visible_part;
}

/**
 * Hashea datos para búsquedas (sin posibilidad de reversa)
 * Útil para buscar datos encriptados sin desencriptarlos
 *
 * @param string $dato Dato a hashear
 * @return string Hash SHA-256 del dato
 *
 * @example
 * // Guardar hash junto con dato encriptado para búsquedas
 * $hash_tarjeta = hashearParaBusqueda("4111111111111111");
 * // SELECT * FROM pagos WHERE hash_tarjeta = ?
 */
function hashearParaBusqueda($dato) {
    return hash('sha256', $dato);
}

/**
 * Valida que un dato encriptado sea válido (puede desencriptarse)
 *
 * @param string $dato_encriptado Dato encriptado a validar
 * @return bool True si es válido y puede desencriptarse
 */
function validarDatoEncriptado($dato_encriptado) {
    $desencriptado = desencriptarDato($dato_encriptado);
    return $desencriptado !== false;
}

// ========================================================================
// EJEMPLOS DE USO PARA IMPLEMENTACIÓN FUTURA
// ========================================================================

/**
 * EJEMPLO 1: Encriptar número de tarjeta al guardar
 *
 * // En archivo de pago (futuro)
 * $numero_tarjeta = limpiarCadena($_POST['numero_tarjeta']);
 * $cvv = limpiarCadena($_POST['cvv']);
 *
 * // Encriptar datos sensibles
 * $tarjeta_encriptada = encriptarDato($numero_tarjeta);
 * $cvv_encriptado = encriptarDato($cvv);
 *
 * // Guardar hash para búsquedas
 * $hash_tarjeta = hashearParaBusqueda($numero_tarjeta);
 *
 * // INSERT en BD
 * $sql = "INSERT INTO metodo_pago (numero_tarjeta, cvv, hash_tarjeta) VALUES (?, ?, ?)";
 * ejecutarConsultaPreparada($sql, "sss", [$tarjeta_encriptada, $cvv_encriptado, $hash_tarjeta]);
 */

/**
 * EJEMPLO 2: Desencriptar para mostrar (enmascarado)
 *
 * // Obtener de BD
 * $row = $resultado->fetch_object();
 *
 * // Desencriptar
 * $numero_tarjeta = desencriptarDato($row->numero_tarjeta);
 *
 * // Mostrar enmascarado en UI
 * echo enmascararDato($numero_tarjeta, 4); // ************1111
 */

/**
 * EJEMPLO 3: Encriptar datos bancarios
 *
 * $datos_bancarios = [
 *     'numero_cuenta' => '191-123456789',
 *     'cci' => '00219100012345678901',
 *     'titular' => 'Juan Pérez'
 * ];
 *
 * $datos_encriptados = encriptarArray($datos_bancarios);
 *
 * // Guardar en BD
 * $sql = "UPDATE empresa SET
 *         numero_cuenta = ?,
 *         cci = ?,
 *         titular_cuenta = ?
 *         WHERE idempresa = ?";
 */

/**
 * EJEMPLO 4: Buscar por dato encriptado usando hash
 *
 * $numero_tarjeta_buscar = "4111111111111111";
 * $hash_busqueda = hashearParaBusqueda($numero_tarjeta_buscar);
 *
 * $sql = "SELECT * FROM metodo_pago WHERE hash_tarjeta = ?";
 * $resultado = ejecutarConsultaPreparada($sql, "s", [$hash_busqueda]);
 */
?>
