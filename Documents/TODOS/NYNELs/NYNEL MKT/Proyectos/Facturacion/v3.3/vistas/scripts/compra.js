var tabla;
var unidadesSUNAT = []; // Array global para almacenar unidades SUNAT

//Función que se ejecuta al inicio
function init(){
    //mostrarform(false);
    listarArticulos();
    listar();
    cargarUnidadesSUNAT(); // Cargar unidades SUNAT al inicio
    cargarAlmacenes(); // Cargar almacenes disponibles

    $("#formulario").on("submit",function(e)
    {
        guardaryeditar(e);
    });


    $("#fnuevoprovee").on("submit",function(e)
  {
    guardaryeditarnproveedor(e);
  });


    //Cargamos los items al select proveedor
    $.post("../ajax/compra.php?op=selectProveedor", function(r){
          $("#idproveedor").html(r);

    });

    conNO=1;

    // Contador de caracteres para descripcion_compra
    $("#descripcion_compra").on('input', function() {
        var length = $(this).val().length;
        $("#char_count").text(length);

        // Advertencia visual cuando se acerca al límite
        if (length > 450) {
            $("#char_count").css('color', 'orange');
        }
        if (length >= 490) {
            $("#char_count").css('color', 'red');
        } else if (length <= 450) {
            $("#char_count").css('color', '#6c757d'); // text-muted de Bootstrap
        }
    });

}

// Función para cargar unidades SUNAT desde el servidor
function cargarUnidadesSUNAT() {
    $.ajax({
        url: "../ajax/compra.php?op=listarUnidadesSUNAT",
        type: "GET",
        dataType: "json",
        success: function(data) {
            unidadesSUNAT = data;
            console.log("Unidades SUNAT cargadas:", unidadesSUNAT.length);
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar unidades SUNAT:", error);
            unidadesSUNAT = []; // Array vacío en caso de error
        }
    });
}

// Función para cargar almacenes activos
function cargarAlmacenes() {
    $.ajax({
        url: "../ajax/almacen.php?op=selectAlmacenes",
        type: "GET",
        dataType: "json",
        success: function(data) {
            var select = $("#idalmacen");
            select.html('<option value="">Seleccione almacén...</option>');

            if (data && data.length > 0) {
                $.each(data, function(index, almacen) {
                    select.append('<option value="' + almacen.idalmacen + '">' +
                                  almacen.nombre + ' - ' + almacen.direccion + '</option>');
                });

                // Inicializar Bootstrap Select si está disponible
                if (typeof $.fn.selectpicker !== 'undefined') {
                    select.selectpicker('refresh');
                }

                console.log("Almacenes cargados:", data.length);
            } else {
                console.warn("No se encontraron almacenes activos");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar almacenes:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los almacenes disponibles'
            });
        }
    });
}

function validarProveedor() {
  // Aquí puedes realizar las validaciones necesarias
  // y ejecutar las acciones correspondientes
  // cuando se produce el evento onblur en el campo de entrada.
  // Por ejemplo:
  var numeroDocumento = document.getElementById('numero_documento').value;
  
  // Validar el número de documento y realizar acciones adicionales
  // según tus necesidades.
  // ...
}


function redirectToPage(){
window.location.href = "compralistas";
}


function redirectToPage2(){
window.location.href = "compra";
}






function guardaryeditarnproveedor(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardarNP").prop("disabled", true);
  var formData = new FormData($("#fnuevoprovee")[0]);

  $.ajax({
    url: "../ajax/persona.php?op=guardaryeditarnproveedor",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      Swal.fire({
        title: "¡Éxito!",
        text: datos,
        icon: "success",
        showConfirmButton: false,
        timer: 1500
      }).then(function() {
        
        $.post("../ajax/compra.php?op=selectProveedor", function(r) {
          $("#idproveedor").html(r);
          $("#idproveedor").val(datos); // Establecer el valor seleccionado
          //$('#idproveedor').selectpicker('refresh');
        });
      });
    },    

    error: function (jqXHR, textStatus, errorThrown) {
      Swal.fire({
        title: "¡Error!",
        text: "Hubo un error al procesar la solicitud.",
        icon: "error",
        showConfirmButton: false,
        timer: 1500
      });
    },

    complete: function () {
      limpiar();
      $("#ModalNcategoria").modal("hide");
    },
  });
}




//Función limpiar
function limpiar()
{
    $("#idcompra").val("");
    $("#idproveedor").val("");
    $("#idalmacen").val("");
    $("#fecha_emision").val("");
    $("#proveedor").val("");
    $("#serie_comprobante").val("");
    $("#num_comprobante").val("");
    $("#guia").val("");

    $("#subtotal").val("");
    $("#igv_").val("");
    $("#total").val("");
    $(".filas").remove();
    $("#subtotal").html("0");
    $("#igv_").html("0");
    $("#total").html("0");
    $("#tcambio").val("1");

    // Refrescar Bootstrap Select para almacén si está inicializado
    if (typeof $.fn.selectpicker !== 'undefined' && $("#idalmacen").hasClass('selectpicker')) {
        $("#idalmacen").selectpicker('refresh');
    }



    //Obtenemos la fecha actual
    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fecha_emision').val(today);

    //Marcamos el primer tipo_documento
    $("#tipo_comprobante").val("FACTURA");
    //$("#tipo_comprobante").selectpicker('refresh');

    $("#codigos").val("");
    $("#nombrea").val("");
    $("#stocka").val("");
    $("#codigob").val("");
    $("#umcompra").val("");
    $("#umventa").val("");
    $("#factorc").val("");
    $("#vunitario").val("");
    $("#subarticulo").val("0");



    conNO=1;
}



