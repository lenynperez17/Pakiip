# 📘 GUÍA DE REFACTORIZACIÓN - SISTEMA DE FACTURACIÓN v3.3

## 🎯 Objetivo

Esta guía documenta las nuevas clases de arquitectura implementadas para mejorar la seguridad, mantenibilidad y escalabilidad del sistema.

---

## 📦 NUEVAS CLASES IMPLEMENTADAS

1. **Database.php** - Capa de abstracción de base de datos (PDO)
2. **Validator.php** - Sistema de validación centralizado
3. **ApiResponse.php** - Respuestas API estandarizadas
4. **Logger.php** - Sistema de logging estructurado
5. **SecurityHelper.php** - Helpers de seguridad consolidados

---

## 1️⃣ DATABASE.PHP - Capa de Abstracción PDO

### ✨ Características
- Singleton pattern (una sola conexión)
- Prepared statements obligatorios (prevención SQL Injection)
- Transacciones con auto-rollback
- Estadísticas de queries
- Connection pooling

### 📌 Uso Básico

```php
<?php
require_once 'config/Database.php';

// OPCIÓN 1: Usar instancia singleton
$db = Database::getInstance();

// SELECT múltiples filas
$usuarios = $db->select("SELECT * FROM usuario WHERE estado = ?", [1]);

// SELECT una sola fila
$usuario = $db->selectOne("SELECT * FROM usuario WHERE idusuario = ?", [5]);

// INSERT (retorna ID insertado)
$nuevoId = $db->insert(
    "INSERT INTO persona (nombre, email) VALUES (?, ?)",
    ['Juan Pérez', 'juan@example.com']
);

// UPDATE (retorna filas afectadas)
$filasActualizadas = $db->update(
    "UPDATE persona SET email = ? WHERE idpersona = ?",
    ['nuevo@email.com', 10]
);

// DELETE (retorna filas eliminadas)
$filasEliminadas = $db->delete(
    "DELETE FROM persona WHERE idpersona = ?",
    [10]
);
```

### 🔄 Transacciones Manuales

```php
<?php
$db = Database::getInstance();

$db->beginTransaction();

try {
    // Operación 1
    $idCompra = $db->insert(
        "INSERT INTO compra (idproveedor, total) VALUES (?, ?)",
        [5, 1000.00]
    );

    // Operación 2
    $db->insert(
        "INSERT INTO detalle_compra_producto (idcompra, idarticulo, cantidad) VALUES (?, ?, ?)",
        [$idCompra, 10, 5]
    );

    // Si todo OK, confirmar
    $db->commit();

} catch (Exception $e) {
    // Si hay error, revertir
    $db->rollback();
    echo "Error: " . $e->getMessage();
}
```

### 🔄 Transacciones con Callback

```php
<?php
$resultado = db_transaction(function($db) {
    $idVenta = $db->insert("INSERT INTO venta (total) VALUES (?)", [500.00]);

    $db->insert("INSERT INTO detalle_venta (idventa, cantidad) VALUES (?, ?)", [$idVenta, 10]);

    return $idVenta; // Retornar resultado
});
```

### 🎯 Stored Procedures

```php
<?php
$db = Database::getInstance();

// Llamar stored procedure con parámetros OUT
$result = $db->callProcedure(
    'sp_obtener_siguiente_numero',
    [1], // Parámetros IN
    ['numero_completo', 'numero', 'resultado'] // Parámetros OUT
);

echo $result['numero_completo']; // F001-00000001
```

### 📊 Helpers Globales

```php
<?php
// Shortcuts globales
$usuarios = db_select("SELECT * FROM usuario WHERE activo = ?", [1]);
$usuario = db_select_one("SELECT * FROM usuario WHERE id = ?", [1]);
$id = db_insert("INSERT INTO ...", []);
$rows = db_update("UPDATE ...", []);
$rows = db_delete("DELETE ...", []);
```

---

## 2️⃣ VALIDATOR.PHP - Sistema de Validación

