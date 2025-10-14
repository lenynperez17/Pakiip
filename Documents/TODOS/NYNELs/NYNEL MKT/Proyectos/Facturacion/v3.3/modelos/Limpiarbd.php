<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Limpiarbd
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    // Método para eliminar datos de facturación por empresa
    public function limpiarbd($idempresa)
    {
        // Validar que idempresa sea numérico
        if (!is_numeric($idempresa) || $idempresa <= 0) {
            return false;
        }

        $idempresa = intval($idempresa);

        // Query con prepared statement para prevenir SQL injection
        $sql = "DELETE dfa FROM detalle_fac_art dfa
                INNER JOIN factura f ON dfa.idfactura = f.idfactura
                INNER JOIN empresa e ON f.idempresa = e.idempresa
                WHERE e.idempresa = ?";

        $result = ejecutarConsultaPreparada($sql, "i", [$idempresa]);
        return $result !== false;
    }


    public function listar()
    {
        $sql="select * from empresa";
        return ejecutarConsulta($sql);      
    }

    public function copiabdweb()
    {
        try {
            // Credenciales de base de datos
            $dbName = escapeshellarg(DB_NAME);
            $dbUser = escapeshellarg(DB_USERNAME);
            $dbPass = escapeshellarg(DB_PASSWORD);
            $dbHost = escapeshellarg(DB_HOST);

            $fecha = date("Ymd-His");
            $nombreBase = DB_NAME;

            // Crear directorio de copias si no existe
            $dirCopia = "../copia/";
            if (!is_dir($dirCopia)) {
                mkdir($dirCopia, 0755, true);
            }

            // Archivos temporales
            $archivoSQL = "../ajax/{$nombreBase}_{$fecha}.sql";
            $archivoZIP = "{$nombreBase}_{$fecha}.zip";
            $rutaTempZIP = "../ajax/{$archivoZIP}";
            $rutaFinalZIP = "{$dirCopia}{$archivoZIP}";

            // Comando mysqldump con seguridad
            $command = sprintf(
                'mysqldump --opt -h%s -u%s -p%s %s > %s 2>&1',
                $dbHost,
                $dbUser,
                $dbPass,
                $dbName,
                escapeshellarg($archivoSQL)
            );

            exec($command, $output, $resultado);

            if ($resultado !== 0 || !file_exists($archivoSQL)) {
                throw new Exception("Error al generar backup SQL: " . implode("\n", $output));
            }

            // Comprimir en ZIP
            $zip = new ZipArchive();
            if ($zip->open($rutaTempZIP, ZIPARCHIVE::CREATE) !== true) {
                unlink($archivoSQL);
                throw new Exception("Error al crear archivo ZIP");
            }

            $zip->addFile($archivoSQL, basename($archivoSQL));
            $zip->close();

            // Limpiar SQL temporal
            unlink($archivoSQL);

            // Mover a carpeta final
            if (!rename($rutaTempZIP, $rutaFinalZIP)) {
                throw new Exception("Error al mover archivo ZIP a carpeta de copias");
            }

            return [
                'nombrearchivo' => $archivoZIP,
                'rutaarchivo' => $rutaFinalZIP
            ];

        } catch (Exception $e) {
            // Limpiar archivos temporales en caso de error
            if (isset($archivoSQL) && file_exists($archivoSQL)) {
                unlink($archivoSQL);
            }
            if (isset($rutaTempZIP) && file_exists($rutaTempZIP)) {
                unlink($rutaTempZIP);
            }

            error_log("Error en copiabdweb: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
  
  
  
  
  

    public function copiabdlocal($rutaMysqldump)
    {
        try {
            // Validar que la ruta de mysqldump no esté vacía
            if (empty($rutaMysqldump)) {
                throw new Exception("Ruta de mysqldump no proporcionada");
            }

            // Credenciales de base de datos
            $dbHost = escapeshellarg(DB_HOST);
            $dbName = escapeshellarg(DB_NAME);
            $dbUser = escapeshellarg(DB_USERNAME);
            $dbPass = escapeshellarg(DB_PASSWORD);

            $fecha = date("Ymd-His");
            $nombreBase = DB_NAME;

            // Crear directorio de copias si no existe
            $dirCopia = "../copia/";
            if (!is_dir($dirCopia)) {
                mkdir($dirCopia, 0755, true);
            }

            // Archivos temporales
            $archivoSQL = "../ajax/{$nombreBase}_{$fecha}.sql";
            $archivoZIP = "{$nombreBase}_{$fecha}.zip";
            $rutaTempZIP = "../ajax/{$archivoZIP}";
            $rutaFinalZIP = "{$dirCopia}{$archivoZIP}";

            // Validar que la ruta de mysqldump existe
            $rutaMysqldumpEscaped = escapeshellarg($rutaMysqldump);

            // Comando mysqldump con seguridad
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s %s > %s 2>&1',
                $rutaMysqldumpEscaped,
                $dbUser,
                $dbPass,
                $dbHost,
                $dbName,
                escapeshellarg($archivoSQL)
            );

            exec($command, $output, $resultado);

            if ($resultado !== 0 || !file_exists($archivoSQL)) {
                throw new Exception("Error al generar backup SQL: " . implode("\n", $output));
            }

            // Comprimir en ZIP
            $zip = new ZipArchive();
            if ($zip->open($rutaTempZIP, ZIPARCHIVE::CREATE) !== true) {
                unlink($archivoSQL);
                throw new Exception("Error al crear archivo ZIP");
            }

            $zip->addFile($archivoSQL, basename($archivoSQL));
            $zip->close();

            // Limpiar SQL temporal
            unlink($archivoSQL);

            // Mover a carpeta final
            if (!rename($rutaTempZIP, $rutaFinalZIP)) {
                throw new Exception("Error al mover archivo ZIP a carpeta de copias");
            }

            return [
                'nombrearchivo' => $archivoZIP,
                'rutaarchivo' => $rutaFinalZIP
            ];

        } catch (Exception $e) {
            // Limpiar archivos temporales en caso de error
            if (isset($archivoSQL) && file_exists($archivoSQL)) {
                unlink($archivoSQL);
            }
            if (isset($rutaTempZIP) && file_exists($rutaTempZIP)) {
                unlink($rutaTempZIP);
            }

            error_log("Error en copiabdlocal: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

 
 
}
 
?>