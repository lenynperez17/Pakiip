var tabla;
	
//FunciÃ³n que se ejecuta al inicio
function init(){

	mostrarform(false);


	fechapagoboleta
	var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fechapagoboleta').val(today);
	
	$("#formulario").on("submit",function(e)
	{
		guardaryeditarboletapago(e);	
	})


	$.post("../ajax/sueldoBoleta.php?op=cargarempresas", function(r){
              $("#empresa").html(r);
              //$('#empresa').selectpicker('refresh');

             idempre=$("#empresa").val();
  	 		 $.post("../ajax/sueldoBoleta.php?op=cargarempleadosdeempresa&idem="+idempre, function(r){
             $("#idempleado").html(r);
             //$('#idempleado').selectpicker('refresh');

    ide=$("#idempleado").val();
	// Validar que haya un empleado seleccionado antes de hacer la petición
	if(ide && ide !== '' && ide !== 'null' && ide !== 'undefined') {
		$.post("../ajax/sueldoBoleta.php?op=seleccionempleado&idemple="+ide, function(data, status)
		{
			data = JSON.parse(data);

			// Validar que la respuesta contenga datos válidos
			if(data && data.nombresE) {
				$("#nombreemple").val(data.nombresE);
				$("#apeemple").val(data.apellidosE);
				$("#ocupacione").val(data.ocupacion);
				$("#docide").val(data.dni);
				$("#tiporemun").val(data.tiporemuneracion);
				$("#fechai").val(data.fechaing);
				$("#cuapp").val(data.cusspp);


				$("#sueldomensu").val(data.sueldoBruto);
				$("#hextras").val(data.horasT);
				$("#asigfam").val(data.asigFam);
				$("#sobrthr").val(data.trabNoct);


				$("#nombreseg").text(data.nombreSeguro);
				$("#tasaafp").val(data.aoafp);
				$("#tasais").val(data.invsob);
				$("#tasacomi").val(data.comiafp);
				$("#tasasnp").val(data.snp);

				calculartbruto();
				calculardctos();
				calculohorastrabajadas();
				calculoessalud();
			} else {
				console.warn('No se recibieron datos válidos del empleado');
			}
		});
	}

  	});

  	});

    limpiar();
    listar();
    incremetarNum();

    
}


function incremetarNum() {
  $.post("../ajax/sueldoBoleta.php?op=selectSerie", function(data,status) {
      if (data) {
          data = JSON.parse(data);
          if (data.idnumeracion) {
              $("#idserie").val(data.idnumeracion);
              var idserie = data.idnumeracion;
              $.post("../ajax/sueldoBoleta.php?op=autonumeracion&ser="+idserie, function(r){
                  var autosuma = pad(r, 0);
                  document.getElementById("nboleta").innerHTML = data.serie +"-"+ autosuma;
                  $("#nrobol").val(autosuma);
                  $("#nboleta2").val(data.serie +"-"+ autosuma); 
              });
          } else {
              console.error('La propiedad "idnumeracion" no está definida en la respuesta del servidor.');
          }
      } else {
          console.error('No se recibió ninguna respuesta del servidor.');
      }
  });
}



//FunciÃ³n para poner ceros antes del numero siguiente de la factura

function pad (n, length)
{
    var n= n.toString();
    while(n.length<length)
    n="0" + n;
    return n;
}