### ✨ Características
- 40+ validaciones predefinidas
- Validaciones específicas de Perú/SUNAT
- Mensajes en español
- Fluent interface
- Integración con ApiResponse

### 📌 Uso con Reglas

```php
<?php
require_once 'config/Validator.php';

$data = [
    'nombre' => 'Juan',
    'email' => 'juan@example.com',
    'ruc' => '20123456789',
    'telefono' => '987654321'
];

$validator = Validator::make($data)
    ->rules([
        'nombre' => 'required|min:3|max:100|alpha',
        'email' => 'required|email',
        'ruc' => 'required|ruc',
        'telefono' => 'required|telefonoPeru'
    ])
    ->messages([
        'nombre.required' => 'El nombre es obligatorio',
        'email.email' => 'Formato de email inválido'
    ]);

if ($validator->validate()) {
    echo "Validación exitosa!";
} else {
    // Obtener errores
    print_r($validator->errors());

    // Obtener primer error de un campo
    echo $validator->first('email');

    // Obtener todos los errores como array plano
    print_r($validator->allErrors());
}
```

### 🎯 Validaciones Disponibles

#### Básicas:
- `required` - Campo obligatorio
- `email` - Email válido
- `integer` - Número entero
- `numeric` - Numérico
- `min:5` - Mínimo 5 caracteres
- `max:100` - Máximo 100 caracteres
- `length:8` - Exactamente 8 caracteres
- `minValue:10` - Valor mínimo 10
- `maxValue:100` - Valor máximo 100
- `in:A,B,C` - Debe ser A, B o C
- `notIn:X,Y` - No puede ser X o Y
- `url` - URL válida
- `ip` - IP válida
- `date` - Fecha válida (Y-m-d)
- `regex:/pattern/` - Patrón regex
- `alpha` - Solo letras
- `alphaNum` - Letras y números
- `json` - JSON válido
- `boolean` - Booleano

#### Específicas de Perú/SUNAT:
- `ruc` - RUC válido (11 dígitos con dígito verificador)
- `dni` - DNI válido (8 dígitos)
- `ce` - Carnet de Extranjería (9 dígitos)
- `telefonoPeru` - Teléfono peruano (9 dígitos, empieza con 9)
- `codigoPostal` - Código postal (5 dígitos)
- `partidaArancelaria` - Partida arancelaria SUNAT (10 dígitos)
- `tipoComprobanteSunat` - Tipo de comprobante (01, 03, 07, etc.)
- `serieComprobante` - Serie de comprobante (F001, B001, etc.)

#### Archivos:
- `maxFileSize:5000` - Máximo 5000 KB
- `fileExtension:pdf,jpg,png` - Extensiones permitidas
- `mimeType:application/pdf` - MIME type permitido

### 🚀 Validaciones Rápidas

```php
<?php
// Validaciones estáticas rápidas
if (Validator::isValidRUC('20123456789')) {
    echo "RUC válido";
}

if (Validator::isValidDNI('12345678')) {
    echo "DNI válido";
}

if (Validator::isValidEmail('test@example.com')) {
    echo "Email válido";
}

if (Validator::isValidTelefono('987654321')) {
    echo "Teléfono válido";
}
```

### 🧹 Sanitización

```php
<?php
// Sanitizar string (eliminar HTML, espacios)
$clean = Validator::sanitize('<script>alert("xss")</script>Texto');
// Resultado: Texto

// Sanitizar número
$number = Validator::sanitizeNumber('$1,234.56');
// Resultado: 1234.56

// Sanitizar email
$email = Validator::sanitizeEmail('TEST@EXAMPLE.COM');
// Resultado: test@example.com
```

---

## 3️⃣ APIRESPONSE.PHP - Respuestas Estandarizadas

### ✨ Características
- Formato JSON consistente
- Códigos HTTP apropiados
- Paginación integrada
- Soporte CORS
- Logging automático de errores

### 📌 Respuestas de Éxito

