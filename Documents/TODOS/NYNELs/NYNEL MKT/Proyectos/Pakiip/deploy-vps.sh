#!/bin/bash

###############################################################################
# SCRIPT DE DEPLOYMENT PARA PAKIIP EN VPS
# Ejecutar este script EN EL VPS después de subir pakiip-build.tar.gz
###############################################################################

set -e  # Salir si hay errores

echo "🚀 =========================================="
echo "🚀 DEPLOYMENT DE PAKIIP EN VPS"
echo "🚀 =========================================="
echo ""

# Configuración
APP_NAME="pakiip"
APP_DIR="/var/www/$APP_NAME"
PORT=3000
DOMAIN="pakiip.com"

# 1. Instalar Node.js y dependencias si no existen
echo "📦 1. Verificando Node.js..."
if ! command -v node &> /dev/null; then
    echo "   Instalando Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
else
    echo "   ✅ Node.js ya instalado: $(node -v)"
fi

# 2. Instalar PM2 globalmente
echo ""
echo "📦 2. Verificando PM2..."
if ! command -v pm2 &> /dev/null; then
    echo "   Instalando PM2..."
    npm install -g pm2
else
    echo "   ✅ PM2 ya instalado: $(pm2 -v)"
fi

# 3. Instalar Nginx si no existe
echo ""
echo "📦 3. Verificando Nginx..."
if ! command -v nginx &> /dev/null; then
    echo "   Instalando Nginx..."
    apt-get update
    apt-get install -y nginx
    systemctl enable nginx
else
    echo "   ✅ Nginx ya instalado"
fi

# 4. Crear directorio de la aplicación
echo ""
echo "📁 4. Creando directorio de la aplicación..."
mkdir -p $APP_DIR
cd $APP_DIR

# 5. Detener aplicación anterior si existe
echo ""
echo "🛑 5. Deteniendo aplicación anterior..."
pm2 delete $APP_NAME 2>/dev/null || echo "   No hay aplicación anterior"

# 6. Limpiar directorio
echo ""
echo "🧹 6. Limpiando directorio..."
rm -rf standalone/

# 7. Extraer nuevo build
echo ""
echo "📦 7. Extrayendo nuevo build..."
if [ -f ~/pakiip-build.tar.gz ]; then
    tar -xzf ~/pakiip-build.tar.gz -C $APP_DIR
    echo "   ✅ Build extraído correctamente"
else
    echo "   ❌ ERROR: No se encontró ~/pakiip-build.tar.gz"
    echo "   Sube el archivo pakiip-build.tar.gz al home del usuario root"
    exit 1
fi

# 8. Copiar variables de entorno
echo ""
echo "🔐 8. Configurando variables de entorno..."
if [ -f ~/pakiip.env ]; then
    cp ~/pakiip.env $APP_DIR/standalone/.env
    echo "   ✅ Variables de entorno copiadas"
else
    echo "   ⚠️  ADVERTENCIA: No se encontró ~/pakiip.env"
    echo "   La aplicación podría no funcionar sin las variables de entorno"
fi

# 9. Configurar PM2
echo ""
echo "⚙️  9. Configurando PM2..."
cd $APP_DIR/standalone

# Crear archivo ecosystem para PM2
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'pakiip',
    script: './server.js',
    instances: 'max',
    exec_mode: 'cluster',
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    },
    error_file: '/var/log/pakiip-error.log',
    out_file: '/var/log/pakiip-out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    max_memory_restart: '500M',
    restart_delay: 4000
  }]
}
EOF

# 10. Iniciar aplicación con PM2
echo ""
echo "🚀 10. Iniciando aplicación con PM2..."
pm2 start ecosystem.config.js
pm2 save
pm2 startup systemd -u root --hp /root

echo ""
echo "✅ Aplicación iniciada correctamente"
pm2 status

# 11. Configurar Nginx
echo ""
echo "🌐 11. Configurando Nginx..."
cat > /etc/nginx/sites-available/$APP_NAME << EOF
# Configuración de Nginx para Pakiip
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;

    # Logs
    access_log /var/log/nginx/${APP_NAME}-access.log;
    error_log /var/log/nginx/${APP_NAME}-error.log;

    # Proxy a Next.js
    location / {
        proxy_pass http://localhost:$PORT;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # Assets estáticos (con caché)
    location /_next/static {
        proxy_pass http://localhost:$PORT;
        proxy_cache_valid 200 1y;
        add_header Cache-Control "public, immutable";
    }

    # Imágenes (con caché)
    location /_next/image {
        proxy_pass http://localhost:$PORT;
        proxy_cache_valid 200 1h;
    }
}
EOF

# Habilitar sitio
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/

# Eliminar sitio por defecto si existe
rm -f /etc/nginx/sites-enabled/default

# Verificar configuración de Nginx
echo ""
echo "🔍 Verificando configuración de Nginx..."
nginx -t

# Reiniciar Nginx
echo ""
echo "🔄 Reiniciando Nginx..."
systemctl restart nginx

echo ""
echo "✅ =========================================="
echo "✅ DEPLOYMENT COMPLETADO"
echo "✅ =========================================="
echo ""
echo "📊 Estado de la aplicación:"
pm2 status
echo ""
echo "🌐 La aplicación debería estar accesible en:"
echo "   http://$DOMAIN"
echo "   http://$(curl -s ifconfig.me)"
echo ""
echo "📝 Comandos útiles:"
echo "   pm2 logs $APP_NAME       - Ver logs en tiempo real"
echo "   pm2 restart $APP_NAME    - Reiniciar aplicación"
echo "   pm2 stop $APP_NAME       - Detener aplicación"
echo "   pm2 status               - Ver estado"
echo "   nginx -t                 - Verificar config de Nginx"
echo "   systemctl status nginx   - Ver estado de Nginx"
echo ""
echo "⚠️  IMPORTANTE: Asegúrate de que el DNS de $DOMAIN apunte a la IP de este servidor"
echo "   IP del servidor: $(curl -s ifconfig.me)"
echo ""