//Función mostrar formulario
function mostrarform(flag)
{
    limpiar();
    if (flag)
    {
        $("#listadoregistros").hide();
        $("#formularioregistros").show();
        //$("#btnGuardar").prop("disabled",false);
        $("#btnagregar").hide();
        listarArticulos();

        $("#btnGuardar").hide();
        $("#btnCancelar").show();
        detalles=0;
        $("#btnAgregarArt").show();
    }
    else
    {
        $("#listadoregistros").show();
        $("#formularioregistros").hide();
        $("#tipo_comprobante").val("01");
        $("#btnagregar").show();
    }

}

//Función cancelarform
function cancelarform()
{
    var mensaje=confirm("¿Desea cancelar el ingreso?")
    if (mensaje){

    limpiar();
    mostrarform(false);
 }
}


$idempresa=$("#idempresa").val();
//Función Listar
function listar()
{
    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [
              
                ],
        "ajax":
                {
                    url: '../ajax/compra.php?op=listar&idempresa='+$idempresa,
                    type : "get",
                    dataType : "json",
                    error: function(e){
                        console.log(e.responseText);
                    }
                },
        "bDestroy": true,
        "iDisplayLength": 5,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();
}


//Función ListarArticulos
function listarArticulos()
{
    subarticulo=$("#subarticulo")
    tabla=$('#tblarticulos').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [

                ],
        "ajax":
                {
                    url: '../ajax/compra.php?op=listarArticulos&subarti='+subarticulo,
                    type : "get",
                    dataType : "json",
                    error: function(e){
                        console.log(e.responseText);
                    }
                },
        "bDestroy": true,
        "iDisplayLength": 15,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();

}
//Función para guardar o editar
//F



function guardaryeditar(e) {
    e.preventDefault();
    var cant = document.getElementsByName("cantidad[]");
    var prec = document.getElementsByName("valor_unitario[]");

    var sw=0;
    for (var i = 0; i <cant.length; i++) {
        var inpC=cant[i];
        var inpP=prec[i];

        if (inpC.value==0 || inpC.value=="" ){
           sw=sw+1;
        }
    }

    if(sw!=0){
        Swal.fire({
            icon: 'error',
            title: 'Revisar cantidad!',
            text: 'Por favor revise la cantidad de los productos.'
        });
        inpP.focus();
    } else {
        Swal.fire({
            title: '¿Desea guardar la compra?',
            text: "Esta acción no se puede deshacer.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, guardar!'
        }).then((result) => {
            if (result.value) {
                capturarhora();
                var formData = new FormData($("#formulario")[0]);

                $.ajax({
                    url: "../ajax/compra.php?op=guardaryeditar",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(datos) {
                      Swal.fire({
                          icon: 'success',
                          title: 'Excelente',
                          text: 'Los compra han sido guardados correctamente.'
                      });
                      mostrarform(false);
                      listar();
                    }
                });
                limpiar();
                sw=0;
            }
        });
    }
}



function mostrar(idcompra)
{
    $.post("../ajax/compra.php?op=mostrar",{idcompra : idcompra}, function(data, status)
    {
        data = JSON.parse(data);
        mostrarform(true);

        $("#idproveedor").val(data.idproveedor);
        //$("#idproveedor").selectpicker('refresh');
        $("#tipo_comprobante").val(data.tipo_documento);
        //$("#tipo_comprobante").selectpicker('refresh');
        $("#serie_comprobante").val(data.serie);
        $("#num_comprobante").val(data.numero);
        $("#fecha_hora").val(data.fecha);
        $("#impuesto").val(data.igv);
        $("#idcompra").val(data.idcompra);

        //Ocultar y mostrar los botones
        $("#btnGuardar").hide();
        $("#btnCancelar").show();
        $("#btnAgregarArt").hide();
    });

    $.post("../ajax/compra.php?op=listarDetalle&id="+idcompra,function(r){
            $("#detalles").html(r);
    });
}


// Función para eliminar la compra
function eliminarcompra(idcompra) {
  Swal.fire({
    title: "¿Está seguro de anular la compra?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, anular la compra",
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/compra.php?op=eliminarcompra", { idcompra: idcompra }, function (e) {
        Swal.fire({
          title: "Eliminado",
          text: e,
          icon: "success",
           showConfirmButton: false,
             timer: 1500
        }).then(() => {
          tabla.ajax.reload();
        });
      }).fail(function () {
        Swal.fire({
          title: "Error",
          text: "No se pudo eliminar la compra",
          icon: "error",
           showConfirmButton: false,
            timer: 1500
        });
      });
    }
  });
}

// Función para anular un registro
function anular(idingreso) {
  Swal.fire({
    title: "¿Está seguro de anular el ingreso?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, anular el ingreso",
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/ingreso.php?op=anular", { idingreso: idingreso }, function (e) {
        Swal.fire({
          title: "Anulado",
          text: e,
          icon: "success",
           showConfirmButton: false,
             timer: 1500
        }).then(() => {
          tabla.ajax.reload();
        });
      }).fail(function () {
        Swal.fire({
          title: "Error",
          text: "No se pudo anular el ingreso",
          icon: "error",
           showConfirmButton: false,
             timer: 1500
        });
      });
    }
  });
}


//Declaración de variables necesarias para trabajar con las compras y
//sus detalles
var impuesto=18;
var cont=0;
var detalles=0;
//$("#guardar").hide();
$("#btnGuardar").hide();
$("#tipo_comprobante").change(marcarImpuesto);