```php
<?php
require_once 'config/ApiResponse.php';

// Éxito básico
ApiResponse::success(['id' => 1], 'Usuario creado')->send();

// Recurso creado (201)
ApiResponse::created(['id' => 5], 'Producto creado exitosamente')->send();

// Recurso actualizado
ApiResponse::updated(['id' => 10], 'Datos actualizados')->send();

// Recurso eliminado
ApiResponse::deleted('Registro eliminado exitosamente')->send();

// Sin contenido (204)
ApiResponse::noContent()->send();
```

### ❌ Respuestas de Error

```php
<?php
// Error genérico (400)
ApiResponse::error('Datos inválidos', ['campo' => 'error específico'])->send();

// Error de validación (422)
ApiResponse::validationError($validator->errors(), 'Datos incorrectos')->send();

// No autorizado (401)
ApiResponse::unauthorized('Debe iniciar sesión')->send();

// Prohibido (403)
ApiResponse::forbidden('No tiene permisos')->send();

// No encontrado (404)
ApiResponse::notFound('Usuario no encontrado')->send();

// Conflicto (409)
ApiResponse::conflict('El RUC ya está registrado')->send();

// Error interno del servidor (500)
ApiResponse::serverError('Error al procesar', $exception)->send();
```

### 📄 Respuesta con Paginación

```php
<?php
$items = [/* ... array de items ... */];
$total = 150;
$page = 1;
$perPage = 20;

ApiResponse::paginated($items, $total, $page, $perPage, 'Datos obtenidos')->send();

/* Respuesta JSON:
{
    "success": true,
    "message": "Datos obtenidos",
    "data": [...],
    "meta": {
        "pagination": {
            "total": 150,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 8,
            "from": 1,
            "to": 20,
            "has_more": true
        }
    },
    "timestamp": "2025-01-15 10:30:00"
}
*/
```

### 🔗 Fluent Interface

```php
<?php
ApiResponse::success()
    ->withData(['users' => $users])
    ->withMessage('Usuarios obtenidos')
    ->meta(['total' => 100, 'version' => '1.0'])
    ->httpCode(200)
    ->send();
```

### 🚀 Helpers Globales

```php
<?php
// Shortcuts globales
api_success($data, 'Mensaje')->send();
api_error('Error', [])->send();
api_created($data)->send();
api_updated($data)->send();
api_deleted('Eliminado')->send();
api_unauthorized()->send();
api_not_found()->send();
api_server_error('Error', $exception)->send();
```

### ✅ Integración con Validator

```php
<?php
$validator = Validator::make($data)->rules($rules);

if (!$validator->validate()) {
    // Respuesta automática de validación
    ApiResponse::fromValidator($validator)->send();
}
```

---

## 4️⃣ LOGGER.PHP - Sistema de Logging

### ✨ Características
- Niveles PSR-3 (debug, info, warning, error, etc.)
- Canales separados (app, sql, security, audit)
- Rotación automática de archivos
- Logs estructurados con contexto
- Estadísticas de logs

### 📌 Uso Básico

```php
<?php
require_once 'config/Logger.php';

// Logger del canal 'app'
$logger = Logger::channel('app');

// Diferentes niveles
$logger->debug('Valor de variable X', ['x' => 123]);
$logger->info('Usuario inició sesión', ['user_id' => 5]);
$logger->warning('Stock bajo', ['producto' => 'A001', 'stock' => 2]);
$logger->error('Error al procesar pago', ['error' => 'Timeout']);
$logger->critical('Base de datos no responde');
```

### 🎯 Canales Especializados

```php
<?php
// Canal SQL
Logger::channel('sql')->sql(
    "SELECT * FROM usuario WHERE id = ?",
    [5],
    0.0023 // Duración en segundos
);

// Canal Security
Logger::channel('security')->warning('Intento de acceso no autorizado', [
    'ip' => '192.168.1.100',
    'user_id' => 5
]);

// Canal Audit (siempre se registra)
Logger::channel('audit')->audit('Eliminó producto', [
    'producto_id' => 10,
    'motivo' => 'Descontinuado'
]);

// Canal API
Logger::channel('api')->info('Petición API recibida', [
    'endpoint' => '/api/usuarios',
    'method' => 'GET'
]);
```

