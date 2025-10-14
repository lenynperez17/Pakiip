<?php

require_once "../config/Conexion.php";

switch ($_GET["op"]){
    case 'empresa':
        // Retornar la empresa actual configurada
        $empresas = array(
            array(DB_NAME, DB_NAME)
        );
        echo json_encode($empresas);
        break;

    case 'verificarempresa':
        // No hacer nada, ya está configurada la empresa correcta
        echo json_encode(array("status" => "ok"));
        break;
}

?>