function marcarImpuesto()
  {
    var tipo_comprobante=$("#tipo_comprobante option:selected").text();
    if (tipo_comprobante=='Factura')
    {
        $("#impuesto").val(impuesto);
    }
    else
    {
        $("#impuesto").val("0");
    }
  }



  //Función para aceptar solo numeros con dos decimales
  function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46
  key = e.keyCode ? e.keyCode : e.which
  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
        if (key > 47 && key < 58) {
          if (field.value === "") return true;
          var existePto = (/[.]/).test(field.val());
          if (existePto === false){
              regexp = /.[0-9]{10}$/;
          }
          else {
            regexp = /.[0-9]{2}$/;
          }
          return !(regexp.test(field.value()));
        }

        if (key == 46) {
          if (field.value === "") return false;
          regexp = /^[0-9]+$/;
          return regexp.test(field.value());
        }
        return false;
}

// Función auxiliar para generar select de UM SUNAT con las 447 unidades
function generarSelectUMSUNAT(valorPorDefecto) {
    var select = '<select class="form-select form-select-sm" name="unidad_medida_sunat[]" style="width: 120px;">';
    select += '<option value="">Seleccionar...</option>';

    for (var i = 0; i < unidadesSUNAT.length; i++) {
        var selected = (unidadesSUNAT[i].codigo === valorPorDefecto) ? 'selected' : '';
        select += '<option value="' + unidadesSUNAT[i].codigo + '" ' + selected + '>' +
                  unidadesSUNAT[i].codigo + ' - ' + unidadesSUNAT[i].descripcion + '</option>';
    }

    select += '</select>';
    return select;
}


function agregarDetalle(idarticulo,familia,codigo_proveedor,codigo,nombre,
    precio_factura,stock,umedidacompra, precio_unitario, valor_unitario, factorc, nombreum)
  {


    var subarticulooption = $("#subarticulo").val();

    if (subarticulooption=='0') {

    var cantidad=1;
    if (idarticulo!="")
    {
        var subtotal=cantidad*precio_factura;
        var igv= subtotal * 0.18;
        var total_fin;
        var contador=1;
        var precio_venta_unitario=0;

        // Generar fila con los 9 campos (3 nuevos campos SUNAT agregados)
        var fila='<tr class="filas" id="fila'+cont+'">'+
        // Columna 1: Botón eliminar
        '<td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle('+cont+')">x</button></td>'+

        // Columna 2: Artículo (nombre del sistema)
        '<td><input type="hidden" name="idarticulo[]" id="idarticulo[]" value="'+idarticulo+'">'+nombre+'</td>'+

        // Columna 3: NUEVA - Código Producto (editable, según comprobante)
        '<td><input type="text" class="form-control form-control-sm" name="codigo_producto[]" '+
        'value="'+codigo+'" placeholder="Cód. producto" style="width: 100px;"></td>'+

        // Columna 4: NUEVA - Descripción Producto (editable, según comprobante)
        '<td><input type="text" class="form-control form-control-sm" name="descripcion_producto[]" '+
        'value="'+nombre+'" placeholder="Descripción" style="width: 200px;" maxlength="500"></td>'+

        // Columna 5: UM Sistema (readonly, ya existía)
        '<td><input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]">'+
        '<input type="text" name="codigo[]" id="codigo[]" value="'+codigo+'" style="display:none;">'+
        '<input type="text" class="form-control form-control-sm" name="unidad_medida[]" '+
        'value="'+umedidacompra+'" readonly style="width: 80px;"></td>'+

        // Columna 6: NUEVA - UM SUNAT (select con 447 unidades del Catálogo 03)
        '<td>'+generarSelectUMSUNAT('NIU')+'</td>'+

        // Columna 7: Cantidad
        '<td><input type="text" required="true" class="form-control form-control-sm" name="cantidad[]" '+
        'onBlur="modificarSubototales()" size="5" onkeypress="return NumCheck(event, this)" '+
        'style="background-color: #D5FFC9; font-weight:bold;" value="1"></td>'+

        // Columna 8: Costo Unitario
        '<td><input type="text" required="true" class="form-control form-control-sm" name="valor_unitario[]" '+
        'onBlur="modificarSubototales()" size="5" onkeypress="return NumCheck(event, this)" '+
        'style="background-color: #D5FFC9;font-weight:bold;"></td>'+

        // Columna 9: Total (calculado)
        '<td><span name="subtotal" id="subtotal'+cont+'" >'+ subtotal.toFixed(2)+'</span>'+
        '<input type="hidden" name="subtotalBD[]" id="subtotalBD[]" value="'+subtotal.toFixed(2)+'">'+
        '<span name="igvG" id="igvG'+cont+'" style="display:none">'+igv.toFixed(2)+'</span>'+
        '<input type="hidden" name="igvBD[]" id="igvBD[]" value="'+igv+'">'+
        '<span name="total" id="total'+cont+'" style="display:none"></span>'+
        '<span name="totalcanti" id="totalcanti'+cont+'" style="display:none"></span>'+
        '<span name="totalcostouni" id="totalcostouni'+cont+'" style="display:none"></span>'+
        '<input style="display:none" type="text" name="precio_venta_unitario" id="precio_venta_unitario'+cont+'" size="5" value="'+precio_venta_unitario+'"></td>'+

        '</tr>';
var conNO;
 var id = document.getElementsByName("idarticulo[]");
        var can = document.getElementsByName("cantidad[]");

        for (var i = 0; i < id.length; i++) {
             var idA=id[i];
             var cantiS=can[i];
         if (idA.value==idarticulo) {
        cantiS.value=parseFloat(cantiS.value) + 1;
         fila="";
         cont=cont - 1;
         conNO=conNO -1;
         }else{
         detalles=detalles;
         }
        }//Fin while

        cont++;
        detalles=detalles+1;
        conNO++;

        $('#detalles').append(fila);
        modificarSubototales();
        $('#myModal').modal('hide');


    }
    else
    {
        alert("Error al ingresar el detalle, revisar los datos del artículo");
        cont=0;
    }


}else{


    $(".filas").remove();
    conNO=1;

    $("#idarticulonarti").val(idarticulo);

    iddarti=$("#idarticulonarti").val();
    $.post("../ajax/compra.php?op=mostrarumventa&idarti="+iddarti, function(data, status)
    {
            data=JSON.parse(data);
            $("#umventa").val(data.nombreum2);
            $("#idumventa").val(data.abre2);
    });


    $("#codigos").val(codigo);
    $("#nombrea").val(nombre);
    $("#stocka").val(stock);
    $("#umcompra").val(nombreum+" | "+umedidacompra);
    $("#factorc").val(factorc);
    $('#myModal').modal('hide');

    setTimeout(function(){
        document.getElementById("codigob").focus();
        },500);
}



  }


