<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Cajachica
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}


    //mostrar todo el total de caja con todo y ventas
    public function TotalVentas()
    {
        
        $sql="SELECT SUM(total_venta) as total_venta 
        FROM (
          SELECT SUM(importe_total_venta_27) as total_venta
          FROM factura 
          WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
          UNION ALL
          SELECT SUM(importe_total_23) as total_venta
          FROM boleta 
          WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
          UNION ALL
          SELECT SUM(importe_total_23) as total_venta
          FROM notapedido 
          WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
          UNION ALL
          SELECT SUM(ingreso - gasto) as total_venta
          FROM insumos
          WHERE DATE(fecharegistro) = CURRENT_DATE
        ) as tbl1";
        return ejecutarConsulta($sql);
    }


    //hacer consulta a columa ingreso

    public function verIngresos()
    {
        $sql="SELECT SUM(ingreso) as total_ingreso FROM insumos WHERE DATE(fecharegistro) = CURRENT_DATE";
        return ejecutarConsulta($sql);		
    }

    //hacer consulta a columna egreso

    public function verEgresos()
    {
        $sql="SELECT SUM(gasto) as total_gasto FROM insumos WHERE DATE(fecharegistro) = CURRENT_DATE";
        return ejecutarConsulta($sql);		
    }


    // INSERTAR saldo inicial por día actual
    public function insertarSaldoInicial($saldo_inicial)
    {
        if ($this->existeSaldoInicialDiaActual()) {
            // Ya existe un saldo inicial para el día actual, no se puede insertar otro
            return false;
        }
        $sql = "INSERT INTO saldocaja (idsaldoini, saldo_inicial, fecha_creacion) 
                VALUES (null, '$saldo_inicial', CURRENT_DATE())";
        return ejecutarConsulta($sql);
    }

    // Verificar si ya existe un saldo inicial para el día actual
    public function existeSaldoInicialDiaActual()
    {
        $sql = "SELECT COUNT(*) as total FROM saldocaja 
                WHERE fecha_creacion = CURRENT_DATE()";
        $resultado = ejecutarConsultaSimpleFila($sql);
        return $resultado['total'] > 0;
    }



    //ver slaod inciial

    public function verSaldoini()
    {
        $sql="SELECT idsaldoini, saldo_inicial FROM saldocaja WHERE fecha_creacion = CURRENT_DATE()";
        return ejecutarConsulta($sql);		
    }


    public function cerrarCaja()
    {
     $sql="INSERT INTO cierrecaja (fecha_cierre, total_caja)
     SELECT 
       CURDATE(), 
       (SELECT SUM(saldo_inicial) FROM saldocaja) 
       + (SELECT SUM(gasto) FROM insumos) 
       + (SELECT SUM(ingreso) FROM insumos) AS total"; 
       return ejecutarConsulta($sql);	 
    }

    
    public function listarCierre(){
        $sql="SELECT cierrecaja.fecha_cierre, cierrecaja.total_caja, 
        SUM(insumos.ingreso) AS total_ingreso, 
        SUM(insumos.gasto) AS total_gasto, 
        saldocaja.saldo_inicial 
        FROM cierrecaja 
        JOIN insumos ON DATE(insumos.fecharegistro) = cierrecaja.fecha_cierre 
        JOIN saldocaja ON DATE(saldocaja.fecha_creacion) = cierrecaja.fecha_cierre 
        GROUP BY cierrecaja.fecha_cierre, cierrecaja.total_caja, saldocaja.saldo_inicial";
        return ejecutarConsulta($sql);	 
    }


}