### 🚨 Log de Excepciones

```php
<?php
try {
    // Código que puede fallar
    throw new Exception('Error de negocio');
} catch (Exception $e) {
    // Log completo de la excepción
    Logger::channel('app')->exception($e, 'Error al procesar compra');
}
```

### 🚀 Helpers Globales

```php
<?php
// Shortcuts globales
log_info('Información importante', ['data' => 'valor']);
log_error('Error crítico', ['code' => 500]);
log_warning('Advertencia');
log_debug('Debug info', ['var' => $x]);

// Logs especializados
log_audit('Modificó configuración', ['campo' => 'email']);
log_sql($sql, $params, $duration);
log_exception($exception);
```

### 📊 Estadísticas y Utilidades

```php
<?php
$logger = Logger::channel('app');

// Obtener estadísticas del log actual
$stats = $logger->getStats();
/*
Array (
    'file' => '/path/to/app-2025-01-15.log',
    'exists' => true,
    'size' => 1048576,
    'size_human' => '1.00 MB',
    'lines' => 5000,
    'last_modified' => 1642252800,
    'last_modified_human' => '2025-01-15 10:30:00'
)
*/

// Leer últimas 50 líneas del log
$lines = $logger->tail(50);
foreach ($lines as $line) {
    echo $line;
}

// Limpiar logs antiguos (más de 30 días)
Logger::cleanOldLogs(30);
```

### ⚙️ Configuración

```php
<?php
// Establecer directorio de logs
Logger::setLogDir('/var/log/sistema');

// Establecer tamaño máximo de archivo (5MB)
Logger::setMaxFileSize(5 * 1024 * 1024);

// Establecer número máximo de archivos rotados
Logger::setMaxFiles(10);

// Establecer nivel mínimo de log
$logger = Logger::channel('app');
$logger->setMinLevel(Logger::WARNING); // Solo WARNING y superiores
```

---

## 5️⃣ SECURITYHELPER.PHP - Seguridad Consolidada

### ✨ Características
- Protección CSRF
- Protección XSS
- Sanitización completa
- Encriptación AES-256
- Password hashing (bcrypt)
- Rate limiting
- Headers de seguridad HTTP

### 🛡️ Protección CSRF

```php
<?php
require_once 'config/SecurityHelper.php';

// En formularios HTML
?>
<form method="POST">
    <?php echo SecurityHelper::csrfField(); ?>
    <!-- Campos del formulario -->
</form>

<!-- O en el <head> para AJAX -->
<?php echo SecurityHelper::csrfMeta(); ?>

<script>
// Obtener token para AJAX
const token = document.querySelector('meta[name="csrf-token"]').content;

fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-Token': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
});
</script>

<?php
// Validar CSRF en el servidor
if (SecurityHelper::validateCsrfToken()) {
    // Token válido, procesar
} else {
    // Token inválido
    api_error('Token de seguridad inválido', [], 403)->send();
}

// O validar y terminar si falla
SecurityHelper::validateCsrfOrDie();
```

### 🔒 Protección XSS

```php
<?php
// Escapar output HTML
$nombre = SecurityHelper::escapeHtml($_POST['nombre']);
echo "<h1>{$nombre}</h1>";

// Escapar atributos
$id = SecurityHelper::escapeAttr($_GET['id']);
echo "<input type='hidden' value='{$id}'>";

// Escapar para JavaScript
$data = SecurityHelper::escapeJs($array);
echo "<script>var data = {$data};</script>";

// Limpiar HTML manteniendo tags específicos
$html = SecurityHelper::cleanHtml($htmlSucio, ['p', 'br', 'strong']);
```

### 🧹 Sanitización