function redondeo(numero, decimales)
{
var flotante = parseFloat(numero);
var resultado = Math.round(flotante*Math.pow(10,decimales))/Math.pow(10,decimales);
return resultado;
}






function agregarDetalleBarra(e)
  {

    var cantidad=1;
    var codigob=$("#codigob").val();


    if(e.keyCode===13  && !e.shiftKey)

    {
  //     $.post("../ajax/compra.php?op=listarArticuloscompraxcodigo&codigob="+codigob, function(data,status)
  //   {
  //       data=JSON.parse(data);

  //       var subtotal=0;
  //       var igv= subtotal * 0.18;
  //       var total_fin;
  //       var contador=1;
  //       var precio_unitario=0;

  //       if (data != null)
  //       {

  //       var contador=1;

  //       var fila='<tr class="filas" id="fila'+cont+'">'+
  //       '<td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle('+cont+')">X</button></td>'+
  //       '<td><input type="hidden" name="idarticulo[]" id="idarticulo[]" value="'+data.idarticulo+'">'+data.nombre+'</td>'+
  //       '<td><input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]" >'+data.codigo_proveedor+'</td>'+
  //       '<td><input type="text" class="" name="codigo[]" id="codigo[]" value="'+data.codigo+'" style="display:none;"  ></td>'+
  //       '<td><input type="text" class="" name="unidad_medida[]" id="unidad_medida[]" value="'+data.abre+'" ></td>'+

  //       '<td><input type="text" required="true" class="" name="cantidad[]" id="cantidad[]" onBlur="modificarSubototales()" size="5" onkeypress="return NumCheck(event, this)" style="background-color: #D5FFC9; font-weight:bold; " value="1"></td>'+
  //       '<td><input type="text" required="true" class="" name="valor_unitario[]" id="valor_unitario[]" onBlur="modificarSubototales()" size="5" onkeypress="return NumCheck(event, this)" style="background-color: #D5FFC9;font-weight:bold; "></td>'+
  //       //'<td><input type="text"  class="" name="valor_venta[]" id="valor_venta[]" size="5" onkeypress="return NumCheck(event, this)" value="'+precio_unitario+'" style="background-color: #FF94A0; font-weight:bold; display:none;"></td>'+

  //       '<td><span name="subtotal" id="subtotal'+cont+'" >'+ subtotal.toFixed(2)+'</span>'+
  //       '<input type="hidden" name="subtotalBD[]" id="subtotalBD[]" value="'+subtotal.toFixed(2)+'">'+

  //       '<span name="igvG" id="igvG'+cont+'" style="display:none">'+igv.toFixed(2)+'</span>'+
  //       '<input type="hidden" name="igvBD[]" id="igvBD[]" value="'+igv+'">'+

  //       '<span name="total" id="total'+cont+'" style="display:none">'+
  //       '<input  style="display:none" type="text" name="precio_venta_unitario" id="precio_venta_unitario'+cont+'" size="5" value="'+precio_unitario+'"></td>'+
  //       //'<td><button type="button" onclick="modificarSubototales()" class="btn btn-info"><i class="fa fa-refresh"></i></button></td>'+
  //       '</tr>';


  //       var id = document.getElementsByName("idarticulo[]");
  //       var can = document.getElementsByName("cantidad[]");

  //       for (var i = 0; i < id.length; i++) {
  //            var idA=id[i];
  //            var cantiS=can[i];
  //        if (idA.value==data.idarticulo) {
  //       cantiS.value=parseFloat(cantiS.value) + 1;
  //        fila="";
  //        cont=cont - 1;
  //        conNO=conNO -1;
  //        }else{
  //        detalles=detalles;
  //        }
  //       }//Fin while

  //       cont++;
  //       detalles=detalles+1;
  //       conNO++;

  //       $('#detalles').append(fila);
  //       modificarSubototales();
  //       $("#codigob").val("");
  //       document.getElementById("codigob").focus();

  //       }
  //       else
  //       {
  //      alert("Artículo no esta registrado en el sistema");
  //      $('#codigob').val("");
  //      document.getElementById("codigob").focus();

  //   }



  // });

  var idarti=$("#idarticulonarti").val();
  var codbar=$("#codigob").val();
  var umedida=$("#umventa").val();
  var iduni=$("#idumventa").val();
  var nunmventa=$("#umventa").val();

        var subtotal=0;
        var igv= subtotal * 0.18;
        var total_fin;
        var contador=1;
        var precio_unitario=0;

        if (codigob != null)
        {


        var contador=1;

        var fila='<tr class="filas" id="fila'+cont+'">'+
        '<td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle('+cont+')">X</button></td>'+

        '<td><input type="hidden" name="idarticulo[]" id="idarticulo[]" value="'+idarti+'">'+
        '<input type="hidden" name="codigobarra[]" id="codigobarra[]"  value="'+codbar+'"><span>'+codbar+'</span></td>'+

        '<td><input type="text" class="" name="codigo[]" id="codigo[]" value="'+codbar+'" style="display:none;">'+
        '<input type="hidden" class="" name="unidad_medida[]" id="unidad_medida[]" value="'+iduni+'" > <span>'+nunmventa+'</span>'+
        '</td>'+

        '<td><input type="text" required="true" class="" name="cantidad[]" id="cantidad[]" onBlur="modificarSubototales()"   value="1">'+

        '</td>'+

        '<td><input type="text" required="true" class="" name="valor_unitario[]" id="valor_unitario[]" onBlur="modificarSubototales()" size="5" onkeypress="return NumCheck(event, this)" style="background-color: #D5FFC9;font-weight:bold;">'+
        '</td>'+

        '<td><span name="subtotal" id="subtotal'+cont+'" >'+ subtotal.toFixed(2)+'</span>'+
        '<input type="hidden" name="subtotalBD[]" id="subtotalBD[]" value="'+subtotal.toFixed(2)+'">'+
        '<span name="igvG" id="igvG'+cont+'" style="display:none">'+igv.toFixed(2)+'</span>'+
        '<input type="hidden" name="igvBD[]" id="igvBD[]" value="'+igv+'">'+
        '<span name="total" id="total'+cont+'" style="display:none"></span>'+

        '<span name="totalcanti" id="totalcanti'+cont+'" style="display:none"></span>'+
        '<span name="totalcostouni" id="totalcostouni'+cont+'" style="display:none"></span>'+

        '<input style="display:none" type="text" name="precio_venta_unitario" id="precio_venta_unitario'+cont+'" size="5" value="'+precio_unitario+'"></td>'+
        '</tr>';


        var id = document.getElementsByName("idarticulo[]");
        var can = document.getElementsByName("cantidad[]");

        for (var i = 0; i < id.length; i++) {
             var idA=id[i];
             var cantiS=can[i];

        }//Fin while

        cont++;
        detalles=detalles+1;
        conNO++;

        $('#detalles').append(fila);
        modificarSubototales();
        $("#codigob").val("");
        document.getElementById("codigob").focus();

        }
        else
        {
       alert("Artículo no esta registrado en el sistema");
       $('#codigob').val("");
       document.getElementById("codigob").focus();

    }




}



}



