<?php

//Incluímos inicialmente la conexión a la base de datos

require "../config/Conexion.php";



class Articulo
{

    //Implementamos nuestro constructor

    public function __construct()
    {




    }



    //Implementamos un método para insertar registros

    public function insertar($idalmacen, $codigo_proveedor, $codigo, $nombre, $idfamilia, $unidad_medida, $costo_compra, $saldo_iniu, $valor_iniu, $saldo_finu, $valor_finu, $stock, $comprast, $ventast, $portador, $merma, $precio_venta, $imagen, $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi, $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett, $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante, $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra, $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem, $umedidacompra, $factorc, $descripcion)
    {
        // SEGURIDAD: Usar prepared statements para prevenir SQL Injection

        // Manejar fechas NULL (convertir 'NULL' string a NULL real)
        $fechafabricacion = ($fechafabricacion === 'NULL') ? NULL : $fechafabricacion;
        $fechavencimiento = ($fechavencimiento === 'NULL') ? NULL : $fechavencimiento;
        $fechaingalm = ($fechaingalm === 'NULL') ? NULL : $fechaingalm;
        $fechafinalma = ($fechafinalma === 'NULL') ? NULL : $fechafinalma;
        $fechafacturacompra = ($fechafacturacompra === 'NULL') ? NULL : $fechafacturacompra;

        // INSERT 1: Tabla articulo (50 campos)
        $sql = "INSERT INTO articulo (
            idalmacen, codigo_proveedor, codigo, nombre, idfamilia, unidad_medida,
            costo_compra, saldo_iniu, valor_iniu, saldo_finu, valor_finu, stock,
            comprast, ventast, portador, merma, precio_venta, imagen,
            valor_fin_kardex, precio_final_kardex, fecharegistro,
            codigosunat, ccontable, precio2, precio3, cicbper, nticbperi,
            ctticbperi, mticbperu, codigott, desctt, codigointtt, nombrett,
            lote, marca, fechafabricacion, fechavencimiento, procedencia, fabricante,
            registrosanitario, fechaingalm, fechafinalma, proveedor, seriefaccompra,
            numerofaccompra, fechafacturacompra, limitestock, tipoitem, umedidacompra,
            factorc, descrip
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Tipos: i=int, d=decimal, s=string
        // Total: 50 parámetros (valor_fin_kardex=$valor_finu, precio_final_kardex=$costo_compra, fecharegistro=NOW())
        $tipos = "isssissddddddddddsssssddssssssssssssssssssssssdss";

        $params = [
            $idalmacen, $codigo_proveedor, $codigo, $nombre, $idfamilia, $unidad_medida,
            $costo_compra, $saldo_iniu, $valor_iniu, $saldo_finu, $valor_finu, $stock,
            $comprast, $ventast, $portador, $merma, $precio_venta, $imagen,
            $valor_finu, $costo_compra, // valor_fin_kardex, precio_final_kardex
            $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi,
            $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett,
            $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante,
            $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra,
            $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem, $umedidacompra,
            $factorc, $descripcion
        ];

        $idartinew = ejecutarConsultaPreparada_retornarID($sql, $tipos, $params);

        // INSERT 2: Tabla reginventariosanos (11 campos, usa year(CURDATE()))
        $sqlreginv = "INSERT INTO reginventariosanos (
            codigo, denominacion, costoinicial, saldoinicial, valorinicial,
            compras, ventas, saldofinal, costo, valorfinal, ano
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, YEAR(CURDATE()))";

        ejecutarConsultaPreparada($sqlreginv, "ssddddddddd", [
            $codigo, $nombre, $costo_compra, $saldo_iniu, $valor_iniu,
            $comprast, $ventast, $saldo_finu, $costo_compra, $valor_finu
        ]);