```php
<?php
// Sanitizar string general
$texto = SecurityHelper::sanitize($_POST['texto']);

// Sanitizar email
$email = SecurityHelper::sanitizeEmail($_POST['email']);

// Sanitizar URL
$url = SecurityHelper::sanitizeUrl($_POST['url']);

// Sanitizar número
$precio = SecurityHelper::sanitizeNumber($_POST['precio']);

// Sanitizar nombre de archivo
$filename = SecurityHelper::sanitizeFilename($_FILES['documento']['name']);

// Sanitizar path (prevenir directory traversal)
$path = SecurityHelper::sanitizePath($_GET['path']);
```

### 🔐 Encriptación

```php
<?php
// Establecer clave de encriptación (hacer UNA VEZ al inicio)
SecurityHelper::setEncryptionKey('tu-clave-segura-de-32-caracteres-minimo');

// Encriptar datos sensibles
$numeroTarjeta = '4111111111111111';
$encriptado = SecurityHelper::encrypt($numeroTarjeta);
// Guardar en BD: $encriptado

// Desencriptar
$desencriptado = SecurityHelper::decrypt($encriptado);
// Usar: $desencriptado
```

### 🔑 Passwords

```php
<?php
// Hash password al registrar usuario
$passwordHash = SecurityHelper::hashPassword($_POST['password']);
// Guardar en BD: $passwordHash

// Verificar password al login
$passwordIngresado = $_POST['password'];
$hashAlmacenado = $usuario['password_hash'];

if (SecurityHelper::verifyPassword($passwordIngresado, $hashAlmacenado)) {
    // Password correcto

    // Verificar si necesita rehash (por cambio de algoritmo)
    if (SecurityHelper::needsRehash($hashAlmacenado)) {
        $nuevoHash = SecurityHelper::hashPassword($passwordIngresado);
        // Actualizar en BD
    }
} else {
    // Password incorrecto
}

// Generar password aleatorio seguro
$passwordTemporal = SecurityHelper::generatePassword(16);
echo $passwordTemporal; // "aB3!xY9#mN2$pQ7&"
```

### 🎲 Tokens y Códigos

```php
<?php
// Generar token hexadecimal seguro (para reset password, etc.)
$token = SecurityHelper::generateToken(32);
echo $token; // "a1b2c3d4e5f6..."

// Generar código numérico (para 2FA, verificación, etc.)
$codigo = SecurityHelper::generateCode(6);
echo $codigo; // "123456"
```

### ⏱️ Rate Limiting

```php
<?php
// Limitar intentos de login
$ip = SecurityHelper::getClientIp();
$key = "login_attempts:{$ip}";

if (!SecurityHelper::checkRateLimit($key, 5, 300)) {
    // Demasiados intentos (5 en 300 segundos)
    api_error('Demasiados intentos. Espere 5 minutos.', [], 429)->send();
}

// Procesar login...

// Si login exitoso, resetear contador
SecurityHelper::resetRateLimit($key);
```

### 🌐 IP y Headers

```php
<?php
// Obtener IP del cliente (considerando proxies)
$ip = SecurityHelper::getClientIp();

// Verificar whitelist
$whitelist = ['192.168.1.100', '10.0.0.1'];
if (!SecurityHelper::isIpWhitelisted($ip, $whitelist)) {
    api_error('IP no permitida', [], 403)->send();
}

// Verificar blacklist
$blacklist = ['1.2.3.4', '5.6.7.8'];
if (SecurityHelper::isIpBlacklisted($ip, $blacklist)) {
    api_error('IP bloqueada', [], 403)->send();
}
```

### 🔐 Headers de Seguridad HTTP

```php
<?php
// Establecer headers de seguridad (llamar al inicio de cada request)
SecurityHelper::setSecurityHeaders();

// Headers establecidos:
// - Content-Security-Policy
// - X-Frame-Options: DENY
// - X-Content-Type-Options: nosniff
// - X-XSS-Protection
// - Referrer-Policy
// - Permissions-Policy
// - Strict-Transport-Security (si es HTTPS)
```

### 🔒 Sesiones Seguras