function redondeo(numero, decimales)
{
var flotante = parseFloat(numero);
var resultado = Math.round(flotante*Math.pow(10,decimales))/Math.pow(10,decimales);
return resultado;
}





  function modificarSubototales()
  {
    var cant = document.getElementsByName("cantidad[]");
    var cot = document.getElementsByName("valor_unitario[]");
    var sub = document.getElementsByName("subtotal");
    var igv = document.getElementsByName("igvG");
    var tot = document.getElementsByName("total");

    var totalcanti = document.getElementsByName("totalcanti");
    var totalcostouni = document.getElementsByName("totalcostouni");

    var tipoca=$("#tcambio").val();
    for (var i = 0; i < cant.length; i++) {
        var inpC=cant[i];
        var inpP=cot[i];
        var inpS=sub[i];
        var inpI=igv[i];
        var inpT=tot[i];

        var inpCsuma=totalcanti[i];
        var inpCsumacostouni=totalcostouni[i];

         document.getElementsByName("valor_unitario[]")[i].value= inpP.value ;

            if ($("#moneda").val()=='USD') {
            var tipoca=$("#tcambio").val();

                }else{
            var tipoca=1;
                }

        //inpI.value=inpI.value;
        //inpS.value=inpC.value * inpP.value * tipoca;
        //inpI.value=inpS.value * 0.18 ;
        //inpT.value=inpS.value + inpI.value;

        inpT.value=(inpC.value * inpP.value) * tipoca;
        inpS.value=(inpT.value / 1.18) * tipoca;

        inpI.value=(inpT.value - inpS.value) * tipoca;

        inpCsuma.value = parseFloat(inpC.value);
        inpCsumacostouni.value = inpP.value;


        document.getElementsByName("subtotal")[i].innerHTML = redondeo(inpT.value,2);
        document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value,2);
        document.getElementsByName("total")[i].innerHTML = redondeo(inpT.value,2);

        document.getElementsByName("totalcanti")[i].innerHTML = inpCsuma.value;
        document.getElementsByName("totalcostouni")[i].innerHTML = inpCsumacostouni.value;
    }

    calcularTotales();
}









  function calcularTotales(){
    var sub = document.getElementsByName("subtotal");
    var igv = document.getElementsByName("igvG");
    var tot = document.getElementsByName("total");


    var subtotal = 0.0;
    var total_igv=0.0;
    var total = 0.0;

    var totalcanti = 0;
    var totalcostouni = 0;

    for (var i = 0; i <sub.length; i++) {
        subtotal += document.getElementsByName("subtotal")[i].value;
        total_igv+=document.getElementsByName("igvG")[i].value;
        total+=document.getElementsByName("total")[i].value;

        totalcanti+=document.getElementsByName("totalcanti")[i].value;
        totalcostouni+=document.getElementsByName("totalcostouni")[i].value;
    }





   $("#subtotal").html(number_format(redondeo(subtotal,2),2));
    $("#subtotal_compra").val(redondeo(subtotal,2));
    $("#igv_").html(number_format(redondeo(total_igv,4),2));
    $("#total_igv").val(redondeo(total_igv,4));
    $("#total").html(number_format(redondeo(total,2),2));
    $("#total_final").val(redondeo(total,2));

    $("#totalcantidad").val(totalcanti);
    $("#totalcostounitario").val(redondeo(totalcostouni,2));


    evaluar();
  }

  function evaluar(){
    if (detalles>0)
    {
      $("#btnGuardar").show();
    }
    else
    {
      $("#btnGuardar").hide();
      cont=0;
    }
  }

  function eliminarDetalle(indice){
    $("#fila" + indice).remove();
    calcularTotales();
    detalles=detalles-1;
    conNO=conNO - 1;
    evaluar();
  }


  function round(value, exp) {
  if (typeof exp === 'undefined' || +exp === 0)
    return Math.round(value);
  value = +value;
  exp  = +exp;

  if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
    return NaN;
  // Shift
  value = value.toString().split('e');
  value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));
  // Shift back
  value = value.toString().split('e');
  return +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));
}


