# 🚀 INSTRUCCIONES DE DEPLOYMENT EN VPS

## ⚠️ PROBLEMA IDENTIFICADO

Firebase Hosting NO puede servir Next.js con rutas dinámicas correctamente. La aplicación tiene 4 rutas dinámicas esenciales:
- `/admin/orders/[orderId]`
- `/driver/orders/[orderId]`
- `/order/[orderId]`
- `/vendor/[vendorId]`

Por esta razón, DEBES usar tu VPS para alojar la aplicación.

---

## 📋 REQUISITOS PREVIOS

- **VPS**: srv830342.hstgr.cloud (147.79.74.193)
- **Acceso SSH**: root@147.79.74.193
- **Contraseña**: Lenynperez17?
- **Sistema Operativo**: Ubuntu 24.04 LTS

---

## 📦 ARCHIVOS GENERADOS

En tu carpeta del proyecto encontrarás 3 archivos nuevos:

1. **pakiip-build.tar.gz** (26 MB) - Build compilado de la aplicación
2. **pakiip.env** - Variables de entorno para producción
3. **deploy-vps.sh** - Script automatizado de deployment

---

## 🔧 PASO 1: SUBIR ARCHIVOS AL VPS

### Opción A: Usando SCP desde PowerShell (Recomendado)

Abre PowerShell y ejecuta:

```powershell
# Navegar a la carpeta del proyecto
cd "C:\Users\Lenyn\Documents\TODOS\NYNELs\NYNEL MKT\Proyectos\Pakiip"

# Subir build
scp pakiip-build.tar.gz root@147.79.74.193:~/

# Subir variables de entorno
scp pakiip.env root@147.79.74.193:~/

# Subir script de deployment
scp deploy-vps.sh root@147.79.74.193:~/
```

Te pedirá la contraseña 3 veces: `Lenynperez17?`

### Opción B: Usando WinSCP (Interfaz Gráfica)

1. Descarga WinSCP: https://winscp.net/
2. Conecta al VPS:
   - Host: 147.79.74.193
   - Usuario: root
   - Contraseña: Lenynperez17?
3. Arrastra estos 3 archivos a `/root/`:
   - pakiip-build.tar.gz
   - pakiip.env
   - deploy-vps.sh

---

## 🚀 PASO 2: EJECUTAR DEPLOYMENT EN EL VPS

### 1. Conectar por SSH

Desde PowerShell:

```powershell
ssh root@147.79.74.193
```

Contraseña: `Lenynperez17?`

### 2. Dar permisos de ejecución al script

```bash
chmod +x ~/deploy-vps.sh
```

### 3. Ejecutar el script de deployment

```bash
bash ~/deploy-vps.sh
```

El script hará AUTOMÁTICAMENTE:
1. ✅ Instalar Node.js 18 (si no existe)
2. ✅ Instalar PM2 (gestor de procesos)
3. ✅ Instalar Nginx (servidor web)
4. ✅ Crear directorio `/var/www/pakiip`
5. ✅ Extraer el build
6. ✅ Configurar variables de entorno
7. ✅ Iniciar la aplicación con PM2
8. ✅ Configurar Nginx como reverse proxy
9. ✅ Configurar PM2 para auto-inicio

**Duración estimada:** 3-5 minutos

---

## 🌐 PASO 3: CONFIGURAR DNS

### Verificar IP del VPS

Después del deployment, el script te mostrará la IP del servidor. Verifica que sea:

```
147.79.74.193
```

### Configurar DNS de pakiip.com

Ve a tu proveedor de DNS (donde compraste el dominio) y configura:

**Registro A:**
```
Nombre: @
Tipo: A
Valor: 147.79.74.193
TTL: 3600
```

**Registro A (www):**
```
Nombre: www
Tipo: A
Valor: 147.79.74.193
TTL: 3600
```

**⏱️ Tiempo de propagación:** 5 minutos a 24 horas (usualmente 15 minutos)

---

## ✅ VERIFICACIÓN

### 1. Verificar que la aplicación esté corriendo

```bash
pm2 status
```

Deberías ver:

```
┌─────┬──────────┬─────────────┬─────────┬─────────┬──────────┐
│ id  │ name     │ mode        │ ↺       │ status  │ cpu      │
├─────┼──────────┼─────────────┼─────────┼─────────┼──────────┤
│ 0   │ pakiip   │ cluster     │ 0       │ online  │ 0%       │
└─────┴──────────┴─────────────┴─────────┴─────────┴──────────┘
```

### 2. Ver logs en tiempo real

```bash
pm2 logs pakiip
```

### 3. Probar desde el navegador

