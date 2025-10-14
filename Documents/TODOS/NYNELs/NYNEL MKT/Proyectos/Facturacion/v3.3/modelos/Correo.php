<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Correo
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    //Implementamos un método para insertar registros
    public function insertar($nombre, $username, $host, $password, $smtpsecure, $port, $mensaje, $correoavisos)
    {
        $sql="insert into
         correo (nombre,
            username,
            host,
            password,
            smtpsecure, 
            port, 
            mensaje,
            correoavisos
            
            )
        values ('$nombre','$username','$host','$password','$smtpsecure','$port','$mensaje', '$correoavisos')";
        return ejecutarConsulta($sql);
    }
 
    //Implementamos un método para editar registros
    public function editar($idcorreo,$nombre, $username, $host, $password, $smtpsecure, $port, $mensaje, $correoavisos)
    {
        $sql="update correo 
        set 
        nombre='$nombre', 
        username='$username', 
        host='$host', 
        password='$password', 
        smtpsecure='$smtpsecure', 
        port='$port', 
        mensaje='$mensaje',
        correoavisos='$correoavisos'
        where 
        idcorreo='$idcorreo'";
        return ejecutarConsulta($sql);
    }
 

    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idcorreo)
    {
        // Intentar buscar el registro con el ID solicitado
        $sql="SELECT * FROM correo WHERE idcorreo='$idcorreo'";
        $resultado = ejecutarConsultaSimpleFila($sql);

        // Si no existe ese ID, buscar el primer registro disponible
        if (!$resultado) {
            $sql="SELECT * FROM correo ORDER BY idcorreo ASC LIMIT 1";
            $resultado = ejecutarConsultaSimpleFila($sql);
        }

        // Si la tabla está vacía, retornar estructura vacía para permitir crear el primer registro
        if (!$resultado) {
            $resultado = array(
                'idcorreo' => '',
                'nombre' => '',
                'username' => '',
                'host' => '',
                'password' => '',
                'smtpsecure' => '',
                'port' => '',
                'mensaje' => '',
                'correoavisos' => ''
            );
        }

        return $resultado;
    }
 
 
}
 
?>