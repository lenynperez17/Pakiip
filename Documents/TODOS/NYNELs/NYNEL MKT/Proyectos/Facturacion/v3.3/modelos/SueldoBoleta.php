<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class tiposeguro
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertarts($tiposeguro, $nombreSeguro, $snp, $aoafp, $invsob, $comiafp)
	{
		
		$sql="insert into tiposeguro 
		(tiposeguro, nombreSeguro, snp, aoafp, invsob, comiafp)
		values 
		('$tiposeguro', '$nombreSeguro', '$snp', '$aoafp', '$invsob', '$comiafp')";
		
		return ejecutarConsulta($sql);
	}


	public function mostrar($idtiposeguro)
	{
		$sql="select * from tiposeguro where idtiposeguro='$idtiposeguro'";
		return ejecutarConsultaSimpleFila($sql);
	}

	
	public function editarts($idtiposeguro, $tiposeguro, $nombreSeguro, $snp, $aoafp, $invsob, $comiafp)
	{
		$sql="update tiposeguro set tiposeguro='$tiposeguro' , nombreSeguro='$nombreSeguro',
		snp='$snp',aoafp='$aoafp',invsob='$invsob',
		comiafp='$comiafp' where idtiposeguro='$idtiposeguro'";
		return ejecutarConsulta($sql);
	}

	public function listarts()
	{
		$sql="select * from tiposeguro";
		return ejecutarConsulta($sql);
	}

	public function eliminarts($idtiposeguro)
	{
		$sql="delete from tiposeguro where idtiposeguro='$idtiposeguro'";
		return ejecutarConsulta($sql);
	}




	
}






Class empleadoboleta
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}


	public function insertarempleado($nombresE, $apellidosE, $fechaingreso, $ocupacion, $tiporemuneracion, $dni, $autogenessa, $cusspp, $sueldoBruto, $horasT, $asigFam, $trabNoct, $idempresab, $idtiposeguro)
	{
		
		$sql="insert into empleadoboleta 
		(nombresE, apellidosE, fechaingreso, ocupacion,  tiporemuneracion, dni, autogenessa, cusspp, sueldoBruto, horasT, asigFam, trabNoct, idempresab, idtiposeguro)
		values 
		('$nombresE', '$apellidosE', '$fechaingreso', '$ocupacion', '$tiporemuneracion', '$dni', '$autogenessa', '$cusspp', '$sueldoBruto', '$horasT', '$asigFam', '$trabNoct', '$idempresab', '$idtiposeguro')";
		
		return ejecutarConsulta($sql);
	}


	public function editarempleado($idempleado, $nombresE, $apellidosE, $fechaingreso, $ocupacion, $tiporemuneracion, $dni, $autogenessa, $cusspp, $sueldoBruto, $horasT, $asigFam, $trabNoct, $idempresab, $idtiposeguro)
	{
		$sql="update empleadoboleta set 
		nombresE='$nombresE', 
		apellidosE='$apellidosE',
		fechaingreso='$fechaingreso',
		ocupacion='$ocupacion', 
		tiporemuneracion='$tiporemuneracion',
		dni='$dni',
		autogenessa='$autogenessa',
		cusspp='$cusspp',
		sueldoBruto='$sueldoBruto',
		horasT='$horasT' ,
		asigFam='$asigFam' ,
		trabNoct='$trabNoct',
		idempresab='$idempresab',
		idtiposeguro='$idtiposeguro'
		where idempleado='$idempleado'";
		return ejecutarConsulta($sql);
	}


	public function listarempleado()
	{
		$sql="select 
		idempleado, 
		nombresE, 
		apellidosE, 
		date_format(fechaingreso, '%d/%m/%Y') as fechaingreso, 
		ocupacion, 
		tiporemuneracion, 
		dni, 
		cusspp, 
		sueldoBruto, 
		horasT, 
		asigFam, 
		trabNoct, 
		idempresab,
        ts.nombreSeguro,
        e.nombre_comercial
		from 
		empleadoboleta  eb inner join empresa e on eb.idempresab=e.idempresa 
		inner join tiposeguro ts on eb.idtiposeguro=ts.idtiposeguro";
		return ejecutarConsulta($sql);
	}


	public function listar2()
	{
		$sql="select idempleado, nombresE, apellidosE from empleadoboleta";
		return ejecutarConsulta($sql);	
	}

	

	public function cargarempresa()
	{
		$sql="select * from empresa";
		return ejecutarConsulta($sql);
	}

	public function cargarseguro()
	{
		$sql="select * from tiposeguro";
		return ejecutarConsulta($sql);
	}

	public function eliminaremple($idempleado)
	{
		$sql="delete from empleadoboleta where idempleado='$idempleado'";
		return ejecutarConsulta($sql);
	}


	public function mostrarempleado($idempleado)
	{
		$sql="select 
		idempleado, 
		nombresE, 
		apellidosE, 
		date_format(fechaingreso, '%Y-%m-%d') as fechaingresoe, 
		ocupacion, 
		tiporemuneracion, 
		dni,
		autogenessa, 
		cusspp, 
		sueldoBruto, 
		horasT, 
		asigFam, 
		trabNoct, 
		eb.idempresab,
		eb.idtiposeguro  
		from empleadoboleta eb inner join empresa e on eb.idempresab=e.idempresa
		     where idempleado='$idempleado'";
		return ejecutarConsultaSimpleFila($sql);
	}

}






