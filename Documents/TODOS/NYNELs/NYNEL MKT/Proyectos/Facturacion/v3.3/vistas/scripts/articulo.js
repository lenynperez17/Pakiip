var tabla;
var tablas;
var selectedValue = "productos";
var modoDemo = false;

toastr.options = {
  closeButton: false,
  debug: false,
  newestOnTop: false,
  progressBar: false,
  rtl: false,
  positionClass: 'toast-bottom-center',
  preventDuplicates: false,
  onclick: null
};

//var div = document.getElementById("masdatos");
//divT.style.visibility = "hidden";
//Función que se ejecuta al inicio

function init() {


  mostrarform(false);

  listar();
  listarservicios();



  $("#formulario").on("submit", function (e) {

    guardaryeditar(e);

  })





  $("#formnewalmacen").on("submit", function (e) {

    guardaryeditarAlmacen(e);

  })







  $("#formprintbar").on("submit", function (e) {



  })



  // Cargar opciones de select familia
  $.post("../ajax/articulo.php?op=selectFamilia", function (r) {
    $("#idfamilia").html(r);
  });





  // Cargar opciones de select almacén para formulario
  $idempresa = $("#idempresa").val();

  $.post("../ajax/articulo.php?op=selectAlmacen&idempresa=" + $idempresa, function (r) {
    $("#idalmacen").html(r);
  });

  // Cargar filtro de almacén con manejo de errores y Bootstrap Select
  $.post("../ajax/articulo.php?op=selectAlmacen&idempresa=" + $idempresa, function (r) {
    console.log("Respuesta del servidor para filtro_almacen:", r);
    if (r && r.trim() !== '') {
      $("#filtro_almacen").html('<option value="">Todos los almacenes</option>' + r);
      $("#filtro_almacen").selectpicker('refresh');
      console.log("Filtro de almacén cargado exitosamente");
    } else {
      console.warn("No se recibieron datos para el filtro de almacén");
    }
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.error("Error al cargar filtro de almacén:", textStatus, errorThrown);
    toastr.error('Error al cargar almacenes para el filtro');
  });

  // Inicializar Bootstrap Select en filtros
  $("#filtro_almacen").selectpicker();
  $("#filtro_estado").selectpicker();

  // Manejador para el botón de filtrar
  $("#btnFiltrar").on("click", function() {
    console.log("Aplicando filtros - Almacén:", $("#filtro_almacen").val(), "Estado:", $("#filtro_estado").val());
    tabla.ajax.reload();
  });



  // Cargar opciones de unidad de medida
  $.post("../ajax/articulo.php?op=selectUnidad", function (r) {
    $("#unidad_medida").html(r);
    $("#umedidacompra").html(r);
  });





  $("#imagenmuestra").hide();

}



//Función limpiar

function limpiar() {

  $("#codigo").val("");

  $("#codigo_proveedor").val("-");

  $("#nombre").val("");

  $("#costo_compra").val("");

  $("#saldo_iniu").val("");
  $(".salini").val("0");

  $("#valor_iniu").val("0.00");

  $("#saldo_finu").val("");
  $(".salfin").val("0");

  $("#valor_finu").val("0.00");

  $("#stock").val("");

  $(".stokservicio").val("0");


  $("#comprast").val("0.00");

  $("#ventast").val("0.00");

  $("#portador").val("0.00");

  $("#merma").val("0.00");

  $("#valor_venta").val("");

  $("#imagenmuestra").attr("src", "");

  $("#imagenactual").val("");

  $("#print").hide();

  $("#idarticulo").val("");

  $("#Nnombre").val("");

  $("#codigosunat").val("");

  $("#ccontable").val("");

  $("#precio2").val("0.00");

  $("#precio3").val("0.00");





  //Nuevos campos

  $("#cicbper").val("");

  $("#nticbperi").val("");

  $("#ctticbperi").val("");

  $("#mticbperu").val("0.00");

  //Nuevos campos



  $("#lote").val("");

  $("#marca").val("");

  $("#fechafabricacion").val("");

  $("#fechavencimiento").val("");

  $("#procedencia").val("");

  $("#fabricante").val("");



  $("#registrosanitario").val("");

  $("#fechaingalm").val("");

  $("#fechafinalma").val("");

  $("#proveedor").val("");

  $("#seriefaccompra").val("");

  $("#numerofaccompra").val("");

  $("#fechafacturacompra").val("");



  $("#preview").empty();



  $("#limitestock").val("0.00");

  //$("#tipoitem").val("productos");



  $("#equivalencia").val("");

  $("#factorc").val("1.0");

  $("#fconversion").val("");
  $(".convumventa").val("1");

  $("#descripcion").val("");

  document.getElementById("btnGuardar").innerHTML = "Agregar";
  //document.getElementsByClassName("stokservicio")[0].value = "0";


}