//Función para el formato de los montos
function number_format(amount, decimals) {

    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0)
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}


function cambioproveedor()
{
    // Obtener RUC del proveedor seleccionado (si está disponible)
    var proveedorSeleccionado = $("#idproveedor option:selected").text();

    // Intentar extraer RUC del texto del proveedor
    // Formato típico: "20123456789 - NOMBRE EMPRESA" o similar
    var rucMatch = proveedorSeleccionado.match(/\b(\d{11})\b/);

    if (rucMatch && rucMatch[1]) {
        $("#ruc_emisor").val(rucMatch[1]);
    } else {
        // Si no se encuentra RUC en el texto, buscar en el servidor
        var idProveedor = $("#idproveedor").val();
        if (idProveedor) {
            $.post("../ajax/persona.php?op=obtenerRUC", {idpersona: idProveedor}, function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.ruc) {
                        $("#ruc_emisor").val(data.ruc);
                    }
                } catch(e) {
                    console.log("No se pudo obtener RUC del proveedor");
                }
            });
        }
    }

    document.getElementById("fecha_emision").focus();
}

function handler(e)
{
    document.getElementById("tipo_comprobante").focus();
}

function cambiotcomprobante()
{
    ///document.getElementById("serie_comprobante").focus();
}

function cambiotcambio()
{

  if ($("#moneda").val()=="USD") {
    //$("#modalTcambio").modal("show");
    $("#tcambio").prop("disabled",false);
    $("#tcambio").css("background-color","#FAE5D3");
    document.getElementById("tcambio").focus();
    modificarSubototales();
  }else{
    $("#tcambio").val("1");
    $("#tcambio").prop("disabled",true);
    $("#tcambio").css("background-color","#FDFEFE");
    document.getElementById("btnAgregarArt").focus();
    modificarSubototales();
  }
}

function mayus(e) {
    e.value = e.value.toUpperCase();
}


function EnterSerie(e)
{
    if(e.keyCode===13  && !e.shiftKey){
       document.getElementById('num_comprobante').focus();

    }
}


function EnterNumero(e, field) {
    if(e.keyCode === 13 && !e.shiftKey) {
        document.getElementById('codigob').focus();
    }

    key = e.keyCode ? e.keyCode : e.which;

    if (key === 8 || key === 9) return true; // backspace and tab

    if (key >= 48 && key <= 57) { // numbers
        return true;
    }

    return false;
}


function Entertcambio2(){

  if ($("#moneda").val()=="USD" && $("#tcambio").val()=="") {
    document.getElementById("tcambio").focus();

   }else{
       document.getElementById('btnAgregarArt').focus();
     }
}

function Entertcambio(e,field)
{
    if(e.keyCode===13  && !e.shiftKey){
      if ($("#moneda").val()=="USD" && $("#tcambio").val()=="" ) {
      alert("Ingrese el tipo de cambio o cambie el tipo de moneda");
      document.getElementById("tcambio").focus();
   }else{
       document.getElementById('btnAgregarArt').focus();
     }
   }
        key = e.keyCode ? e.keyCode : e.which
  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
          //if (key == 13) return true;
        if (key > 47 && key < 58) {
          if (field.value() === "") return true;
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


function EnterVuni(e,field) {
    if(e.keyCode === 13 && !e.shiftKey) {
        llenarvu();
        document.getElementById('btnllenarvu').focus();
    }

    key = e.keyCode ? e.keyCode : e.which;

    // Backspace = 8, Tab = 9, ’0′ = 48, ’9′ = 57, ‘.’ = 46
    if (key == 8) return true;
    if (key == 9) return true;

    // Permitir ingresar números y punto decimal
    if ((key >= 48 && key <= 57) || key == 46) {
        // Si no hay ningún caracter en el campo, permitir ingresar cualquier cosa
        if (field.value.length === 0) return true;

        // Verificar si ya existe un punto decimal en el campo
        var existePto = field.value.indexOf(".") !== -1;

        // Si ya existe un punto decimal, permitir ingresar solo dos números después del punto
        if (existePto && key != 46) {
            var decimales = field.value.substring(field.value.indexOf(".")+1);
            if (decimales.length >= 2) return false;
        }

        return true;
    }
    return false;
}





function stopRKey(evt) {
var evt = (evt) ? evt : ((event) ? event : null);
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
}

function llenarvu(){

 var vuni0=$("#vunitario").val();
 var vuni = document.getElementsByName("valor_unitario[]");
 var vunitario=0;

    for (var i = 0; i <= vuni.length; i++) {
        var vunitario=vuni[i];
        vunitario.value=vuni0;


}

}


function NumCheck(e, field) {
    var key = e.keyCode ? e.keyCode : e.which;
    var value = field.value;
    var regexp;

    // backspace
    if (key === 8) return true;
    if (key === 9) return true;

    // números
    if (key > 47 && key < 58) {
        if (!value) return true;
        var existePto = value.indexOf(".") !== -1;
        if (!existePto) {
            regexp = /^\d{0,10}(\.\d{0,2})?$/;
        } else {
            regexp = /^\d{0,10}\.\d{0,2}$/;
        }
        return regexp.test(value);
    }

    // punto
    if (key === 46) {
        if (!value) return false;
        regexp = /^\d+$/;
        return regexp.test(value);
    }

    return false;
}


document.onkeypress = stopRKey;

function capturarhora(){
var f=new Date();
cad=f.getHours()+":"+f.getMinutes()+":"+f.getSeconds();
$("#hora").val(cad);
}

// ========== ESCÁNER QR DE COMPROBANTES ==========
var html5QrCode = null;
var escanerActivo = false;

// Esperar a que el DOM esté listo
$(document).ready(function() {
    // Iniciar escáner cuando se abre el modal (Bootstrap 5)
    var modalElement = document.getElementById('modalEscanerQR');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function () {
            iniciarEscanerQR();
        });

        // Detener escáner cuando se cierra el modal
        modalElement.addEventListener('hidden.bs.modal', function () {
            detenerEscanerQR();
        });
    }
});

