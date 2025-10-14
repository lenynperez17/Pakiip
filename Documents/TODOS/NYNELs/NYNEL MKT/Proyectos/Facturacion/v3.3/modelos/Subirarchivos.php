<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Subirarchivos
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    public function comprasfecha($fecha_inicio,$fecha_fin)
    {
        $sql="select date(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado from ingreso i inner join persona p on i.idproveedor=p.idpersona inner join usuario u on i.idusuario=u.idusuario where date(i.fecha_hora)>='$fecha_inicio' and date(i.fecha_hora)<='$fecha_fin'";
        return ejecutarConsulta($sql);      
    }


    public function rutascontingencia()
    {
  
  
    //Inclusion de la tabla RUTAS
    
    $rutacontingencia=opendir('../sfs/contingencia/');

    while ($elemento != "." &&  $elemento  != ".." ){

    // Tratamos los elementos . y .. que tienen todas las carpetas
        if( $elemento != "." && $elemento != ".."){
            // Si es una carpeta
            if( is_dir($path.$elemento) ){
                // Muestro la carpeta
                echo "<p><strong>CARPETA: ". $elemento ."</strong></p>";
            // Si es un fichero
            } else {
                // Muestro el fichero
                echo "<br />". $elemento;
            }
        }
    }

    
   //  $rutarptazip=$rutarpta.'R'.$ruc."-".$tipocomp."-".$numerodoc.".zip";
  
   // $rutaxmlrpta=$rutaunzipxml.'R-'.$ruc."-".$tipocomp."-".$numerodoc.".xml";
   // $rpta = array ('comprobante'=>$rutarptazip, 'femision'=> $rutaxmlrpta, 'vendedor'=>$rutarptazip);
   // return $rpta;
  }
 



}
 
?>