//mostrar comrpas en registro
document.getElementById("agregarCompra").addEventListener("change", function () {
  var mostrarCompra = document.getElementById("mostrarCompra");
  if (this.checked) {
    mostrarCompra.style.display = "block";
  } else {
    mostrarCompra.style.display = "none";
  }
});

document.getElementById("agregarOtrosCampos").addEventListener("change", function () {
  var mostrarCompra = document.getElementById("mostraOtroscampos");
  if (this.checked) {
    mostrarCompra.style.display = "block";
  } else {
    mostrarCompra.style.display = "none";
  }
});

function limpiaralmacen() {

  $("#nombrea").val("");

}




function mostrarcampos() {

  var x = document.getElementById("chk1").checked;
  var div = document.getElementById("masdatos");
  if (x) {

    div.style.visibility = "visible";
  } else {
    div.style.visibility = "hidden";
  }


}







//Función mostrar formulario

function mostrarform(flag) {

  limpiar();

  if (flag) {

    $("#listadoregistros").hide();
    $("#listadoregistrosservicios").hide();
    $("#formularioregistros").show();
    $("#btnGuardar").prop("disabled", false);
    $("#btnagregar").hide();
    $("#costo_compra").attr('readonly', false);
    $("#saldo_iniu").attr('readonly', false);
    $("#valor_iniu").attr('readonly', false);
    $("#saldo_finu").attr('readonly', false);
    $("#valor_finu").attr('readonly', false);
    $("#stock").attr('readonly', false);
    $("#comprast").attr('readonly', false);
    $("#ventast").attr('readonly', false);
    $("#preview").empty();



  }

  else {

    $("#listadoregistros").show();
    $("#listadoregistrosservicios").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();

  }

}



//Función cancelarform

function cancelarform() {
  limpiar();
  mostrarform(false);
}





//Función Listar

function listar() {
  var $idempresa = $("#idempresa").val();
  tabla = $('#tbllistado').dataTable(
    {
      "aProcessing": true,//Activamos el procesamiento del datatables
      "aServerSide": true,//Paginación y filtrado realizados por el servidor

      dom: 'Bfrtip',//Definimos los elementos del control de tabla


      buttons: [

      ],

      "ajax":
      {
        url: '../ajax/articulo.php?op=listar&idempresa=' + $idempresa,
        type: "get",
        dataType: "json",
        data: function(d) {
          d.filtro_almacen = $("#filtro_almacen").val();
          d.filtro_estado = $("#filtro_estado").val();
        },
        error: function (e) {
          console.log(e.responseText);

        }

      },

      "bDestroy": true,
      "iDisplayLength": 15,//Paginación
      "order": [[4, "desc"]]//Ordenar (columna,orden)
    }).DataTable();

}





function listarservicios() {
  var $idempresa = $("#idempresa").val();
  tabla = $('#tbllistadoservicios').dataTable(
    {
      "aProcessing": true,//Activamos el procesamiento del datatables
      "aServerSide": true,//Paginación y filtrado realizados por el servidor
      dom: 'Bfrtip',//Definimos los elementos del control de tabla
      buttons: [



      ],

      "ajax":
      {
        url: '../ajax/articulo.php?op=listarservicios&idempresa=' + $idempresa,
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);

        }

      },

      "bDestroy": true,
      "iDisplayLength": 15,//Paginación
      "order": [[4, "desc"]]//Ordenar (columna,orden)
    }).DataTable();

}





//Función para guardar o editar



function guardaryeditar(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardar").prop("disabled", true);
  var formData = new FormData($("#formulario")[0]);

  $.ajax({
    url: "../ajax/articulo.php?op=guardaryeditar",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (datos) {
      Swal.fire({
        icon: 'success',
        title: 'Guardado exitoso',
        showConfirmButton: false,
        timer: 1000,
        text: datos,
      }).then((result) => {
        mostrarform(false);
        tabla.ajax.reload();
        listar();
        limpiar();
      });
    },
    error: function () {
      Swal.fire({
        icon: 'error',
        title: 'Error al guardar',
        text: 'Ha ocurrido un error al guardar los datos',
      });
    }
  });
}