```php
<?php
// Forzar HTTPS
SecurityHelper::forceHttps();

// Prevenir session fixation (al login)
SecurityHelper::regenerateSession();

// Destruir sesión completamente (al logout)
SecurityHelper::destroySession();

// Verificar si es HTTPS
if (SecurityHelper::isHttps()) {
    // Operación sensible
}
```

---

## 📋 EJEMPLO COMPLETO - CRUD CON NUEVAS CLASES

```php
<?php
// archivo: ajax/producto_refactorizado.php

require_once '../config/Database.php';
require_once '../config/Validator.php';
require_once '../config/ApiResponse.php';
require_once '../config/Logger.php';
require_once '../config/SecurityHelper.php';

// Establecer headers de seguridad
SecurityHelper::setSecurityHeaders();

// Validar que sea petición AJAX
$error = ApiResponse::validateRequest('POST');
if ($error) {
    $error->send();
}

// Validar CSRF
SecurityHelper::validateCsrfOrDie();

// Obtener operación
$op = $_GET['op'] ?? '';

switch ($op) {
    case 'guardar':
        // Log de auditoría
        log_audit('Guardando producto', ['op' => 'guardar']);

        // Validar datos
        $validator = Validator::make($_POST)
            ->rules([
                'nombre' => 'required|min:3|max:100',
                'codigo' => 'required|alphaNum|max:20',
                'precio' => 'required|numeric|minValue:0',
                'stock' => 'required|integer|minValue:0'
            ]);

        if (!$validator->validate()) {
            ApiResponse::fromValidator($validator)->send();
        }

        // Sanitizar inputs
        $nombre = SecurityHelper::sanitize($_POST['nombre']);
        $codigo = SecurityHelper::sanitize($_POST['codigo']);
        $precio = SecurityHelper::sanitizeNumber($_POST['precio']);
        $stock = SecurityHelper::sanitizeNumber($_POST['stock']);

        // Insertar en BD usando transacción
        $resultado = db_transaction(function($db) use ($nombre, $codigo, $precio, $stock) {
            $id = $db->insert(
                "INSERT INTO producto (nombre, codigo, precio, stock) VALUES (?, ?, ?, ?)",
                [$nombre, $codigo, $precio, $stock]
            );

            if (!$id) {
                throw new Exception('Error al insertar producto');
            }

            return $id;
        });

        if ($resultado) {
            log_info('Producto creado exitosamente', ['id' => $resultado]);
            api_created(['id' => $resultado], 'Producto creado exitosamente')->send();
        } else {
            log_error('Error al crear producto');
            api_server_error('Error al crear producto')->send();
        }
        break;

    case 'listar':
        try {
            $productos = db_select("SELECT * FROM producto WHERE estado = ?", [1]);

            api_success($productos, 'Productos obtenidos')->send();

        } catch (Exception $e) {
            log_exception($e, 'Error al listar productos');
            api_server_error('Error al obtener productos', $e)->send();
        }
        break;

    case 'actualizar':
        $validator = Validator::make($_POST)
            ->rules([
                'idproducto' => 'required|integer',
                'nombre' => 'required|min:3',
                'precio' => 'required|numeric|minValue:0'
            ]);

        if (!$validator->validate()) {
            ApiResponse::fromValidator($validator)->send();
        }

        $id = $_POST['idproducto'];
        $nombre = SecurityHelper::sanitize($_POST['nombre']);
        $precio = SecurityHelper::sanitizeNumber($_POST['precio']);

        $filas = db_update(
            "UPDATE producto SET nombre = ?, precio = ? WHERE idproducto = ?",
            [$nombre, $precio, $id]
        );

        if ($filas > 0) {
            log_audit('Producto actualizado', ['id' => $id]);
            api_updated(['id' => $id], 'Producto actualizado')->send();
        } else {
            api_not_found('Producto no encontrado')->send();
        }
        break;

    case 'eliminar':
        $id = $_POST['idproducto'] ?? 0;

        if (!$id) {
            api_error('ID no proporcionado')->send();
        }

        $filas = db_delete("DELETE FROM producto WHERE idproducto = ?", [$id]);

        if ($filas > 0) {
            log_audit('Producto eliminado', ['id' => $id]);
            api_deleted('Producto eliminado')->send();
        } else {
            api_not_found('Producto no encontrado')->send();
        }
        break;

    default:
        api_error('Operación no válida', [], 400)->send();
}
```

