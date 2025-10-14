<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Rutas
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    //Implementamos un método para insertar registros
    public function insertar($rutadata, $rutafirma, $rutaenvio, $rutarpta, $rutadatalt,
        $rutabaja, $rutaresumen, $rutadescargas, $rutaple, $idempresa, $unziprpta,
        $rutaarticulos, $rutalogo, $rutausuarios, $salidafacturas, $salidaboletas)
    {
        global $conexion;

        $sql = "INSERT INTO rutas (
            rutadata, rutafirma, rutaenvio, rutarpta, rutadatalt, rutabaja,
            rutaresumen, rutadescargas, rutaple, idempresa, unziprpta,
            rutaarticulos, rutalogo, rutausuarios, salidafacturas, salidaboletas
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando INSERT en rutas: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("sssssssssissssss",
            $rutadata, $rutafirma, $rutaenvio, $rutarpta, $rutadatalt, $rutabaja,
            $rutaresumen, $rutadescargas, $rutaple, $idempresa, $unziprpta,
            $rutaarticulos, $rutalogo, $rutausuarios, $salidafacturas, $salidaboletas
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    //Implementamos un método para editar registros
    public function editar($idruta, $rutadata, $rutafirma, $rutaenvio, $rutarpta,
        $rutadatalt, $rutabaja, $rutaresumen, $rutadescargas, $rutaple, $idempresa,
        $unziprpta, $rutaarticulos, $rutalogo, $rutausuarios, $salidafacturas, $salidaboletas)
    {
        global $conexion;

        $sql = "UPDATE rutas SET
            rutadata = ?, rutafirma = ?, rutaenvio = ?, rutarpta = ?,
            rutadatalt = ?, rutabaja = ?, rutaresumen = ?, rutadescargas = ?,
            rutaple = ?, idempresa = ?, unziprpta = ?, rutaarticulos = ?,
            rutalogo = ?, rutausuarios = ?, salidafacturas = ?, salidaboletas = ?
            WHERE idruta = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando UPDATE en rutas: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("sssssssssissssssi",
            $rutadata, $rutafirma, $rutaenvio, $rutarpta, $rutadatalt, $rutabaja,
            $rutaresumen, $rutadescargas, $rutaple, $idempresa, $unziprpta,
            $rutaarticulos, $rutalogo, $rutausuarios, $salidafacturas, $salidaboletas,
            $idruta
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idruta)
    {
        global $conexion;

        $sql = "SELECT * FROM rutas WHERE idruta = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando SELECT en rutas: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idruta);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar2($idempresa)
    {
        global $conexion;

        $sql = "SELECT * FROM rutas r
                INNER JOIN empresa e ON r.idempresa = e.idempresa
                WHERE e.idempresa = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando SELECT en rutas (mostrar2): " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idempresa);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    public function listar($idempresa)
    {
        global $conexion;

        $sql = "SELECT * FROM rutas r
                INNER JOIN empresa e ON r.idempresa = e.idempresa
                WHERE e.idempresa = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando SELECT en rutas (listar): " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idempresa);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }
}

?>