function seleccionempleado()
{
	ide=$("#idempleado").val();

	// Validar que haya un empleado seleccionado antes de hacer la petición
	if(ide && ide !== '' && ide !== 'null' && ide !== 'undefined') {
		$.post("../ajax/sueldoBoleta.php?op=seleccionempleado&idemple="+ide, function(data, status)
		{
			data = JSON.parse(data);

			// Validar que la respuesta contenga datos válidos
			if(data && data.nombresE) {
				$("#nombreemple").val(data.nombresE);
				$("#apeemple").val(data.apellidosE);
				$("#ocupacione").val(data.ocupacion);
				$("#docide").val(data.dni);
				$("#tiporemun").val(data.tiporemuneracion);
				$("#fechai").val(data.fechaing);
				$("#cuapp").val(data.cusspp);
				$("#totalbruto").val();

				$("#sueldomensu").val(data.sueldoBruto);
				$("#hextras").val(data.horasT);
				$("#asigfam").val(data.asigFam);
				$("#sobrthr").val(data.trabNoct);

				$("#nombreseg").text(data.nombreSeguro);
				$("#tasaafp").val(data.aoafp);
				$("#tasais").val(data.invsob);
				$("#tasacomi").val(data.comiafp);
				$("#tasasnp").val(data.snp);

				calculartbruto();
				calculardctos();
				calculohorastrabajadas();
				calculoessalud();
			} else {
				console.warn('No se recibieron datos válidos del empleado');
			}
		});
	}
}


function seleccionempleadoeditar(idemp)
{
	// Validar que se reciba un ID válido de empleado
	if(idemp && idemp !== '' && idemp !== 'null' && idemp !== 'undefined') {
		$.post("../ajax/sueldoBoleta.php?op=seleccionempleado&idemple="+idemp, function(data, status)
		{
			data = JSON.parse(data);

			// Validar que la respuesta contenga datos válidos
			if(data && data.nombresE) {
				$("#nombreemple").val(data.nombresE);
				$("#apeemple").val(data.apellidosE);
				$("#ocupacione").val(data.ocupacion);
				$("#docide").val(data.dni);
				$("#tiporemun").val(data.tiporemuneracion);
				$("#fechai").val(data.fechaing);
				$("#cuapp").val(data.cusspp);
				$("#totalbruto").val();

				$("#sueldomensu").val(data.sueldoBruto);
				$("#hextras").val(data.horasT);
				$("#asigfam").val(data.asigFam);
				$("#sobrthr").val(data.trabNoct);

				$("#nombreseg").text(data.nombreSeguro);
				$("#tasaafp").val(data.aoafp);
				$("#tasais").val(data.invsob);
				$("#tasacomi").val(data.comiafp);
				$("#tasasnp").val(data.snp);

				calculartbruto();
				calculardctos();
				calculohorastrabajadas();
				calculoessalud();
			} else {
				console.warn('No se recibieron datos válidos del empleado para edición');
			}
		});
	}
}


function calculartbruto()
{
	sueldomensual=parseFloat($("#sueldomensu").val());
    horasextras=parseFloat($("#hextras").val());
    asignafam=parseFloat($("#asigfam").val());
    sobreti=parseFloat($("#sobrthr").val());
    
    var concepadi=parseFloat($("#importeconcepto").val());

    if (Number.isNaN(concepadi)) {
    	concepadi=0;
    }
	
	totalbruto=sueldomensual + horasextras + asignafam + sobreti + concepadi;
	$("#totalsbru").val(totalbruto);

	calculardctos();
	calculoessalud();

}


function calculardctos()
{
	importeafp=parseFloat(($("#tasaafp").val() * $("#totalsbru").val())/100);
	$("#importetasa").val(importeafp.toFixed(2));

	importeais=parseFloat(($("#tasais").val() * $("#totalsbru").val())/100);
	$("#importetasais").val(importeais.toFixed(2));

	importecomi=parseFloat(($("#tasacomi").val() * $("#totalsbru").val())/100);
	$("#importetasacomi").val(importecomi.toFixed(2));

	//importe5tt=0;
	//importe5tt=parseFloat(($("#totalsbru").val() * 8)/100 );
	importe5tt=parseFloat($("#importe5t").val());


	importesnpp=parseFloat(($("#tasasnp").val() * $("#totalsbru").val())/100);
	$("#importesnp").val(importesnpp.toFixed(2));

	totaldesc=importeafp + importeais + importecomi + importe5tt + importesnpp;
	$("#totaldescu").val(totaldesc.toFixed(2));
	saldopa=parseFloat($("#totalsbru").val() - $("#totaldescu").val());
	$("#saldopagar").val(saldopa.toFixed(2));

}

