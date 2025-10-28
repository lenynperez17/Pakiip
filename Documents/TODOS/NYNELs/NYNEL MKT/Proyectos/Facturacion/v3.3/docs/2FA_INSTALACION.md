# 🔐 INSTALACIÓN Y USO DE AUTENTICACIÓN DE DOS FACTORES (2FA)

## Sistema de Facturación v3.3 - Autenticación de Dos Factores con TOTP

---

## 📋 TABLA DE CONTENIDOS

1. [Requisitos](#requisitos)
2. [Instalación de Base de Datos](#instalación-de-base-de-datos)
3. [Configuración del Sistema](#configuración-del-sistema)
4. [Integración en Login](#integración-en-login)
5. [Interfaz de Usuario](#interfaz-de-usuario)
6. [Códigos de Respaldo](#códigos-de-respaldo)
7. [Seguridad y Mejores Prácticas](#seguridad-y-mejores-prácticas)
8. [Solución de Problemas](#solución-de-problemas)

---

## 📦 REQUISITOS

### Extensiones PHP requeridas:
- ✅ PHP 7.4 o superior
- ✅ OpenSSL (para encriptación)
- ✅ MySQLi (ya instalado)

### Aplicaciones móviles compatibles:
- Google Authenticator (Android/iOS)
- Microsoft Authenticator (Android/iOS)
- Authy (Android/iOS)
- FreeOTP (Android/iOS)

---

## 💾 INSTALACIÓN DE BASE DE DATOS

### Paso 1: Ejecutar script SQL

```bash
# Ubicación del script
/config/2fa_table.sql
```

```sql
# Ejecutar en MySQL
mysql -u [usuario] -p [base_de_datos] < config/2fa_table.sql
```

### Paso 2: Verificar creación de tablas

```sql
-- Verificar tablas creadas
SHOW TABLES LIKE 'user_2fa%';

-- Resultado esperado:
-- user_2fa
-- user_2fa_log

-- Verificar vista
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- Resultado esperado incluye:
-- v_users_2fa_status
```

---

## ⚙️ CONFIGURACIÓN DEL SISTEMA

### Paso 1: Configurar clave de encriptación

Editar archivo `.env` o configuración del servidor:

```bash
# .env
ENCRYPTION_KEY=tu_clave_super_secreta_de_al_menos_32_caracteres
```

**IMPORTANTE**: Esta clave debe ser:
- Única y secreta
- Mínimo 32 caracteres
- Nunca compartirla en repositorios públicos
- Cambiarla periódicamente

### Paso 2: Verificar archivos creados

```
/config/
├── 2fa_table.sql         ✅ Script de base de datos
├── 2fa_helper.php        ✅ Funciones de 2FA
└── ajax/
    └── 2fa.php           ✅ Endpoint AJAX
```

---

## 🔐 INTEGRACIÓN EN LOGIN

### Modificar proceso de login existente

**Archivo:** `/ajax/usuario.php` (o equivalente)

```php
case 'verificar':
    // ... código existente de verificación de usuario/contraseña ...

    if ($rspta && password_verify($clave, $reg->clave)) {

        // ========== NUEVO: Verificar si tiene 2FA activo ==========
        require_once "../config/2fa_helper.php";

        if (tiene2FAActivo($reg->idusuario)) {
            // Usuario tiene 2FA - requiere segundo factor
            echo json_encode([
                'success' => true,
                'requires_2fa' => true,
                'idusuario' => $reg->idusuario,
                'message' => 'Ingresa tu código de autenticación'
            ]);
            exit();
        }

        // Si no tiene 2FA, proceder con login normal
        $_SESSION["idusuario"] = $reg->idusuario;
        $_SESSION["nombre"] = $reg->nombre;
        // ... resto del código de sesión ...

        echo json_encode(['success' => true, 'requires_2fa' => false]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    }
    break;

case 'verificar2FA':
    // Nuevo caso para verificar código 2FA
    $idusuario = isset($_POST['idusuario']) ? limpiarCadena($_POST['idusuario']) : '';
    $code = isset($_POST['code']) ? limpiarCadena($_POST['code']) : '';

    require_once "../config/2fa_helper.php";

    $resultado = verificar2FALogin($idusuario, $code);

    if ($resultado['success']) {
        // Código válido - crear sesión
        $stmt = $conexion->prepare("SELECT * FROM usuario WHERE idusuario = ?");
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $reg = $stmt->get_result()->fetch_object();

        $_SESSION["idusuario"] = $reg->idusuario;
        $_SESSION["nombre"] = $reg->nombre;
        // ... resto de variables de sesión ...

        echo json_encode(['success' => true, 'message' => 'Login exitoso']);
    } else {
        echo json_encode(['success' => false, 'message' => $resultado['message']]);
    }
    break;
```

---

## 🎨 INTERFAZ DE USUARIO

### 1. Página de configuración 2FA (ejemplo)

**Archivo:** `/vistas/configuracion_2fa.php`

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración 2FA</title>
    <style>
        .qr-container { text-align: center; margin: 20px 0; }
        .backup-codes {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
        }
        .backup-code {
            display: inline-block;
            margin: 5px;
            padding: 5px 10px;
            background: white;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div id="2fa-setup" style="display: none;">
        <h2>Configurar Autenticación de Dos Factores</h2>

        <div class="qr-container">
            <p>Escanea este código QR con tu aplicación de autenticación:</p>
            <img id="qr-code" src="" alt="QR Code">
            <p>O ingresa este código manualmente:</p>
            <code id="secret-key"></code>
        </div>

        <div>
            <label>Ingresa el código de 6 dígitos de tu aplicación:</label>
            <input type="text" id="2fa-code" maxlength="6" pattern="\d{6}">
            <button onclick="activar2FA()">Activar 2FA</button>
        </div>

        <div id="backup-codes-container" style="display: none;">
            <h3>⚠️ Códigos de Respaldo - GUÁRDALOS EN UN LUGAR SEGURO</h3>
            <div class="backup-codes" id="backup-codes"></div>
            <button onclick="imprimirBackupCodes()">Imprimir Códigos</button>
        </div>
    </div>

    <div id="2fa-status">
        <h2>Estado de 2FA</h2>
        <p id="status-message">Cargando...</p>
        <button id="btn-iniciar" onclick="iniciarSetup2FA()">Activar 2FA</button>
        <button id="btn-desactivar" onclick="desactivar2FA()" style="display: none;">Desactivar 2FA</button>
    </div>

    <script>
        // Obtener token CSRF del meta tag
        const csrf_token = document.querySelector('meta[name="csrf-token"]').content;

        // Verificar status actual
        fetch('../ajax/2fa.php?op=verificarStatus', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=${csrf_token}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.datos.tiene_2fa) {
                document.getElementById('status-message').textContent = 'Estado: Activo ✅';
                document.getElementById('btn-iniciar').style.display = 'none';
                document.getElementById('btn-desactivar').style.display = 'inline-block';
            } else {
                document.getElementById('status-message').textContent = 'Estado: Inactivo';
            }
        });

        function iniciarSetup2FA() {
            fetch('../ajax/2fa.php?op=iniciarSetup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${csrf_token}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.exito) {
                    document.getElementById('qr-code').src = data.datos.qr_code_url;
                    document.getElementById('secret-key').textContent = data.datos.secret;

                    // Mostrar códigos de respaldo
                    const backupCodesDiv = document.getElementById('backup-codes');
                    backupCodesDiv.innerHTML = data.datos.backup_codes
                        .map(code => `<span class="backup-code">${code}</span>`)
                        .join('');

                    document.getElementById('backup-codes-container').style.display = 'block';
                    document.getElementById('2fa-setup').style.display = 'block';
                    document.getElementById('2fa-status').style.display = 'none';
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        }

        function activar2FA() {
            const code = document.getElementById('2fa-code').value;

            if (!/^\d{6}$/.test(code)) {
                alert('El código debe contener 6 dígitos');
                return;
            }

            fetch('../ajax/2fa.php?op=activar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `code=${code}&csrf_token=${csrf_token}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.exito) {
                    alert('✅ 2FA activado exitosamente!');
                    location.reload();
                } else {
                    alert('❌ ' + data.mensaje);
                }
            });
        }

        function desactivar2FA() {
            const code = prompt('Ingresa tu código 2FA actual para desactivar:');

            if (!code) return;

            fetch('../ajax/2fa.php?op=desactivar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `code=${code}&csrf_token=${csrf_token}`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.mensaje);
                if (data.exito) location.reload();
            });
        }

        function imprimirBackupCodes() {
            window.print();
        }
    </script>
</body>
</html>
```

### 2. Modal de verificación 2FA en login

```html
<!-- Modal para ingresar código 2FA -->
<div id="modal-2fa" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Autenticación de Dos Factores</h3>
        <p>Ingresa el código de 6 dígitos de tu aplicación de autenticación:</p>

        <input type="text" id="2fa-login-code" maxlength="6" pattern="\d{6}"
               placeholder="000000" autofocus>

        <p class="text-muted">O usa un código de respaldo:</p>
        <input type="text" id="2fa-backup-code" maxlength="9" pattern="[A-Z0-9]{4}-[A-Z0-9]{4}"
               placeholder="XXXX-XXXX">

        <button onclick="verificar2FALogin()">Verificar</button>
        <button onclick="cancelar2FA()">Cancelar</button>
    </div>
</div>

<script>
let usuario_id_temporal = null;

function verificar2FALogin() {
    const code = document.getElementById('2fa-login-code').value ||
                 document.getElementById('2fa-backup-code').value;

    if (!code) {
        alert('Debes ingresar un código');
        return;
    }

    fetch('../ajax/usuario.php?op=verificar2FA', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `idusuario=${usuario_id_temporal}&code=${code}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '../vistas/escritorio.php';
        } else {
            alert(data.message);
        }
    });
}

// En la función de login existente, si requires_2fa es true:
function loginExitoso(data) {
    if (data.requires_2fa) {
        usuario_id_temporal = data.idusuario;
        document.getElementById('modal-2fa').style.display = 'block';
    } else {
        window.location.href = '../vistas/escritorio.php';
    }
}
</script>
```

---

## 🔑 CÓDIGOS DE RESPALDO

### ¿Qué son los códigos de respaldo?

Los códigos de respaldo son códigos de un solo uso que permiten acceder a tu cuenta si:
- Pierdes tu teléfono
- No tienes acceso a la app de autenticación
- El dispositivo se daña

### Características:
- ✅ 8 códigos generados automáticamente
- ✅ Formato: `XXXX-XXXX` (ej: `A3B4-C5D6`)
- ✅ Se invalidan después de usarse
- ✅ Se pueden regenerar en cualquier momento

### Buenas prácticas:
1. **Guardar en lugar seguro** (no en el teléfono)
2. **Imprimir** y guardar físicamente
3. **NO compartir** con nadie
4. **Regenerar** si sospechas que fueron comprometidos

---

## 🛡️ SEGURIDAD Y MEJORES PRÁCTICAS

### Configuración recomendada:

```php
// En 2fa_helper.php, ajustar si es necesario:

// Ventana de tiempo para códigos TOTP (±30 segundos)
$window = 1; // Recomendado: 1 (permite ±30s de desfase de reloj)

// Máximo de intentos fallidos antes de bloqueo
$max_intentos = 5; // Recomendado: 5

// Tiempo de bloqueo (en minutos)
$tiempo_bloqueo = 15; // Recomendado: 15 minutos
```

### Medidas de seguridad implementadas:

1. ✅ **Encriptación AES-256-CBC** para secret keys
2. ✅ **Rate limiting** (20 requests/minuto)
3. ✅ **Bloqueo temporal** tras 5 intentos fallidos
4. ✅ **Logging completo** de eventos 2FA
5. ✅ **Validación CSRF** en todas las operaciones
6. ✅ **Auditoría integrada** con sistema existente

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Problema: "Código inválido" constante

**Causa**: Desfase de reloj entre servidor y dispositivo

**Solución**:
```bash
# Sincronizar reloj del servidor
sudo ntpdate -s time.nist.gov

# O configurar NTP permanente
sudo systemctl enable systemd-timesyncd
sudo systemctl start systemd-timesyncd
```

### Problema: Usuario bloqueado

**Solución manual**:
```sql
-- Desbloquear usuario manualmente
UPDATE user_2fa
SET failed_attempts = 0, locked_until = NULL
WHERE idusuario = [ID_USUARIO];
```

### Problema: Códigos de respaldo perdidos

**Solución**:
1. Usuario debe ingresar con código TOTP actual
2. Ir a configuración 2FA
3. Usar opción "Regenerar códigos de respaldo"
4. Guardar nuevos códigos en lugar seguro

### Problema: No se genera QR code

**Verificar**:
```php
// Probar generación manual de QR
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" .
          urlencode("otpauth://totp/Test:test@example.com?secret=ABCDEFGHIJKLMNOP&issuer=Test");

// Probar en navegador directamente
```

---

## 📊 MONITOREO Y ESTADÍSTICAS

### Consultas útiles:

```sql
-- Ver usuarios con 2FA activo
SELECT * FROM v_users_2fa_status WHERE status_2fa = 'Activo';

-- Ver eventos recientes de 2FA
SELECT * FROM user_2fa_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;

-- Ver intentos fallidos por usuario
SELECT
    u.nombre,
    tfa.failed_attempts,
    tfa.locked_until
FROM user_2fa tfa
JOIN usuario u ON tfa.idusuario = u.idusuario
WHERE tfa.failed_attempts > 0;

-- Ver uso de códigos de respaldo
SELECT
    u.nombre,
    l.created_at,
    l.details
FROM user_2fa_log l
JOIN usuario u ON l.idusuario = u.idusuario
WHERE l.event_type = 'BACKUP_CODE_USED'
ORDER BY l.created_at DESC;
```

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] Ejecutar script SQL `2fa_table.sql`
- [ ] Verificar tablas creadas (`user_2fa`, `user_2fa_log`)
- [ ] Configurar `ENCRYPTION_KEY` en entorno
- [ ] Copiar archivos PHP (`2fa_helper.php`, `ajax/2fa.php`)
- [ ] Modificar login para integrar 2FA
- [ ] Crear página de configuración de usuario
- [ ] Probar flujo completo de activación
- [ ] Probar flujo completo de login con 2FA
- [ ] Probar códigos de respaldo
- [ ] Verificar logging de eventos
- [ ] Configurar sincronización de reloj (NTP)
- [ ] Documentar para usuarios finales

---

## 📞 SOPORTE

Para problemas técnicos o preguntas:
- Revisar logs: `/var/log/php-errors.log`
- Revisar tabla: `user_2fa_log`
- Consultar auditoría: `audit_log` donde `modulo = 'usuario'`

---

**Sistema de Facturación v3.3**
**Autenticación de Dos Factores (2FA) - Documentación Completa**
**Fecha:** 2025-10-10
