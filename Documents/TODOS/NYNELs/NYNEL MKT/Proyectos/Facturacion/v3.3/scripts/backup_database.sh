#!/bin/bash
################################################################################
# SCRIPT DE BACKUP AUTOMÁTICO DE BASE DE DATOS
# Sistema de Facturación v3.3
#
# Implementa estrategia GFS (Grandfather-Father-Son):
# - Daily: 7 backups diarios
# - Weekly: 4 backups semanales (domingos)
# - Monthly: 12 backups mensuales (primer día del mes)
#
# Uso:
#   ./backup_database.sh
#
# Cron (ejecutar diariamente a las 2:00 AM):
#   0 2 * * * /ruta/completa/backup_database.sh >> /var/log/backup_db.log 2>&1
################################################################################

# ========== CONFIGURACIÓN ==========

# Credenciales de base de datos
DB_USER="root"
DB_PASSWORD=""  # CAMBIAR en producción o usar archivo de configuración
DB_HOST="localhost"
DB_NAME="dbsistema"

# Directorios de backup
BACKUP_ROOT="/var/backups/facturacion"
BACKUP_DAILY="${BACKUP_ROOT}/daily"
BACKUP_WEEKLY="${BACKUP_ROOT}/weekly"
BACKUP_MONTHLY="${BACKUP_ROOT}/monthly"

# Retención (cantidad de backups a mantener)
DAILY_RETENTION=7     # 7 días
WEEKLY_RETENTION=4    # 4 semanas
MONTHLY_RETENTION=12  # 12 meses

# Log
LOG_FILE="/var/log/backup_db.log"
ERROR_LOG="/var/log/backup_db_error.log"

# Email para notificaciones (opcional)
NOTIFICATION_EMAIL=""  # Dejar vacío para deshabilitar

# ========== FUNCIONES ==========

# Función para logging con timestamp
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Función para logging de errores
log_error() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" | tee -a "$LOG_FILE" "$ERROR_LOG"
}

# Función para enviar notificación por email
send_notification() {
    local subject="$1"
    local message="$2"

    if [ -n "$NOTIFICATION_EMAIL" ]; then
        echo "$message" | mail -s "$subject" "$NOTIFICATION_EMAIL"
    fi
}

# Función para crear directorios si no existen
create_directories() {
    mkdir -p "$BACKUP_DAILY" "$BACKUP_WEEKLY" "$BACKUP_MONTHLY"

    if [ $? -ne 0 ]; then
        log_error "No se pudieron crear los directorios de backup"
        exit 1
    fi
}

# Función para verificar espacio en disco
check_disk_space() {
    local available=$(df -BG "$BACKUP_ROOT" | awk 'NR==2 {print $4}' | sed 's/G//')
    local threshold=5  # GB mínimos requeridos

    if [ "$available" -lt "$threshold" ]; then
        log_error "Espacio en disco insuficiente: ${available}GB disponibles (mínimo: ${threshold}GB)"
        send_notification "⚠️ Backup FALLO - Espacio en disco" "Espacio disponible: ${available}GB"
        exit 1
    fi

    log_message "Espacio en disco: ${available}GB disponibles"
}

# Función para realizar backup
perform_backup() {
    local backup_file="$1"

    log_message "Iniciando backup: $backup_file"

    # Realizar dump de la base de datos con compresión
    if [ -z "$DB_PASSWORD" ]; then
        # Sin contraseña
        mysqldump --user="$DB_USER" \
                  --host="$DB_HOST" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  "$DB_NAME" | gzip > "$backup_file"
    else
        # Con contraseña
        mysqldump --user="$DB_USER" \
                  --password="$DB_PASSWORD" \
                  --host="$DB_HOST" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  "$DB_NAME" | gzip > "$backup_file"
    fi

    # Verificar si el backup fue exitoso
    if [ $? -eq 0 ] && [ -f "$backup_file" ]; then
        local size=$(du -h "$backup_file" | cut -f1)
        log_message "✅ Backup completado exitosamente: $backup_file (Tamaño: $size)"
        return 0
    else
        log_error "Fallo al crear backup: $backup_file"
        return 1
    fi
}