function mostrar(idarticulo) {

  $.post("../ajax/articulo.php?op=mostrar", { idarticulo: idarticulo }, function (data, status) {

    data = JSON.parse(data);

    mostrarform(true);

    $("#idarticulo").val(data.idarticulo);
    $("#idfamilia").val(data.idfamilia);
    $("#idalmacen").val(data.idalmacen);
    $("#unidad_medida").val(data.unidad_medida);
    $("#codigo_proveedor").val(data.codigo_proveedor);
    $("#codigo").val(data.codigo);
    $("#nombre").val(data.nombre);
    $("#unidad_medidad").val(data.unidad_medidad);
    $("#costo_compra").val(data.costo_compra);
    //$("#costo_compra").attr('readonly', true);
    $("#saldo_iniu").val(data.saldo_iniu);
    //$("#saldo_iniu").attr('readonly', true);
    $("#valor_iniu").val(data.valor_iniu);
    //$("#valor_iniu").attr('readonly', true);
    $("#saldo_finu").val(data.saldo_finu);
    //$("#saldo_finu").attr('readonly', true);
    $("#valor_finu").val(data.valor_finu);
    //$("#valor_finu").attr('readonly', true);
    $("#stock").val(data.stock);
    //$("#stock").attr('readonly', true);
    $("#comprast").val(data.comprast);
    //$("#comprast").attr('readonly', true);
    $("#ventast").val(data.ventast);
    //$("#ventast").attr('readonly', true);
    $("#portador").val(data.portador);
    $("#merma").val(data.merma);
    $("#valor_venta").val(data.precio_venta);
    $("#imagenmuestra").show();

    if (data.imagen == "") {
      $("#imagenmuestra").attr("src", "../files/articulos/simagen.png");
      $("#imagenactual").val(data.imagen);
      $("#imagen").val("");
    } else {
      $("#imagenmuestra").attr("src", "../files/articulos/" + data.imagen);
      $("#imagenactual").val(data.imagen);
      $("#imagen").val("");
    }



    $("#codigosunat").val(data.codigosunat);
    $("#ccontable").val(data.ccontable);
    $("#precio2").val(data.precio2);
    $("#precio3").val(data.precio3);

    $("#stockprint").val(data.stock);
    $("#codigoprint").val(data.codigo);
    $("#precioprint").val(data.precio_venta);

    //Nuevos campos

    $("#cicbper").val(data.cicbper);
    $("#nticbperi").val(data.nticbperi);
    $("#ctticbperi").val(data.ctticbperi);
    $("#mticbperu").val(data.mticbperu);
    //Nuevos campos

    $("#codigott").val(data.codigott);
    $("#desctt").val(data.desctt);
    $("#codigointtt").val(data.codigointtt);
    $("#nombrett").val(data.nombrett);


    //Nuevos campos

    $("#lote").val(data.lote);

    $("#marca").val(data.marca);

    $("#fechafabricacion").val(data.fechafabricacion);

    $("#fechavencimiento").val(data.fechavencimiento);

    $("#procedencia").val(data.procedencia);

    $("#fabricante").val(data.fabricante);

    $("#registrosanitario").val(data.registrosanitario);

    $("#fechaingalm").val(data.fechaingalm);

    $("#fechafinalma").val(data.fechafinalma);

    $("#proveedor").val(data.proveedor);

    $("#seriefaccompra").val(data.seriefaccompra);

    $("#numerofaccompra").val(data.numerofaccompra);

    $("#fechafacturacompra").val(data.fechafacturacompra);



    $("#limitestock").val(data.limitestock);

    $("#tipoitem").val(data.tipoitem);



    $("#umedidacompra").val(data.umedidacompra);





    $("#factorc").val(data.factorc);

    $("#descripcion").val(data.descrip);


    var stt = $("#stock").val();
    var fc = $("#factorc").val();

    var stfc = stt * fc;

    $("#fconversion").val(stfc);

    //Nuevos campos
    $('#modalAgregarProducto').modal('show');
    //$('#modalAgregarServicio').modal('show');
    document.getElementById("btnGuardar").innerHTML = "Actualizar";



    //generarbarcode();



  })

}



//Función para desactivar registros

function desactivar(idarticulo) {
  $.post("../ajax/articulo.php?op=desactivar", { idarticulo: idarticulo }, function (e) {
    console.log("Desactivar respuesta:", e);
    tabla.ajax.reload();
  });
}




//Función para activar registros

