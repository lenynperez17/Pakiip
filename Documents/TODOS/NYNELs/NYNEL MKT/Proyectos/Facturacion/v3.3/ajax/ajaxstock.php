<?php
require_once "../config/Conexion.php";
require_once "../modelos/ModeloStock.php";

$modelostock = new ModeloStock();

// $idalmacen = isset($_POST["idalmacen"]) ? limpiarCadena($_POST["idalmacen"]) : "";

$idalmacen = isset($_GET['idalmacen']) ? intval($_GET['idalmacen']) : null;
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";

$id_del_articulo = isset($_POST["idarticulo"]) ? filter_var($_POST["idarticulo"], FILTER_SANITIZE_NUMBER_INT) : null;
$id_del_almacen = isset($_POST["idalmacen"]) ? filter_var($_POST["idalmacen"], FILTER_SANITIZE_NUMBER_INT) : null;
$nombre_almacen = isset($_POST["nombre_almacen"]) ? filter_var($_POST["nombre_almacen"], FILTER_SANITIZE_STRING) : "";
$nuevo_valor_stock = isset($_POST["stock"]) ? filter_var($_POST["stock"], FILTER_SANITIZE_NUMBER_INT) : "";
$id_del_articulo = isset($_POST["idarticulo"]) ? filter_var($_POST["idarticulo"], FILTER_SANITIZE_NUMBER_INT) : "";
$nuevo_valor_costo_compra = isset($_POST["costo_compra"]) ? filter_var($_POST["costo_compra"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : "";
$nuevo_valor_precio_venta = isset($_POST["precio_venta"]) ? filter_var($_POST["precio_venta"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : "";
$nuevo_valor_precio2 = isset($_POST["precio2"]) ? filter_var($_POST["precio2"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : "";
$nuevo_valor_precio3 = isset($_POST["precio3"]) ? filter_var($_POST["precio3"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : "";
$nueva_descripcion = isset($_POST["descripcion"]) ? filter_var($_POST["descripcion"], FILTER_SANITIZE_STRING) : "";

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}


if ($action == 'listarAlmacen') {
    $rspta = $modelostock->totalAlmacenActiva($nombre, $estado);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        $data[] = array(
            'idalmacen' => $reg->idalmacen,
            'nombre' => $reg->nombre,
            'estado' => $reg->estado
        );
    }
    $results = array(
        "aaData" => $data
    );

    header('Content-type: application/json');
    echo json_encode($results);
    exit;
}


switch ($_GET["op"]) {


    case 'listarStockProductos':
        $rspta = $modelostock->listarStockProductos($idalmacen);

        // Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $imagenUrl = (empty($reg->imagen) || !file_exists("../files/articulos/" . $reg->imagen)) ? '../files/articulos/simagen.png' : '../files/articulos/' . $reg->imagen;

            $data[] = array(
                "0" => $reg->idarticulo,
                "1" => $reg->idalmacen,
                "2" => $reg->codigo,
                "3" => $reg->nombre,
                "4" => $reg->marca,
                "5" => '<img src="' . $imagenUrl . '" height="50" width="50" alt="Imagen"/>',
                "6" => ($reg->estado == 1) ? '<span class="badge bg-success-transparent">Activo</span>' : '<span class="badge bg-danger-transparent">Inhabilitado</span>',
                "7" => $reg->nombre_almacen,
                "8" => '<input type="number" style="width:100px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="stock" value="' . $reg->stock . '">',
                "9" => '<input type="number" style="width:90px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="costo_compra" value="' . $reg->costo_compra . '">',
                "10" => '<input type="number" style="width:90px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="precio_venta" value="' . $reg->precio_venta . '">',
                "11" => '<input type="number" style="width:90px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="precio2" value="' . $reg->precio2 . '">',
                "12" => '<input type="number" style="width:90px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="precio3" value="' . $reg->precio3 . '">',
                "13" => '<input type="text" style="width:150px;" class="form-control editable" data-idarticulo="' . $reg->idarticulo . '" data-field="descrip" value="' . $reg->descrip . '">',
                "14" => $reg->total_unidades_vendidas,
                "15" => $reg->total_ventas
            );


        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );

        echo json_encode($results);

        break;

        case 'ActualizarStockProductos':
            
            $fields = ["stock", "costo_compra", "precio_venta", "precio2", "precio3", "descrip"];
            $dataToUpdate = array();
        
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $dataToUpdate[$field] = $_POST[$field];
                }
            }
        
            // Verifica si hay datos para actualizar
            if (empty($dataToUpdate)) {
                echo "No hay datos para actualizar.";
                return;
            }
        
            // Obtén el valor de 'stock'
            $stockValue = isset($_POST['stock']) ? $_POST['stock'] : null;
        
            // Si 'stock' está definido, actualiza también 'saldo_iniu' con el mismo valor
            if ($stockValue !== null) {
                $dataToUpdate['saldo_iniu'] = $stockValue;
            }
        
            $rspta = $modelostock->ActualizarStockProductos($dataToUpdate, $id_del_articulo);
            echo $rspta ? "Datos actualizados" : "El artículo no se pudo actualizar";
        break;
        



}


?>