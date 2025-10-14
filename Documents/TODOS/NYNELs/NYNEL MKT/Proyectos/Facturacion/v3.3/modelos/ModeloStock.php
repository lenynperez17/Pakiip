<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class ModeloStock
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    public function listarStockProductos($idalmacen)
    {
        $sql = "select 
        a.idarticulo,
        a.codigo,
        a.nombre,
        a.marca,
        a.imagen,
        a.estado,
        a.stock,
        a.costo_compra,
        a.precio_venta,
        a.precio2,
        a.precio3,
        a.descrip,
        al.idalmacen,
        al.nombre as nombre_almacen, 
        coalesce(sum(union_tablas.cantidad), 0) as total_unidades_vendidas,
        coalesce(count(union_tablas.idarticulo), 0) as total_ventas
    from articulo a 
    left join (
        select idboleta as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_notapedido_producto
        union all
        select idfactura as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_fac_art
        union all
        select idboleta as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_boleta_producto
    ) as union_tablas on a.idarticulo = union_tablas.idarticulo
    left join almacen al on a.idalmacen = al.idalmacen 
    where a.tipoitem = 'productos' and al.idalmacen = $idalmacen
    group by 
        a.idarticulo, 
        a.codigo, 
        a.nombre, 
        a.imagen, 
        a.estado, 
        a.stock, 
        a.costo_compra, 
        a.precio_venta, 
        a.precio2, 
        a.precio3, 
        a.descrip,
        al.idalmacen, 
        al.nombre 
    order by total_ventas desc, total_unidades_vendidas desc;        
        ";
        return ejecutarConsulta($sql);
    }



    public function ActualizarStockProductos($data, $id_del_articulo)
    {
        $setStatements = [];
        foreach ($data as $key => $value) {
            $setStatements[] = "$key = '$value'";
        }
        $setClause = implode(", ", $setStatements);
        $sql = "update articulo set $setClause where idarticulo = '$id_del_articulo'";
        return ejecutarConsulta($sql);
    }



    public function totalAlmacenActiva($nombre, $estado)
    {
        $sql = "select idalmacen, nombre, estado
            from almacen
            where estado = 1;
            ";
        return ejecutarConsulta($sql);
    }



}

?>