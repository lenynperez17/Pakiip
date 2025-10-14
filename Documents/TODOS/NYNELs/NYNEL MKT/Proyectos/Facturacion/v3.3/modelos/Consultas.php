<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Consultas
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    public function comprasfecha($fecha_inicio, $fecha_fin)
    {
        $sql = "select date(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado from ingreso i inner join persona p on i.idproveedor=p.idpersona inner join usuario u on i.idusuario=u.idusuario where date(i.fecha_hora)>='$fecha_inicio' and date(i.fecha_hora)<='$fecha_fin'";
        return ejecutarConsulta($sql);
    }

    public function ventasfechacliente($fecha_inicio, $fecha_fin, $idcliente)
    {
        $sql = "select date(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado from venta v inner join persona p on v.idcliente=p.idpersona inner join usuario u on v.idusuario=u.idusuario where date(v.fecha_hora)>='$fecha_inicio' and date(v.fecha_hora)<='$fecha_fin' and v.idcliente='$idcliente'";
        return ejecutarConsulta($sql);
    }

    public function totalcomprahoy($idempresa)
    {
        $sql = "select ifnull(sum(total),0) as total_compra from compra c inner join empresa e on c.idempresa=e.idempresa where date(fecha)=current_date and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }

    public function totalventahoy()
    {
        //$sql="select ifnull(sum(importe_total_venta_27),0) as total_venta from factura where date(fecha_emision_01)=current_date";
        $sql = "select sum(importe_total_venta_27) as total_venta 
        from 
        (select  importe_total_venta_27 
        from 
        factura where date(fecha_emision_01)=current_date and estado in('5','1','6')
        union all
        select importe_total_23 
        from 
        boleta where date(fecha_emision_01)=current_date and estado in('5','1','6')) as tbl1";
        return ejecutarConsulta($sql);
    }

    public function totalventahoyFactura($idempresa)
    {
        //$sql="select ifnull(sum(importe_total_venta_27),0) as total_venta from factura where date(fecha_emision_01)=current_date";
        $sql = "select sum(sumafacdia) as total_venta_factura_hoy 
        from 
        (select  if(tipo_moneda_28='USD', importe_total_venta_27 * tcambio ,importe_total_venta_27)  as sumafacdia
        from 
        factura where date(fecha_emision_01)=current_date and estado in('5','1','6','4') and idempresa='$idempresa'
        ) as tbl1   ";
        return ejecutarConsulta($sql);
    }


    public function totalventahoycotizacion($idempresa)
    {
        //$sql="select ifnull(sum(importe_total_venta_27),0) as total_venta from factura where date(fecha_emision_01)=current_date";
        $sql = "select sum(sumacotidia) as total_venta_coti_hoy 
        from 
        (select  if(moneda='USD', total * tipocambio ,total)  as sumacotidia
        from 
        cotizacion where date(fechaemision)=current_date and estado in('1') and idempresa='$idempresa'
        ) as tbl1";
        return ejecutarConsulta($sql);
    }


    public function totalventahoyFacturaServicio()
    {
        //$sql="select ifnull(sum(importe_total_venta_27),0) as total_venta from factura where date(fecha_emision_01)=current_date";
        $sql = "select sum(importe_total_venta_27) as total_venta_factura_hoy 
        from 
        (select  importe_total_venta_27 
        from 
        facturaservicio where date(fecha_emision_01)=current_date and estado in('5','1','6','4')
        ) as tbl1";
        return ejecutarConsulta($sql);
    }

    public function totalventahoyBoletaServicio()
    {
        $sql = "select sum(importe_total_23) as total_venta_boleta_hoy 
        from 
        (select  importe_total_23
        from 
        boletaservicio where date(fecha_emision_01)=current_date and estado in('5','1','6','4')
        ) as tbl1";
        return ejecutarConsulta($sql);
    }

    public function totalventahoyBoleta($idempresa)
    {
        $sql = " select sum(sumaboldia) as total_venta_boleta_hoy 
        from 
        (select  if(tipo_moneda_24='USD', importe_total_23 * tcambio ,importe_total_23)  as sumaboldia
        from 
        boleta where date(fecha_emision_01)=current_date and estado in('5','1','6','4') 
        and idempresa='$idempresa'
        ) as tbl1  ";
        return ejecutarConsulta($sql);
    }


    public function totalventahoyNotapedido($idempresa)
    {
        $sql = "select sum(np.monto_15_2) as total_venta_npedido_hoy
        from 
        notapedido np inner join empresa e on np.idempresa=e.idempresa where date(np.fecha_emision_01)=current_date and np.estado in('5','1','6','4') and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }



    public function comprasultimos_10dias($idempresa)
    {
        $sql = "select 
        concat(day(c.fecha),'-', month(c.fecha)) as fecha,   monthname(c.fecha) as mes,
        sum(c.total) as total
        from 
        compra c inner join empresa e on c.idempresa=e.idempresa group by c.fecha order by c.fecha desc limit 0,10";
        return ejecutarConsulta($sql);
    }

    public function ventasultimos_12meses($idempresa)
    {
        //$sql="select date_format(fecha_emision_01,'%M') as fecha,sum(importe_total_venta_27) as total from factura group by MonTH(fecha_emision_01) order by fecha_emision_01 DESC limit 0,12";
        $sql = "select 
            date_format(fecha_emision_01,'%M') as fecha, 
            sum(monto) as total 
        from 
        (
            select 
                f.fecha_emision_01, 
                f.importe_total_venta_27 as monto 
            from factura f 
            inner join empresa e on f.idempresa=e.idempresa 
            where f.estado in ('5','6') and e.idempresa='$idempresa' 
        
            union all 
        
            select 
                b.fecha_emision_01, 
                b.importe_total_23 as monto 
            from boleta b 
            inner join empresa e on b.idempresa=e.idempresa 
            where b.estado in ('5','6','4','1','3') and e.idempresa='$idempresa'
        
            union all
        
            select 
                n.fecha_emision_01,
                case 
                    when n.adelanto > 0 then n.adelanto
                    else n.monto_15_2
                end as monto
            from notapedido n 
            inner join empresa e on n.idempresa=e.idempresa 
            where e.idempresa='$idempresa'
        ) as tbl2  
        group by month(fecha_emision_01) 
        order by fecha_emision_01 desc 
        limit 0,12
        ";
        return ejecutarConsulta($sql);
    }

    public function mostrarempresa()
    {
        $sql = "select * from empresa";
        //$listadodb=mysql_query("SHOW DATABASES");
        return ejecutarConsulta($sql);
    }



    public function listadodb()
    {
        $enlace = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
        $resultado = mysql_query("SHOW DATABASES");
        $dbs = array();
        while ($fila = mysql_fetch_row($resultado)) {
            $dbs[] = $fila[0];
        }
        return $dbs;
    }


    // MÉTODO OBSOLETO ELIMINADO: conectar()
    // Este método contenía vulnerabilidades de SQL injection y NO se usa en el proyecto
    // El login actual usa Usuario::verificar() con bcrypt (seguro)
    // Eliminado el: 2025-10-10 por auditoría de seguridad





    public function mostrartipocambio($fechahoy)
    {

        $sql = "select idtipocambio, date_format(fecha, '%Y-%m-%d') as fecha, compra, venta from tcambio where fecha='$fechahoy'";
        return ejecutarConsulta($sql);
    }


    public function mostrarcaja($fechahoy, $idempresa)
    {

        $sql = "select idcaja, date_format(fecha, '%Y-%m-%d') as fecha, montoi, montof, estado from caja c inner join empresa e on c.idempresa=e.idempresa where fecha='$fechahoy' and c.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }


    public function paramscerti()
    {
        $sql = "select * from sunatconfig where idcarga='1'";
        return ejecutarConsulta($sql);
    }

    public function selectumedida()
    {
        $sql = "select * from umedida ";
        return ejecutarConsulta($sql);
    }


    public function selectumedidadearticulo($idarticulo)
    {
        $sql = "select um.abre, um.nombreum 
    from umedida um inner join articulo a on um.idunidad=a.umedidacompra where idarticulo='$idarticulo'";
        return ejecutarConsulta($sql);
    }



    public function impuestoglobal()
    {
        $sql = "select * from configuraciones where idconfiguracion='1' ";
        return ejecutarConsultaSimpleFila($sql);
    }


    public function consultaestados()
    {
        $sql = "select fecha, estado, count(id) as totalestados from ( select fecha_emision_01 as fecha, estado, idfactura as id from factura 
       union all                                                
       select fecha_emision_01 as fecha, estado, idboleta as id from boleta) as estadodocs where month(fecha)=month(CURRENT_date()) group by estado";
        return ejecutarConsulta($sql);
    }


    public function consultaestadoscotizaciones()
    {
        $sql = "select fechaemision as fecha, estado, count(idcotizacion) as totalestados from cotizacion where month(fechaemision)=month(current_date())  group by estado";
        return ejecutarConsulta($sql);
    }

    public function consultaestadosdocumentoC()
    {
        $sql = "select fechaemision as fecha, estado, count(idccobranza) as totalestados from doccobranza where month(fechaemision)=month(current_date())  group by estado";
        return ejecutarConsulta($sql);
    }



    public function descargarcomprobante($ano, $mes, $dia, $comprobante, $estado, $idempresa)
    {
        $sql = "select 
        id,
        tipocomp as tipodocu, 
        date_format(fecha_emision_01, '%Y-%m-%d') as fecha, 
        numer as documento, 
        format(subtotal,2) as subtotal, 
        format(igv,2) as igv, 
        format(total,2) as total  
from 
(
select
        idfactura as id, 
        tipo_documento_07 as tipocomp, 
        fecha_emision_01, 
        numeracion_08 as numer,
        total_operaciones_gravadas_monto_18_2 as subtotal, 
        sumatoria_igv_22_1 as igv, 
        importe_total_venta_27 as total, 
        f.estado as est
        from 
        factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa
        where year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes' and day(fecha_emision_01)='$dia' and e.idempresa='$idempresa' and tipo_documento_07='$comprobante' and f.estado='$estado' union all 
 select
        idboleta as id, 
        tipo_documento_06 as tipocomp, 
        fecha_emision_01, 
        numeracion_07 as numer,
        monto_15_2 as subtotal, 
        sumatoria_igv_18_1 as igv, 
        importe_total_23 as total, 
        b.estado as est
        from 
        boleta b inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa
        where year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes' and day(fecha_emision_01)='$dia' and e.idempresa='$idempresa' and tipo_documento_06='$comprobante' and b.estado='$estado') as tbventa order by fecha";
        return ejecutarConsulta($sql);
    }


    public function ventasdiasemana($idempresa)
    {
        $sql = "select dia, 
        sum(ventasdia) as ventasdia,
        max(horaemision) as horaactualizacion
 from 
 (
     select
          dayofweek(f.fecha_emision_01) as dia,
          sum(f.importe_total_venta_27) as ventasdia,
          time(max(f.fecha_emision_01)) as horaemision
      from factura f 
      where yearweek(f.fecha_emision_01, 1) = yearweek(curdate(), 1) 
      group by dayofweek(f.fecha_emision_01)
      
      union all 
      
      select 
          dayofweek(b.fecha_emision_01) as dia,
          sum(b.importe_total_23) as ventasdia,
          time(max(b.fecha_emision_01)) as horaemision
      from boleta b 
      where yearweek(b.fecha_emision_01, 1) = yearweek(curdate(), 1) 
      group by dayofweek(b.fecha_emision_01)
 
     union all
 
     select
         dayofweek(np.fecha_emision_01) as dia,
         sum(np.monto_15_2) as ventasdia,
         time(max(np.fecha_emision_01)) as horaemision
     from notapedido np 
     where yearweek(np.fecha_emision_01, 1) = yearweek(curdate(), 1) 
     and np.estado in(5,1,6,4) 
     and np.idempresa = '1' 
     group by dayofweek(np.fecha_emision_01)
  
 ) as tbl1  
 group by dia
 
    ";
        return ejecutarConsulta($sql);
    }




    public function totalpordia($ano, $mes, $moneda)
    {
        $sql = "select sum(importe_total_venta_27) as total , dia, nombredia from
(select importe_total_venta_27, day(fecha_emision_01) as dia, 
 dayname(fecha_emision_01) as nombredia from factura where month(fecha_emision_01)='$mes' 
 and year(fecha_emision_01)='$ano' and tipo_moneda_28='$moneda'
 union all 
 select importe_total_23, day(fecha_emision_01) as dia, 
 dayname(fecha_emision_01) as nombredia from boleta where month(fecha_emision_01)='$mes' 
 and year(fecha_emision_01)='$ano' and tipo_moneda_24='$moneda')
as tabla group by dia";
        return ejecutarConsulta($sql);
    }


    public function totalpordianotapedido($ano, $mes)
    {
        $sql = "select sum(importe_total_23) as total , dia, nombredia from
(
 select importe_total_23, day(fecha_emision_01) as dia, 
 dayname(fecha_emision_01) as nombredia from notapedido where month(fecha_emision_01)='$mes' 
 and year(fecha_emision_01)='$ano')
as tabla group by dia";
        return ejecutarConsulta($sql);
    }





    public function totalmesfactura($ano, $mes, $moneda)
    {
        $sql = "select sum(importe_total_venta_27) as totalfactura , dia, nombredia from
(select importe_total_venta_27, day(fecha_emision_01) as dia, 
 dayname(fecha_emision_01) as nombredia from factura where month(fecha_emision_01)='$mes' 
 and year(fecha_emision_01)='$ano' and tipo_moneda_28='$moneda')
    as tabla group by dia";
        return ejecutarConsulta($sql);
    }


    public function totalmesboleta($ano, $mes, $moneda)
    {
        $sql = "select sum(importe_total_23) as totalboleta , dia, nombredia from
(select importe_total_23, day(fecha_emision_01) as dia, 
 dayname(fecha_emision_01) as nombredia from boleta where month(fecha_emision_01)='$mes' 
 and year(fecha_emision_01)='$ano' and tipo_moneda_24='$moneda') 
as tabla group by dia";
        return ejecutarConsulta($sql);
    }


    public function registrarxcodigo($idregistro)
    {
        $sql = "";
        $sqlValor = "select * from  valfinarticulo where id='$idregistro'";
        $regVal = ejecutarConsulta($sqlValor);



        $codVal = '';
        $anoVal = '';
        $costoiVal = '';
        $saldoiVal = '';
        $valoriVal = '';
        $costofVal = '';
        $saldofVal = '';
        $valorfVal = '';
        $tcomprasVal = '';
        $tventasVal = '';


        $codReg = '';
        $anoReg = '';


        while ($reg = $regVal->fetch_object()) {

            $codVal = $reg->codigoart;
            $anoVal = $reg->ano;

            $costoiVal = $reg->costoi;
            $saldoiVal = $reg->saldoi;
            $valoriVal = $reg->valori;
            $costofVal = $reg->costof;
            $saldofVal = $reg->saldof;
            $valorfVal = $reg->valorf;
            $tcomprasVal = $reg->tcompras;
            $tventasVal = $reg->tventas;


        }

        $sqlRegistro = "select * from  reginventariosanos where codigo='$codVal' and ano='$anoVal'";
        $regReg = ejecutarConsulta($sqlRegistro);


        while ($reg2 = $regReg->fetch_object()) {

            $codReg = $reg2->codigo;
            $anoReg = $reg2->ano;
        }

        if ($codVal == $codReg && $anoReg == $anoVal) {

            $sql = "update reginventariosanos set 
                costoinicial='$costoiVal', 
                saldoinicial='$saldoiVal', 
                valorinicial='$valoriVal', 
                compras='$tcomprasVal', 
                ventas='$tventasVal', 
                saldofinal = '$saldofVal', 
                costo= '$costofVal',
                valorfinal= '$valorfVal'
                where 
                codigo='$codVal' and ano='$anoVal'";
            $msg = "Registro actualizado";
            ejecutarConsulta($sql);

        } else {
            $sql = "insert into reginventariosanos
                 (codigo, denominacion, ano, costoinicial, saldoinicial, valorinicial, costo, 
                 saldofinal, valorfinal, compras, ventas) 
                values 
                ('$codVal',(select nombre from articulo where codigo='$codVal') ,'$anoVal','$costoiVal','$saldoiVal','$valoriVal',
                '$costofVal','$saldofVal','$valorfVal', '$tcomprasVal', '$tventasVal')";
            ejecutarConsulta($sql);
            $msg = "Registro nuevo";
        }

        return $msg;

    }


    //Categorias Activas
    public function totalcategoriaActiva()
    {
        $sql = "select count(*) as total from familia where estado = 1";
        return ejecutarConsulta($sql);
    }

    //Categorias Inactivas
    public function totalcategoriaInactiva()
    {
        $sql = "select count(*) as total from familia where estado = 0";
        return ejecutarConsulta($sql);
    }

    //total usuarios registrados
    public function totaUsuarioRegistrados()
    {
        $sql = "select count(*) as total from usuario";
        return ejecutarConsulta($sql);
    }

    public function totaArticulosRegistrados()
    {
        $sql = "select count(*) as total from articulo";
        return ejecutarConsulta($sql);
    }

    public function totaClientesRegistrados()
    {
        $sql = "select count(*) as total 
        from persona 
        where (tipo_persona = 'cliente' or tipo_persona = 'CLIENTE') 
        and numero_documento is not null 
        and trim(numero_documento) <> '' 
        and numero_documento <> 'VARIOS';
        ";
        return ejecutarConsulta($sql);
    }


    //select count(*) as total from usuario where condicion = 1
    //ProductosMas ventidos
    public function productosmasvendidos()
    {
        $sql = "
        select
    a.codigo,
    a.nombre,
    a.imagen,
    a.estado,
    sum(union_tablas.cantidad) as total_unidades_vendidas,
    count(union_tablas.idarticulo) as total_ventas
from (
    select idboleta as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_notapedido_producto
    union all
    select idfactura as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_fac_art
    union all
    select idboleta as idventa, idarticulo, cantidad_item_12 as cantidad from detalle_boleta_producto
) as union_tablas
join articulo a on union_tablas.idarticulo = a.idarticulo
group by union_tablas.idarticulo, a.codigo, a.nombre, a.imagen, a.estado
order by total_ventas desc, total_unidades_vendidas DESC
limit 7;
        ";
        return ejecutarconsulta($sql);
    }


    public function insertarArticulosMasivo($codigo, $familia_descripcion, $nombre, $marca, $descrip, $costo_compra, $precio_venta, $stock, $saldo_iniu, $valor_iniu, $tipoitem, $codigott, $desctt, $codigointtt, $nombrett, $nombre_almacen, $saldo_finu)
    {
        $sql = "CALL InsertarDatos('$codigo', '$familia_descripcion', '$nombre', '$marca', '$descrip', $costo_compra, $precio_venta, $stock, $saldo_iniu, $valor_iniu, '$tipoitem', '$codigott', '$desctt', '$codigointtt', '$nombrett', '$nombre_almacen', $saldo_finu)";
        return ejecutarConsulta($sql);
    }


    public function ClientesTop()
    {
        $sql = "
        select 
        p.idpersona,
        case 
            when p.tipo_documento = '6' then p.razon_social
            when p.tipo_documento = '1' then p.nombres
            else 'Desconocido'
        end as nombrecliente,
        case 
            when p.tipo_documento = '6' then p.domicilio_fiscal
            when p.tipo_documento = '1' then p.numero_documento
            else 'Desconocido'
        end as detallecliente,
        coalesce(f.total_factura, 0) + coalesce(b.total_boleta, 0) + coalesce(n.total_notapedido, 0) as totalgastado
    from 
        persona p
    left join 
        (select idcliente, sum(importe_total_venta_27) as total_factura from factura group by idcliente) f on p.idpersona = f.idcliente
    left join 
        (select idcliente, sum(importe_total_23) as total_boleta from boleta group by idcliente) b on p.idpersona = b.idcliente
    left join 
        (select idcliente, sum(importe_total_23) as total_notapedido from notapedido group by idcliente) n on p.idpersona = n.idcliente
    where 
        p.tipo_documento in ('1', '6')
    group by 
        p.idpersona, p.razon_social, p.nombres, p.apellidos, p.domicilio_fiscal, p.numero_documento
    order by 
        totalgastado desc
    limit 10;    
        ";
        return ejecutarconsulta($sql);
    }

    public function insertarventadiaria($total)
    {
        $sql = "insert into ventadiaria (idcategoriav, fecharegistroingreso, tipo, base, igv, total) 
                values (0, curdate(), 'efectivot', null, null, '$total')
                on duplicate key update total = '$total'";
        return ejecutarconsulta($sql);
    }






}

?>