---

## 🎯 MEJORES PRÁCTICAS

### ✅ DO (Hacer)

1. **SIEMPRE usar prepared statements**
   ```php
   db_select("SELECT * FROM usuario WHERE id = ?", [$id]);
   ```

2. **SIEMPRE validar inputs**
   ```php
   $validator->rules(['email' => 'required|email']);
   ```

3. **SIEMPRE sanitizar outputs**
   ```php
   echo SecurityHelper::escapeHtml($nombre);
   ```

4. **SIEMPRE usar transacciones para operaciones múltiples**
   ```php
   db_transaction(function($db) { /* operaciones */ });
   ```

5. **SIEMPRE loggear operaciones críticas**
   ```php
   log_audit('Usuario eliminado', ['id' => $userId]);
   ```

6. **SIEMPRE usar ApiResponse para respuestas AJAX**
   ```php
   api_success($data, 'Mensaje')->send();
   ```

### ❌ DON'T (No hacer)

1. **NO concatenar SQL directamente**
   ```php
   // MAL
   $sql = "SELECT * FROM usuario WHERE id = $id";

   // BIEN
   db_select("SELECT * FROM usuario WHERE id = ?", [$id]);
   ```

2. **NO confiar en inputs del usuario**
   ```php
   // MAL
   $nombre = $_POST['nombre'];

   // BIEN
   $nombre = SecurityHelper::sanitize($_POST['nombre']);
   ```

3. **NO hacer echo de datos sin escapar**
   ```php
   // MAL
   echo $nombre;

   // BIEN
   echo SecurityHelper::escapeHtml($nombre);
   ```

4. **NO ignorar validaciones**
   ```php
   // MAL
   if ($_POST['email']) { /* procesar */ }

   // BIEN
   if (Validator::isValidEmail($_POST['email'])) { /* procesar */ }
   ```

5. **NO usar password_hash directamente**
   ```php
   // MAL
   $hash = password_hash($password, PASSWORD_DEFAULT);

   // BIEN
   $hash = SecurityHelper::hashPassword($password);
   ```

---

## 🚀 MIGRACIÓN GRADUAL

### Paso 1: Incluir nuevas clases
```php
require_once 'config/Database.php';
require_once 'config/Validator.php';
require_once 'config/ApiResponse.php';
require_once 'config/Logger.php';
require_once 'config/SecurityHelper.php';
```

### Paso 2: Reemplazar consultas SQL
```php
// Antes
$sql = "SELECT * FROM usuario WHERE id = '$id'";
$result = ejecutarConsulta($sql);

// Después
$result = db_select("SELECT * FROM usuario WHERE id = ?", [$id]);
```

### Paso 3: Agregar validaciones
```php
// Antes
if (empty($_POST['email'])) { /* error */ }

// Después
$validator = Validator::make($_POST)->rules(['email' => 'required|email']);
if (!$validator->validate()) {
    ApiResponse::fromValidator($validator)->send();
}
```

### Paso 4: Estandarizar respuestas
```php
// Antes
echo json_encode(['success' => true, 'data' => $data]);

// Después
api_success($data, 'Operación exitosa')->send();
```

### Paso 5: Agregar logging
```php
// Agregar en operaciones críticas
log_audit('Usuario creado', ['id' => $userId]);
log_error('Error al procesar pago', ['error' => $e->getMessage()]);
```

---

## 📞 SOPORTE

Para dudas o problemas con las nuevas clases:
1. Revisar esta guía completa
2. Verificar logs en `/v3.3/logs/`
3. Consultar código fuente de las clases en `/v3.3/config/`

---

**Última actualización: 2025-01-15**
**Versión: 1.0**