        // INSERT 3: Tabla subarticulo (7 campos, usa $idartinew del primer INSERT)
        $sqlsubarti = "INSERT INTO subarticulo (
            idarticulo, codigobarra, valorunitario, preciounitario, stock, umventa, estado
        ) VALUES (?, ?, ?, ?, ?, ?, '1')";

        return ejecutarConsultaPreparada($sqlsubarti, "isddds", [
            $idartinew, $codigo, $costo_compra, $costo_compra, $stock, $unidad_medida
        ]);





    }



    //Implementamos un método para editar registros

    public function editar($idarticulo, $idalmacen, $codigo_proveedor, $codigo, $nombre, $idfamilia, $unidad_medida, $costo_compra, $saldo_iniu, $valor_iniu, $saldo_finu, $valor_finu, $stock, $comprast, $ventast, $portador, $merma, $precio_venta, $imagen, $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi, $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett, $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante, $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra, $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem, $umedidacompra, $factorc, $descripcion)
    {
        // SEGURIDAD: Usar prepared statements para prevenir SQL Injection

        // Manejar fechas NULL (si es 'NULL' string, convertir a NULL real)
        $fechafabricacion = ($fechafabricacion === 'NULL') ? NULL : $fechafabricacion;
        $fechavencimiento = ($fechavencimiento === 'NULL') ? NULL : $fechavencimiento;
        $fechaingalm = ($fechaingalm === 'NULL') ? NULL : $fechaingalm;
        $fechafinalma = ($fechafinalma === 'NULL') ? NULL : $fechafinalma;
        $fechafacturacompra = ($fechafacturacompra === 'NULL') ? NULL : $fechafacturacompra;

        $sql = "UPDATE articulo SET
                idalmacen=?, codigo_proveedor=?, codigo=?, nombre=?, idfamilia=?, unidad_medida=?,
                costo_compra=?, saldo_iniu=?, valor_iniu=?, saldo_finu=?, valor_finu=?, stock=?,
                comprast=?, ventast=?, portador=?, merma=?, precio_venta=?, imagen=?,
                codigosunat=?, ccontable=?, precio2=?, precio3=?, cicbper=?, nticbperi=?,
                ctticbperi=?, mticbperu=?, codigott=?, desctt=?, codigointtt=?, nombrett=?,
                lote=?, marca=?, fechafabricacion=?, fechavencimiento=?, procedencia=?, fabricante=?,
                registrosanitario=?, fechaingalm=?, fechafinalma=?, proveedor=?, seriefaccompra=?,
                numerofaccompra=?, fechafacturacompra=?, limitestock=?, tipoitem=?, umedidacompra=?,
                factorc=?, descrip=?
                WHERE idarticulo=?";

        // Tipos: i=int, d=decimal, s=string
        // Total: 48 parámetros (47 valores + 1 WHERE)
        $tipos = "isssissddddddddddsssssddssssssssssssssssssdssdi";

        $params = [
            $idalmacen, $codigo_proveedor, $codigo, $nombre, $idfamilia, $unidad_medida,
            $costo_compra, $saldo_iniu, $valor_iniu, $saldo_finu, $valor_finu, $stock,
            $comprast, $ventast, $portador, $merma, $precio_venta, $imagen,
            $codigosunat, $ccontable, $precio2, $precio3, $cicbper, $nticbperi,
            $ctticbperi, $mticbperu, $codigott, $desctt, $codigointtt, $nombrett,
            $lote, $marca, $fechafabricacion, $fechavencimiento, $procedencia, $fabricante,
            $registrosanitario, $fechaingalm, $fechafinalma, $proveedor, $seriefaccompra,
            $numerofaccompra, $fechafacturacompra, $limitestock, $tipoitem, $umedidacompra,
            $factorc, $descripcion, $idarticulo
        ];

        ejecutarConsultaPreparada($sql, $tipos, $params);

        // SEGURIDAD: Prepared statement para INSERT de subarticulo
        $sqlsubarticrear = "INSERT INTO subarticulo (idarticulo, codigobarra, valorunitario, preciounitario, stock, umventa, estado)
                            VALUES (?, ?, ?, ?, ?, ?, '1')";
        return ejecutarConsultaPreparada($sqlsubarticrear, "isddds", [$idarticulo, $codigo, $costo_compra, $costo_compra, $stock, $unidad_medida]);
    }


    public function editarStockArticulo($idarticuloproduct, $stockproduct)
    {
        // SEGURIDAD: Usar prepared statements para prevenir SQL Injection
        $sql = "UPDATE articulo
                SET stock=?, saldo_iniu=?, saldo_finu=?
                WHERE idarticulo=?";
        ejecutarConsultaPreparada($sql, "dddi", [$stockproduct, $stockproduct, $stockproduct, $idarticuloproduct]);

        $sqlsubartiupdate = "UPDATE subarticulo SET stock=? WHERE idarticulo=?";
        return ejecutarConsultaPreparada($sqlsubartiupdate, "di", [$stockproduct, $idarticuloproduct]);
    }

    //Implementamos un método para desactivar registros

    public function desactivar($idarticulo)
    {
        // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
        $sql = "UPDATE articulo SET estado='0' WHERE idarticulo=?";
        return ejecutarConsultaPreparada($sql, "i", [$idarticulo]);
    }



    //Implementamos un método para activar registros

    public function activar($idarticulo)
    {
        // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
        $sql = "UPDATE articulo SET estado='1' WHERE idarticulo=?";
        return ejecutarConsultaPreparada($sql, "i", [$idarticulo]);
    }



    //Implementar un método para mostrar los datos de un registro a modificar

    public function mostrar($idarticulo)
    {

        $sql = "select * from  articulo  
         a inner join familia f on a.idfamilia=f.idfamilia  inner join umedida um on a.unidad_medida=um.idunidad
         where 
         a.idarticulo='$idarticulo'";
        return ejecutarConsultaSimpleFila($sql);

    }





    public function valoresiniciales($codigoarti)
    {

        $sql = "select year(fecharegistro) as anoarti from  articulo  where codigo='$codigoarti'";
        return ejecutarConsulta($sql);

    }

    public function valoresinicialesTodos($ano)
    {

        $sql = "select year(fecharegistro) as anoarti from  articulo 
        where not idarticulo='1' and not tipoitem='servicios' and year(fecharegistro)='$ano' group by anoarti";
        return ejecutarConsulta($sql);

    }





    public function articuloBusqueda($codigo)
    {

        $sql = "select nombre, stock, precio_venta, um.abre  as  unidad_medida from articulo a inner join umedida um on a.unidad_medida=um.idunidad   where codigo='$codigo'";

        return ejecutarConsultaSimpleFila($sql);

    }







    //Implementar un método para listar los registros

    public function listar($idempresa)
    {

        $sql = "select 
        a.idarticulo, 
        f.idfamilia, 
        a.codigo_proveedor, 
        a.codigo, 
        f.descripcion as familia, 
        left(a.nombre, 50) as nombre, 
        format(a.stock,2) as stock, 
        a.precio_venta as precio, 
        a.costo_compra,
        a.marca,
        a.imagen, 
        a.estado, 
        a.precio_final_kardex,
        a.unidad_medida,
        a.ccontable,
        a.stock as st2,
        um.nombreum,
        date_format(a.fechavencimiento, '%d/%m/%Y') as fechavencimiento,
        al.nombre as nombreal

        from 

        articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa inner join umedida um on a.umedidacompra=um.idunidad and a.tipoitem='productos'
         where 
         not a.nombre='1000ncdg' and e.idempresa='$idempresa' and al.estado='1'";

        return ejecutarConsulta($sql);

    }


    public function listarservicios($idempresa)
    {

        $sql = "select 
        a.idarticulo, 
        f.idfamilia, 
        a.codigo_proveedor, 
        a.codigo, 
        f.descripcion as familia, 
        a.nombre, 
        format(a.stock,2) as stock, 
        a.precio_venta as precio, 
        a.imagen, 
        a.estado, 
        a.precio_final_kardex,
        a.unidad_medida,
        a.ccontable,
        a.stock as st2,
        um.nombreum,
        date_format(a.fechavencimiento, '%d/%m/%Y') as fechavencimiento,
        al.nombre as nombreal

        from 

        articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa inner join umedida um on a.umedidacompra=um.idunidad and a.tipoitem='servicios'
         where 
         not a.nombre='1000ncdg' and e.idempresa='$idempresa' and al.estado='1'";

        return ejecutarConsulta($sql);

    }



    //Implementar un método para listar los registros activos

    public function listarActivos($idempresa)
    {

        $sql = "select a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        a.precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        um.abre,
        um.nombreum, 
        (a.precio_venta * 1.18) as precio_unitario, 
        a.precio_final_kardex,
        a.factorc
        from 
        articulo a inner join familia f ON a.idfamilia=f.idfamilia  inner join  almacen al on a.idalmacen=al.idalmacen inner join  empresa e on al.idempresa=e.idempresa  inner join umedida um on a.umedidacompra=um.idunidad
        where a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and al.estado='1'";
        return ejecutarConsulta($sql);

    }


    public function listarActivosumventa($ida)
    {

        $sql = "select
        a.unidad_medida as iduni,
        um.abre as abre2,
        um.nombreum as nombreum2
        from 
        articulo a inner join familia f ON a.idfamilia=f.idfamilia  inner join umedida um on a.unidad_medida=um.idunidad
        where a.estado='1' and not a.nombre ='1000ncdg' and a.idarticulo='$ida'";
        return ejecutarConsultaSimpleFila($sql);

    }


    public function listarActivossubarticulo($idempresa)
    {

        $sql = "select a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        a.precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        um.abre,
        um.nombreum, 
        (a.precio_venta * 1.18) as precio_unitario, 
        a.precio_final_kardex
        from 
        articulo a inner join familia f ON a.idfamilia=f.idfamilia  inner join  almacen al on a.idalmacen=al.idalmacen inner join  empresa e on al.idempresa=e.idempresa  inner join umedida um on a.umedidacompra=um.idunidad
        where a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and al.estado='1'";
        return ejecutarConsulta($sql);

    }



    //Implementar un método para listar los registros activos, su último precio y el stock (vamos a unir con el último registro de la tabla detalle_ingreso)

    public function listarActivosVentaSoloServicio($idempresa)
    {

        $sql = "select a.idarticulo,

        a.idalmacen, 

        a.codigo_proveedor, 

        a.codigo, 

        a.nombre, 

        a.idfamilia, 

        f.descripcion as familia, 

        a.costo_compra, 

        (a.precio_venta) as precio_venta, 

        a.stock, 

        a.imagen, 

        a.estado, 

        a.unidad_medida,

        (a.precio_venta * 1.18) as precio_unitario,

        a.cicbper,

        a.nticbperi,

        a.ctticbperi,

        format(a.mticbperu,2) as mticbperu 

        from articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa  where a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and f.descripcion='SERVICIO'  order by a.stock desc";

        return ejecutarConsulta($sql);

    }





    public function listarActivosVentaCoti($idempresa, $tpc, $alm)
    {

        $sql = "select a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio_venta) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida,
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc

        from 
        articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa  inner join umedida um on a.unidad_medida=um.idunidad   

        where 

        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc";

        return ejecutarConsulta($sql);

    }



    public function listarActivosVentaumventa($idempresa, $tpc, $alm, $tipoprecioa)
    {
        switch ($tipoprecioa) {
            case '1':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio_venta) as precio_venta, 
        
        a.imagen, 
        a.estado, 
        a.unidad_medida,
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip,
        a.tipoitem
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.umedidacompra=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;

            case '2':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio2) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.umedidacompra,
        (a.precio2 * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip,
        a.tipoitem
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.umedidacompra=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;

            case '3':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio3) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.umedidacompra,
        (a.precio3 * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip,
        a.tipoitem
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.unidad_medida=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;
        }


        return ejecutarConsulta($sql);
    }


    public function listarActivosVentaumcompra($idempresa, $tpc, $alm, $tipoprecioa)
    {
        switch ($tipoprecioa) {
            case '1':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio_venta) as precio_venta, 
        
        a.imagen, 
        a.estado, 
        a.unidad_medida,
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.umedidacompra=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;

            case '2':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio2) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida,
        (a.precio2 * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.umedidacompra=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;


            case '3':
                $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio3) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida,
        (a.precio3 * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        a.descrip
        from 
        articulo a 
        inner join familia f on a.idfamilia=f.idfamilia 
        inner join almacen al on a.idalmacen=al.idalmacen 
        inner join empresa e on al.idempresa=e.idempresa 
        inner join umedida um on a.umedidacompra=um.idunidad  
        where 
        a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.idalmacen='$alm' and al.estado='1' order by a.stock desc ";
                break;
        }



        return ejecutarConsulta($sql);

    }



    public function listarActivosVenta2($idempresa, $tpc)
    {

        $sql = "select a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio2) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida, 
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia

         from articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa inner join umedida um on a.unidad_medida=um.idunidad  

          where a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' and al.estado='1' order by a.stock desc";

        return ejecutarConsulta($sql);

    }



    public function listarActivosVenta3($idempresa, $tpc)
    {

        $sql = "select a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        (a.precio3) as precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.unidad_medida, 
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        a.limitestock,
        um.nombreum,
        um.abre,
        um.equivalencia
         from articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa inner join umedida um on a.unidad_medida=um.idunidad  

          where a.estado='1' and not a.nombre ='1000ncdg' and e.idempresa='$idempresa' and a.tipoitem='$tpc' order by a.stock desc";

        return ejecutarConsulta($sql);

    }



    public function listarActivosOrdenServicio()
    {

        $sql = "select a.idarticulo,

        a.idalmacen, 

        a.codigo_proveedor, 

        a.codigo, 

        a.nombre, 

        a.idfamilia, 

        f.descripcion as familia, 

        a.costo_compra, 

        (a.precio_venta) as precio_venta, 

        a.stock, 

        a.imagen, 

        a.estado, 

        a.unidad_medida, 

        (a.precio_venta * 1.18) as precio_unitario

         from articulo a inner join familia f on a.idfamilia=f.idfamilia where a.estado='1' and not a.nombre ='1000ncdg'";

        return ejecutarConsulta($sql);

    }





    //=============================KARDEX=======================================





    public function kardexArticulo($fecha1, $fecha2, $codigo, $idempresa)
    {

        $sql = "select 
        a.codigo, 
        a.nombre, 
        a.saldo_iniu, 
        a.costo_compra, 
        a.valor_iniu, 
        a.valor_finu, 
        date_format(k.fecha, '%d/%m/%y') as fecha, 
        ct1.descripcion, 
        k.numero_doc, 
        k.transaccion, 
        k.cantidad, 
        format(k.costo_1,2) as costo_1, 
        um.abre as unidad_medida, 
        k.saldo_final, 
        k.costo_2, 
        format(k.valor_final,2) as valor_final,
        a.ventast,
        a.comprast,
        a.saldo_finu,
        k.tcambio,
        k.moneda,
        a.precio_final_kardex,
        year(a.fecharegistro) as anoarti,
        a.idarticulo,
        k.idkardex
        from 
        kardex k 
        inner join articulo a on k.idarticulo=a.idarticulo 
        inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo
        inner join umedida um on a.unidad_medida=um.idunidad
         where  
         k.fecha between '$fecha1' and '$fecha2'  and  k.codigo='$codigo' and k.transaccion in('compra', 'venta') 
        order by k.fecha, k.idkardex";

        return ejecutarConsulta($sql);

    }





    public function kardexArticulotodosotroano($ano, $idempresa)
    {
        $sql = "select
        a.idarticulo, 
        a.codigo as codigodkardex, 
        a.nombre, 
        a.saldo_iniu, 
        a.costo_compra, 
        a.valor_iniu, 
        a.valor_finu, 
        date_format(k.fecha, '%d/%m/%y') as fecha, 
        ct1.descripcion, 
        k.numero_doc, 
        k.transaccion, 
        k.cantidad as cantidad, 
        k.costo_1, 
        k.unidad_medida, 
        k.saldo_final, 
        k.costo_2, 
        format(k.valor_final,2) as valor_final,
        a.ventast,
        a.comprast,
        a.saldo_finu,
        k.tcambio,
        k.moneda,
        a.precio_final_kardex,
        year(a.fecharegistro) as anoarti,
        a.idarticulo,
        k.idkardex,
        a.factorc

        from 

        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo inner join  almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa 
         where 
         year(k.fecha)='$ano' and e.idempresa='$idempresa' order by k.fecha, k.idkardex";

        return ejecutarConsulta($sql);

    }



    public function kardexArticulovaloriniciales($ano, $fecha, $codigo, $idempresa)
    {



        $sql = "select 

        a.codigo, 

        a.nombre, 

        a.saldo_iniu, 

        a.costo_compra, 

        a.valor_iniu, 

        a.valor_finu, 

        date_format(k.fecha, '%d/%m/%y') as fecha, 

        ct1.descripcion, 

        k.numero_doc, 

        k.transaccion, 

        k.cantidad as cantidad, 

        k.costo_1, 

        a.unidad_medida, 

        k.saldo_final, 

        k.costo_2, 

        format(k.valor_final,2) as valor_final,

        a.ventast,

        a.comprast,

        a.saldo_finu,

        k.tcambio,

        k.moneda,

        a.idarticulo,

        a.precio_final_kardex,

        year(a.fecharegistro) as anoarti

        from 

        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo inner join  almacen al on a.idalmacen=al.idalmacen inner join empresa e on al.idempresa=e.idempresa where year(k.fecha)='$ano'  and  a.codigo='$codigo' and month(k.fecha) in ($fecha) and e.idempresa='$idempresa' order by k.fecha, k.idkardex";

        return ejecutarConsulta($sql);

    }



    public function obteneridarticulo($codigo)
    {



        $sql = "select
        idarticulo,
        nombre,
        costo_compra, 
        saldo_iniu, 
        valor_iniu, 
        valor_finu, 
        saldo_finu,
        stock,
        factorc
        from 
        articulo
        where
        codigo='$codigo'";

        return ejecutarConsulta($sql);

    }

    public function obteneridarticulotodos($ano)
    {



        $sql = "select
        idarticulo,
        nombre,
        costo_compra, 
        saldo_iniu, 
        valor_iniu, 
        valor_finu, 
        saldo_finu,
        stock,
        factorc,
        codigo
        from 
        articulo
        where not idarticulo='1' and not tipoitem='servicios' and year(fecharegistro)='$ano'";

        return ejecutarConsulta($sql);

    }


    public function consultaridarticulo($codigo)
    {

        $sql = "select idarticulo from articulo where codigo='$codigo'";
        return ejecutarConsulta($sql);
    }




    public function insertarkardexArticulo(
        $idempresa,
        $idarticulo,
        $codigoarti,
        $ano,
        $costoi,
        $saldoi,
        $valori,
        $costof,
        $saldof,
        $valorf,
        $tcompras,
        $tventas,
        $transac
    ) {

        $sqlValor = "select * from  valfinarticulo";
        $regVal = ejecutarConsulta($sqlValor);

        $idempresa_ = '';
        $idarticulo_ = '';
        $codigoa_ = '';
        $ano_ = '';
        $date = date('Y-m-d', time());

        while ($reg = $regVal->fetch_object()) {
            $idempresa_ = $reg->idempresa;
            $idarticulo_ = $reg->idarticulo;
            $codigoa_ = $reg->codigoart;
            $ano_ = $reg->ano;
        }



        if (
            $idempresa_ == $idempresa_ && $ano == $ano_ && $codigoa_ == $codigoarti
            && $transac == 'VENTA'
        ) {

            $sql = "update valfinarticulo set 
                costoi='$costoi', 
                saldoi='$saldoi', 
                valori=costoi * saldoi , 
                tventas='$tventas', 
                saldof = '$saldof', 
                costof= '$costof',
                valorf= '$valorf'
                where 
                idempresa='$idempresa' and idarticulo='$idarticulo' and codigoart='$codigoarti' and ano='$ano'";

        } else if (
            $idempresa_ == $idempresa_ && $ano == $ano_ && $codigoa_ == $codigoarti
            && $transac == 'COMPRA'
        ) {

            $sql = "update valfinarticulo set 
                costoi='$costoi', 
                saldoi='$saldoi', 
                valori=costoi * saldoi , 
                tcompras='$tcompras', 
                saldof = '$saldof', 
                costof= '$costof',
                valorf= '$valorf'
                where 
                idempresa='$idempresa' and idarticulo='$idarticulo' and codigoart='$codigoarti' and ano='$ano'";
            //costof= ((saldof * costoi) + (tcompras * '$costof' )) / (saldof + tcompras) ,

        } else {
            $sql = "insert into valfinarticulo
                 (idempresa, idarticulo, codigoart, ano, costoi, saldoi, valori, costof, saldof, valorf, fechag, tcompras, tventas) 
                values 
                ('$idempresa','$idarticulo', '$codigoarti','$ano','$costoi','$saldoi','$valori','$costof','$saldof','$valorf','$date', '$tcompras', '$tventas')";
            $idNew = ejecutarConsulta_retornarID($sql);
            $sql = "update valfinarticulo  set  
                costoi='$costoi', 
                saldoi='$saldoi', 
                valori=costoi * saldoi , 
                tcompras='$tcompras', 
                tventas='$tventas',
                saldof = '$saldof', 
                costof= '$costof',
                valorf= '$valorf'
                where 
                id='$idNew'";
        }




        return ejecutarConsulta($sql);
    }



    public function kardexxarticuloFechas()
    {
        $sql = "select va.id as idregistro,  a.codigo, a.nombre , va.ano, va.costoi, va.saldoi as saldoi ,va.valori as valori, va.costof as costof, va.saldof as saldof, va.valorf as valorf, va.fechag, va.tcompras as tcompras , va.tventas  as tventas
        from 
        valfinarticulo va inner join articulo a on va.codigoart=a.codigo order by va.id desc ";
        return ejecutarConsulta($sql);
        //format(a.precio_venta,2) as precio_venta,   
    }


    public function mostraractual()
    {
        $sql = "select va.id as idregistro,   a.codigo, a.nombre , va.ano, va.costoi, format(va.saldoi,2) as saldoi ,format(va.valori,2) as valori, format(va.costof,2) as costof, format(va.saldof,2) as saldof, format(va.valorf,2) as valorf, va.fechag, format(va.tcompras,2) as tcompras , format(va.tventas,2)  as tventas
        from 
        valfinarticulo va inner join articulo a on  va.codigoart=a.codigo order by va.id desc";
        return ejecutarConsulta($sql);
        //format(a.precio_venta,2) as precio_venta,   
    }


    public function insertarkardexArticuloTodos($idempresa, $idarticulo, $codigoarti, $ano, $costoi, $saldoi, $valori, $costof, $saldof, $valorf, $date, $tcompras, $tventas)
    {



        $sql = "insert into valfinarticulo (idempresa, idarticulo, codigoart, ano, costoi, saldoi, valori, costof, saldof, valorf, date, fechag, tcompras, tventas) 
                values 
                ('$idempresa','$idarticulo', '$codigoarti','$ano','$costoi','$saldoi','$valori','$costof','$saldof','$valorf','$date', '$tcompras', '$tventas')";

        ejecutarConsulta($sql);

        return ejecutarConsulta($sql);

    }



    public function kardexxarticulo($xcodigot, $ano, $codigo)
    {
        if ($xcodigot == '0') {
            $sql = "select 
        va.codigoart, a.nombre , va.ano, va.costoi, format(va.saldoi,2) as saldoi ,format(va.valori,2) as valori, format(va.costof,2) as costof, format(va.saldof,2) as saldof, format(va.valorf,2) as valorf, va.fechag, va.tcompras, va.tventas
        from  valfinarticulo va inner join articulo a on va.idarticulo=a.idarticulo where va.codigoart='$codigo' and va.ano='$ano' order by va.id desc limit 1";

        } else {
            $sql = "select 
        va.codigoart, a.nombre , va.ano, va.costoi, format(va.saldoi,2) as saldoi ,format(va.valori,2) as valori, format(va.costof,2) as costof, format(va.saldof,2) as saldof, format(va.valorf,2) as valorf, va.fechag , va.tcompras, va.tventas
        from  valfinarticulo va inner join articulo a on va.idarticulo=a.idarticulo order by va.id desc";

        }
        return ejecutarConsulta($sql);

        //format(a.precio_venta,2) as precio_venta,   

    }







    public function saldoanterior($ano, $codigo, $idempresa)
    {
        $sql = "select costof, saldof, valorf from valfinarticulo where codigoart='$codigo' and ano='$ano' - 1 and idempresa='$idempresa' order by id desc limit 1";

        return ejecutarConsulta($sql);

    }


    public function saldoanteriorTodos($ano, $idempresa)
    {
        $sql = "select costof, saldof, valorf from valfinarticulo where ano='$ano' - 1 and idempresa='$idempresa' order by id desc";

        return ejecutarConsulta($sql);

    }





    public function saldoinicialV($ano, $codigo, $idempresa)
    {

        $sql = "select costoi, saldoi, valori, nombre, factorc, um.nombreum from valfinarticulo vfa inner join articulo a on vfa.idarticulo=a.idarticulo  inner join umedida um on a.umedidacompra=um.idunidad
        where codigoart='$codigo' and ano='$ano' and idempresa='$idempresa' order by id desc limit 1";

        return ejecutarConsulta($sql);

    }



    public function kardexArticulototales($ano, $fecha, $codigo)
    {



        $sql = "select 

        a.saldo_iniu, 

        a.costo_compra, 

        a.valor_iniu, 

        a.valor_finu, 

        date_format(k.fecha, '%d/%m/%y') as fecha, 

        ct1.descripcion, 

        k.numero_doc, 

        k.transaccion, 

        k.cantidad as cantidad, 

        format(k.costo_1,2) as costo_1, 

        a.unidad_medida, 

        k.saldo_final, 

        k.costo_2, 

        format(k.valor_final,2) as valor_final,

        a.ventast,

        a.comprast

        from 

        kardex k inner join articulo a on k.idarticulo=a.idarticulo inner join catalogo1 ct1 on k.tipo_documento=ct1.codigo  where year(k.fecha)='$ano'  and  a.codigo='$codigo' and month(k.fecha) in ($fecha) order by k.fecha, k.idkardex";

        return ejecutarConsulta($sql);

    }



    //===========================INVENTARIO==============================



    public function inventariovalorizado($ano)
    {

        $sql = "select 
        ano, 
        idregistro, 
        codigo, 
        denominacion, 
        format(costoinicial,2) as costoinicial, 
        format(saldoinicial,2) as saldoinicial,
        format(valorinicial,2) as valorinicial, 
        format(compras,2) as compras, 
        format(ventas,2) as ventas, 
        format(saldofinal,2) as saldofinal, 
        costo, 
        format(valorfinal,2) as valorfinal
        from 
        reginventariosanos where ano='$ano' order by codigo";

        return ejecutarConsulta($sql);

    }



    public function inventariovalorizadoxcodigo($codigo)
    {

        $sql = "select 

        a.codigo, 

        a.nombre, 

        format(a.saldo_iniu,2) as saldo_iniu, 

        format(a.comprast,2) as comprast, 

        format(a.ventast,2) as ventast, 

        format(a.saldo_finu,2) as saldo_finu, 

        a.costo_compra, 

        format(a.valor_finu,2) as valor_finu 

        from 

        articulo a inner join almacen al on a.idalmacen=al.idalmacen 

        where a.codigo='$codigo' and not a.codigo='1000ncdg'";

        return ejecutarConsulta($sql);

    }



    public function totalinventariovalorizado($ano)
    {

        $sql = "select 
        format(sum(saldoinicial),2) as saldoinicial, 
        format(sum(compras),2) as compras, 
        format(sum(ventas),2) as ventas, 
        format(sum(saldofinal),2) as saldofinal, 
        format(sum(valorfinal),2) as  valorfinal  
        from 
        reginventariosanos where ano='$ano'";

        return ejecutarConsulta($sql);

    }





    public function listarActivosVentaxCodigo($codigob)
    {

        $sql = "select 
        a.idarticulo,
        a.idalmacen, 
        a.codigo_proveedor, 
        a.codigo, 
        a.nombre, 
        a.idfamilia, 
        f.descripcion as familia, 
        a.costo_compra, 
        a.precio_venta, 
        a.stock, 
        a.imagen, 
        a.estado, 
        a.umedidacompra, 
        (a.precio_venta * 1.18) as precio_unitario,
        a.cicbper,
        a.nticbperi,
        a.ctticbperi,
        format(a.mticbperu,2) as mticbperu,
        (a.factorc * a.stock) as factorconversion,
        a.stock,
        a.factorc,
        um.nombreum,
        um.abre, 
        a.tipoitem

         from articulo a inner join familia f on a.idfamilia=f.idfamilia inner join almacen al on a.idalmacen=al.idalmacen inner join   umedida um on a.umedidacompra=um.idunidad
         inner join subarticulo sub on a.idarticulo=sub.idarticulo 
         where a.estado='1' and sub.codigobarra='$codigob'  or a.codigo='$codigob'";

        return ejecutarConsultaSimpleFila($sql);

        //format(a.precio_venta,2) as precio_venta,   

    }









    public function resetearvalores($codigo, $ano)
    {

        $sql = "delete from valfinarticulo";
        return ejecutarConsulta($sql);

    }

    public function resetearvaloresTodos()
    {
        $sql = "delete from valfinarticulo";
        return ejecutarConsulta($sql);
    }





    public function buscararticulo($key)
    {



        define('DB_SERVER', 'localhost');

        define('DB_SERVER_USERNAME', 'YOUR DATA BASE USERNAME');

        define('DB_SERVER_PASSWORD', 'YOUR DATA BASE PASSWORD');

        define('DB_DATABASE', 'YOUR DATA BASE NAME');



        $connexion = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);



        $html = '';



        $result = $connexion->query(

            'select

        a.idarticulo,

        a.idalmacen, 

        a.codigo_proveedor, 

        a.codigo, 

        a.nombre, 

        a.idfamilia, 

        f.descripcion as familia, 

        a.costo_compra, 

        format(a.precio_venta,2) as precio_venta, 

        a.stock, 

        a.imagen, 

        a.estado, 

        a.unidad_medida, 

        (a.precio_venta * 1.18) as precio_unitario,

        a.cicbper,

        a.mticbperu

        from articulo a inner join familia f on a.idfamilia=f.idfamilia

        where a.estado="1" and not a.nombre ="1000ncdg" and a.nombre like "%' . strip_tags($key) . '%" limit 8'

        );

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                $html .=

                    '<div><a class="suggest-element"  

        codigo="' . utf8_encode($row['codigo']) . '"  

        unidad_medida="' . utf8_encode($row['unidad_medida']) . '"  

        precio_venta="' . utf8_encode($row['precio_venta']) . '" 

        stock="' . utf8_encode($row['stock']) . '"  

        nombre="' . utf8_encode($row['nombre']) . '"  

        precio_unitario="' . utf8_encode($row['precio_unitario']) . '" 

        id="' . $row['idarticulo'] . '" 

        cicbper="' . utf8_encode($row['cicbper']) . '" 

        mticbperu="' . $row['mticbperu'] . ' ">'

                    . utf8_encode($row['nombre']) .

                    '</a></div>';

            }

        }

        echo $html;

    }





    public function comboarticulo($anor, $alma)
    {
        $sql = "select 
        a.idarticulo, 
        a.codigo, 
        a.nombre, 
        date_format(a.fecharegistro, '%Y') as anoregistro 
        from articulo a 
        inner join almacen al on a.idalmacen=al.idalmacen 
        where 
        not codigo='1000ncdg' and tipoitem='productos' and al.idalmacen='$alma' and al.estado='1'";

        return ejecutarConsulta($sql);
    }


    public function comboarticuloKardex()
    {
        $sql = "select codigo, nombre from articulo";
        return ejecutarConsulta($sql);

    }



    public function listarDetalledc($idcobranza)
    {

        $sql = "select ddc.id, ddc.iditem, a.nombre, ddc.cantidad, ddc.precio from detalle_doccobranza ddc inner join articulo a on ddc.iditem=a.idarticulo where ddc.iddoccobranza='$idcobranza'";

        return ejecutarConsulta($sql);

    }





    public function Updatecosto2($idkardex, $costoi)
    {

        $sql = "update kardex set costo_2='$costoi' where idkardex='$idkardex'";

        return ejecutarConsulta($sql);



    }











    public function costoGlobal($ano, $mes)
    {

        $promcosto = "select a.codigo, format(sum(dc.valor_unitario)/count(dc.valor_unitario), 2) as promedio  

            from 

            detalle_compra_producto dc inner join articulo a on dc.idarticulo=a.idarticulo inner join compra c on c.idcompra=dc.idcompra 

            where 

             a.tipoitem NOT IN('servicios') and a.idarticulo not in('1') and year(c.fecha)='$ano' and month(c.fecha)='$mes' group by codigo";



        return $costounitario = ejecutarConsulta($promcosto);

    }



    public function obtenerdatos($ano, $mes, $codigoarticulo)
    {

        $promcosto = "select *  

            from 

            articulo  

            where 

            idarticulo='$codigoarticulo'";



        return $costounitario = ejecutarConsulta($promcosto);

    }





    public function obtenerdatosmargeng($ano, $mes)
    {

        $promcosto = "select a.codigo, a.nombre, sum(mg.totalventas) as totalventas, 
         sum(mg.totalcompras) as totalcompras, 
                    format((sum(mg.totalventas) - sum(mg.totalcompras)) / sum(mg.totalventas) ,2) as ganancia, 
        format(((sum(mg.totalventas) - sum(mg.totalcompras))/ sum(mg.totalventas)) * 100,2) as porcentaje from  margenganancia mg inner join articulo a on mg.idarticulo=a.idarticulo where  mg.ano='$ano' and mg.mes='$mes' group by a.nombre 
            ";



        return $costounitario = ejecutarConsulta($promcosto);

    }

    public function obtenerdatosmargengindividual($ano, $mes)
    {

        $promcosto = "select a.codigo, a.nombre, mg.totalventas, mg.totalcompras, mg.ganancia, mg.porcentaje from  margenganancia mg inner join articulo a on mg.idarticulo=a.idarticulo 
            ";



        return $costounitario = ejecutarConsulta($promcosto);

    }






    public function promedioboleta($ano, $mes, $codigoarticulo)
    {

        $promboleta = "select format(sum(db.valor_uni_item_31)/count(db.valor_uni_item_31), 2) as promedio 

        from 

        detalle_boleta_producto db inner join articulo a on db.idarticulo=a.idarticulo inner join boleta b on b.idboleta=db.idboleta 

        where 

        a.idarticulo='$codigoarticulo' and year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes'";



        return $promediobol = ejecutarConsulta($promboleta);

    }





    public function promediofabotodos($ano, $mes)
    {

        $promboleta = "select codigo, nombre, sum(promedio) as promedio from 



(select a.codigo, a.nombre, format(sum(db.valor_uni_item_31)/count(db.valor_uni_item_31), 2) as promedio from detalle_boleta_producto db inner join articulo a on db.idarticulo=a.idarticulo inner join boleta b on b.idboleta=db.idboleta where a.idarticulo not in('1') and a.tipoitem not in ('servicios') and year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' group by codigo

 union all 

 select a.codigo, a.nombre, format(sum(df.valor_uni_item_14)/count(df.valor_uni_item_14), 2) as promedio from detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo inner join factura f on f.idfactura=df.idfactura where a.idarticulo not in('1') and a.tipoitem not in ('servicios') and year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes' group by codigo) 

 as tabla group by codigo";



        return $promediobol = ejecutarConsulta($promboleta);

    }





    public function promediofactura($ano, $mes, $codigoarticulo)
    {

        $promfactura = "select 

        format(sum(df.valor_uni_item_14)/count(df.valor_uni_item_14), 2) as promedio

        from 

        detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo inner join factura f on f.idfactura=df.idfactura 

        where 

        a.idarticulo='$codigoarticulo' and year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes'";



        return $promediofac = ejecutarConsulta($promfactura);

    }



    public function promediofacturatodos($ano, $mes, $codigoarticulo)
    {

        $promfactura = "select

        a.codigo,  format(sum(df.valor_uni_item_14)/count(df.valor_uni_item_14), 2) as promedio

        from 

        detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo inner join factura f on f.idfactura=df.idfactura 

        where 

          a.idarticulo not in('1') and a.tipoitem not in ('servicios') and year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes'

       group by codigo";



        return $promediofac = ejecutarConsulta($promfactura);

    }







    public function validarcodigo($codigo)
    {

        $sql = "select codigo from articulo where codigo='$codigo'";

        return ejecutarConsultaSimpleFila($sql);

    }




    public function totalcomprasxcodigo($idarticulo, $ano, $mes)
    {
        $sql = "select sum(dc.subtotal) as totalcostocompra from detalle_compra_producto dc inner join articulo a on dc.idarticulo=a.idarticulo
        inner join compra c on dc.idcompra=c.idcompra where dc.idarticulo='$idarticulo' and year(c.fecha)='$ano' and month(c.fecha)='$mes'  ";
        return ejecutarConsulta($sql);
    }


    public function totalventasxcodigo($idarticulo, $ano, $mes)
    {
        $sql = "select sum(tventas) as totalingresoventa from (
select  valor_venta_item_32 as tventas from detalle_boleta_producto db inner join articulo a on db.idarticulo=a.idarticulo
        inner join boleta b on db.idboleta=b.idboleta where db.idarticulo='$idarticulo' and year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' 
    union all
    select  valor_venta_item_21 as tventas from detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo
        inner join factura f on df.idfactura=f.idfactura where df.idarticulo='$idarticulo' and year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes'
        union all
        select  valor_venta_item_32 as tventas from detalle_notapedido_producto dn inner join articulo a on dn.idarticulo=a.idarticulo
        inner join notapedido np on dn.idboleta=np.idboleta where dn.idarticulo='$idarticulo' and year(np.fecha_emision_01)='$ano' and month(np.fecha_emision_01)='$mes' 
        )
        as tabla ";
        return ejecutarConsulta($sql);
    }



    public function totalcomprasgeneral($ano, $mes)
    {
        $sql = "select a.idarticulo, sum(dc.subtotal) as totalcostocompra from detalle_compra_producto dc inner join articulo a on dc.idarticulo=a.idarticulo
        inner join compra c on dc.idcompra=c.idcompra where year(c.fecha)='$ano' and month(c.fecha)='$mes' group by a.idarticulo ORDER BY a.idarticulo asc  ";
        return ejecutarConsulta($sql);
    }

    public function totalventasgeneral($ano, $mes)
    {
        $sql = "select idarticulo, sum(totalingresoventa) as totaliventa from 
        (select a.idarticulo, valor_venta_item_32 as totalingresoventa  from  detalle_boleta_producto db inner join articulo a on db.idarticulo=a.idarticulo
        inner join boleta b on db.idboleta=b.idboleta where year(b.fecha_emision_01)='$ano' and month(b.fecha_emision_01)='$mes' 
        union all
        select   a.idarticulo,  valor_venta_item_21 as totalingresoventa  from  detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo
        inner join factura f on df.idfactura=f.idfactura where year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes'
        union all 
        select a.idarticulo,  valor_venta_item_32 as totalingresoventa  from  detalle_notapedido_producto dnp inner join articulo a on dnp.idarticulo=a.idarticulo
        inner join notapedido np on dnp.idboleta=np.idboleta where year(np.fecha_emision_01)='$ano' and month(np.fecha_emision_01)='$mes' 

        ) as tabla GROUP BY idarticulo ORDER BY idarticulo asc ";
        return ejecutarConsulta($sql);
    }



    public function insertarmargenganancia($idarticulo, $ano, $mes, $totalventas, $totalcompras, $ganancia, $porcentaje)
    {

        $sqlmgborrar = "delete  from  margenganancia";
        ejecutarConsulta($sqlmgborrar);

        $sqlValor = "select * from  margenganancia";
        $regVal = ejecutarConsulta($sqlValor);


        $idarticulo_ = '';


        while ($reg = $regVal->fetch_object()) {
            $idarticulo_ = $reg->idarticulo;
            $ano_ = $reg->ano;
            $mes_ = $reg->mes;
        }

        if ($idarticulo_ == $idarticulo && $ano_ == $ano && $mes_ == $mes) {

            $sql = "update margenganancia set ano='$ano', mes='$mes', totalventas='$totalventas', totalcompras='$totalcompras', ganancia='$ganancia', porcentaje='$porcentaje' 
                where 
                idarticulo='$idarticulo'";

        } else {

            $sql = "insert into margenganancia (idarticulo, ano, mes, totalventas, totalcompras, ganancia, porcentaje) 
                values 
                ('$idarticulo','$ano', '$mes','$totalventas','$totalcompras','$ganancia','$porcentaje')";

            $idNew = ejecutarConsulta_retornarID($sql);
            $sql = "update margenganancia set  id='$idNew' where idarticulo='$idarticulo' and ano='$ano' and mes='$mes'";

        }

        return ejecutarConsulta($sql);

    }



    public function insertarmargengananciageneral($idarticulo, $ano, $mes, $totalventas, $totalcompras, $ganancia, $porcentaje)
    {

        //$sqlmgborrar="delete  from  margenganancia";
        //ejecutarConsulta($sqlmgborrar);   

        $sqlValor = "select * from  margenganancia";
        $regVal = ejecutarConsulta($sqlValor);


        $idarticulo_ = '';


        while ($reg = $regVal->fetch_object()) {
            $idarticulo_ = $reg->idarticulo;
            $ano_ = $reg->ano;
            $mes_ = $reg->mes;
        }

        if ($idarticulo_ == $idarticulo && $ano_ == $ano && $mes_ == $mes) {

            $sql = "update margenganancia set  totalventas='$totalventas', ganancia='$ganancia', porcentaje='$porcentaje' 
                where 
                idarticulo='$idarticulo'";

        } else {
            $sql = "insert into margenganancia (idarticulo, ano, mes, totalventas, totalcompras, ganancia, porcentaje) 
                values 
                ('$idarticulo','$ano', '$mes','$totalventas','$totalcompras','$ganancia','$porcentaje')";

            //$idNew=ejecutarConsulta_retornarID($sql);
            //$sql="update margenganancia set  idmargeng='$idNew' where idarticulo='$idarticulo' and ano='$ano' and mes='$mes'";
        }



        return ejecutarConsulta($sql);

    }



    public function insertarmargengangeneralventas($idarticulo, $ano, $mes, $totalventas, $totalcompras, $ganancia, $porcentaje)
    {



        $sql = "update margenganancia set ano='$ano', mes='$mes',  totalcompras='$totalcompras', ganancia='$ganancia', porcentaje='$porcentaje' 
               where 
            idarticulo='$idarticulo'";

        return ejecutarConsulta($sql);

    }



    public function mostrarmargeng($idarticulo, $ano, $mes)
    {
        $sql = "select a.nombre, mg.totalventas, mg.totalcompras, mg.ganancia, mg.porcentaje from  margenganancia mg inner join articulo a on mg.idarticulo=a.idarticulo where mg.idarticulo='$idarticulo' and mg.ano='$ano' and mg.mes='$mes' ";
        return ejecutarConsulta($sql);
    }


    public function mostrarmargengtodos($ano, $mes)
    {
        $sql = "select a.nombre, sum(mg.totalventas) as totalventas, sum(mg.totalcompras) as totalcompras, 
format((sum(mg.totalventas) - sum(mg.totalcompras)) / sum(mg.totalventas) ,2) as ganancia, 
format(((sum(mg.totalventas) - sum(mg.totalcompras))/ sum(mg.totalventas)) * 100,2) as porcentaje from  margenganancia mg inner join articulo a on mg.idarticulo=a.idarticulo where  mg.ano='$ano' and mg.mes='$mes' group by a.nombre ";
        return ejecutarConsulta($sql);
    }

    public function almacenlista()
    {

        $sql = "select * from almacen where not idalmacen='1' order by idalmacen";
        return ejecutarConsulta($sql);
    }


    public function kardexArticuloxfechasVentas($fecha1, $fecha2, $vvV, $xxC)
    {

        if ($vvV == "xcod") {
            $sql = "select
        date_format(k.fecha, '%d/%m/%y') as fecha,
        k.codigo,
        k.cantidad,
        format((k.costo_1),2) as costo_1, 
        k.tcambio,
        k.moneda,
        k.idkardex,
        year(k.fecha) as ano,
        k.transaccion
        
        from 
        kardex k  
        where  k.fecha between '$fecha1' and '$fecha2' and  k.codigo='$xxC' and k.transaccion 
        in('VENTA', 'COMPRA')
        order by k.fecha, k.idkardex";
        } else {
            $sql = "select
        k.codigo,
        k.cantidad,
        format((k.costo_1),2) as costo_1, 
        k.tcambio,
        k.moneda,
        k.idkardex,
        year(k.fecha) as ano,
        k.transaccion
        from 
        kardex k 
        where  k.fecha between '$fecha1' and '$fecha2'   and  k.transaccion 
        in('VENTA', 'COMPRA')
        order by k.fecha, k.idkardex";


        }
        return ejecutarConsulta($sql);


    }


    public function valoresinicialesInventario($ano, $codigo)
    {

        $sql = "select * from reginventariosanos where ano='$ano' and codigo='$codigo' ";
        return ejecutarConsulta($sql);
    }


    public function anoregistroarti($codigo)
    {

        $sql = "select year(fecharegistro) as anorega from articulo where codigo='$codigo' ";
        return ejecutarConsulta($sql);
    }


    public function kArtxfechasCompras($fecha1, $fecha2, $codigo)
    {

        $sql = "select
        k.codigo,
        k.transaccion, 
        avg(format((k.costo_1),2)) as promcosto

        from 
        kardex k 
        where  k.fecha between '$fecha1' and '$fecha2' and  codigo='$codigo' and transaccion='compra' group by codigo
        order by k.codigo";
        return ejecutarConsulta($sql);
    }

    public function saldoinicialV2($ano, $codigo, $idempresa)
    {

        $sql = "select costoinicial as costoi, saldoinicial as saldoi, valorinicial as valori, denominacion as nombre 
        from 
        reginventariosanos 
        where 
        codigo='$codigo' and ano='$ano' order by idregistro desc limit 1";
        return ejecutarConsulta($sql);
    }



    // public function insertarArticulosMasivo($codigo, $familia_descripcion, $nombre, $marca, $descrip, $costo_compra, $precio_venta, $stock, $saldo_iniu, $valor_iniu, $tipoitem, $codigott, $desctt, $codigointtt, $nombrett, $nombre_almacen, $saldo_finu)
    // {
    //     $sql = "CALL InsertarDatos('$codigo', '$familia_descripcion', '$nombre', '$marca', '$descrip', $costo_compra, $precio_venta, $stock, $saldo_iniu, $valor_iniu, '$tipoitem', '$codigott', '$desctt', '$codigointtt', '$nombrett', '$nombre_almacen', $saldo_finu)";
    //     return ejecutarConsulta($sql);
    // }


    public function GenerarCodigoCorrelativoAutomatico($codigoExistente = null)
    {
        if ($codigoExistente) {
            return $codigoExistente;
        }

        // Consulta para obtener el último código que comienza con 'PR' de la tabla
        $sql = "select max(codigo) as last_code from articulo where codigo like 'PR%'";
        $result = ejecutarConsulta($sql);

        $row = $result->fetch_assoc();
        $last_code = $row['last_code'];

        // Determinar el nuevo número
        if ($last_code == NULL) {
            $new_num = 1;
        } else {
            $num_part = (int) substr($last_code, 2);
            $new_num = $num_part + 1;
        }

        // Crear el nuevo código
        $new_code = "PR" . str_pad($new_num, 8, "0", STR_PAD_LEFT);

        return $new_code;
    }






}



?>