function calculohorastrabajadas()
{
	horastraba=$("#cdias").val() * 8;
	$("#choras").val(horastraba);
	hextrascalc=$("#cdias").val() * $("#cchoras").val();
	$("#horasex").val(hextrascalc);
	importeessal=parseFloat(($("#tasaessa").val() * $("#totalsbru").val())/100);
	$("#importeessa").val(importeessal);
}






function calculoessalud()
{
	aporteemp=parseFloat(($("#tasaessa").val() * $("#totalsbru").val())/100);
	$("#importeessa").val(aporteemp.toFixed(2));
	$("#totalessa").val(aporteemp.toFixed(2));
}





function mostrarform(flag , guaedi)

{

  

if (guaedi=='nuevo') {
  if (flag)
  {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    $("#btnGuardar").prop("disabled",false);
    $("#btnagregar").hide();
    $("#nboleta").show();
    $("#serie").show();
    $("#datosempleado").show();
  }
  else
  {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
    $("#nboleta").hide();
    $("#serie").hide();
  }
  incremetarNum();
  limpiar();

}else{

	if (flag)
  {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    $("#btnGuardar").prop("disabled",false);
    $("#btnagregar").hide();
    $("#nboleta").show();
    $("#serie").show();
  }
  else
  {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
    $("#nboleta").hide();
    $("#serie").hide();
  }

}




}



function cargarempleadocombo()
{
	idempre=$("#empresa").val();
  	$.post("../ajax/sueldoBoleta.php?op=cargarempleadosdeempresa&idem="+idempre, function(r){
              $("#idempleado").html(r);
              //$('#idempleado').selectpicker('refresh');

              seleccionempleado();
  	});


}



function cancelarform()
{
  limpiar();
  mostrarform(false);
}




//FunciÃ³n limpiar
function limpiar()
{
	$("#idboletaPago").val("");
	//$("#nrobol").val("");
	//$("#idserie").val("");
	//$("#nboleta2").val("");
	$("#mes").val("01");
	$("#ano").val("2021");
	$("#cdias").val("");
	$("#choras").val("");
	$("#cchoras").val("1.00");
	$("#horasT").val("");
	$("#horasex").val("");
	$("#importe5t").val("0");
	$("#saldopagar").val("0");
	$("#importeconcepto").val("0");
	$("#conceptoadicional").val("");
    
	
		calculartbruto();
        calculardctos();
        calculohorastrabajadas();
        calculoessalud();
	
	 
}



function focusTest(el)
{
   el.select();
}




//FunciÃ³n Listar
function listar()
{

	tabla=$('#tbllistado').dataTable(
	{
		"aProcessing": true,//Activamos el procesamiento del datatables
	    "aServerSide": true,//PaginaciÃ³n y filtrado realizados por el servidor
	    dom: 'Bfrtip',//Definimos los elementos del control de tabla
	    buttons: [		          
		            
		            // 'excelHtml5',
		           
		            // 'pdf'
		        ],
		"ajax":
				{
					url: '../ajax/sueldoBoleta.php?op=listarboletapago',
					type : "get",
					dataType : "json",						
					error: function(e){
						console.log(e.responseText);	
					}
				},
		"bDestroy": true,
		"iDisplayLength": 5,//PaginaciÃ³n
	    "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
	}).DataTable();
}
//FunciÃ³n para guardar o editar





