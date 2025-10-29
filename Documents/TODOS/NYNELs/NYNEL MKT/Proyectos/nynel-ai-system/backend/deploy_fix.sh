#!/bin/bash

# Script para desplegar corrección de cotizaciones
echo "🚀 Desplegando corrección de cotizaciones al VPS..."

VPS_IP="147.79.74.193"
VPS_PATH="/var/www/nynel-ai-system/backend"

echo ""
echo "📤 Subiendo archivo corregido..."
scp src/services/master-conversational-ai.service.ts root@${VPS_IP}:${VPS_PATH}/src/services/

echo ""
echo "🔄 Reiniciando PM2..."
ssh root@${VPS_IP} "cd ${VPS_PATH} && pm2 restart nynel-ai-backend"

echo ""
echo "📊 Verificando logs..."
ssh root@${VPS_IP} "cd ${VPS_PATH} && pm2 logs nynel-ai-backend --lines 20 --nostream"

echo ""
echo "✅ Deployment completado!"