Abre en tu navegador:
- `http://147.79.74.193` (debería funcionar inmediatamente)
- `http://pakiip.com` (funcionará después de propagar el DNS)

### 4. Verificar que NO haya el error antiguo

Abre la consola del navegador (F12) y verifica que NO aparezcan:
- `be5607365279cd3e.js` (archivo viejo)
- Error: "Cannot read properties of undefined (reading 'indexOf')"

Si ves archivos nuevos con hash diferente, ¡el deployment funcionó! ✅

---

## 📝 COMANDOS ÚTILES

### PM2 (Gestión de la aplicación)

```bash
pm2 status              # Ver estado de la aplicación
pm2 logs pakiip         # Ver logs en tiempo real
pm2 restart pakiip      # Reiniciar aplicación
pm2 stop pakiip         # Detener aplicación
pm2 start pakiip        # Iniciar aplicación
pm2 delete pakiip       # Eliminar aplicación de PM2
```

### Nginx (Servidor web)

```bash
systemctl status nginx      # Ver estado de Nginx
systemctl restart nginx     # Reiniciar Nginx
systemctl stop nginx        # Detener Nginx
systemctl start nginx       # Iniciar Nginx
nginx -t                    # Verificar configuración
```

### Ver logs del sistema

```bash
tail -f /var/log/pakiip-error.log   # Logs de errores de la app
tail -f /var/log/pakiip-out.log     # Logs de salida de la app
tail -f /var/log/nginx/pakiip-access.log  # Logs de acceso de Nginx
tail -f /var/log/nginx/pakiip-error.log   # Logs de errores de Nginx
```

---

## 🔄 ACTUALIZAR LA APLICACIÓN (FUTUROS DEPLOYMENTS)

Cuando hagas cambios en el código y quieras actualizar:

### 1. Desde tu máquina local (PowerShell):

```powershell
# En la carpeta del proyecto
npm run build

# Comprimir build
cd .next
tar -czf ../pakiip-build.tar.gz standalone/
cd ..

# Subir al VPS
scp pakiip-build.tar.gz root@147.79.74.193:~/
```

### 2. En el VPS (SSH):

```bash
# Ejecutar el script de deployment nuevamente
bash ~/deploy-vps.sh
```

El script se encargará de:
1. Detener la aplicación antigua
2. Limpiar archivos viejos
3. Extraer el nuevo build
4. Reiniciar la aplicación

**No necesitas reinstalar Node.js, PM2 o Nginx en futuros deployments.**

---

## 🔒 CONFIGURAR SSL/HTTPS (OPCIONAL PERO RECOMENDADO)

Una vez que el DNS esté propagado:

```bash
# Instalar Certbot
apt-get install -y certbot python3-certbot-nginx

# Obtener certificado SSL gratuito
certbot --nginx -d pakiip.com -d www.pakiip.com

# El certificado se renovará automáticamente cada 90 días
```

Certbot configurará automáticamente Nginx para usar HTTPS.

---

## ❓ SOLUCIÓN DE PROBLEMAS

### La aplicación no inicia

```bash
pm2 logs pakiip --lines 50
```

Revisa los logs y busca errores.

### Nginx da error 502 Bad Gateway

```bash
# Verificar que la app esté corriendo
pm2 status

# Si está detenida, iniciarla
pm2 start pakiip

# Verificar que escuche en el puerto 3000
netstat -tulpn | grep 3000
```

### No puedo acceder por el dominio

```bash
# Verificar que el DNS esté apuntando correctamente
nslookup pakiip.com

# Debería mostrar: 147.79.74.193
```

Si no, revisa la configuración de DNS en tu proveedor.

### La aplicación consume mucha memoria

```bash
# Verificar consumo
pm2 status

# Reiniciar si es necesario
pm2 restart pakiip
```

PM2 está configurado para reiniciar automáticamente si supera 500MB.

---

## 🎉 ¡LISTO!

Una vez completados todos los pasos:

✅ Tu aplicación estará corriendo en el VPS
✅ Nginx servirá la aplicación en el puerto 80
✅ PM2 mantendrá la aplicación corriendo 24/7
✅ La aplicación se reiniciará automáticamente si falla
✅ TODOS los cambios de código se reflejarán correctamente
✅ Las rutas dinámicas funcionarán perfectamente

---

## 📞 SOPORTE

Si tienes problemas:

1. Revisa los logs: `pm2 logs pakiip`
2. Verifica el estado: `pm2 status`
3. Verifica Nginx: `nginx -t`
4. Consulta la documentación de PM2: https://pm2.keymetrics.io/
5. Consulta la documentación de Next.js: https://nextjs.org/docs

---

**Fecha de creación:** 2025-11-20
**Versión:** 1.0
