<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Empresa
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    //Implementamos un método para insertar registros
    public function insertar($razonsocial, $ncomercial, $domicilio, $ruc, $tel1, $tel2, $correo,
        $web, $webconsul, $imagen, $ubigueo, $igv, $porDesc, $codubigueo, $ciudad, $distrito,
        $interior, $codigopais, $banco1, $cuenta1, $banco2, $cuenta2, $banco3, $cuenta3,
        $banco4, $cuenta4, $cuentacci1, $cuentacci2, $cuentacci3, $cuentacci4,
        $tipoimpresion, $textolibre)
    {
        global $conexion;
        $sw = true;

        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        try {
            // Insertar empresa con prepared statement
            $sql = "INSERT INTO empresa (
                nombre_razon_social, nombre_comercial, domicilio_fiscal, numero_ruc,
                telefono1, telefono2, correo, web, webconsul, logo, ubigueo, codubigueo,
                ciudad, distrito, interior, codigopais, banco1, cuenta1, banco2, cuenta2,
                banco3, cuenta3, banco4, cuenta4, cuentacci1, cuentacci2, cuentacci3,
                cuentacci4, tipoimpresion, textolibre
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                error_log("Error preparando INSERT en empresa: " . $conexion->error);
                throw new Exception("Error al preparar inserción de empresa");
            }

            $stmt->bind_param("ssssssssssssssssssssssssssssss",
                $razonsocial, $ncomercial, $domicilio, $ruc, $tel1, $tel2, $correo,
                $web, $webconsul, $imagen, $ubigueo, $codubigueo, $ciudad, $distrito,
                $interior, $codigopais, $banco1, $cuenta1, $banco2, $cuenta2,
                $banco3, $cuenta3, $banco4, $cuenta4, $cuentacci1, $cuentacci2,
                $cuentacci3, $cuentacci4, $tipoimpresion, $textolibre
            );

            if (!$stmt->execute()) {
                error_log("Error ejecutando INSERT en empresa: " . $stmt->error);
                throw new Exception("Error al insertar empresa");
            }

            $idempresanew = $stmt->insert_id;
            $stmt->close();

            // Insertar configuraciones con prepared statement
            $sqlConf = "INSERT INTO configuraciones (idempresa, igv, porDesc) VALUES (?, ?, ?)";

            $stmtConf = $conexion->prepare($sqlConf);
            if (!$stmtConf) {
                error_log("Error preparando INSERT en configuraciones: " . $conexion->error);
                throw new Exception("Error al preparar inserción de configuraciones");
            }

            $stmtConf->bind_param("iss", $idempresanew, $igv, $porDesc);

            if (!$stmtConf->execute()) {
                error_log("Error ejecutando INSERT en configuraciones: " . $stmtConf->error);
                throw new Exception("Error al insertar configuraciones");
            }

            $stmtConf->close();

            // Confirmar transacción
            mysqli_commit($conexion);

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            mysqli_rollback($conexion);
            error_log("Error en transacción de insertar empresa: " . $e->getMessage());
            $sw = false;
        }

        return $sw;
    }

    //Implementamos un método para editar registros
    public function editar($idempresa, $razonsocial, $ncomercial, $domicilio, $ruc, $tel1,
        $tel2, $correo, $web, $webconsul, $imagen, $ubigueo, $igv, $porDesc, $codubigueo,
        $ciudad, $distrito, $interior, $codigopais, $banco1, $cuenta1, $banco2, $cuenta2,
        $banco3, $cuenta3, $banco4, $cuenta4, $cuentacci1, $cuentacci2, $cuentacci3,
        $cuentacci4, $tipoimpresion, $textolibre)
    {
        global $conexion;
        $sw = true;

        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        try {
            // Actualizar empresa con prepared statement
            $sql = "UPDATE empresa SET
                nombre_razon_social = ?, nombre_comercial = ?, domicilio_fiscal = ?,
                numero_ruc = ?, telefono1 = ?, telefono2 = ?, correo = ?, web = ?,
                webconsul = ?, logo = ?, ubigueo = ?, codubigueo = ?, ciudad = ?,
                distrito = ?, interior = ?, banco1 = ?, cuenta1 = ?, banco2 = ?,
                cuenta2 = ?, banco3 = ?, cuenta3 = ?, banco4 = ?, cuenta4 = ?,
                cuentacci1 = ?, cuentacci2 = ?, cuentacci3 = ?, cuentacci4 = ?,
                codigopais = ?, tipoimpresion = ?, textolibre = ?
                WHERE idempresa = ?";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                error_log("Error preparando UPDATE en empresa: " . $conexion->error);
                throw new Exception("Error al preparar actualización de empresa");
            }

            $stmt->bind_param("ssssssssssssssssssssssssssssssi",
                $razonsocial, $ncomercial, $domicilio, $ruc, $tel1, $tel2, $correo,
                $web, $webconsul, $imagen, $ubigueo, $codubigueo, $ciudad, $distrito,
                $interior, $banco1, $cuenta1, $banco2, $cuenta2, $banco3, $cuenta3,
                $banco4, $cuenta4, $cuentacci1, $cuentacci2, $cuentacci3, $cuentacci4,
                $codigopais, $tipoimpresion, $textolibre, $idempresa
            );

            if (!$stmt->execute()) {
                error_log("Error ejecutando UPDATE en empresa: " . $stmt->error);
                throw new Exception("Error al actualizar empresa");
            }

            $stmt->close();

            // Actualizar configuraciones con prepared statement
            $sqlConf = "UPDATE configuraciones SET igv = ?, porDesc = ? WHERE idempresa = ?";

            $stmtConf = $conexion->prepare($sqlConf);
            if (!$stmtConf) {
                error_log("Error preparando UPDATE en configuraciones: " . $conexion->error);
                throw new Exception("Error al preparar actualización de configuraciones");
            }

            $stmtConf->bind_param("ssi", $igv, $porDesc, $idempresa);

            if (!$stmtConf->execute()) {
                error_log("Error ejecutando UPDATE en configuraciones: " . $stmtConf->error);
                throw new Exception("Error al actualizar configuraciones");
            }

            $stmtConf->close();

            // Confirmar transacción
            mysqli_commit($conexion);

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            mysqli_rollback($conexion);
            error_log("Error en transacción de editar empresa: " . $e->getMessage());
            $sw = false;
        }

        return $sw;
    }

    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idempresa)
    {
        global $conexion;

        $sql = "SELECT * FROM empresa e
                INNER JOIN configuraciones cf ON e.idempresa = cf.idempresa
                INNER JOIN rutas r ON e.idempresa = r.idempresa
                WHERE e.idempresa = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando SELECT en empresa: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idempresa);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function listar()
    {
        global $conexion;

        $sql = "SELECT * FROM empresa e
                INNER JOIN rutas r ON e.idempresa = r.idempresa
                WHERE e.idempresa = '1'";

        $resultado = $conexion->query($sql);

        return $resultado;
    }
}

?>
