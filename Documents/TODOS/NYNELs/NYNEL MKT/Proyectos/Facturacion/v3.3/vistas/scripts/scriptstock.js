var tabla;
var modoDemo = false;
//Función que se ejecuta al inicio
function init() {
    //mostrarform(false);
    listar();
    $("#formulario").on("submit", function (e) {
        guardaryeditar(e);
    })
}

//Función limpiar
function limpiar() {
    $("#nombrea").val("");
    $("#direccion").val("");
    $("#idalmacen").val("");
    document.getElementById("btnGuardar").innerHTML = "Agregar";
}

//Función cancelarform
function cancelarform() {
    limpiar();
    //mostrarform(false);
}

//Función Listar
function listar() {
    tabla = $('#tbllistado').dataTable({
        "rowCallback": function (row, data) {
            var stockValue = $(data[8]).attr('value');  // Extrae el valor del atributo 'value'

            if (parseFloat(stockValue) <= 5) {
                // Si el stock es menor o igual a 5
                $(row).css({
                    'background-color': 'rgba(255, 0, 0, 0.5)',  // Color rojo medio transparente para toda la fila
                    'font-weight': 'bold'  // Texto más negrito
                });

                // Cambiar el estilo del input en específico
                $(row).find('input[data-field="stock"]').css({
                    'background-color': 'red',  // Color rojo para el input
                    'color': 'white'  // Texto blanco para el input
                });
            } else {
                // Si el stock es mayor a 5
                $(row).css({
                    'background-color': '',  // Restaurar el color de fondo
                    'font-weight': ''  // Restaurar el estilo del texto
                });

                // Restaurar el estilo del input
                $(row).find('input[data-field="stock"]').css({
                    'background-color': '',  // Restaurar el color de fondo del input
                    'color': ''  // Restaurar el color del texto del input
                });
            }
        },
        "aProcessing": true, //Activamos el procesamiento del datatables
        "aServerSide": true, //Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip', //Definimos los elementos del control de tabla
        buttons: [],
        "ajax": {
            url: '../ajax/ajaxstock.php?op=listarStockProductos&idalmacen=' + $("#seleccionarAlmacenes").val(),
            type: "get",
            dataType: "json",
            error: function (e) {
                console.log(e.responseText);
            }
        },
        "columnDefs": [
            {
                "targets": [0],
                "visible": false // Esto oculta la primera columna
            },
            {
                "targets": [1],
                "visible": false // Esto oculta la segunda
            },
            {
                "className": "stockColumn",
                "targets": [5]
            } // Aplica la clase stockColumn a la 5ª columna (0-indexado)
        ],
        "bDestroy": true,
        "iDisplayLength": 15, //Paginación
        "order": [
            [0, "desc"]
        ] //Ordenar (columna,orden)
    }).DataTable();
}


$(document).on('keyup', '.editable', function (e) {
    if (e.keyCode === 13) { // 13 es el código de tecla para Enter
        guardarYEditar(e);
    }
});


function guardarYEditar(e) {
    e.preventDefault();
    if (modoDemo) {
        Swal.fire({
            icon: 'warning',
            title: 'Modo demo',
            text: 'Módulo reservado, solicita tu demostración',
        });
        return;
    }
    var idarticulo = $(e.target).data("idarticulo");
    var field = $(e.target).data("field");
    var value = $(e.target).val();

    var dataToSend = {
        idarticulo: idarticulo
    };
    dataToSend[field] = value;

    $.ajax({
        url: "../ajax/ajaxstock.php?op=ActualizarStockProductos",
        type: "POST",
        data: dataToSend,
        success: function (datos) {
            Swal.fire({
                icon: 'success',
                title: 'Actualizado exitoso',
                text: datos,
                showConfirmButton: false,
                timer: 1500
            });
            tabla.ajax.reload();
        }
    });
}


$(document).ready(function () {
    $.ajax({
        url: '../ajax/ajaxstock.php?action=listarAlmacen',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.aaData) {
                fillSelectWithData(data.aaData);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error AJAX completo:", {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            console.error("URL llamada:", '../ajax/ajaxstock.php?action=listarAlmacen');
        }
    });
});



function fillSelectWithData(data) {
    var $select = $(".js-example-placeholder-single.js-states");
    $select.empty();
    $select.append('<option value="">Seleccionar Almacen</option>');

    data.forEach(function (almacen) {
        $select.append('<option value="' + almacen.idalmacen + '">' + almacen.nombre + '</option>');
    });

    $select.select2({
        placeholder: "Selecciona almacen",
        allowClear: true,
        maximumResultsForSearch: 3,
        minimumResultsForSearch: 4
    });

    $select.val("1").trigger('change'); // Esto selecciona por defecto el idalmacen 1

    // Llamar a listar aquí
    listar();
}

$(".js-example-placeholder-single.js-states").on("change", function (e) {
    var idalmacen = $(this).val();
    tabla.ajax.url('../ajax/ajaxstock.php?op=listarStockProductos&idalmacen=' + idalmacen).load();

});



/* single select with placeholder *///escogido
$(".js-example-placeholder-single").select2({
    placeholder: "Selecciona almacen",
    allowClear: true,
    // dir: "ltr"
});




function mayus(e) {
    e.value = e.value.toUpperCase();
}

init();
