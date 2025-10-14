<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Creditospendiente
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    // public function listarDetalleComprobante($tipodocumento)
    public function listarDetalleComprobante()
    {
        $sql = "SELECT
                    1 AS tipo,
                    f.idfactura AS idcomprobante,
                    f.idcliente,
                    CONCAT ( p.nombres, ' ', p.apellidos ) AS cliente,
                    f.importe_total_venta_27 AS importe_total,
                    f.fechavenc,
                    f.ccuotas,
                    c.montocuota,
                    f.ipagado AS t_pagado,
                    f.saldo - f.ipagado AS t_restante

                FROM
                    factura f

                INNER JOIN
                    cuotas c ON f.idfactura = c.idcomprobante
                INNER JOIN
                    persona p ON f.idcliente = p.idpersona
                    
                WHERE f.tipopago != 'Contado'

                UNION

                SELECT 
                    2 AS tipo,
                    b.idboleta AS idcomprobante,
                    b.idcliente,
                    CONCAT ( p.nombres, ' ', p.apellidos ) AS cliente,
                    b.importe_total_23 AS importe_total,
                    b.fechavenc,
                    b.ccuotas,
                    c.montocuota,
                    b.ipagado AS t_pagado,
                    b.saldo - b.ipagado AS t_restante

                FROM
                    boleta b

                INNER JOIN
                    cuotas c ON b.idboleta = c.idcomprobante
                INNER JOIN
                    persona p ON b.idcliente = p.idpersona
                    
                WHERE b.tipopago != 'Contado'";

         return ejecutarConsulta($sql);

    }

    // public function listarDetalleComprobante($tipoComprobante)
    // {
    //     $whereClause = ""; // Inicializamos la cláusula WHERE

    //     if ($tipoComprobante == 1) {
    //         $whereClause = "WHERE tipo = 1"; // Filtrar por tipo de comprobante 1 (factura)
    //     } elseif ($tipoComprobante == 2) {
    //         $whereClause = "WHERE tipo = 2"; // Filtrar por tipo de comprobante 2 (boleta)
    //     } elseif ($tipoComprobante == 0) {
    //         $whereClause = "WHERE tipo IN (1, 2)"; // Filtrar por ambos tipos (factura y boleta)
    //     }

    //     $sql = "SELECT
    //                 tipo,
    //                 idcomprobante,
    //                 idcliente,
    //                 cliente,
    //                 importe_total,
    //                 fechavenc,
    //                 ccuotas,
    //                 montocuota,
    //                 t_pagado,
    //                 t_restante
    //             FROM (
    //                 SELECT
    //                     1 AS tipo,
    //                     f.idfactura AS idcomprobante,
    //                     f.idcliente,
    //                     CONCAT ( p.nombres, ' ', p.apellidos ) AS cliente,
    //                     f.importe_total_venta_27 AS importe_total,
    //                     f.fechavenc,
    //                     f.ccuotas,
    //                     c.montocuota,
    //                     f.ipagado AS t_pagado,
    //                     f.saldo - f.ipagado AS t_restante
                                    
    //                 FROM
    //                     factura f
                        
    //                 INNER JOIN
    //                     cuotas c ON f.idfactura = c.idcomprobante
    //                 INNER JOIN
    //                     persona p ON f.idcliente = p.idpersona

    //                 WHERE f.tipopago != 'Contado'

    //                 UNION ALL
                    
    //                 SELECT
    //                     2 AS tipo,
    //                     b.idboleta AS idcomprobante,
    //                     b.idcliente,
    //                     CONCAT ( p.nombres, ' ', p.apellidos ) AS cliente,
    //                     b.importe_total_23 AS importe_total,
    //                     b.fechavenc,
    //                     b.ccuotas,
    //                     c.montocuota,
    //                     b.ipagado AS t_pagado,
    //                     b.saldo - b.ipagado AS t_restante
                    
    //                 FROM
    //                     boleta b
                        
    //                 INNER JOIN
    //                     cuotas c ON b.idboleta = c.idcomprobante
    //                 INNER JOIN
    //                     persona p ON b.idcliente = p.idpersona

    //                 WHERE b.tipopago != 'Contado'
    //             ) AS detalles $whereClause";

    //     return ejecutarConsulta($sql);
    // }

    public function listarCuotas($idcomprobante)
    {
        $sql = "SELECT
                    idcuota,
                    ncuota,
                    montocuota,
                    fechacuota,
                    estadocuota
                
                FROM
                    cuotas 
                    
                WHERE idcomprobante = '$idcomprobante'
                
                ORDER BY ncuota";

        return ejecutarConsulta($sql);
    }



}

?>