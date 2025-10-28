<?php

// SEGURIDAD: Usar Conexion.php que ya maneja sesiones de forma segura
require_once "../config/Conexion.php";
//Activamos el almacenamiento del Buffer
ob_start();


if (!isset($_SESSION["nombre"])) {
    header("Location: ../vistas/login.php");
} else {

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["dataArticulo"])) {

        $tmpName = $_FILES["dataArticulo"]["tmp_name"];
        $error = $_FILES["dataArticulo"]["error"];

        if ($error !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
            exit;
        }

        require_once "../PHPExcel/Classes/PHPExcel.php";
        require_once "../modelos/Consultas.php";
        require_once "../config/Conexion.php";

        $consultaObj = new Consultas();
        $excel = PHPExcel_IOFactory::load($tmpName);

        $excel->setActiveSheetIndex(0);
        $numerofila = $excel->setActiveSheetIndex(0)->getHighestRow();

        for ($i = 2; $i <= $numerofila; $i++) {
            $codigo = $excel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue();
            if ($codigo != "") {
                $familia_descripcion = $excel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue();
                $nombre = $excel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue();
                $marca = $excel->getActiveSheet()->getCell('D' . $i)->getCalculatedValue();
                $descrip = $excel->getActiveSheet()->getCell('E' . $i)->getCalculatedValue();
                $costo_compra = $excel->getActiveSheet()->getCell('F' . $i)->getCalculatedValue();
                $precio_venta = $excel->getActiveSheet()->getCell('G' . $i)->getCalculatedValue();
                $stock = $excel->getActiveSheet()->getCell('H' . $i)->getCalculatedValue();
                $saldo_iniu = $excel->getActiveSheet()->getCell('I' . $i)->getCalculatedValue();
                $valor_iniu = $excel->getActiveSheet()->getCell('J' . $i)->getCalculatedValue();
                $tipoitem = $excel->getActiveSheet()->getCell('K' . $i)->getCalculatedValue();
                $codigott = $excel->getActiveSheet()->getCell('L' . $i)->getCalculatedValue();
                $desctt = $excel->getActiveSheet()->getCell('M' . $i)->getCalculatedValue();
                $codigointtt = $excel->getActiveSheet()->getCell('N' . $i)->getCalculatedValue();
                $nombrett = $excel->getActiveSheet()->getCell('O' . $i)->getCalculatedValue();
                $nombre_almacen = $excel->getActiveSheet()->getCell('P' . $i)->getCalculatedValue();
                $saldo_finu = $excel->getActiveSheet()->getCell('Q' . $i)->getCalculatedValue();

                $consultaObj->insertarArticulosMasivo(
                    $codigo,
                    $familia_descripcion,
                    $nombre,
                    $marca,
                    $descrip,
                    $costo_compra,
                    $precio_venta,
                    $stock,
                    $saldo_iniu,
                    $valor_iniu,
                    $tipoitem,
                    $codigott,
                    $desctt,
                    $codigointtt,
                    $nombrett,
                    $nombre_almacen,
                    $saldo_finu
                );
            }
        }

        // Al final, envía una respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);

    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se ha enviado un archivo válido.']);
    }

}
ob_end_flush();
?>