function iniciarEscanerQR() {
    if (escanerActivo) return;

    $('#qr-status').show().find('span').text('Iniciando cámara...');
    $('#qr-result').hide();
    $('#qr-error').hide();

    html5QrCode = new Html5Qrcode("reader");

    // Configuración del escáner
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    // Iniciar escáner
    html5QrCode.start(
        { facingMode: "environment" }, // Cámara trasera en móviles
        config,
        onScanSuccess,
        onScanError
    ).then(() => {
        escanerActivo = true;
        $('#qr-status').hide();
    }).catch((err) => {
        $('#qr-status').hide();
        $('#qr-error').show().find('#qr-error-text').text('Error al iniciar cámara: ' + err);
        console.error("Error al iniciar escáner:", err);
    });
}

function detenerEscanerQR() {
    if (html5QrCode && escanerActivo) {
        html5QrCode.stop().then(() => {
            escanerActivo = false;
            html5QrCode = null;
        }).catch((err) => {
            console.error("Error al detener escáner:", err);
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Mostrar resultado
    $('#qr-result').show().find('#qr-result-text').text('QR escaneado correctamente');

    // Detener escáner
    detenerEscanerQR();

    // Procesar datos del QR
    setTimeout(() => {
        procesarDatosQR(decodedText);
        $('#modalEscanerQR').modal('hide');
    }, 1000);
}

function onScanError(errorMessage) {
    // No mostrar errores continuos del escáner
}

function procesarDatosQR(datosQR) {
    console.log("Datos QR:", datosQR);

    // Formato estándar QR SUNAT:
    // RUC|TIPO_CPE|SERIE|NUMERO|IGV|TOTAL|FECHA|TIPO_DOC_RECEPTOR|NUM_DOC_RECEPTOR
    // Ejemplo: 20603949791|01|F001|00000123|18.00|118.00|2024-10-05|6|10459585961

    var partes = datosQR.split('|');

    if (partes.length < 7) {
        Swal.fire({
            title: "Error",
            text: "El código QR no tiene el formato esperado de SUNAT",
            icon: "error"
        });
        return;
    }

    var rucEmisor = partes[0];
    var tipoCPE = partes[1];
    var serie = partes[2];
    var numero = partes[3];
    var igv = partes[4];
    var total = partes[5];
    var fecha = partes[6];

    // Buscar proveedor por RUC
    buscarProveedorPorRUC(rucEmisor, function(proveedorEncontrado) {
        if (!proveedorEncontrado) {
            Swal.fire({
                title: "Proveedor no encontrado",
                text: "El RUC " + rucEmisor + " no está registrado. ¿Desea registrarlo?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, registrar",
                cancelButtonText: "No"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí se podría abrir modal de nuevo proveedor con RUC pre-llenado
                    $('#numero_documento').val(rucEmisor);
                }
            });
        }

        // Llenar campos del formulario (AHORA INCLUYE RUC EMISOR)
        llenarFormularioDesdeQR(tipoCPE, serie, numero, igv, fecha, rucEmisor);
    });
}

function buscarProveedorPorRUC(ruc, callback) {
    $.post("../ajax/compra.php?op=buscarProveedorPorRUC", {ruc: ruc}, function(response) {
        try {
            var data = JSON.parse(response);
            if (data.encontrado) {
                // Seleccionar proveedor en el select
                $('#idproveedor').val(data.idpersona);
                // Refrescar select si usa selectpicker
                if ($('#idproveedor').hasClass('selectpicker')) {
                    $('#idproveedor').selectpicker('refresh');
                }
                callback(true);
            } else {
                callback(false);
            }
        } catch(e) {
            console.error("Error al procesar respuesta:", e);
            callback(false);
        }
    });
}

function llenarFormularioDesdeQR(tipoCPE, serie, numero, igv, fecha, rucEmisor) {
    // Convertir tipo de comprobante (compra.php usa códigos directos)
    var tipoComprobante = "01"; // Por defecto Factura
    if (tipoCPE === "01") {
        tipoComprobante = "01"; // FACTURA
    } else if (tipoCPE === "03") {
        tipoComprobante = "03"; // BOLETA
    } else if (tipoCPE === "12" || tipoCPE === "56") {
        tipoComprobante = "56"; // GUIA REMISIÓN
    }

    // Llenar campos (compra.php tiene diferentes IDs)
    $('#tipo_comprobante').val(tipoComprobante);
    $('#serie_comprobante').val(serie);
    $('#num_comprobante').val(numero);

    // AUTO-LLENAR RUC EMISOR (NUEVO - CAMPO SUNAT)
    if (rucEmisor) {
        $('#ruc_emisor').val(rucEmisor);
    }

    // Convertir fecha de YYYY-MM-DD si es necesario
    if (fecha && fecha.length === 10) {
        $('#fecha_emision').val(fecha);
    }

    Swal.fire({
        title: "¡Datos cargados!",
        text: "Los datos del comprobante se han llenado automáticamente",
        icon: "success",
        timer: 2000,
        showConfirmButton: false
    });
}
// ========== FIN ESCÁNER QR ==========


init();

// ========== FUNCIONES MODAL AGREGAR COMPRA ==========

// Cargar unidades de medida en el modal al abrir
$('#modalAgregarCompra').on('shown.bs.modal', function () {
    cargarUnidadesMedidaModal();
});

// Función para cargar unidades de medida en el select del modal
function cargarUnidadesMedidaModal() {
    $.ajax({
        url: "../ajax/compra.php?op=listarUnidadesSUNAT",
        type: "GET",
        dataType: "json",
        success: function(data) {
            var select = $("#unidad_medida_modal");
            select.html('<option value="">Seleccione...</option>');
            
            if (data && data.length > 0) {
                $.each(data, function(index, unidad) {
                    select.append('<option value="' + unidad.codigo + '">' + 
                                  unidad.codigo + ' - ' + unidad.descripcion + '</option>');
                });
                
                // Preseleccionar NIU (Unidad por defecto)
                select.val('NIU');
                
                console.log("Unidades de medida cargadas en modal:", data.length);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar unidades de medida:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las unidades de medida'
            });
        }
    });
}

// Función para calcular IGV e Importe Total en el modal
function calcularTotalModal() {
    var cantidad = parseFloat($("#cantidad_modal").val()) || 0;
    var baseImponible = parseFloat($("#base_imponible_modal").val()) || 0;
    
    // Calcular IGV (18%)
    var igv = baseImponible * 0.18;
    
    // Calcular Importe Total
    var total = baseImponible + igv;
    
    // Actualizar campos
    $("#igv_modal").val(igv.toFixed(2));
    $("#importe_total_modal").val(total.toFixed(2));
}

// Función para registrar la compra desde el modal
function registrarCompraModal() {
    // Validar formulario
    var form = document.getElementById('formAgregarCompra');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Obtener valores del formulario
    var fecha = $("#fecha_compra").val();
    var tipoComprobante = $("#tipo_comprobante_modal").val();
    var serie = $("#serie_modal").val();
    var numero = $("#numero_modal").val();
    var moneda = $("#moneda_modal").val();
    var codigoBarra = $("#codigo_barra_modal").val();
    var unidadMedida = $("#unidad_medida_modal").val();
    var nombreArticulo = $("#nombre_articulo_modal").val();
    var cantidad = $("#cantidad_modal").val();
    var baseImponible = $("#base_imponible_modal").val();
    var igv = $("#igv_modal").val();
    var importeTotal = $("#importe_total_modal").val();
    
    // Confirmar con el usuario
    Swal.fire({
        title: '¿Registrar esta compra?',
        html: '<div class="text-start">' +
              '<p><strong>Fecha:</strong> ' + fecha + '</p>' +
              '<p><strong>Comprobante:</strong> ' + serie + '-' + numero + '</p>' +
              '<p><strong>Artículo:</strong> ' + nombreArticulo + '</p>' +
              '<p><strong>Cantidad:</strong> ' + cantidad + '</p>' +
              '<p><strong>Total:</strong> S/ ' + importeTotal + '</p>' +
              '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Preparar datos para enviar
            var formData = new FormData();
            formData.append('csrf_token', $('input[name="csrf_token"]').first().val());
            formData.append('fecha_compra', fecha);
            formData.append('tipo_comprobante', tipoComprobante);
            formData.append('serie', serie);
            formData.append('numero', numero);
            formData.append('moneda', moneda);
            formData.append('codigo_barra', codigoBarra);
            formData.append('unidad_medida', unidadMedida);
            formData.append('nombre_articulo', nombreArticulo);
            formData.append('cantidad', cantidad);
            formData.append('base_imponible', baseImponible);
            formData.append('igv', igv);
            formData.append('importe_total', importeTotal);
            
            // Enviar datos al servidor
            $.ajax({
                url: "../ajax/compra.php?op=registrarCompraRapida",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            html: '<div class="text-start">' +
                                  '<p>' + response.message + '</p>' +
                                  '<p><strong>Serie-Número:</strong> ' + response.serie + '-' + response.numero + '</p>' +
                                  '</div>',
                            timer: 3000,
                            showConfirmButton: true
                        });

                        // Limpiar y cerrar modal
                        limpiarModalCompra();
                        $('#modalAgregarCompra').modal('hide');

                        // Recargar tabla si existe
                        if (typeof tabla !== 'undefined' && tabla) {
                            tabla.ajax.reload();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo registrar la compra'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al registrar compra:", error);
                    console.error("Respuesta del servidor:", xhr.responseText);

                    var mensaje = 'No se pudo registrar la compra';

                    // Intentar parsear respuesta JSON del error
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        mensaje = errorResponse.message || mensaje;
                    } catch(e) {
                        // Si no es JSON, mostrar texto plano
                        mensaje = xhr.responseText || error || mensaje;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje
                    });
                }
            });
        }
    });
}

// Función para limpiar el formulario del modal
function limpiarModalCompra() {
    $("#formAgregarCompra")[0].reset();
    $("#fecha_compra").val('<?php echo date("Y-m-d"); ?>');
    $("#igv_modal").val('0.00');
    $("#importe_total_modal").val('0.00');
}

// Limpiar modal al cerrarlo
$('#modalAgregarCompra').on('hidden.bs.modal', function () {
    limpiarModalCompra();
});

// ========== FIN FUNCIONES MODAL AGREGAR COMPRA ==========