function activar(idarticulo) {
  Swal.fire({
    title: '¿Está Seguro de activar el Artículo?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, activar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/articulo.php?op=activar", { idarticulo: idarticulo }, function (e) {
        Swal.fire({
          title: 'Artículo activado',
          text: e,
          icon: 'success',
          showConfirmButton: false,
          timer: 1000
        }).then(() => {
          tabla.ajax.reload();
          listar();
        });
      });
    }
  });
}




//función para generar el código de barras

// function generarbarcode() {

//   codigo = $("#codigo").val();

//   // descrip=$("#nombre").val();

//   // unidadm=$("#unidad_medida").val();

//   // codigof=codigo.concat(descrip, unidadm);

//   JsBarcode("#barcode", codigo, {
//     format: "code128",
//   });
//   $("#print").show();

// }



//Función para imprimir el Código de barras

function imprimir() {

  $("#print").printArea();

}





function calcula_valor_ini() {

  costo_compra = $("#costo_compra").val();

  saldo_iniu = $("#saldo_iniu").val();



  resu = costo_compra * saldo_iniu;



  $("#valor_iniu").val(resu.toFixed(2));

  $("#saldo_finu").val(saldo_iniu);

}


$("#stock").change(function () {
  var stockValue = $(this).val();
  $("#saldo_iniu").val(stockValue);
  $("#saldo_finu").val(stockValue);
  $("#fconversion").val(stockValue);
});



function sfinalstock() {

  sf = $("#saldo_finu").val();

  $("#stock").val(sf);

}




// var valorInicial;
//  document.getElementById("igv").addEventListener("change", function(){
//    if(this.checked){
//      valorInicial = document.getElementById("valor_venta").value;
//      var valorVenta = parseFloat(valorInicial);
//      var valorIgv = (valorVenta * 18) / 100;
//      document.getElementById("valor_venta").value = valorIgv + valorVenta;
//    } else {
//      document.getElementById("valor_venta").value = valorInicial;
//    }
//  });


function mayus(e) {

  e.value = e.value.toUpperCase();

}








function guardaryeditarAlmacen(e) {

  e.preventDefault(); //No se activará la acción predeterminada del evento



  var formData = new FormData($("#formnewalmacen")[0]);



  $.ajax({

    url: "../ajax/familia.php?op=guardaryeditaralmacen",

    type: "POST",

    data: formData,

    contentType: false,

    processData: false,



    success: function (datos) {

      bootbox.alert(datos);

      tabla.ajax.reload();

      actalmacen();

    }

  });

  limpiaralmacen();

  $("#ModalNalmacen").modal('hide');

}















function actalmacen() {
  // Actualizar opciones del select almacén
  $idempresa = $("#idempresa").val();

  $.post("../ajax/articulo.php?op=selectAlmacen&idempresa=" + $idempresa, function (r) {
    $("#idalmacen").html(r);
  });
}








document.onkeypress = stopRKey;





function stopRKey(evt) {

  var evt = (evt) ? evt : ((event) ? event : null);

  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);

  if ((evt.keyCode == 13) && (node.type == "text")) { return false; }

}





function focusfamil() {

  document.getElementById('idfamilia').focus();

}



function tipoitem() {

  document.getElementById('tipoitem').focus();

}



function focuscodprov() {

  //document.getElementById('codigo_proveedor').focus();
  selectedValue = document.getElementById("tipoitem").value;
}



function focusnomb(e, field) {

  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('nombre').focus();

  }

}



function focusum(e, field) {

  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('unidad_medida').focus();

  }

}



function limitestockf(e, field) {

  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('limitestock').focus();

  }

}



function costoco() {



  //idun= $('#unidad_medida').val();

  //$.post("../ajax/articulo.php?op=mostrarequivalencia&iduni="+idun, function(data,status)

  //{

  // data=JSON.parse(data);

  //$('#factorc').val(data.equivalencia);

  //});





  document.getElementById('factorc').focus();

}



function umventa(e, field) {

  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('unidad_medida').focus();

  }

}





function cinicial() {

  document.getElementById('factorc').focus();

}





//Función para aceptar solo numeros con dos decimales

function focussaldoi(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('saldo_iniu').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;

}