Class BoletaPago
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}


	public function eliminarbol($idboletapago)
	{
		$sql="delete from boletapago where idboletapago='$idboletapago'";
		return ejecutarConsulta($sql);
	}


	public function datosemp($idempresa)
    {
    $sql="select * from empresa where idempresa='$idempresa'";
    return ejecutarConsulta($sql);      
    }


    public function datosboletapago($idboletapago, $idempresa){
        $sql="select 
        idboletapago,
		em.idempresa,
		em.nombre_comercial,
		em.domicilio_fiscal,
		em.numero_ruc,
		eb.idempleado,
		nroboleta, 
 		mes,
 		ano,
 		diast,
 		totaldiast,
 		horasEx,
 		totalhorasEx,
 		totalbruto,
 		total5t,
 		totaldcto,
 		sueldopagar,
 		date_format(fechapago, '%d-%m-%Y') as fechapago,
 		totalaportee,
 		nroboleta,
 		eb.sueldobruto,
 		eb.horast,
 		eb.asigfam,
 		tp.nombreSeguro,
 		tp.tiposeguro,
 		tp.aoafp,
 		tp.invsob,
 		tp.comiafp,
 		tp.snp,
 		bp.taoafp,
 		bp.tinvsob,
 		bp.tcomiafp,
 		bp.tsnp,
 		eb.nombresE,
 		eb.apellidosE,
 		date_format(eb.fechaingreso,'%d/%m/%Y') as fechaingreso,
 		eb.ocupacion,
 		eb.tiporemuneracion,
 		eb.dni,
 		eb.cusspp,
 		eb.trabNoct,
 		conceptoadicional,
 		importeconcepto
		from 
		boletapago bp inner join empleadoboleta eb on  bp.idempleado=eb.idempleado 
		inner join empresa em on eb.idempresab=em.idempresa 
		inner join tiposeguro tp on eb.idtiposeguro= tp.idtiposeguro 
		where idboletapago='$idboletapago'";
        return ejecutarConsulta($sql);

    }


	


	public function editarboletapago($idboletapago, $mesbp, $anobp, $choras, $totalhoras, $hextras, $totalhoex, $totalsbru, 
		$importe5t, $totaldescu, $saldopagar, $fechapagoboleta, $importeessa, $nboleta, $nrobol, $idserie, $taoafp, $tinvsob,
				$tcomiafp, $tsnp , $conceptoadicional, $importeconcepto)
	{
		$sql="update boletapago set 
		mes='$mesbp', 
		ano='$anobp',
		diast='$choras',
		totaldiast='$totalhoras', 
		horasEx='$hextras',
		totalhorasEx='$totalhoex',
		totalBruto='$totalsbru',
		total5t='$importe5t',
		totalDcto='$totaldescu',
		sueldoPagar='$saldopagar',
		fechaPago='$fechapagoboleta',
		totalAporteE='$importeessa',
		nroBoleta='$nboleta',
		taoafp='$taoafp',
		tinvsob='$tinvsob',
		tcomiafp='$tcomiafp',
		tsnp='$tsnp',
		conceptoadicional='$conceptoadicional', 
		importeconcepto='$importeconcepto'
		where idboletapago='$idboletapago'";
		return ejecutarConsulta($sql);
	}





	public function insertar($idempleado, $mesbp, $anobp, $choras, $totalhoras, $hextras, $totalhoex, $totalsbru, $importe5t, $totaldescu, $saldopagar, $fechapagoboleta, $importeessa, $nboleta, $nrobol, $idserie, $taoafp, $tinvsob, $tcomiafp, $tsnp , $conceptoadicional, $importeconcepto)
	{
		$sw=true;
		$sqlinsertar="insert into boletapago (idempleado, mes, ano, diast, totaldiast, horasEx, totalhorasEx, totalbruto, total5t, totalDcto, sueldoPagar, fechaPago, totalAporteE, nroBoleta, taoafp, tinvsob, tcomiafp, tsnp, conceptoadicional, importeconcepto	)
		 values 
		 ('$idempleado', '$mesbp', '$anobp', '$choras', '$totalhoras', '$hextras', '$totalhoex',  '$totalsbru', '$importe5t', '$totaldescu', '$saldopagar', '$fechapagoboleta', '$importeessa', '$nboleta', '$taoafp', '$tinvsob', '$tcomiafp', '$tsnp', '$conceptoadicional', 
		 '$importeconcepto')";
				ejecutarConsulta($sqlinsertar) or $sw=false;


		  $sql_update_numeracion="update 
         numeracion 
         set 
         numero='$nrobol' where idnumeracion='$idserie'";
        ejecutarConsulta($sql_update_numeracion) or $sw=false;
		return $sw;
	}


	public function listarboletapago()
	{
		$sql="select
		idboletapago,
		nroboleta, 
		concat(eb.nombrese,' ',eb.apellidosE)  as  empleado,
 		mes,
 		ano,
 		diast,
 		totaldiast,
 		horasex,
 		totalhorasEx,
 		totalbruto,
 		totaldcto,
 		sueldopagar,
 		fechapago,
 		totalaportee,
 		nroboleta,
 		em.nombre_comercial
		from 
		boletapago bp inner join empleadoboleta eb on bp.idempleado=eb.idempleado 
		inner join empresa em on em.idempresa=eb.idempresab
		order by idboletapago desc";
		return ejecutarConsulta($sql);
	}



	public function mostrarboletapago($idb)
	{
		$sql="select
		idboletapago,
		em.idempresa,
		eb.idempleado,
		nroboleta, 
 		mes,
 		ano,
 		diast,
 		totaldiast,
 		horasEx,
 		totalhorasEx,
 		totalbruto,
 		total5t,
 		totaldcto,
 		sueldopagar,
 		date_format(fechapago, '%Y-%m-%d') as fechapago,
 		totalaportee,
 		nroboleta,
 		conceptoadicional,
 		importeconcepto
		from 
		boletapago bp inner join empleadoboleta eb on  bp.idempleado=eb.idempleado 
		inner join empresa em on eb.idempresab=em.idempresa 
		inner join tiposeguro tp on eb.idtiposeguro= tp.idtiposeguro 
		where idboletapago='$idb'";
		return ejecutarConsultaSimpleFila($sql);
	}



	public function llenarSerieBol($idusuario)
	{
    $sql="select n.idnumeracion, n.serie from numeracion n inner join detalle_usuario_numeracion dn on n.idnumeracion=dn.idnumeracion inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='90' and dn.idusuario='$idusuario' group by n.serie";
    return ejecutarConsultaSimpleFila($sql);    // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario       
    }


    public function llenarNumeroBolpago($serie){
    $sql="select (n.numero+1) as Nnumero from numeracion n inner join detalle_usuario_numeracion dn on n.idnumeracion=dn.idnumeracion inner join usuario u on dn.idusuario=u.idusuario where n.tipo_documento='90' and n.idnumeracion='$serie' limit 1";
    return ejecutarConsulta($sql);  // Las series van deacuerdo a las asginaciones que e le de en los permisos de usuario     
    }




	public function ultimaboleta()
	{
		$sql="select if(nroboleta='','0',nroboleta + 1) as 
		nroboleta 
		from  boletapago  order by idboletapago desc  limit 1 ";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function seleccionempleado($idempleado)
	{
		$sql="select 
		idempleado, 
		nombresE, 
		apellidosE, 
		date_format(fechaingreso, '%Y-%m-%d') as fechaing, 
		ocupacion, 
		tiporemuneracion, 
		dni, 
		cusspp, 
		sueldoBruto, 
		horasT, 
		asigFam, 
		trabNoct, 
		eb.idempresab,
		eb.idtiposeguro,
        tp.tiposeguro,
        tp.nombreSeguro,
        tp.aoafp,
        tp.invsob,
        tp.comiafp,
        tp.snp
		from empleadoboleta eb inner join empresa e on eb.idempresab=e.idempresa
        inner join tiposeguro tp on eb.idtiposeguro=tp.idtiposeguro
		where idempleado='$idempleado'";
		return ejecutarConsultaSimpleFila($sql);
	}


	public function cargarempleados($idempresa)
	{
		$sql="select eb.idempleado, eb.nombresE, eb.apellidosE 
		from 
		empleadoboleta eb inner join empresa e on eb.idempresab=e.idempresa 
		where 
		eb.idempresab='$idempresa'" ;
		return ejecutarConsulta($sql);
	}
	

	
}
?>