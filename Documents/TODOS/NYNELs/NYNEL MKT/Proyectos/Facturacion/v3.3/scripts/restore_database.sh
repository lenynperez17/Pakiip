#!/bin/bash
################################################################################
# SCRIPT DE RESTAURACIÓN DE BASE DE DATOS DESDE BACKUP
# Sistema de Facturación v3.3
#
# Permite restaurar la base de datos desde un archivo de backup
#
# Uso:
#   ./restore_database.sh [archivo_backup.sql.gz]
#
# Ejemplo:
#   ./restore_database.sh /var/backups/facturacion/daily/backup_dbsistema_20251010_020000.sql.gz
################################################################################

# ========== CONFIGURACIÓN ==========

# Credenciales de base de datos
DB_USER="root"
DB_PASSWORD=""  # CAMBIAR en producción
DB_HOST="localhost"
DB_NAME="dbsistema"

# Log
LOG_FILE="/var/log/restore_db.log"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # Sin color

# ========== FUNCIONES ==========

# Función para logging
log_message() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Función para mostrar uso
show_usage() {
    echo -e "${YELLOW}Uso:${NC}"
    echo "  $0 [archivo_backup.sql.gz]"
    echo ""
    echo -e "${YELLOW}Ejemplos:${NC}"
    echo "  $0 /var/backups/facturacion/daily/backup_dbsistema_20251010_020000.sql.gz"
    echo "  $0 backup.sql.gz"
    echo ""
    echo -e "${YELLOW}Backups disponibles:${NC}"
    echo ""
    echo "📁 Diarios (últimos 7 días):"
    ls -lht /var/backups/facturacion/daily/*.sql.gz 2>/dev/null | head -7 || echo "  No hay backups diarios"
    echo ""
    echo "📁 Semanales (últimos 4 semanas):"
    ls -lht /var/backups/facturacion/weekly/*.sql.gz 2>/dev/null | head -4 || echo "  No hay backups semanales"
    echo ""
    echo "📁 Mensuales (últimos 12 meses):"
    ls -lht /var/backups/facturacion/monthly/*.sql.gz 2>/dev/null | head -12 || echo "  No hay backups mensuales"
}

# Función para verificar archivo de backup
verify_backup_file() {
    local backup_file="$1"

    # Verificar que el archivo existe
    if [ ! -f "$backup_file" ]; then
        log_message "${RED}❌ ERROR: El archivo no existe: $backup_file${NC}"
        return 1
    fi

    # Verificar que el archivo no esté vacío
    if [ ! -s "$backup_file" ]; then
        log_message "${RED}❌ ERROR: El archivo está vacío: $backup_file${NC}"
        return 1
    fi

    # Verificar integridad del archivo gzip
    if ! gzip -t "$backup_file" 2>/dev/null; then
        log_message "${RED}❌ ERROR: El archivo está corrupto: $backup_file${NC}"
        return 1
    fi

    log_message "${GREEN}✅ Archivo de backup válido${NC}"
    return 0
}

# Función para crear backup de seguridad antes de restaurar
create_safety_backup() {
    local safety_dir="/var/backups/facturacion/pre_restore"
    mkdir -p "$safety_dir"

    local safety_file="${safety_dir}/backup_pre_restore_$(date '+%Y%m%d_%H%M%S').sql.gz"

    log_message "${YELLOW}📦 Creando backup de seguridad antes de restaurar...${NC}"

    if [ -z "$DB_PASSWORD" ]; then
        mysqldump --user="$DB_USER" \
                  --host="$DB_HOST" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  "$DB_NAME" | gzip > "$safety_file"
    else
        mysqldump --user="$DB_USER" \
                  --password="$DB_PASSWORD" \
                  --host="$DB_HOST" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  "$DB_NAME" | gzip > "$safety_file"
    fi

    if [ $? -eq 0 ]; then
        log_message "${GREEN}✅ Backup de seguridad creado: $safety_file${NC}"
        return 0
    else
        log_message "${RED}❌ ERROR: No se pudo crear backup de seguridad${NC}"
        return 1
    fi
}

# Función para restaurar base de datos
restore_database() {
    local backup_file="$1"

    log_message "${YELLOW}🔄 Restaurando base de datos desde: $(basename "$backup_file")${NC}"

    # Descomprimir y restaurar
    if [ -z "$DB_PASSWORD" ]; then
        gunzip < "$backup_file" | mysql --user="$DB_USER" \
                                        --host="$DB_HOST" \
                                        "$DB_NAME"
    else
        gunzip < "$backup_file" | mysql --user="$DB_USER" \
                                        --password="$DB_PASSWORD" \
                                        --host="$DB_HOST" \
                                        "$DB_NAME"
    fi

    if [ $? -eq 0 ]; then
        log_message "${GREEN}✅ Base de datos restaurada exitosamente${NC}"
        return 0
    else
        log_message "${RED}❌ ERROR: Fallo al restaurar la base de datos${NC}"
        return 1
    fi
}

# Función de confirmación
confirm_restore() {
    local backup_file="$1"

    echo ""
    echo -e "${RED}⚠️  ADVERTENCIA: Esta operación sobrescribirá la base de datos actual${NC}"
    echo ""
    echo -e "  Base de datos: ${YELLOW}$DB_NAME${NC}"
    echo -e "  Archivo backup: ${YELLOW}$(basename "$backup_file")${NC}"
    echo -e "  Tamaño: ${YELLOW}$(du -h "$backup_file" | cut -f1)${NC}"
    echo -e "  Fecha creación: ${YELLOW}$(date -r "$backup_file" '+%Y-%m-%d %H:%M:%S')${NC}"
    echo ""
    echo -e "${YELLOW}Se creará un backup de seguridad antes de proceder.${NC}"
    echo ""
    read -p "¿Desea continuar? (escriba 'RESTAURAR' para confirmar): " confirmation

    if [ "$confirmation" == "RESTAURAR" ]; then
        return 0
    else
        echo -e "${RED}Operación cancelada por el usuario${NC}"
        return 1
    fi
}

# ========== SCRIPT PRINCIPAL ==========

log_message "========== INICIANDO PROCESO DE RESTAURACIÓN =========="

# Verificar argumentos
if [ $# -eq 0 ]; then
    show_usage
    exit 1
fi

BACKUP_FILE="$1"

# Verificar que mysql esté instalado
if ! command -v mysql &> /dev/null; then
    log_message "${RED}❌ ERROR: mysql no está instalado${NC}"
    exit 1
fi

# Verificar archivo de backup
if ! verify_backup_file "$BACKUP_FILE"; then
    exit 1
fi

# Solicitar confirmación
if ! confirm_restore "$BACKUP_FILE"; then
    exit 1
fi

# Crear backup de seguridad
if ! create_safety_backup; then
    echo -e "${RED}❌ No se pudo crear backup de seguridad. ¿Desea continuar de todas formas? (s/n)${NC}"
    read -p "> " continue_anyway
    if [ "$continue_anyway" != "s" ]; then
        echo -e "${RED}Operación cancelada${NC}"
        exit 1
    fi
fi

# Restaurar base de datos
if restore_database "$BACKUP_FILE"; then
    log_message "${GREEN}========== RESTAURACIÓN COMPLETADA EXITOSAMENTE ==========${NC}"
    echo ""
    echo -e "${GREEN}✅ La base de datos ha sido restaurada correctamente${NC}"
    echo -e "${YELLOW}📝 Log guardado en: $LOG_FILE${NC}"
    exit 0
else
    log_message "${RED}========== RESTAURACIÓN FALLIDA ==========${NC}"
    echo ""
    echo -e "${RED}❌ Hubo un error al restaurar la base de datos${NC}"
    echo -e "${YELLOW}📝 Revisa el log en: $LOG_FILE${NC}"
    echo -e "${YELLOW}💡 Puedes restaurar el backup de seguridad desde: /var/backups/facturacion/pre_restore/${NC}"
    exit 1
fi