function valori(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('valor_iniu').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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



function saldof(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('saldo_finu').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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





function valorf(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('valor_finu').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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



function st(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('stock').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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



function totalc(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which

  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById('valor_venta').focus();
    var stt = document.getElementById("stock").value;
    var fc = document.getElementById("factorc").value;
    var stfc = stt * fc;
    document.getElementById("fconversion").value = stfc;

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;


}




function totalv(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('ventast').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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



function porta(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('portador').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.val() === "") return true;

    var existePto = (/[.]/).test(field.val());

    if (existePto === false) {

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



function mer(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('merma').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;

}



function preciov(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('valor_venta').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;

}





function limitest(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('unidad_medida').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;

}





function codigoi(e, field) {

  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which



  if (e.keyCode === 13 && !e.shiftKey) {

    document.getElementById('codigo').focus();

  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {

    if (field.value === "") return true;

    var existePto = (/[.]/).test(field.value);

    if (existePto === false) {

      regexp = /.[0-9]{10}$/;

    }

    else {

      regexp = /.[0-9]{2}$/;

    }

    return !(regexp.test(field.value));

  }



  if (key == 46) {

    if (field.value === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.value);

  }

  return false;

}





$(".modal-wide").on("show.bs.modal", function () {

  var height = $(window).height() - 200;

  $(this).find(".modal-body").css("max-height", height);

});



function unidadvalor() {

  valor = $("#nombreu").val();

  $("#abre").val(valor);

}







function refrescartabla() {

  tabla.ajax.reload();
  //tablas.ajax.reload();

}







document.getElementById("imagen").onchange = function (e) {
  // Creamos el objeto de la clase FileReader
  let reader = new FileReader();
  // Leemos el archivo subido y se lo pasamos a nuestro fileReader
  reader.readAsDataURL(e.target.files[0]);
  // Le decimos que cuando este listo ejecute el código interno
  reader.onload = function () {
    let preview = document.getElementById('preview'),
      image = document.createElement('img');

    image.src = reader.result;
    image.width = "50";
    image.height = "50";
    preview.innerHTML = '';
    preview.append(image);
    toastr.success('Imagen cargada');
  };

}


function mostrarequivalencia() {



  idun = $('#unidad_medida').val();

  $.post("../ajax/articulo.php?op=mostrarequivalencia&iduni=" + idun, function (data, status) {

    data = JSON.parse(data);

    $('#equivalencia').val(data.equivalencia);

  });



}







function validarcodigo() {



  cod = $('#codigo').val();

  $.post("../ajax/articulo.php?op=validarcodigo&cdd=" + cod, function (data, status) {

    data = JSON.parse(data);



    if (data && data.codigo == cod) {

      alert("código Existe, debe cambiarlo");

      document.getElementById('codigo').focus();

    }





  });

}


function generarcodigonarti() {
  //alert("asdasdas");
  var caracteres1 = $("#nombre").val();
  var codale = "";
  codale = caracteres1.substring(-3, 3);
  var caracteres2 = "ABCDEFGHJKMNPQRTUVWXYZ012346789";
  codale2 = "";
  for (i = 0; i < 3; i++) {
    var autocodigo = "";
    codale2 += caracteres2.charAt(Math.floor(Math.random() * caracteres2.length));
  }
  $("#codigo").val(codale + codale2);

}



function generarCodigoAutomatico() {
  if ($('#generar-cod-correlativo').prop('checked') && $('#codigo').val() === '') {
    $.getJSON('../ajax/articulo.php?action=GenerarCodigo', function (data) {
      $('#codigo').val(data.codigo);
      setCodigoFieldReadonly();  // Asegura que el campo esté como solo lectura
    });
  }
}


$('#modalAgregarProducto').on('shown.bs.modal', function (e) {
  generarCodigoAutomatico();
});


if (localStorage.getItem("checkboxState") === "checked") {

  $('#generar-cod-correlativo').prop('checked', true);
}

$('label.toggle-switch').on('mousedown', function (e) {
  var checkbox = $('#generar-cod-correlativo');

  // Si el checkbox ya está marcado y estamos en modo demo
  if (modoDemo && checkbox.prop('checked')) {
    e.preventDefault();  // Prevenir la acción por defecto (desmarcar el checkbox)

    Swal.fire({
      icon: 'warning',
      title: 'Modo demo',
      text: 'No puedes desmarcar en modo demo',
    });
  }
});

$('#generar-cod-correlativo').change(function () {
  // Actualizamos el estado en el localStorage basándonos en el nuevo estado del checkbox.
  if ($(this).prop('checked')) {
    localStorage.setItem("checkboxState", "checked");
  } else {
    localStorage.removeItem("checkboxState");
  }
});



// Función para establecer el campo de código como solo lectura
function setCodigoFieldReadonly() {
  $('#codigo').attr('readonly', 'readonly');
}


// Esperar a que el DOM esté completamente cargado antes de inicializar
$(document).ready(function() {
  init();
});
