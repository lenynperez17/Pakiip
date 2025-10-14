<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Articuloscreditex
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    
   
    //Implementar un método para listar los registros
    public function listado()
    {
        $sql="select 
        articulo, nombre, date_format(fechacompra,'%d-%m-%Y') as fechacompra, valorunitario, igvunitario, preciounitario
        from 
        articuloscreditex";
        return ejecutarConsulta($sql);      
    }
 }
 
?>