function guardaryeditarboletapago(e) {
  e.preventDefault();    //No se activará la acción predeterminada del evento

  swal.fire({
      title: '¿Desea crear la boleta de pago?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'No',
  }).then((result) => {
      if (result.isConfirmed) {
          var formData = new FormData($("#formulario")[0]);

          $.ajax({
              url: "../ajax/sueldoBoleta.php?op=guardareditarboletapago",
              type: "POST",
              data: formData,
              contentType: false,
              processData: false,

              success: function(datos) { 
                  swal.fire({
                      title: 'Éxito',
                      text: datos,
                      icon: 'success',
                      showConfirmButton: false,
                      timer: 1500
                  });	          
                  limpiar();
                  refrescartabla();
                  mostrarform(false);
              },
              error: function(error) {
                  swal.fire({
                      title: 'Error',
                      text: 'Ha ocurrido un error al crear la boleta de pago',
                      icon: 'error',
                      showConfirmButton: false,
                      timer: 1500
                  });	 
              }
          });
      } else {
          limpiar();
      }
  });
}





function stopRKey(evt) {
var evt = (evt) ? evt : ((event) ? event : null);
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
}


//FunciÃ³n para aceptar solo numeros con dos decimales
  function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, â€™0â€² = 48, â€™9â€² = 57, â€˜.â€™ = 46
  key = e.keyCode ? e.keyCode : e.which

  if(e.keyCode===13  && !e.shiftKey)
    {
       document.getElementById('btnGuardar').focus();  
    }


  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
        if (key > 44 && key < 58) {
          if (field.val() === "") return true;
          var existePto = (/[.]/).test(field.val());
          if (existePto === false){
              regexp = /.[0-9]{10}$/;
          }
          else {
            regexp = /.[0-9]{2}$/;
          }
          return !(regexp.test(field.val()));
        }

        if (key == 46) {
          if (field.val() === "") return false;
          regexp = /^[0-9]+$/;
          return regexp.test(field.val());
        }
        return false;
}



//BLOQUEA ENTER 
document.onkeypress = stopRKey; 

function editarboleta(idboletaPago)
{
	$.post("../ajax/sueldoBoleta.php?op=mostrarbolpago",{idboletaPago : idboletaPago}, function(data, status)
	{

		data = JSON.parse(data);		
		mostrarform(true, 'editar');

		

		$("#idboletaPago").val(data.idboletapago);

		$("#empresa").val(data.idempresa);
		//$('#empresa').selectpicker('refresh');

		//$("#idempleado").val(data.idempleado);
		//$('#idempleado').selectpicker('refresh');

		seleccionempleadoeditar(data.idempleado);
		$("#datosempleado").hide();

	
		$("#mes").val(data.mes);
		$("#ano").val(data.ano);
		$("#cdias").val(data.diast);
		$("#choras").val(data.totaldiast);
		$("#cchoras").val(data.horasEx);
		$("#horasex").val(data.totalhorasEx);

		$("#importe5t").val(data.total5t);
		$("#totaldescu").val(data.totaldcto);
		$("#saldopagar").val(data.sueldopagar);

		$("#fechapagoboleta").val(data.fechapago);

		$("#saldopagar").val(data.sueldopagar);
		$("#totalessa").val(data.totalaportee);

		$("#totalessa").val(data.totalaportee);

		 document.getElementById("nboleta").innerHTML = data.nroboleta;
        $("#nboleta2").val(data.nroboleta);

        $("#conceptoadicional").val(data.conceptoadicional);
        $("#importeconcepto").val(data.importeconcepto);

	
 	})
}




function mayus(e) {
     e.value = e.value.toUpperCase();
}


//FunciÃ³n para desactivar registros
function eliminar(idboletaPago) {
  Swal.fire({
    title: '¿Está seguro de eliminar la boleta de pago?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/sueldoBoleta.php?op=eliminarboleta", {idboletaPago : idboletaPago}, function(e){
        Swal.fire({
          title: 'Boleta eliminada',
          icon: 'success'
        });
        refrescartabla();
        listar();
      });	
    }
  })
}



function refrescartabla()
{
tabla.ajax.reload();
}


function previoprint(idboletap)
{

              var rutacarpeta='../reportes/sueldoboletaprint.php?id='+idboletap;
              $("#modalCom").attr('src',rutacarpeta);
              $("#modalPreview2").modal("show");
}


init();