# Función para limpiar backups antiguos
cleanup_old_backups() {
    local directory="$1"
    local retention="$2"
    local type="$3"

    log_message "Limpiando backups antiguos de ${type}..."

    # Contar archivos en el directorio
    local count=$(ls -1 "$directory"/*.sql.gz 2>/dev/null | wc -l)

    if [ "$count" -gt "$retention" ]; then
        # Eliminar los backups más antiguos
        ls -1t "$directory"/*.sql.gz | tail -n +$((retention + 1)) | xargs rm -f
        local deleted=$((count - retention))
        log_message "Eliminados $deleted backups antiguos de ${type}"
    else
        log_message "No hay backups antiguos para eliminar en ${type} (actual: $count, retención: $retention)"
    fi
}

# Función para verificar integridad del backup
verify_backup() {
    local backup_file="$1"

    # Verificar que el archivo no esté vacío
    if [ ! -s "$backup_file" ]; then
        log_error "El archivo de backup está vacío: $backup_file"
        return 1
    fi

    # Verificar integridad del archivo gzip
    if ! gzip -t "$backup_file" 2>/dev/null; then
        log_error "El archivo de backup está corrupto: $backup_file"
        return 1
    fi

    log_message "✅ Verificación de integridad exitosa: $backup_file"
    return 0
}

# ========== SCRIPT PRINCIPAL ==========

log_message "========== INICIANDO BACKUP DE BASE DE DATOS =========="
log_message "Base de datos: $DB_NAME"
log_message "Host: $DB_HOST"

# Verificar que mysqldump esté instalado
if ! command -v mysqldump &> /dev/null; then
    log_error "mysqldump no está instalado"
    send_notification "⚠️ Backup FALLO - mysqldump no encontrado" "Instalar mysql-client o mariadb-client"
    exit 1
fi

# Crear directorios necesarios
create_directories

# Verificar espacio en disco
check_disk_space

# Generar timestamp
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
DAY_OF_WEEK=$(date '+%u')  # 1=Lunes, 7=Domingo
DAY_OF_MONTH=$(date '+%d')

# Determinar el tipo de backup según el día
BACKUP_TYPE="daily"

if [ "$DAY_OF_WEEK" -eq 7 ]; then
    BACKUP_TYPE="weekly"
fi

if [ "$DAY_OF_MONTH" -eq 01 ]; then
    BACKUP_TYPE="monthly"
fi

log_message "Tipo de backup: $BACKUP_TYPE"

# Determinar directorio y nombre de archivo según tipo de backup
case $BACKUP_TYPE in
    daily)
        BACKUP_DIR="$BACKUP_DAILY"
        BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql.gz"
        ;;
    weekly)
        BACKUP_DIR="$BACKUP_WEEKLY"
        BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_weekly_${TIMESTAMP}.sql.gz"
        ;;
    monthly)
        BACKUP_DIR="$BACKUP_MONTHLY"
        BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_monthly_${TIMESTAMP}.sql.gz"
        ;;
esac

# Realizar el backup
if perform_backup "$BACKUP_FILE"; then

    # Verificar integridad
    if verify_backup "$BACKUP_FILE"; then

        # Limpiar backups antiguos según retención
        cleanup_old_backups "$BACKUP_DAILY" "$DAILY_RETENTION" "daily"

        if [ "$BACKUP_TYPE" == "weekly" ] || [ "$DAY_OF_WEEK" -eq 7 ]; then
            cleanup_old_backups "$BACKUP_WEEKLY" "$WEEKLY_RETENTION" "weekly"
        fi

        if [ "$BACKUP_TYPE" == "monthly" ] || [ "$DAY_OF_MONTH" -eq 01 ]; then
            cleanup_old_backups "$BACKUP_MONTHLY" "$MONTHLY_RETENTION" "monthly"
        fi

        # Resumen final
        log_message "========== BACKUP COMPLETADO EXITOSAMENTE =========="
        log_message "Archivo: $BACKUP_FILE"
        log_message "Tamaño: $(du -h "$BACKUP_FILE" | cut -f1)"

        # Estadísticas de backups
        DAILY_COUNT=$(ls -1 "$BACKUP_DAILY"/*.sql.gz 2>/dev/null | wc -l)
        WEEKLY_COUNT=$(ls -1 "$BACKUP_WEEKLY"/*.sql.gz 2>/dev/null | wc -l)
        MONTHLY_COUNT=$(ls -1 "$BACKUP_MONTHLY"/*.sql.gz 2>/dev/null | wc -l)

        log_message "Backups actuales - Diarios: $DAILY_COUNT, Semanales: $WEEKLY_COUNT, Mensuales: $MONTHLY_COUNT"

        # Notificación de éxito (opcional)
        if [ -n "$NOTIFICATION_EMAIL" ]; then
            send_notification "✅ Backup Exitoso - $BACKUP_TYPE" "Backup completado: $(basename "$BACKUP_FILE")"
        fi

        exit 0

    else
        log_error "Fallo en la verificación de integridad"
        send_notification "⚠️ Backup FALLO - Integridad" "El backup se creó pero está corrupto"
        exit 1
    fi

else
    log_error "Fallo al crear el backup"
    send_notification "⚠️ Backup FALLO - Creación" "No se pudo crear el archivo de backup"
    exit 1
fi
