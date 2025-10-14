///URL CONSUMO GLOBAL
var baseURL = window.location.protocol + '//' + window.location.host;

// Verificar si pathname contiene '/vistas/' y eliminarlo.
var path = window.location.pathname;
if (path.includes("/vistas/")) {
  path = path.replace("/vistas/", "/");
}

// Asegurarnos de que el path termine en "/ajax/"
if (!path.endsWith("/ajax/")) {
  var lastSlashIndex = path.lastIndexOf("/");
  path = path.substring(0, lastSlashIndex) + "/ajax/";
}

// Construir urlconsumo /consumir solo urlconsumo + "archivo.php?action="
var urlconsumo = new URL(path, baseURL);


//INICIALIZAR TODAS LAS FUNCIONES CORRESPONDIENTES

/* ---------------------------------------------------------------- */
//                          VARIABLES GLOBALES    

$idempresa = $("#idempresa").val();
$iva = $("#iva").val();

// Recupera el contenido HTML del Session Storage
var contenidoRecuperado = sessionStorage.getItem('miContenidoHTML');

if (contenidoRecuperado) {

  // Restaura el contenido en un elemento HTML
  $('.items-order').html(contenidoRecuperado);


  var id = document.getElementsByName("idarticulo[]");
  var cant = document.getElementsByName("cantidad_item_12[]");
  var cantiRe = document.getElementsByName("cantidadreal[]");
  for (var i = 0; i < id.length; i++) {
    var cant2 = cant[i];
    var cantiRe2 = cantiRe[i];
    cant2.value = cantiRe2.value;
    //cantiRe[i].value=cantidadreal;
  }

  modificarSubtotales();
}


function almacenarItems() {
  // Obtén el contenido HTML del elemento
  var htmlContent = $('.items-order').html();

  // Almacena el contenido HTML en el Session Storage
  sessionStorage.setItem('miContenidoHTML', htmlContent);
}


/* ---------------------------------------------------------------- */
//                      LISTAR CATEGORIAS

function listarCategorias() {

  $.ajax({
    url: urlconsumo + 'pos.php?action=listarCategorias',
    type: "get",
    dataType: "json",
    success: function (data) {

      const categoriaContainer = document.getElementById('category-content');


      data.ListaCategorias.forEach(categoria => {

        var card = document.createElement('div');
        card.classList.add('swiper-slide');

        card.innerHTML = `
          <div class="rounded-pill slider-item categoryclic" data-idfamilia="${categoria.idfamilia}">
              <img
                  src="https://htmlcolorcodes.com/assets/images/colors/sky-blue-color-solid-background-1920x1080.png"
                  alt="dd" height="30px" width="30px" class="rounded-circle me-2">
              <span class="fw-600 f-12 category">${categoria.familia}</span>
          </div>
        `;


        categoriaContainer.appendChild(card);

        // Add a click event listener to the category
        card.querySelector('.categoryclic').addEventListener('click', listarPorCategoria);

      });

    },
    error: function (error) {
      console.error(error);
    }
  });

}

listarCategorias();



/* ---------------------------------------------------------------- */
//                   LISTAR PRODUCTOS (BUSQUEDA)

var url_send;
function listarProductos(busqueda) {

  $('#loader_product').show();

  if (busqueda != '') {
    url_send = urlconsumo + 'pos.php?action=listarProducto&busqueda=' + busqueda;
  } else {
    url_send = urlconsumo + 'pos.php?action=listarProducto';
  }

  $.ajax({
    url: url_send,
    type: 'get',
    dataType: 'json',
    success: function (data) {

      listarCardProductos(data);

      $('#loader_product').hide();

      // var productContainer = $('#product-container');
      // productContainer.empty(); // Limpiar productos existentes

      // if (data.ListaProductos && data.ListaProductos.length > 0) {
      //   data.ListaProductos.forEach(product => {

      //     let productImage = product.imagen;

      //     if (!productImage || productImage === '../files/articulos/' || productImage === 'https://wfacx.com/sistema/files/articulos/') {

      //       productImage = 'https://www.phswarnerhoward.co.uk/assets/images/no_img_avaliable.jpg';

      //     }

      //     var productCard = document.createElement('div');
      //     productCard.classList.add('col-6', 'col-sm-6', 'col-md-3', 'col-lg-3', 'mb-3');

      //     var productCardAlert = document.createElement('div');

      //     var productStock = parseFloat((product.stock).replace(',', ''));

      //     if (productStock < 5 && productStock > 0) {
      //       productCardAlert.classList.add('card', 'card-warning', 'product-card', 'cursor-pointer');
      //     } else if (productStock == 0) {
      //       productCardAlert.classList.add('card', 'card-danger', 'product-card', 'cursor-pointer');
      //     } else {
      //       productCardAlert.classList.add('card', 'product-card', 'cursor-pointer');
      //     }

      //     productCardAlert.innerHTML = `
      //       <input id="p_stock" class="text-muted mt-auto ms-2" readonly value="Stock - ${productStock}" style="border-radius: 10px;width: 85%;height: 15px;border: none;font-size: 14px;pointer-events: none;user-select: none;">
      //       <input id="p_costo_compra" class="text-muted mt-auto ms-2" readonly value="Compra - S/ ${product.costo_compra}" style="border-radius: 10px;width: 85%;height: 15px;border: none;font-size: 14px;pointer-events: none;user-select: none;">

      //       <div class="text-center mt-1" style="height: 120px;">
      //         <img src="${productImage}" alt="${product.nombre}" height="100%" class=" mb-2">
      //       </div>
      //       <div class="card-body text-center p-0">
      //         <label class="fw-bolder fs-12" id="p_nombre">${product.nombre}</label>
      //         <p class="fs-6 fw-600" >S/ <span id="p_precio">${product.precio}</span></p>
      //       </div>
      //       <input type="hidden" id="p_idarticulo" value="${product.idarticulo}">
      //       <input type="hidden" id="p_codigoprod" value="${product.codigo}">
      //       <input type="hidden" id="p_codigoprov" value="${product.codigo_proveedor}">
      //       <input type="hidden" id="p_unimed" value="${product.abre}">
      //       <input type="hidden" id="p_precio_unitario" value="${product.precio_unitario}">
      //       <input type="hidden" id="p_cicbper" value="${product.cicbper}">
      //       <input type="hidden" id="p_mticbperu" value="${product.mticbperu}">
      //       <input type="hidden" id="p_factorc" value="${product.factorc}">
      //       <input type="hidden" id="p_descrip" value="${product.descrip}">
      //       <input type="hidden" id="p_tipoitem" value="${product.tipoitem}">
      //     `;

      //     productCard.append(productCardAlert);

      //     productContainer.append(productCard);
      //   });
      // } else {
      //   productContainer.html('<p>No hay productos disponibles para esta búsqueda.</p>');
      // }
    },
    error: function (error) {
      console.error(error);

      $('#loader_product').hide();

    }
  });
}

var busqueda = '';
listarProductos(busqueda);


/* ---------------------------------------------------------------- */
//           ACTUALIZAR PRECIOS DE PRODUCTOS CARD (BUSQUEDA)

$('#s_tipo_precio').change(function () {
  busqueda = '';

  listarProductos(busqueda);

})


/* ---------------------------------------------------------------- */
//                  LISTAR PRODUCTOS CAMPO BUSQUEDA

let searchTimeout;

$('#search_product').on('input', function () {
  const searchTerm = $(this).val();

  // Cancelar la búsqueda anterior
  clearTimeout(searchTimeout);

  // Retraso 1s
  searchTimeout = setTimeout(function () {
    listarProductos(searchTerm);
  }, 1000);
});

/* ---------------------------------------------------------------- */
//                   LISTAR PRODUCTOS POR CATEGORIA

function listarPorCategoria(event) {
  event.preventDefault();

  $('#loader_product').show();


  // Get the idfamilia from the clicked category
  var idfamilia = event.target.getAttribute('data-idfamilia');

  // Remove the 'select' class from all category elements
  var allCategories = document.querySelectorAll('.categoryclic');
  allCategories.forEach(category => {
    category.classList.remove('select');
  });

  // Add the 'select' class to the clicked category
  event.target.classList.add('select');

  busqueda = $('#search_product').val();

  if (busqueda != '') {
    url_send = urlconsumo + "pos.php?action=listarProducto&idfamilia=" + idfamilia + "&busqueda=" + busqueda;
  } else {
    url_send = urlconsumo + "pos.php?action=listarProducto&idfamilia=" + idfamilia;
  }

  if (idfamilia) {
    // Fetch products for the selected category
    $.ajax({
      url: url_send,
      type: 'get',
      dataType: 'json',
      success: function (data) {

        listarCardProductos(data);

        $('#loader_product').hide();

      },
      error: function (error) {
        console.error(error);

        $('#loader_product').hide();

      }
    });

  } else {

    $('#product-container').html('<p>Seleccione una categoría.</p>');

    $('#loader_product').hide();

  }


}

/* ---------------------------------------------------------------- */
//                   CARD DE PRODUCTOS

function listarCardProductos(data) {

  var productContainer = $('#product-container');
  productContainer.empty(); // Limpiar productos existentes

  if (data.ListaProductos && data.ListaProductos.length > 0) {
    data.ListaProductos.forEach(product => {

      let productImage = product.imagen;

      // Verifica si la imagen termina con '/files/articulos/'
      if (!productImage || productImage.endsWith('/files/articulos/')) {
        productImage = '../../assets/images/no_img_avaliable.jpg';
      }



      var productCard = document.createElement('div');
      productCard.classList.add('col-6', 'col-sm-6', 'col-md-3', 'col-lg-3', 'mb-3');

      var productCardAlert = document.createElement('div');

      var productStock = parseFloat((product.stock).replace(',', ''));

      if (productStock < 5 && productStock > 0) {
        productCardAlert.classList.add('card', 'card-warning', 'product-card', 'cursor-pointer');
      } else if (productStock == 0) {
        productCardAlert.classList.add('card', 'card-danger', 'product-card', 'cursor-pointer');
      } else {
        productCardAlert.classList.add('card', 'product-card', 'cursor-pointer');
      }

      var card = `
        <span class="d-flex align-items-center text-muted mt-auto ms-2" style="font-size: 14px;"> Stock - <input id="p_stock" class="text-muted" readonly value="${productStock}" style="border-radius: 10px;width: 50%;height: 15px;border: none;font-size: 14px;pointer-events: none;user-select: none;"></span>
        <span class="d-flex align-items-center text-muted mt-auto ms-2" style="font-size: 14px;"> Compra - S/ <input id="p_costo_compra" class="text-muted" readonly value="${product.costo_compra}" style="border-radius: 10px;width: 48%;height: 15px;border: none;font-size: 14px;pointer-events: none;user-select: none;"></span>

        <div class="text-center mt-1" style="height: 120px;">
          <img src="${productImage}" alt="${product.nombre}" height="100%" class=" mb-2">
        </div>
        <div class="card-body text-center p-0">
          <label class="fw-bolder fs-12" id="p_nombre">${product.nombre}</label> `;

      var s_tipo_precio = $('#s_tipo_precio').val();

      if (s_tipo_precio == 0) {

        card += `
          <p class="fs-6 fw-600" >S/ <span id="p_precio">${product.precio}</span></p>`;

      } else if (s_tipo_precio == 1) {

        card += `
          <p class="fs-6 fw-600" >S/ <span id="p_precio">${product.precio2}</span></p>`;

      } else if (s_tipo_precio == 2) {

        card += `
          <p class="fs-6 fw-600" >S/ <span id="p_precio">${product.precio3}</span></p>`;

      }

      card += `
        </div>
        <input type="hidden" id="p_idarticulo" value="${product.idarticulo}">
        <input type="hidden" id="p_codigoprod" value="${product.codigo}">
        <input type="hidden" id="p_codigoprov" value="${product.codigo_proveedor}">
        <input type="hidden" id="p_unimed" value="${product.abre}">
        <input type="hidden" id="p_precio_unitario" value="${product.precio_unitario}">
        <input type="hidden" id="p_cicbper" value="${product.cicbper}">
        <input type="hidden" id="p_mticbperu" value="${product.mticbperu}">
        <input type="hidden" id="p_factorc" value="${product.factorc}">
        <input type="hidden" id="p_descrip" value="${product.descrip}">
        <input type="hidden" id="p_tipoitem" value="${product.tipoitem}">
      `;

      productCardAlert.innerHTML = card;

      productCard.append(productCardAlert);

      productContainer.append(productCard);
    });
  } else {
    productContainer.html('<p>No hay productos disponibles para esta búsqueda.</p>');
  }

}

/* ---------------------------------------------------------------- */
//                    LISTAR TODOS LOS PRODUCTOS

// Enlace "Ver Todos"
$('#ver-todos-link').on('click', function (e) {
  e.preventDefault();

  listarTodosProductos();
});

function listarTodosProductos() {
  var allCategories = document.querySelectorAll('.categoryclic');

  allCategories.forEach(category => {
    category.classList.remove('select');
  });

  listarProductos('');
}

/* ---------------------------------------------------------------- */
//                    LIMPIAR FILTRO BUSQUEDA

$('#btn_deletefilter').on('click', function (e) {
  e.preventDefault();

  $('#search_product').val('');
})

/* ---------------------------------------------------------------- */
//                    CHECKBOX FILTRO CODIGO BARRA

$('#search_codigobarra').hide();


// Verificar el localStorage al cargar la página
if (localStorage.getItem('codigobarraEnabled') === 'true') {
  $('#active_codigobarra').prop('checked', true);
  $('#search_codigobarra').show().focus();
}


$('#active_codigobarra').change(function () {

  if (this.checked) {

    $('#search_codigobarra').show().focus();

  } else {

    $('#search_codigobarra').hide().val('');
  }

  localStorage.setItem('codigobarraEnabled', this.checked);
});


/* ---------------------------------------------------------------- */
//                   AGREGAR PRODUCTO AL PEDIDO

var sub_total = 0;
var igv = 0;
var total = 0;

var numeroOrden = 1;

$(document).on('click', '.product-card', function (e) {
  e.preventDefault();
  // console.log('clic');

  sub_total = parseFloat($('#subtotal_boleta').val());
  // console.log('subt',  sub_total);

  var productImage = $(this).find('img').attr('src');
  var productName = $(this).find('#p_nombre').text();
  var productPrice = parseFloat($(this).find('#p_precio').text());

  var productStock = $(this).find('#p_stock').val();
  var productId = $(this).find('#p_idarticulo').val();

  var productCod = $(this).find('#p_codigoprod').val();
  var productCodProv = $(this).find('#p_codigoprov').val();

  var productUM = $(this).find('#p_unimed').val();
  var productFactC = $(this).find('#p_factorc').val();


  agregarProductPedido(
    productImage,
    productName,
    productPrice,
    productStock,
    productId,
    productCod,
    productCodProv,
    productUM,
    productFactC
  );


});

/* ---------------------------------------------------------------- */
//              FUNCION AGREGAR PROFUCTO POR CODIGO BARRA

function eventoProductoxCodigo(e) {

  if (e.keyCode === 13 && !e.shiftKey) {
    e.preventDefault();
    var busqueda = $('#search_codigobarra').val();

    $.ajax({
      url: '../ajax/boleta.php?op=listarArticulosboletaxcodigo&codigob=' + busqueda + '&idempresa=' +
        $idempresa,
      type: 'get',
      dataType: 'json',
      success: function (data) {

        // var data = data.ListaProductos[0];
        console.log('data', data);
        sub_total = parseFloat($('#subtotal_boleta').val());

        if (data != null) {

          var productImage = '../files/articulos/' + data.imagen;

          if (!productImage || productImage === '../files/articulos/') {

            productImage = '../../assets/images/no_img_avaliable.jpg';

          }

          var productName = data.nombre;
          var productPrice = parseFloat(data.precio_venta);

          var productStock = data.stock;
          var productId = data.idarticulo;

          var productCod = data.codigo;
          var productCodProv = data.codigo_proveedor;

          var productUM = data.abre;

          var productFactC = data.factorc;


          agregarProductPedido(
            productImage,
            productName,
            productPrice,
            productStock,
            productId,
            productCod,
            productCodProv,
            productUM,
            productFactC
          );


        } else {

          swal.fire({
            title: "Error",
            text: 'Este producto no exite.',
            icon: "error",
            timer: 2000,
            showConfirmButton: false
          });
        }

        $('#search_codigobarra').val('');

      },
      error: function (error) {
        console.error(error);


      }
    });
  }
}


function agregarProductPedido(
  productImage,
  productName,
  productPrice,
  productStock,
  productId,
  productCod,
  productCodProv,
  productUM,
  productFactC) {

  var tipocomprobante = $('#d_tipocomprobante').val();

  if (tipocomprobante == 0) {

    var productValUni = (productPrice / ($iva / 100 + 1)).toFixed(5);

    var productSubTotal = (productPrice / ($iva / 100 + 1)).toFixed(2);

    var igv = productPrice - productPrice / ($iva / 100 + 1);

    var productIgv = (igv).toFixed(4);

    var total = (productPrice).toFixed(2);

    var pvu = productPrice / ($iva / 100 + 1);

    var productPvu = (pvu).toFixed(5);
    var productVvu = (pvu).toFixed(5);

    var productIgvBD2 = ((productPvu * $iva) / 100).toFixed(4);

    var mticbperuCalculado = (0.0).toFixed(2);

    var productIgvBD = (igv).toFixed(2);
    var productPvt = '';

  } else if (tipocomprobante == 1) {

    var pvu = productPrice / ($iva / 100 + 1);

    var productPvu = redondeo(pvu, 5);
    var productVvu = (0).toFixed(5);
    var productPvt = (pvu).toFixed(5);

    var productValUni = (pvu).toFixed(5);

    var subtotal = productValUni - (productValUni * 0) / 100;

    var igv = subtotal * ($iva / 100);

    var inpIitem = pvu * ($iva / 100);

    var mticbperuCalculado = 0.0;


    sumtotal = subtotal + igv;
    var total = redondeo(sumtotal, 2);


    var productSubTotal = redondeo(subtotal, 2);

    var productIgv = redondeo(igv, 2);

    var productIgvBD = redondeo(inpIitem, 2);

    var productIgvBD2 = redondeo(igv, 2);


  } else if (tipocomprobante == 2) {

    var productValUni = (productPrice).toFixed(5);

    var productSubTotal = (productPrice).toFixed(2);

    var productIgv = (0).toFixed(4);

    var total = (productPrice).toFixed(2);

    var productPvu = (0).toFixed(5);
    var productVvu = (productPrice).toFixed(5);

    var productIgvBD2 = (productPrice).toFixed(4);

    var mticbperuCalculado = (0.0).toFixed(2);

    var productIgvBD = (productPrice).toFixed(2);
    var productPvt = '';

  } else if (tipocomprobante == 3) {


    var pvu = productPrice / ($iva / 100 + 1);

    var productPvu = redondeo(pvu, 5);
    var productVvu = (0).toFixed(5);
    var productPvt = (pvu).toFixed(5);

    var productValUni = (pvu).toFixed(5);

    var subtotal = productValUni - (productValUni * 0) / 100;

    var igv = subtotal * ($iva / 100);

    var inpIitem = pvu * ($iva / 100);

    var mticbperuCalculado = 0.0;


    sumtotal = subtotal + igv;
    var total = redondeo(sumtotal, 2);


    var productSubTotal = redondeo(subtotal, 2);

    var productIgv = redondeo(igv, 2);

    var productIgvBD = redondeo(inpIitem, 2);

    var productIgvBD2 = redondeo(igv, 2);

  } else if (tipocomprobante == 4 || tipocomprobante == 5) {
    // Nota de Crédito (4) o Nota de Débito (5)
    // Usar la misma lógica que tipo 1 (factura con IGV)
    var pvu = productPrice / ($iva / 100 + 1);

    var productPvu = redondeo(pvu, 5);
    var productVvu = (0).toFixed(5);
    var productPvt = (pvu).toFixed(5);

    var productValUni = (pvu).toFixed(5);

    var subtotal = productValUni - (productValUni * 0) / 100;

    var igv = subtotal * ($iva / 100);

    var inpIitem = pvu * ($iva / 100);

    var mticbperuCalculado = 0.0;

    sumtotal = subtotal + igv;
    var total = redondeo(sumtotal, 2);

    var productSubTotal = redondeo(subtotal, 2);

    var productIgv = redondeo(igv, 2);

    var productIgvBD = redondeo(inpIitem, 2);

    var productIgvBD2 = redondeo(igv, 2);
  }



  if (productStock == 0) {

    swal.fire({
      title: "Error",
      text: 'Este producto no se puede agregar porque no tiene stock.',
      icon: "error",
      timer: 2000,
      showConfirmButton: false
    });

    return;
  }

  // Verificar si el producto ya existe en la lista de pedidos
  var existingItem = $('.items-order .card[data-product-code="' + productId + '"]');


  if (existingItem.length > 0) {
    // aumentar la cantidad en 1
    var inputBox = existingItem.closest('.card').find('.input-box');
    var currentQuantity = parseInt(inputBox.val());

    // console.log( 'Final stock', productStock - (currentQuantity));

    var finalStock = productStock - (currentQuantity);

    if (finalStock == 0) {
      swal.fire({
        title: "Error",
        text: 'Este producto no se puede agregar porque se alcanzó el limite de stock.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      return;
    }

    if (!isNaN(currentQuantity)) {
      var cantidad = currentQuantity + 1;

      inputBox.val(cantidad);


      var inputcant = existingItem.closest('.card').find('input[name="cantidadreal[]"]');
      inputcant.val(cantidad);


      // var tipocomprobante = $('#d_tipocomprobante').val();

      // if (tipocomprobante == 0) {

      //   calcularBoleta(existingItem, cantidad);

      // } else if (tipocomprobante == 1) {

      //   calcularFactura(existingItem, cantidad);

      // } else if (tipocomprobante == 2) {

      //   calcularNotaPedido(existingItem, cantidad);

      // }
      modificarSubtotales();
    }

  } else {

    if ($('.items-order .card').length < 1) {
      numeroOrden = 1;
    }
    // Producto no existe, crear uno nuevo
    var newItem = `
      <div class="card mb-3 p-2" data-product-price data-product-code="${productId}" style="background: #F2F7FB !important; border-radius: .8rem !important; box-shadow: none;">
        <div class="d-flex align-items-center">
          <img src="${productImage}" alt="${productName}" height="40px" width="40px" class="d-none d-sm-inline d-md-inline d-lg-none d-xl-inline me-2">
          <div class="w-100">
            <div class="d-flex justify-content-between align-items-center">
              <label class="fw-700 fs-7" id="ped_name">${productName}</label>
              <div class="quantity rounded-pill d-flex justify-content-center align-items-center">
                <button class="btn btn-sm btn-warning rounded-circle minus" id="ped_disminuir" aria-label="Decrease">&minus;</button>
                <input type="number" class="input-box" name="cantidad_item_12[]" id="ped_cantidad" value="1" min="1" max="${productStock}">
                <button class="btn btn-sm btn-warning rounded-circle plus" id="ped_aumentar" aria-label="Increase">&plus;</button>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-baseline">

              <span>S/  <input type="number" class="border-0" name="precio_unitario[]" id="precio_unitario[]" value="${productPrice}" onBlur="modificarSubtotales(1)" style="background: transparent;"></span>

              <a href="#" class="text-danger text-decoration-none remove-item" style="font-size: 12px;">Eliminar</a>
            </div>
          </div>
        </div>

        <input type="hidden" name="numero_orden_item_29[]" id="numero_orden_item_29[]" value="${numeroOrden}"  >
        <select name="afectacionigv[]" class="" style="display:none;"> <option value="10">10-GOO</option><option value="20">20-EOO</option><option value="30">30-FRE</option></select>
        <input type="hidden" name="idarticulo[]" value="${productId}">
        <input type="hidden" name="codigotributo[]" value="1000">
        <input type="hidden" name="afectacionigv[]" value="10">
        
        <span name="SumDCTO" id="SumDCTO" style="display:none">0</span>
        <input type="hidden" name="descuento[]" id="descuento[]" >
        <input type="hidden" name="sumadcto[]" id="sumadcto[]" value="0" required="true">
        <input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]" value="${productCodProv}">
        <input type="hidden" name="codigo[]" id="codigo[]" value="${productCod}">
        <input type="hidden" name="unidad_medida[]" id="unidad_medida[]" value="${productUM}">
        
        <input type="hidden" name="valor_unitario[]" id="valor_unitario[]" value="${productValUni}" >

        <input type="hidden" name="subtotal" id="subtotal" value="${productSubTotal}">
        <input type="hidden" name="subtotalBD[]" id="subtotalBD[${productId}]" value="${productSubTotal}">
        <span name="igvG" id="igvG" style="display:none;">${productIgv}</span>
        <input type="hidden" name="igvBD[]" id="igvBD[${productId}]" value="${productIgvBD}">
        <input type="hidden" name="igvBD2[]" id="igvBD2[${productId}]" value="${productIgvBD2}">
        <span name="total" id="total" style="display:none;" >${total}</span>
        <input type="hidden" name="vvu[]" id="vvu[${productId}]" value="${productVvu}">
        <input type="hidden" name="pvu_[]" id="pvu_[]" value="${productPvu}">
        <input type="hidden" name="cicbper[]" id="cicbper[${productId}]" value="">
        <input type="hidden" name="mticbperu[]" id="mticbperu[${productId}]" value="0.00">
        <input type="hidden" name="factorc[]" id="factorc[]" value="${productFactC}" required="true">
        <input type="hidden" name="cantidadreal[]" id="cantidadreal[]" value="1" required="true">

        <input type="hidden" id="igvprod" value="${productIgvBD}">

        <span name="mticbperuCalculado" id="mticbperuCalculado" style="display:none;">${mticbperuCalculado}</span>

        <input type="hidden" name="pvt[]" id="pvt[]" value="${productPvt}">
      </div>
    `;

    $('.items-order').append(newItem);

    // Incrementar el número de orden
    numeroOrden++;

    tributocodnon();
  }


  updateTotals();

  almacenarItems();

}

/* ---------------------------------------------------------------- */
//                  INICIALIZAR BOTONES CANTIDAD


$(document).on('click', '.quantity .minus', function (e) {
  e.preventDefault();
  var input = $(this);
  var inputBox = $(this).siblings('.input-box');
  var inputcant = $(this).closest('.card').find('input[name="cantidadreal[]"]');

  decreaseValue(input, inputBox, inputcant);


});

$(document).on('click', '.quantity .plus', function (e) {
  e.preventDefault();

  var input = $(this);
  var inputBox = $(this).siblings('.input-box');
  var inputcant = $(this).closest('.card').find('input[name="cantidadreal[]"]');

  increaseValue(input, inputBox, inputcant);
  // updateTotals();
});

$(document).on('input', '.quantity .input-box', function (e) {
  e.preventDefault();
  var inputcant = $(this).closest('.card').find('input[name="cantidadreal[]"]');
  handleQuantityChange($(this), $(this), inputcant);
});


/* ---------------------------------------------------------------- */
//                   FUNCION DISMINUIR CANTIDAD

function decreaseValue(input, inputBox, inputcant) {
  var value = parseInt(inputBox.val());
  value = isNaN(value) ? 1 : Math.max(value - 1, 1);
  inputBox.val(value);
  inputcant.val(value);

  handleQuantityChange(input, inputBox, inputcant);

  // updateTotals();

}

/* ---------------------------------------------------------------- */
//                   FUNCION AUMENTAR CANTIDAD

function increaseValue(input, inputBox, inputcant) {
  var value = parseInt(inputBox.val());
  if (isNaN(value)) {
    value = 1;
  } else {
    var max = parseInt(inputBox.attr('max'));
    if (!isNaN(max)) {
      value = Math.min(value + 1, max);
    } else {
      value += 1;
    }
  }
  inputBox.val(value);
  inputcant.val(value);
  handleQuantityChange(input, inputBox, inputcant);
  // calcularBoleta( value, inputprecio, inputsubtotalBD, inputsubtotal, inputtotal, inputigvG, inputigvBD);
}

/* ---------------------------------------------------------------- */
//                FUNCION CAMBIO DE CANTIDAD AL INPUT

function handleQuantityChange(input, inputBox, inputcant) {
  var value = parseInt(inputBox.val());
  value = isNaN(value) ? 1 : value;


  // Realiza cualquier lógica adicional aquí, si es necesario.

  // var tipocomprobante = $('#d_tipocomprobante').val();

  // console.log('tipocomprobante', tipocomprobante);

  // if (tipocomprobante == 0) {
  //   calcularBoleta(input, value);

  // } else if (tipocomprobante == 1) {

  //   calcularFactura(input, value);

  // } else if (tipocomprobante == 2) {
  //   calcularNotaPedido(input, value)

  // }


  // Obtén el stock máximo permitido desde el atributo "max" del input
  var maxStock = parseInt(inputBox.attr('max'));

  if (value > maxStock) {
    // Muestra una alerta con SweetAlert
    swal.fire({
      title: "Error",
      text: "El valor de stock máximo permitido es de " + maxStock + ".",
      icon: "error",
      timer: 2000,
      showConfirmButton: false
    });

    // Establece la cantidad máxima permitida como el valor actual del input
    inputBox.val(maxStock);
    inputcant.val(value);

  }
  modificarSubtotales();

  almacenarItems();

  // updateTotals();

}

/* ---------------------------------------------------------------- */
//              CALCULAR TOTALES AL AGREGAR PRODUCTOS

// function calcularBoleta(input, cantidad) {

//   var precio = input.closest('.card').find('input[name="precio_unitario[]"]').val();
//   var inputsubtotalBD = input.closest('.card').find('input[name="subtotalBD[]"]');
//   var inputsubtotal = input.closest('.card').find('input[name="subtotal"]');
//   var inputtotal = input.closest('.card').find('span[name="total"]');
//   var inputigvG = input.closest('.card').find('span[name="igvG"]');
//   var inputigvBD = input.closest('.card').find('input[name="igvBD[]"]');

//   // inputcant.val(cantidad);
//   console.log('cantidad', cantidad);

//   inputsubtotalBD.val((precio * cantidad).toFixed(2));

//   inputsubtotal.val(cantidad * ((precio / ($iva / 100 + 1))).toFixed(2));

//   var total = cantidad * precio - (cantidad * precio * 0) / 100;

//   console.log('tota', total);
//   inputtotal.text((total).toFixed(2));

//   var igv = cantidad * precio - (cantidad * precio) / ($iva / 100 + 1);

//   inputigvG.text(igv.toFixed(4));

//   inputigvBD.val(igv.toFixed(2));
// }

// function calcularNotaPedido(input, cantidad) {

//   var precio = input.closest('.card').find('input[name="precio_unitario[]"]').val();

//   var subtotal = (cantidad * precio);

//   var subtotalBDInput = input.closest('.card').find('input[name="subtotalBD[]"]');
//   subtotalBDInput.val(redondeo(subtotal, 2));

//   var subtotalInput = input.closest('.card').find('input[name="subtotal"]');
//   subtotalInput.val(redondeo(subtotal, 2));

//   var totalInput = input.closest('.card').find('span[name="total"]');
//   totalInput.text(redondeo(subtotal, 2));

//   var igv = 0.0;
//   var pvu = 0.0;

//   var igvGInput = input.closest('.card').find('span[name="igvG"]');
//   igvGInput.text(redondeo(igv, 4));

//   var igvBDInput = input.closest('.card').find('input[name="igvBD[]"]');
//   igvBDInput.val(redondeo(subtotal, 4));

//   var igvBD2Input = input.closest('.card').find('input[name="igvBD2[]"]');
//   igvBD2Input.val(redondeo(subtotal, 4));

//   var pvuInput = input.closest('.card').find('input[name="pvu_[]"]');
//   pvuInput.val(redondeo(pvu, 4));

//   var vvuInput = input.closest('.card').find('input[name="vvu[]"]');
//   vvuInput.val(redondeo(subtotal, 5));

// }

// function calcularFactura(input, cantidad) {

//   var precio = input.closest('.card').find('input[name="valor_unitario[]"]').val();
//   var inputsubtotal = input.closest('.card').find('input[name="subtotal"]');
//   var inputtotal = input.closest('.card').find('span[name="total"]');
//   var inputigvG = input.closest('.card').find('span[name="igvG"]');
//   var inputsubtotalBD = input.closest('.card').find('input[name="subtotalBD[]"]');
//   var inputigvBD2 = input.closest('.card').find('input[name="igvBD2[]"]');

//   var subtotal = cantidad * precio - (cantidad * precio * 0) / 100;

//   console.log('subtotal', subtotal);

//   var igv = subtotal * ($iva / 100);

//   sumtotal = subtotal + igv;

//   inputtotal.text(redondeo(sumtotal, 2));


//   inputsubtotal.val(redondeo(subtotal, 2));
//   inputsubtotalBD.val(redondeo(subtotal, 2));

//   inputigvG.text(redondeo(igv, 2));

//   inputigvBD2.val(redondeo(igv, 2));

// }
/* ---------------------------------------------------------------- */
//                  EVENTO ELIMINAR ITEM DE PEDIDO

$(document).on('click', '.remove-item', function (e) {
  e.preventDefault();
  $(this).closest('.card').remove();

  numeroOrden--;

  actualizarNumerosDeOrden();

  updateTotals();

  almacenarItems();
});

function actualizarNumerosDeOrden() {
  $('.items-order .card').each(function (index) {
    $(this).attr('data-orden', index + 1);
    $(this).find('input[name^="numero_orden_item_29"]').val(index + 1);
  });
}

/* ---------------------------------------------------------------- */
//                    CALCULAR TOTALES PEDIDO

function updateTotals() {
  sub_total = 0;
  total_igv = 0;
  total_mticbperu = 0;
  total = 0;
  pvu = 0;

  $('.items-order .card').each(function (i) {

    sub_total += parseFloat($(this).find('#subtotal').val());
    total_igv += parseFloat($(this).find('#igvG').text());
    total_mticbperu += parseFloat($(this).find('#mticbperuCalculado').text());
    total += parseFloat($(this).find('#total').text());
    pvu += parseFloat($(this).find('#pvu_\\[\\]').val());

    console.log('total_igv', total_igv);

  });


  $("#subtotal_boleta").val(redondeo(sub_total, 2));
  // $("#subtotalflotante").val(redondeo(sub_total, 2));
  $("#total_igv").val(redondeo(total_igv, 2));
  // $("#igvflotante").val(redondeo(total_igv, 2));
  // $("#icbper").val(redondeo(parseFloat(total_mticbperu), 2));
  $("#total_icbper").val(redondeo(total_mticbperu, 4));
  $("#totalpagar").val(formatNumber(total));
  // $("#totalflotante").val(number_format(redondeo(total, 2), 2));
  $("#total_final").val(redondeo(total, 2));
  $("#pre_v_u").val(redondeo(pvu, 2));

}



/* ---------------------------------------------------------------- */
//                    ACTUALIZAR TOTALES CARD

function modificarSubtotales(modificar) {

  if (modificar == 1) {
    var prec = document.getElementsByName("precio_unitario[]");
    var cant = document.getElementsByName("cantidad_item_12[]");

    for (var i = 0; i < cant.length; i++) {
      var inpP = prec[i];

      if (inpP.value == '') {

        document.getElementsByName("precio_unitario[]")[i].value = 0;

      } else if (isNaN(inpP.value) || inpP.value < 0) {
        // Si el valor no es un número válido o está vacío, muestra una alerta
        swal.fire({
          title: "Ops..!",
          text: "El valor insertado no es válido",
          icon: "warning",
          timer: 2000,
          showConfirmButton: false
        });

        document.getElementsByName("precio_unitario[]")[i].focus();
      }
    }

  }

  var tipocomprobante = $('#d_tipocomprobante').val();

  if (tipocomprobante == 0) {

    var noi = document.getElementsByName("numero_orden_item_29[]");
    var cant = document.getElementsByName("cantidad_item_12[]");
    var prec = document.getElementsByName("precio_unitario[]");
    var vuni = document.getElementsByName("valor_unitario[]");
    // var st = document.getElementsByName("stock[]");
    var igv = document.getElementsByName("igvG");
    var sub = document.getElementsByName("subtotal");
    var tot = document.getElementsByName("total");
    var pvu = document.getElementsByName("pvu_[]");
    var mti = document.getElementsByName("mticbperuCalculado");
    var cicbper = document.getElementsByName("cicbper[]");
    var mticbperu = document.getElementsByName("mticbperu[]");
    var dcto = document.getElementsByName("descuento[]");
    var sumadcto = document.getElementsByName("sumadcto[]");
    var dcto2 = document.getElementsByName("SumDCTO");
    var factorc = document.getElementsByName("factorc[]");
    var cantiRe = document.getElementsByName("cantidadreal[]");

    for (var i = 0; i < cant.length; i++) {
      var inpNOI = noi[i];
      var inpC = cant[i];
      var inpP = prec[i];
      var inpS = sub[i];
      var inpI = igv[i];
      var inpT = tot[i];
      var inpPVU = pvu[i];
      // var inStk = st[i];
      var inpVuni = vuni[i];
      var inD2 = dcto2[i];
      var dctO = dcto[i];
      var sumaDcto = sumadcto[i];
      var codIcbper = cicbper[i];
      var mticbperuNN = mticbperu[i];
      var mtiMonto = mti[i];
      var factorcc = factorc[i];
      var inpCantiR = cantiRe[i];

      // inStk.value = inStk.value;
      // mticbperuNN.value = mticbperuNN.value;

      if ($("#codigo_tributo_h").val() == "1000") {
        // +IGV
        //inpPVU.value=inpP.value / 1.18; //Obtener el valor unitario
        inpPVU.value = inpP.value / ($iva / 100 + 1); //Obtener el valor unitario
        document.getElementsByName("valor_unitario[]")[i].value = redondeo(
          inpPVU.value,
          5
        ); // Se asigan el valor al campo
        dctO.value = dctO.value;
        sumaDcto.value = sumaDcto.value;
        inpNOI.value = inpNOI.value;
        inpI.value = inpI.value;
        inpS.value = inpC.value * (inpP.value / ($iva / 100 + 1)); //Calculo de subtotal excluyendo el igv
        inD2.value = (inpC.value * inpP.value * dctO.value) / 100; //Calculo acumulado del descuento
        //FOMULA IGV
        inpI.value =
          inpC.value * inpP.value -
          (inpC.value * inpP.value) / ($iva / 100 + 1); //Calculo de IGV
        inpT.value =
          inpC.value * inpP.value -
          (inpC.value * inpP.value * dctO.value) / 100; //Calculo del total
        inpIitem = (inpPVU.value * $iva) / 100; // Calculo de igv del valor unitario
        mtiMonto.value = 0.0; // Calculo de ICbper * cantidad (0.10 * 20)

        // if (tipoumm == "1") {
        //   inpCantiR.value =
        //     inStk.value / factorcc.value -
        //     (inStk.value - inpC.value) / factorcc.value;
        // } else {
        //   inpCantiR.value = inpC.value;
        // }
        //alert(inpCantiR.value);
      } else {

        // EXONERADA

        //document.getElementsByName("precio_unitario[]")[i].value = redondeo(inpVuni.value,5);
        document.getElementsByName("precio_unitario[]")[i].value = redondeo(
          inpP.value,
          5
        );
        inpNOI.value = inpNOI.value;
        inpI.value = inpI.value;
        dctO.value = dctO.value;
        sumaDcto.value = sumaDcto.value;
        inpS.value = inpC.value * inpP.value;
        inD2.value = (inpC.value * inpVuni.value * dctO.value) / 100; //Calculo acumulado del descuento
        //FOMULA IGV
        inpI.value = 0.0;
        inpT.value =
          inpC.value * inpP.value -
          (inpC.value * inpVuni.value * dctO.value) / 100; //Calculo del total;
        inpPVU.value =
          document.getElementsByName("precio_unitario[]")[i].value;
        //inpIitem = 0.00;
        inpIitem = inpP.value;
        mtiMonto.value = mticbperuNN.value * inpC.value; // Calculo de ICbper * cantidad (0.10 * 20)
        document.getElementsByName("valor_unitario[]")[i].value = redondeo(
          inpP.value,
          5
        ); // Se asigan el valor al campo
      }



      document.getElementsByName("subtotal")[i].innerHTML = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value, 4);
      document.getElementsByName("mticbperuCalculado")[i].innerHTML = redondeo(
        mtiMonto.value,
        2
      );
      document.getElementsByName("total")[i].innerHTML = redondeo(
        inpT.value,
        2
      );
      document.getElementsByName("pvu_[]")[i].innerHTML = redondeo(
        inpPVU.value,
        5
      );

      // document.getElementsByName("numero_orden")[i].innerHTML = inpNOI.value;

      //Lineas abajo son para enviar el arreglo de inputs con los valor de IGV, Subtotal, y precio de venta

      //a la tala detalle_fact_art.

      document.getElementsByName("subtotalBD[]")[i].value = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvBD[]")[i].value = redondeo(inpI.value, 4);
      document.getElementsByName("igvBD2[]")[i].value = redondeo(inpIitem, 4);
      document.getElementsByName("vvu[]")[i].value = redondeo(inpPVU.value, 5);
      document.getElementsByName("SumDCTO")[i].innerHTML = redondeo(
        inD2.value,
        2
      );
      document.getElementsByName("sumadcto[]")[i].value = redondeo(
        inD2.value,
        2
      );

      // updateTotals();
    }

  } else if (tipocomprobante == 1) {

    var noi = document.getElementsByName("numero_orden_item_29[]");
    var cant = document.getElementsByName("cantidad_item_12[]");

    var prec = document.getElementsByName("precio_unitario[]"); //Precio unitario
    var vuni = document.getElementsByName("valor_unitario[]");
    // var st = document.getElementsByName("stock[]");
    var igv = document.getElementsByName("igvG");
    var sub = document.getElementsByName("subtotal");
    var tot = document.getElementsByName("total");
    var pvu = document.getElementsByName("pvu_[]");

    var dcto = document.getElementsByName("descuento[]");
    var sumadcto = document.getElementsByName("SumDCTO");
    // var dcto2 = document.getElementsByName("SumDCTO");

    var cicbper = document.getElementsByName("cicbper[]");
    var mticbperu = document.getElementsByName("mticbperu[]");
    var mti = document.getElementsByName("mticbperuCalculado");

    var factorc = document.getElementsByName("factorc[]");
    var cantiRe = document.getElementsByName("cantidadreal[]");

    for (var i = 0; i < cant.length; i++) {
      var inpNOI = noi[i];
      var inpC = cant[i];
      var inpP = prec[i];
      var inpS = sub[i];
      var inpVuni = vuni[i];
      var inpI = igv[i];

      var inpT = tot[i];
      var inpPVU = pvu[i];
      // var inStk = st[i];

      // var inD2 = dcto2[i];
      var dctO = dcto[i];
      var sumaDcto = sumadcto[i];

      var codIcbper = cicbper[i];
      var mticbperuNN = mticbperu[i];
      var mtiMonto = mti[i];

      var factorcc = factorc[i];
      var inpCantiR = cantiRe[i];

      // EXONERADO CALCULOS

      if ($("#codigo_tributo_h").val() == "1000") {

        // inStk.value = inStk.value;
        inpC.value = inpC.value;
        dctO.value = dctO.value;

        mticbperuNN.value = mticbperuNN.value;
        inpPVU.value = inpP.value / 1.18; //Obtener valor unitario
        inpPVU.value = inpP.value / ($iva / 100 + 1); //Obtener valor unitario
        document.getElementsByName("valor_unitario[]")[i].value = redondeo(
          inpPVU.value,
          5
        ); //Asignar valor unitario
        dctO.value = dctO.value;
        inpNOI.value = inpNOI.value;
        inpI.value = inpI.value;
        sumaDcto.value = sumaDcto.value;
        inpS.value =
          inpC.value * inpVuni.value -
          (inpC.value * inpVuni.value * dctO.value) / 100;
        // inD2.value = (inpC.value * inpVuni.value * dctO.value) / 100;
        //inpI.value= inpS.value * 0.18;
        inpI.value = inpS.value * ($iva / 100);
        // console.log('inpS.value', inpS.value);
        // console.log('inpI.value', inpI.value);
        // console.log('Sum.value', parseFloat(inpS.value) + parseFloat(inpI.value));

        //inpIitem = inpPVU.value * 0.18;
        inpIitem = inpPVU.value * ($iva / 100);
        mtiMonto.value = 0.0;
        inpT.value = parseFloat(inpS.value) + parseFloat(inpI.value);
        //ORIGINAL

        // if (tipoumm == "1") {
        //   inpCantiR.value =
        //     inStk.value / factorcc.value -
        //     (inStk.value - inpC.value) / factorcc.value;
        // } else {
        //   inpCantiR.value = inpC.value;
        // }
      } else {

        document.getElementsByName("valor_unitario[]")[i].value = redondeo(
          inpP.value,
          5
        ); //Asignar valor unitario
        dctO.value = dctO.value;
        inpNOI.value = inpNOI.value;
        inpI.value = inpI.value;
        sumaDcto.value = sumaDcto.value;
        //inpS.value=(inpC.value * inpVuni.value)  - (inpC.value * inpVuni.value *  dctO.value)/100 ;
        inpS.value =
          inpC.value * inpP.value -
          (inpC.value * inpP.value * dctO.value) / 100;
        sumaDcto.value = (inpC.value * inpVuni.value * dctO.value) / 100;
        inpI.value = 0.0;
        inpPVU.value =
          document.getElementsByName("precio_unitario[]")[i].value;
        //inpIitem = inpPVU.value;
        inpIitem = inpP.value;
        inpT.value = parseFloat(inpS.value) + parseFloat(inpI.value);
        mtiMonto.value = mticbperuNN.value * inpC.value; // Calculo de ICbper * cantidad (0.10 * 20)
        //document.getElementsByName("valor_unitario[]")[i].value = redondeo(inpVuni.value,5);
        document.getElementsByName("precio_unitario[]")[i].value = redondeo(
          inpP.value,
          5
        );

      }

      document.getElementsByName("subtotal")[i].innerHTML = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value, 2);
      document.getElementsByName("mticbperuCalculado")[i].innerHTML = redondeo(
        mtiMonto.value,
        2
      );
      document.getElementsByName("total")[i].innerHTML = redondeo(
        inpT.value,
        2
      );
      document.getElementsByName("pvu_[]")[i].innerHTML = redondeo(
        inpPVU.value,
        5
      );

      // document.getElementsByName("numero_orden")[i].innerHTML = inpNOI.value;

      //Lineas abajo son para enviar el arreglo de inputs ocultos con los valor de IGV, Subtotal, y precio de venta
      //a la tala detalle_fact_art.
      document.getElementsByName("subtotalBD[]")[i].value = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvBD[]")[i].value = redondeo(inpIitem, 2);
      document.getElementsByName("igvBD2[]")[i].value = redondeo(inpI.value, 2);
      document.getElementsByName("pvt[]")[i].value = redondeo(inpPVU.value, 5);
      //Fin de comentario

      // document.getElementsByName("SumDCTO")[i].innerHTML = redondeo(
      //   inD2.value,
      //   2
      // );
      // document.getElementsByName("sumadcto[]")[i].value = redondeo(
      //   inD2.value,
      //   2
      // );
    }

  } else if (tipocomprobante == 2) {


    var noi = document.getElementsByName("numero_orden_item_29[]");
    var cant = document.getElementsByName("cantidad_item_12[]");
    var prec = document.getElementsByName("precio_unitario[]");
    // var st = document.getElementsByName("stock[]");
    var stbd = document.getElementsByName("subtotalBD[]");
    var igv = document.getElementsByName("igvG");
    var sub = document.getElementsByName("subtotal");
    var tot = document.getElementsByName("total");
    var fecha = document.getElementsByName("fecha[]");
    var totaldeuda = document.getElementsByName("totaldeuda");
    var tcomp = document.getElementsByName("totalcomp[]");
    var pvu = document.getElementsByName("pvu_[]");

    var factorc = document.getElementsByName("factorc[]");
    var cantiRe = document.getElementsByName("cantidadreal[]");
    // console.log('st', st);

    // var adelanto = $("#adelanto");
    // var faltante = $("#faltante");

    for (var i = 0; i < cant.length; i++) {
      var inpNOI = noi[i];
      var inpC = cant[i];
      var inpP = prec[i];
      var inpS = sub[i];
      var inpI = igv[i];

      var inpT = tot[i];
      var inpPVU = pvu[i];
      // var inStk = st[i];
      var inSTbd = stbd[i];

      var factorcc = factorc[i];
      var inpCantiR = cantiRe[i];

      // console.log('inStk', inStk);

      // inStk.value = inStk.value;
      inSTbd.value = inSTbd.value;
      inpS.value = inpS.value;

      //Validar cantidad no sobrepase stock actual
      // if (parseFloat(inpC.value) > parseFloat(inStk.value)) {
      //   bootbox.alert("Mensaje, La cantidad supera al stock.");
      // } else {
      inpNOI.value = inpNOI.value;
      inpI.value = inpI.value;
      inpS.value = parseFloat(inpP.value) * inpC.value;
      inpI.value = 0.0;
      inpT.value = inpS.value; // + parseFloat(inpT2.value);
      inpPVU.value = 0.0;
      inpIitem = 0.0;
      // inpCantiR.value =
      //   inStk.value / factorcc.value -
      //   (inStk.value - inpC.value) / factorcc.value;

      document.getElementsByName("subtotalBD[]")[i].value = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("subtotal")[i].innerHTML = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value, 4);
      document.getElementsByName("total")[i].innerHTML = redondeo(
        inpT.value,
        2
      );
      document.getElementsByName("pvu_[]")[i].innerHTML = redondeo(
        inpPVU.value,
        5
      );

      // document.getElementsByName("numero_orden")[i].innerHTML = inpNOI.value;

      //Lineas abajo son para enviar el arreglo de inputs con los valor de IGV, Subtotal, y precio de venta
      //a la tala detalle_fact_art.
      //document.getElementsByName("subtotalBD[]")[i].value = redondeo(inpS.value,2);
      document.getElementsByName("igvBD[]")[i].value = redondeo(inpT.value, 4);
      document.getElementsByName("igvBD2[]")[i].value = redondeo(inpT.value, 4);
      document.getElementsByName("vvu[]")[i].value = redondeo(inpT.value, 5);
      //Fin de comentario
      // } //Final de if

    } //Final de for

  } else if (tipocomprobante == 3) { 

    var noi = document.getElementsByName("numero_orden_item[]");
    var cant = document.getElementsByName("cantidad[]");
    var prec = document.getElementsByName("precio_unitario[]");
    var vuni = document.getElementsByName("valor_unitario2[]");
    var igv = document.getElementsByName("igvG");
    var sub = document.getElementsByName("subtotal");
    var tot = document.getElementsByName("total");
    var pvu = document.getElementsByName("pvu_");


    for (var i = 0; i < cant.length; i++) {
        var inpNOI = noi[i];
        var inpC = cant[i];
        var inpP = prec[i];
        var inpS = sub[i];
        var inpVuni = vuni[i];
        var inpI = igv[i];

        var inpT = tot[i];
        var inpPVU = pvu[i];
        inpC.value = inpC.value;
        inpPVU.value = inpP.value / ($iva / 100 + 1); //Obtener valor unitario 
        document.getElementsByName("valor_unitario2[]")[i].value = redondeo(inpPVU.value, 5); //Asignar valor unitario 
        inpNOI.value = inpNOI.value;
        inpI.value = inpI.value;
        inpS.value = (inpC.value * inpVuni.value);
        inpI.value = inpS.value * $iva / 100;
        inpIitem = inpPVU.value * $iva / 100;
        inpT.value = inpS.value + inpI.value;

        document.getElementsByName("subtotal")[i].innerHTML = redondeo(inpS.value, 2);
        document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value, 2);
        document.getElementsByName("total")[i].innerHTML = redondeo(inpT.value, 2);
        document.getElementsByName("pvu_")[i].innerHTML = redondeo(inpPVU.value, 5);
        document.getElementsByName("numero_orden")[i].innerHTML = inpNOI.value;
        //Lineas abajo son para enviar el arreglo de inputs ocultos con los valor de IGV, Subtotal, y precio de venta
        //a la tala detalle_fact_art.
        document.getElementsByName("subtotalBD[]")[i].value = redondeo(inpS.value, 2);
        document.getElementsByName("igvBD[]")[i].value = redondeo(inpIitem, 2);
        document.getElementsByName("igvBD2[]")[i].value = redondeo(inpI.value, 2);
        document.getElementsByName("pvt[]")[i].value = redondeo(inpPVU.value, 5);
        document.getElementsByName("vuniitem[]")[i].value = redondeo(inpPVU.value, 5);
        document.getElementsByName("valorventa[]")[i].value = redondeo(inpS.value, 2);
        //Fin de comentario

    }
  }

  updateTotals();

}


/* ---------------------------------------------------------------- */
//                    EVENTO MOSTRAR DATOS

$('#container_datos').hide();

$('#btn_datos').on('click', function (e) {
  e.preventDefault();

  $('#container_datos').slideToggle();

});

/* ---------------------------------------------------------------- */
//                    FUNCION FORMATO NUMEROS

function formatNumber(number) {
  var parts = number.toFixed(2).split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}

/* ---------------------------------------------------------------- */
//                   FUNCIONES INPUT METODO PAGO

let currentInput = null;

// Al hacer clic en un boton de la calculadora, se enfoca en el input correspondiente.
$(".calculator-button").click(function () {
  // Encuentra el input relacionado al botón clickeado.
  const inputId = $(this).siblings(".calculator-input").attr("id");
  currentInput = $("#" + inputId);

  // Coloca el foco en el input.
  currentInput.focus();
});

$(".calculator-input").click(function () {
  currentInput = $(this);
});

/* ---------------------------------------------------------------- */
//                       FUNCIONES TECLADO

$(".design").click(function () {
  // console.log('this', $(this).text());

  // console.log('cuurr', currentInput.val());
  if (currentInput) {
    const buttonText = $(this).text();
    const inputValue = currentInput.val();

    if (inputValue == 0) {
      currentInput.val(buttonText);

    } else if (buttonText === "." && inputValue.includes(".")) {
      // Evitar agregar más de un punto decimal.
      currentInput.val(inputValue);

    } else {
      // currentInput.val(inputValue + buttonText);
      // Controlar la cantidad de decimales permitidos.
      const decimalIndex = inputValue.indexOf(".");
      if (decimalIndex !== -1 && inputValue.length - decimalIndex > 2) {
        // Si ya hay dos decimales, no permitir más.
        currentInput.val(inputValue);
      } else {
        currentInput.val(inputValue + buttonText);
      }
    }

    calcularPago();
  }

});

//Backspace
$('#backspace').click(function () {

  if (currentInput) {
    var value = currentInput.val();
    if (!(parseInt(parseFloat(value)) == 0 && value.length == 1)) {
      currentInput.val(value.slice(0, value.length - 1));
    }
    if (value.length == 1 || value.length == 0) {
      currentInput.val("0");
    }
    calcularPago();
  }

});

// All Clear
$("#allClear").click(function () {
  // $("#expression").val("0");
  // $("#result").val("0");
  if (currentInput) {
    currentInput.val("0");

    calcularPago();
  }
});

/* ---------------------------------------------------------------- */
//                    MOSTRAR MODAL METODO DE PAGO

$('#btn_metodopago').click(function () {

  if ($('.items-order .card').length === 0) {
    swal.fire({
      title: "Error",
      text: 'Debe agregar al menos un producto al pedido antes de continuar.',
      icon: "error",
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }

  var totalpedido = $('#totalpagar').val().replace(',', '');

  $('#p_pedido').val(totalpedido);
  $('#efectivo').val(parseFloat(totalpedido).toFixed(2));

  // Verificar que completaron datos

  //HERE

  var d_tipocomprobante = $('#d_tipocomprobante').val();

  if ($('#d_tipocomprobante').val() == null) {
    swal.fire({
      title: "Error",
      text: 'Complete los datos antes de continuar.',
      icon: "error",
      timer: 2000,
      showConfirmButton: false
    });

    $('#btn_datos').focus();

    return;

  } else if (d_tipocomprobante == 0 || d_tipocomprobante == 2) {

    if ($('#tipo_doc_ide').val() == '') {

      swal.fire({
        title: "Error",
        text: 'Complete el Tipo de Documento.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    } else if ($('#numero_documento').val() == '' || $('#numero_documento').val() == '-') {

      swal.fire({
        title: "Error",
        text: 'Complete el Número de Documento.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    } else if ($('#razon_social').val() == '' || $('#razon_social').val() == '-') {

      swal.fire({
        title: "Error",
        text: 'Complete los Nombres y Apellidos.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    }

  } else if (d_tipocomprobante == 1 || d_tipocomprobante == 4 || d_tipocomprobante == 5 || d_tipocomprobante == 6) {

    // Validación para Factura, Nota de Crédito, Nota de Débito y Guía de Remisión
    if ($('#tipo_doc_ide').val() == '') {

      swal.fire({
        title: "Error",
        text: 'Complete el Tipo de Documento.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    } else if ($('#numero_documento2').val() == '' || $('#numero_documento2').val() == '-') {

      swal.fire({
        title: "Error",
        text: 'Complete el Número de Documento.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    } else if ($('#razon_social2').val() == '' || $('#razon_social2').val() == '-') {

      swal.fire({
        title: "Error",
        text: 'Complete los Nombres y Apellidos.',
        icon: "error",
        timer: 2000,
        showConfirmButton: false
      });

      $('#btn_datos').focus();
      return;

    }

  }


  $('#modal_metodopago').modal('show');

  setTimeout(function () {
    $('#efectivo').focus();
  }, 500);

  currentInput = $('#efectivo');
  calcularPago();



})


/* ---------------------------------------------------------------- */
//                     CERRAR MODAL METODO DE PAGO

$('#modal_metodopago').on('hidden.bs.modal', function () {

  limpiarMetodoPago();
});

/* ---------------------------------------------------------------- */
//                      FUNCION CALCULAR PAGO

function calcularPago() {

  var p_pedido = parseFloat($('#p_pedido').val());

  var efectivo = parseFloat($('#efectivo').val() || 0);
  // var p_credito = parseFloat( $('#p_credito').val() || 0 );
  var visa = parseFloat($('#visa').val() || 0);
  var yape = parseFloat($('#yape').val() || 0);
  var plin = parseFloat($('#plin').val() || 0);
  var mastercard = parseFloat($('#mastercard').val() || 0);
  var deposito = parseFloat($('#deposito').val() || 0);

  var totalpagado = efectivo + visa + yape + plin + mastercard + deposito;

  $('#p_tpagado').val(formatNumber(totalpagado));

  var totalvuelto = 0;

  totalvuelto = totalpagado - p_pedido

  if (totalpagado > p_pedido) {

    $('#text_vuelto').html('Vuelto <span>S/.</span>');
    $('#text_vuelto').css('color', 'green');
    $('#p_vuelto').css('color', 'green');

  } else if (totalpagado == p_pedido) {

    $('#text_vuelto').html('Completo <span>S/.</span>');
    $('#text_vuelto').css('color', 'blue');
    $('#p_vuelto').css('color', 'blue');

  } else {

    totalvuelto = p_pedido - totalpagado

    $('#text_vuelto').html('Falta <span>S/.</span>');
    $('#text_vuelto').css('color', 'red');
    $('#p_vuelto').css('color', 'red');
  }

  // console.log('vuelto1', totalvuelto);
  $('#p_vuelto').val(formatNumber(totalvuelto));


}

/* ---------------------------------------------------------------- */
//                   EVENTO INPUT CALCULAR PAGO

$('.calculator-input').on('input', calcularPago);

/* ---------------------------------------------------------------- */
//                  LIMPIAR MODAL METODO DE PAGO

function limpiarMetodoPago() {

  $('#p_pedido').val(0);

  $('#efectivo').val(0);
  $('#visa').val(0);
  $('#yape').val(0);
  $('#plin').val(0);
  $('#mastercard').val(0);
  $('#deposito').val(0);

  $('#p_tpagado').val(0);

  $('#text_vuelto').html('Vuelto <span>S/.</span>');
  $('#p_vuelto').val(0);

}


/********************************************************************************/
/*                                LISTAR SELECT                                 */
/********************************************************************************/

$('#d_tipocomprobante').val(0);
$('.doc_dni').show();
$('.doc_ruc').hide();
obtenerSerie();

/* ---------------------------------------------------------------- */
//                    EVENTO CHANGE (d_tipocomprobante)   

// $("#d_tipocomprobante").change(function () {
//   // Verifica si se seleccionó "Factura"
//   if ($(this).val() === "1") {
//     // Selecciona la opción con valor "4" en tipo_doc_ide

//     $("#tipo_doc_ide").val("6");
//     focusI();

//   }
// });

$("#d_tipocomprobante").change(function () {
  // // Obtiene el valor seleccionado
  var selectedValue = $(this).val();

  var tipoDocSelect = $("#tipo_doc_ide");

  limpiarDatos();

  // ============================================================================
  // Por defecto, ocultar campos NC y ND (se mostrarán solo en sus respectivos tipos)
  // ============================================================================
  $("#nc_fields").slideUp(300); // Ocultar NC
  $("#nd_fields").slideUp(300); // Ocultar ND
  console.log("Change tipo comprobante: Campos NC y ND ocultos por defecto");

  // ============================================================================
  // Por defecto, ocultar botones NC y ND, mostrar botón "Pasar a caja"
  // ============================================================================
  $("#btn_guardar_nc").hide();
  $("#btn_guardar_nd").hide();
  $("#btn_metodopago").show();

  // // Llama a la función correspondiente según el valor seleccionado
  if (selectedValue === '0') {

    $("#tipo_doc_ide").val("0");

    // Desactiva las opciones
    tipoDocSelect.find("option[value='4']").prop('disabled', true);
    tipoDocSelect.find("option[value='6']").prop('disabled', true);

    tipoDocSelect.find("option[value='0']").prop('disabled', false);
    tipoDocSelect.find("option[value='1']").prop('disabled', false);
    tipoDocSelect.find("option[value='7']").prop('disabled', false);

  } else if (selectedValue === '1') {

    $("#tipo_doc_ide").val("6");

    // Desactiva las opciones
    tipoDocSelect.find("option[value='0']").prop('disabled', true);
    tipoDocSelect.find("option[value='1']").prop('disabled', true);
    tipoDocSelect.find("option[value='7']").prop('disabled', true);

    tipoDocSelect.find("option[value='4']").prop('disabled', false);
    tipoDocSelect.find("option[value='6']").prop('disabled', false);

  } else if (selectedValue === '2') {

    // Desactiva las opciones
    tipoDocSelect.find("option[value='4']").prop('disabled', true);
    tipoDocSelect.find("option[value='7']").prop('disabled', true);

    tipoDocSelect.find("option[value='0']").prop('disabled', false);
    tipoDocSelect.find("option[value='1']").prop('disabled', false);
    tipoDocSelect.find("option[value='6']").prop('disabled', false);


    $("#tipo_doc_ide").val("0");

  } else if (selectedValue === '3') {

    // Desactiva las opciones
    tipoDocSelect.find("option[value='0']").prop('disabled', true);
    tipoDocSelect.find("option[value='7']").prop('disabled', true);

    tipoDocSelect.find("option[value='4']").prop('disabled', false);
    tipoDocSelect.find("option[value='6']").prop('disabled', false);

    $("#tipo_doc_ide").val("1");

  } else if (selectedValue === '4') {

    // ============================================================================
    // NOTA DE CRÉDITO: El tipo de documento se hereda del comprobante original
    // ============================================================================
    // Normativa SUNAT: Las NC heredan el tipo de documento del comprobante a acreditar
    // - NC para Factura (01) → Requiere RUC (tipo 6)
    // - NC para Boleta (03) → Acepta DNI/CE/Pasaporte (tipos 1, 4, 7)
    //
    // Estrategia: Permitir todos los tipos de documento inicialmente.
    // El tipo correcto se establecerá cuando el usuario seleccione el comprobante
    // a acreditar mediante el modal de búsqueda de comprobantes.
    // ============================================================================

    // Habilitar todos los tipos de documento para NC
    tipoDocSelect.find("option[value='0']").prop('disabled', false); // Sin Documento
    tipoDocSelect.find("option[value='1']").prop('disabled', false); // DNI
    tipoDocSelect.find("option[value='4']").prop('disabled', false); // CE
    tipoDocSelect.find("option[value='6']").prop('disabled', false); // RUC
    tipoDocSelect.find("option[value='7']").prop('disabled', false); // Pasaporte

    // Por defecto, no establecer ningún tipo (el usuario debe seleccionar el comprobante primero)
    // El tipo se heredará automáticamente del comprobante seleccionado
    $("#tipo_doc_ide").val("1"); // Valor por defecto DNI, se actualizará al seleccionar comprobante

    // ============================================================================
    // MOSTRAR campos específicos de Nota de Crédito
    // ============================================================================
    $("#nc_fields").slideDown(300); // Mostrar con animación
    console.log("NC: Campos de Nota de Crédito mostrados");

    // ============================================================================
    // MOSTRAR botón de guardar NC, ocultar "Pasar a caja"
    // ============================================================================
    $("#btn_guardar_nc").show();
    $("#btn_metodopago").hide();
    console.log("NC: Botón 'Guardar Nota de Crédito' visible");

  } else if (selectedValue === '5') {

    // ============================================================================
    // NOTA DE DÉBITO: El tipo de documento se hereda del comprobante original
    // ============================================================================
    // Normativa SUNAT: Las ND heredan el tipo de documento del comprobante a debitar
    // - ND para Factura (01) → Requiere RUC (tipo 6)
    // - ND para Boleta (03) → Acepta DNI/CE/Pasaporte (tipos 1, 4, 7)
    //
    // Estrategia: Permitir todos los tipos de documento inicialmente.
    // El tipo correcto se establecerá cuando el usuario seleccione el comprobante
    // a debitar mediante el modal de búsqueda de comprobantes.
    // ============================================================================

    // Habilitar todos los tipos de documento para ND
    tipoDocSelect.find("option[value='0']").prop('disabled', false); // Sin Documento
    tipoDocSelect.find("option[value='1']").prop('disabled', false); // DNI
    tipoDocSelect.find("option[value='4']").prop('disabled', false); // CE
    tipoDocSelect.find("option[value='6']").prop('disabled', false); // RUC
    tipoDocSelect.find("option[value='7']").prop('disabled', false); // Pasaporte

    // Por defecto, no establecer ningún tipo (el usuario debe seleccionar el comprobante primero)
    // El tipo se heredará automáticamente del comprobante seleccionado
    $("#tipo_doc_ide").val("1"); // Valor por defecto DNI, se actualizará al seleccionar comprobante

    // ============================================================================
    // MOSTRAR campos específicos de Nota de Débito
    // ============================================================================
    $("#nd_fields").slideDown(300); // Mostrar con animación
    console.log("ND: Campos de Nota de Débito mostrados");

    // ============================================================================
    // MOSTRAR botón de guardar ND, ocultar "Pasar a caja"
    // ============================================================================
    $("#btn_guardar_nd").show();
    $("#btn_metodopago").hide();
    console.log("ND: Botón 'Guardar Nota de Débito' visible");

  } else if (selectedValue === '6') {

    // Guía de Remisión - requiere RUC
    $("#tipo_doc_ide").val("6");

    // Desactiva las opciones
    tipoDocSelect.find("option[value='0']").prop('disabled', true);
    tipoDocSelect.find("option[value='1']").prop('disabled', true);
    tipoDocSelect.find("option[value='7']").prop('disabled', true);

    tipoDocSelect.find("option[value='4']").prop('disabled', false);
    tipoDocSelect.find("option[value='6']").prop('disabled', false);

  }

  focusI();


  modificarSubtotales();


  $('#serie').prop('disabled', true);

  obtenerSerie();

});

/* ---------------------------------------------------------------- */
//              FUNCION limpiar datos de clientes   

function limpiarDatos() {

  $('#idcliente').val('');
  $('#idpersona').val('');
  $('#numero_documento').val('');
  $('#numero_documento2').val('');
  $('#razon_social').val('');
  $('#razon_social2').val('');
  $('#domicilio_fiscal').val('');
  $('#domicilio_fiscal2').val('');

}


/* ---------------------------------------------------------------- */
//                 OBTENER IMPUESTO (codigo_tributo_18_3)        

$.post("../ajax/factura.php?op=selectTributo", function (r) {
  $("#codigo_tributo_18_3").html(r);
  // Inicializar codigo_tributo_h con el valor por defecto
  tributocodnon();
});



/* ---------------------------------------------------------------- */
//                   OBTENER VENDEDOR (vendedorsitio)  

$.post(
  "../ajax/vendedorsitio.php?op=selectVendedorsitio&idempresa=" + $idempresa,
  function (r) {
    $("#vendedorsitio").html(r);
  }
);

/* ---------------------------------------------------------------- */
//                   OBTENER TIPO DOCUMENTO (tipo_doc_ide)  

//llenar documentos
function cargarTiposDocIde() {
  $.ajax({
    url: urlconsumo + "catalogo6.php?action=listar2",
    type: "GET",
    dataType: "json",
    success: function (data) {
      llenarSelect(data.aaData);
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar los tipos de documento de identidad");
      console.error(error);
    },
  });
}

function llenarSelect(data) {
  var select = $("#tipo_doc_ide");
  select.empty();
  select.append(
    $("<option>", {
      value: "",
      text: "Seleccionar Tipo documento",
    })
  );
  $.each(data, function (index, value) {
    if (value.estado === "1") {

      var optionText = value.codigo === "0" ? "SIN DOCUMENTO" : value.documento;

      select.append(
        $("<option>", {
          value: value.codigo,
          text: optionText,
        })
      );
    }
  });

  var tipoDocSelect = $("#tipo_doc_ide");

  tipoDocSelect.val(0);

  tipoDocSelect.find("option[value='1']").prop('disabled', false);
  tipoDocSelect.find("option[value='7']").prop('disabled', false);

  tipoDocSelect.find("option[value='4']").prop('disabled', true);
  tipoDocSelect.find("option[value='6']").prop('disabled', true);

  focusI();
}

cargarTiposDocIde();

/* ---------------------------------------------------------------- */
//                       OBTENER SERIE (serie)  


function obtenerSerie() {

  select_tipocomp = $('#d_tipocomprobante').val();

  // Si es boleta
  if (select_tipocomp == 0) {

    $.post("../ajax/boleta.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      //$("#serie").selectpicker('refresh');
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/boleta.php?op=autonumeracion&ser=" +
        opt +
        "&idempresa=" +
        $idempresa,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else if (select_tipocomp == 1) {

    // Si es factura

    $.post("../ajax/factura.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      //$("#serie").selectpicker('refresh');
      var serieL = document.getElementById("serie");
      var opt = serieL.value;

      // Llenar el campo hidden idnumeracion con el value del primer option
      $("#idnumeracion").val(opt);

      $.post(
        "../ajax/factura.php?op=autonumeracion&ser=" +
        opt +
        "&idempresa=" +
        $idempresa,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });



  } else if (select_tipocomp == 2) {

    // Si es Nota de Pedido

    $.post("../ajax/notapedido.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      //$("#serie").selectpicker('refresh');

      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/notapedido.php?op=autonumeracion&ser=" + opt,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);

          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else if (select_tipocomp == 3) {

    // Si es cotización
    $.post("../ajax/cotizacion.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      //$("#serie").selectpicker('refresh');
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/cotizacion.php?op=autonumeracion&ser=" +
        opt +
        "&idempresa=" +
        $idempresa,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else if (select_tipocomp == 4) {

    // Si es Nota de Crédito
    $.post("../ajax/notac.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/notac.php?op=autonumeracion&ser=" + opt,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else if (select_tipocomp == 5) {

    // Si es Nota de Débito
    $.post("../ajax/notacd.php?op=selectSerieDebito", function (r) {
      $("#serie").html(r);
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/notacd.php?op=autonumeracion&ser=" + opt,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else if (select_tipocomp == 6) {

    // Si es Guía de Remisión
    $.post("../ajax/guiaremision.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/guiaremision.php?op=autonumeracion&ser=" + opt,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);

          $('#serie').prop('disabled', false);
        }
      );
    });

  } else {

    $("#serie").html('');
    $("#numero_boleta").val('');
    $("#SerieReal").val('');
  }

  focusI();
}


/* ---------------------------------------------------------------- */
//                   FUNCION INCREMENTAR NUM  

function incremetarNum() {
  var serie = $("#serie option:selected").val();

  // Llenar el campo hidden idnumeracion con el value del select (que ES el idnumeracion)
  $("#idnumeracion").val(serie);

  $.post(
    "../ajax/boleta.php?op=autonumeracion&ser=" +
    serie +
    "&idempresa=" +
    $idempresa,
    function (r) {
      var n2 = pad(r, 0);
      $("#numero_boleta").val(n2);
      var SerieReal = $("#serie option:selected").text();
      $("#SerieReal").val(SerieReal);
    }
  );
  document.getElementById("tipo_doc_ide").focus();
}

/* ---------------------------------------------------------------- */
//                 Funcion para poner ceros antes del numero   

function pad(n, length) {
  var n = n.toString();
  while (n.length < length) n = "0" + n;
  return n;
}

/* ---------------------------------------------------------------- */
//                 Obtener la fecha actual  

$("#fecha_emision_01").prop("disabled", false);

var now = new Date();
var day = ("0" + now.getDate()).slice(-2);
var month = ("0" + (now.getMonth() + 1)).slice(-2);
var today = now.getFullYear() + "-" + month + "-" + day;
$("#fecha_emision_01").val(today);
$("#fechavenc").val(today);

// Lista ventas
$('#fechaDesde').val(today);
$('#fechaHasta').val(today);

/* ---------------------------------------------------------------- */
//                   FUNCION FOCUS (tipo_doc_ide)

function focusTdoc() {
  document.getElementById("tipo_doc_ide").focus();
}

/* ---------------------------------------------------------------- */
//                   FUNCION FOCUS (tipo_doc_ide)

function focusI() {
  var tipo = $("#tipo_doc_ide option:selected").val();

  limpiarDatos();

  if (tipo == "0") {
    $.post(
      "../ajax/persona.php?op=mostrarClienteVarios",
      function (data, status) {
        data = JSON.parse(data);
        $("#idcliente").val(data.idpersona);
        $("#numero_documento").val(data.numero_documento);
        $("#razon_social").val(data.razon_social);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);

        $("#numero_documento").prop('readonly', true);
        $("#razon_social").prop('readonly', true);
        $("#domicilio_fiscal").prop('readonly', true);
      }
    );

    //document.getElementById('numero_documento').focus();

    $('.doc_dni').show();
    $('.doc_ruc').hide();

  }

  if (tipo == "1") {
    //$('#idcliente').val("");
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 20;


    enabledTipoDoc();

    $('.doc_dni').show();
    $('.doc_ruc').hide();
  }

  if (tipo == "3") {
    //$('#idcliente').val("");
    $("#numero_documento").val(data.numero_documento);
    $("#razon_social").val(data.razon_social);
    $("#domicilio_fiscal").val(data.domicilio_fiscal);

    $("#numero_documento").prop('readonly', true);
    $("#razon_social").prop('readonly', true);
    $("#domicilio_fiscal").prop('readonly', true);
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 20;


    enabledTipoDoc();

    $('.doc_dni').show();
    $('.doc_ruc').hide();
  }

  if (tipo == "4") {
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 15;

    enabledTipoDoc();
    $('.doc_dni').show();
    $('.doc_ruc').hide();
  }

  if (tipo == "7") {
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 15;

    enabledTipoDoc();
    $('.doc_dni').show();
    $('.doc_ruc').hide();
  }

  if (tipo == "A") {
    $("#numero_documento").val("");

    $("#razon_social").val("");

    $("#domicilio_fiscal").val("");

    document.getElementById("numero_documento").focus();

    document.getElementById("numero_documento").maxLength = 15;


    enabledTipoDoc();
    $('.doc_dni').show();
    $('.doc_ruc').hide();
  }

  if (tipo == "6") {
    $("#numero_documento").val("");

    $("#razon_social").val("");

    $("#domicilio_fiscal").val("");

    document.getElementById("numero_documento").focus();

    document.getElementById("numero_documento").maxLength = 11;

    $('.doc_dni').hide();
    $('.doc_ruc').show();

    enabledTipoDoc();

  }
}

/* ---------------------------------------------------------------- */
//               Funcion habilitar campos de tipo doc

function enabledTipoDoc() {
  $("#numero_documento").prop('readonly', false);
  $("#razon_social").prop('readonly', false);
  $("#domicilio_fiscal").prop('readonly', false);

}

/* ---------------------------------------------------------------- */
//             FUNCION buscarClientePorDocumento (REUTILIZABLE)
// ============================================================================
// Función auxiliar que realiza la búsqueda del cliente por número de documento
// Se puede llamar desde múltiples eventos: Enter (keypress) o blur (perder foco)
// ============================================================================

function buscarClientePorDocumento() {
  var dni = $("#numero_documento").val().trim();

  // Validar que se haya ingresado un número de documento
  if (!dni || dni === "" || dni === "-") {
    return; // No hacer nada si el campo está vacío
  }

  $("#razon_social").val("");
  $("#domicilio_fiscal").val("");

  $.post(
    "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idcliente").val(data.idpersona);
        $("#razon_social").val(data.nombres);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else if ($("#tipo_doc_ide").val() == "1") {
        // SI ES DNI
        $("#razon_social").val("");
        $("#domicilio_fiscal").val("");
        var dni = $("#numero_documento").val();
        $.post(
          "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + dni,
          function (data, status) {
            data = JSON.parse(data);
            if (data != null) {
              $("#idcliente").val("N");
              $("#razon_social").val(data.nombre);
            } else {
              alert(data);
              document.getElementById("razon_social").focus();
              $("#idcliente").val("N");
            }
          }
        );
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else if ($("#tipo_doc_ide").val() == "6") {
        // SI ES RUC
        $("#razon_social").val("");
        $("#domicilio_fiscal").val("");
        var dni = $("#numero_documento").val();
        $.post(
          "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + dni,
          function (data, status) {
            data = JSON.parse(data);
            if (data != null) {
              $("#idcliente").val(data.idpersona);
              $("#razon_social").val(data.razon_social);
              $("#domicilio_fiscal").val(data.domicilio_fiscal);
            } else {
              $("#idcliente").val("");
              $("#razon_social").val("No registrado");
              $("#domicilio_fiscal").val("No registrado");
              Swal.fire({
                title: "Cliente no registrado",
                icon: "warning",
              });

              $("#ModalNcliente").modal("show");
              $("#nruc").val($("#numero_documento").val());
            }
          }
        );
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else {
        $("#idcliente").val("N");
        $("#razon_social").val("");
        document.getElementById("razon_social").placeholder = "No Registrado";
        $("#domicilio_fiscal").val("");
        document.getElementById("domicilio_fiscal").placeholder = "No Registrado";
        document.getElementById("razon_social").style.Color = "#35770c";
        document.getElementById("razon_social").focus();
      }
    }
  );
}

/* ---------------------------------------------------------------- */
//             FUNCION agregarClientexDoc (numero_documento)
// ============================================================================
// Evento onkeypress: Ejecuta búsqueda cuando el usuario presiona Enter
// ============================================================================

function agregarClientexDoc(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    e.preventDefault();
    buscarClientePorDocumento();
  }
}

/* ---------------------------------------------------------------- */
// EVENTO onblur: Ejecuta búsqueda automática cuando el campo pierde el foco
// ============================================================================
// Esto mejora la UX porque el usuario no necesita presionar Enter explícitamente
// ============================================================================

$(document).ready(function() {
  $("#numero_documento").on('blur', function() {
    // Solo buscar si hay contenido en el campo
    var dni = $(this).val().trim();
    if (dni && dni !== "" && dni !== "-") {
      buscarClientePorDocumento();
    }
  });
});



$(document).ready(function () {
  $('#numero_documento').on('input', function () {
    if ($(this).val().length == 11 && $('#tipo_doc_ide').val() == "6") {
      buscarRUCcliente();
    }
  });
});

function buscarRUCcliente() {
  var ruc = $("#numero_documento").val();

  $.ajax({
    url: "https://dniruc.apisperu.com/api/v1/ruc/" + ruc + "?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImFsbGluZXJwc29mdHdhcmVzYWNAZ21haWwuY29tIn0.CqzKsBSzn3-lV-AAnRjurJuGrR_ebBOIvnsEiuj7PMk",
    type: "GET",
    dataType: "json",
    success: function (data) {
      // Suponiendo que la respuesta tiene la estructura que diste,
      // asignas los valores a tus inputs:
      $("#razon_social").val(data.razonSocial);
      $("#domicilio_fiscal").val(data.direccion);

      // Luego aquí puedes hacer tu consulta a tu propio backend, 
      // por ejemplo verificar si ese RUC ya está registrado en tu sistema, etc.
    },
    error: function (error) {
      console.log("Error al consultar RUC", error);
    }
  });

}




/* ---------------------------------------------------------------- */
//             FUNCION agregarClientexDocCha (numero_documento)

// function agregarClientexDocCha() {
//   var dni = $("#numero_documento").val();

//   $("#razon_social").val("");
//   $("#domicilio_fiscal").val("");

//   $.post(
//     "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
//     function (data, status) {
//       data = JSON.parse(data);
//       if (data != null) {
//         $("#idcliente").val(data.idpersona);
//         $("#razon_social").val(data.nombres);
//         $("#domicilio_fiscal").val(data.domicilio_fiscal);
//         document.getElementById("btnAgregarArt").style.backgroundColor =
//           "#367fa9";
//         document.getElementById("mensaje700").style.display = "none";
//         document.getElementById("btnAgregarArt").focus();
//         $("#suggestions").fadeOut();
//         $("#suggestions2").fadeOut();
//         $("#suggestions3").fadeOut();
//       } else if ($("#tipo_doc_ide").val() == "1") {
//         // SI ES DNI
//         $("#razon_social").val("");
//         $("#domicilio_fiscal").val("");
//         var dni = $("#numero_documento").val();
//         console.log(dni);
//         //var url = '../ajax/consulta_reniec.php';
//         $.post(
//           "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + dni,
//           function (data, status) {
//             data = JSON.parse(data);
//             console.log(data);
//             //swal.fire("Error","Nro DNI debe contener 8 digitos", "error");
//             if (data != null) {
//               $("#idcliente").val("N");

//               console.log(data);
//               //$("#numero_documento3").val(data.numeroDocumento);
//               $("#razon_social").val(data.nombre);
//               console.log(data.nombre);
//               //swal.fire("Error","Datos no encontrados", "error");
//             } else {
//               swal.fire("Error", "Datos no encontrados", "error");
//               //alert(data);
//               console.log(data);
//               document.getElementById("razon_social").focus();
//               $("#idcliente").val("N");
//             }
//           }
//         );
//         $("#suggestions").fadeOut();
//         $("#suggestions2").fadeOut();
//         $("#suggestions3").fadeOut();
//       } else if ($("#tipo_doc_ide").val() == "6") {
//         // SI ES RUC
//         $("#razon_social").val("");
//         $("#domicilio_fiscal").val("");
//         var dni = $("#numero_documento").val();
//         $.post(
//           "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + dni,
//           function (data, status) {
//             data = JSON.parse(data);
//             if (data != null) {
//               $("#idcliente").val(data.idpersona);
//               $("#razon_social").val(data.razon_social);
//               $("#domicilio_fiscal").val(data.domicilio_fiscal);
//             } else {
//               $("#idcliente").val("");
//               $("#razon_social").val("No registrado");
//               $("#domicilio_fiscal").val("No registrado");
//               // Swal.fire({
//               //   title: "Cliente no registrado",
//               //   icon: "warning",
//               // });

//               $("#ModalNcliente").modal("show");
//               $("#nruc").val($("#numero_documento").val());
//             }
//           }
//         );
//         $("#suggestions").fadeOut();
//         $("#suggestions2").fadeOut();
//         $("#suggestions3").fadeOut();
//       } else {
//         $("#idcliente").val("N");
//         $("#razon_social").val("");
//         document.getElementById("razon_social").placeholder = "No Registrado";
//         $("#domicilio_fiscal").val("");
//         document.getElementById("domicilio_fiscal").placeholder =
//           "No Registrado";
//         document.getElementById("btnAgregarArt").style.backgroundColor =
//           "#35770c";
//         document.getElementById("razon_social").style.Color = "#35770c";
//         document.getElementById("razon_social").focus();
//       }
//     }
//   );
// }

/* ---------------------------------------------------------------- */
//             FUNCION quitasuge2 (numero_documento)

function quitasuge2() {
  if ($("#razon_social").val() == "") {
    $("#suggestions2").fadeOut();
  }

  $("#suggestions2").fadeOut();
}


/********************************************************************************/
/*                              FACTURA CLIENTE                                 */
/********************************************************************************/

/* ---------------------------------------------------------------- */
//                      FUNCION (numero_documento2)

function agregarClientexRuc(e) {
  var documento = $("#numero_documento2").val();
  if (e.keyCode === 13 && !e.shiftKey) {
    $.post(
      "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + documento,
      function (data, status) {
        data = JSON.parse(data);
        if (data != null && data.idpersona != null) {
          // Agregamos verificación adicional para la entrada nula
          $("#idpersona").val(data.idpersona);
          $("#razon_social2").val(data.razon_social);
          $("#domicilio_fiscal2").val(data.domicilio_fiscal);
          $("#correocli").val(data.email);

          document.getElementById("correocli").focus();
          $("#suggestions").fadeOut();
        } else {
          $("#idpersona").val("");
          $("#razon_social2").val("No existe");
          $("#domicilio_fiscal2").val("No existe");
          swal
            .fire({
              title: "Cliente no registrado",
              text: "Vamos agregar uno nuevo",
              icon: "warning",
              timer: 1500,
              showConfirmButton: false,
            })
            .then(function () {
              $("#ModalNcliente").modal("show");
              $("#nruc").val($("#numero_documento2").val());
              $("#suggestions").fadeOut();
            });
        }
      }
    );
  } else if (e.keyCode === 11 && !e.shiftKey) {
    $.post(
      "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + documento,
      function (data, status) {
        data = JSON.parse(data);
        if (data != null && data.idpersona != null) {
          // Agregamos verificación adicional para la entrada nula
          $("#idpersona").val(data.idpersona);
          $("#razon_social2").val(data.razon_social);
          $("#domicilio_fiscal2").val(data.domicilio_fiscal);

          if (data.email == "") {
            $("#correocli").css("background-color", "#FBC6AA");
            document.getElementById("correocli").focus();
          } else {
            document.getElementById("btnAgregarArt").style.backgroundColor =
              "#367fa9";
            document.getElementById("btnAgregarArt").focus();
          }
        }
      }
    );
  }
}

$("#numero_documento2").on("keyup", function () {
  var key = $(this).val();
  $("#suggestions2").fadeOut();
  $("#suggestions3").fadeOut();
  var dataString = "key=" + key;
  $.ajax({
    type: "POST",
    url: "../ajax/persona.php?op=buscarclienteRuc",
    data: dataString,

    success: function (data) {
      //Escribimos las sugerencias que nos manda la consulta
      $("#suggestions").fadeIn().html(data);

      //Al hacer click en algua de las sugerencias
      $(".suggest-element").on("click", function () {
        //Obtenemos la id unica de la sugerencia pulsada
        var id = $(this).attr("id");
        //Editamos el valor del input con data de la sugerencia pulsada
        $("#numero_documento2").val($("#" + id).attr("ndocumento"));
        $("#razon_social2").val($("#" + id).attr("ncomercial"));
        $("#domicilio_fiscal2").val($("#" + id).attr("domicilio"));
        $("#correocli").val($("#" + id).attr("email"));
        $("#idpersona").val(id);
        //$("#resultado").html("<p align='center'><img src='../public/images/spinner.gif' /></p>");
        //Hacemos desaparecer el resto de sugerencias

        $("#suggestions").fadeOut();
        //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
        return false;
      });
    },
  });
});

function quitasuge1() {
  if ($("#numero_documento2").val() == "") {
    $("#suggestions").fadeOut();
  }

  $("#suggestions").fadeOut();
}

/* ---------------------------------------------------------------- */
//             FUNCION TIPO CAMBIO SUNAT (tipo_moneda_24)

// $("#tcambio").val("0");

function tipodecambiosunat() {
  if ($("#tipo_moneda_24").val() == "USD") {
    fechatcf = $("#fecha_emision_01").val();
    $.post(
      "../ajax/boleta.php?op=tcambiog&feccf=" + fechatcf,
      function (data, status) {
        data = JSON.parse(data);
        $("#tcambio").val(data.venta);
      }
    );
  } else {
    $("#tcambio").val("0");
  }
}

/* ---------------------------------------------------------------- */
//             FUNCION TRIBUTOCODNON (codigo_tributo_18_3)


function tributocodnon() {
  $("#codigo_tributo_h").val($("#codigo_tributo_18_3 option:selected").val());
  $("#nombre_tributo_h").val($("#codigo_tributo_18_3").text());

  tribD = $("#codigo_tributo_h").val();
  var id = document.getElementsByName("idarticulo[]");
  var codtrib = document.getElementsByName("codigotributo[]");
  var nombretrib = document.getElementsByName("afectacionigv[]");
  var cantiRe = document.getElementsByName("cantidadreal[]");

  if (tribD == "1000") {
    for (var i = 0; i < id.length; i++) {
      var codtrib2 = codtrib[i];
      var nombretrib2 = nombretrib[i];
      codtrib2.value = "1000";
      nombretrib2.value = "10";
      //cantiRe[i].value=cantidadreal;
    } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
  } else if (tribD == "9997") {
    for (var i = 0; i < id.length; i++) {
      var codtrib2 = codtrib[i];
      var nombretrib2 = nombretrib[i];
      codtrib2.value = "9997";
      nombretrib2.value = "20";
    } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
  }

  console.log('here');
  modificarSubtotales();
}

/* ---------------------------------------------------------------- */
//                     FUNCION CAPTURAR HORA (hora)

function capturarhora() {
  var f = new Date();

  cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();

  $("#hora").val(cad);
}

/* ---------------------------------------------------------------- */
//                       FUNCION MAYUSCULA

function mayus(e) {
  e.value = e.value.toUpperCase();
}

/* ---------------------------------------------------------------- */
//                   FUNCION FOCUS (domicilio_fiscal)

function focusDir(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("domicilio_fiscal").focus();
  }
}

/* ---------------------------------------------------------------- */
//                   FUNCION FOCUS (btnAgregarArt)

function agregarArt(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("btnAgregarArt").focus();
  }
}

/* ---------------------------------------------------------------- */
//                     FUNCION REALIZAR PAGO
var url_pago;

$('#btn_realizarpago').click(function () {

  $("#ccuotas").val("0");
  $("#tadc").val("0");
  $("#trans").val("0");
  $("#itemno").val("0");

  select_tipocomp = $('#d_tipocomprobante').val();


  if (select_tipocomp == 0) {
    $("#idcliente").val("N");
    $("#tipo_documento_06").val('03');

    // Si es boleta
    url_pago = "../ajax/boleta.php?op=guardaryeditarBoleta";

  } else if (select_tipocomp == 1) {

    // ============================================================================
    // VALIDACIÓN CRÍTICA SUNAT: Factura requiere RUC obligatoriamente
    // Resolución de Superintendencia N° 097-2012/SUNAT
    // ============================================================================
    var tipoDocCliente = $('#tipo_doc_ide').val();

    if (tipoDocCliente != "6") {
        Swal.fire({
            title: "Error de Validación SUNAT",
            html: "<strong>Las facturas solo pueden emitirse a clientes con RUC.</strong><br><br>" +
                  "Tipo de documento actual: " + $('#tipo_doc_ide option:selected').text() + "<br><br>" +
                  "<b>Opciones:</b><br>" +
                  "• Seleccione un cliente con RUC (tipo documento 6)<br>" +
                  "• Cambie a Boleta para clientes sin RUC",
            icon: "error",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#d33"
        });
        return false;
    }
    // ============================================================================

    // console.log('tipo_doc_ide', $('#tipo_doc_ide').val());

    var fecha_emision_01 = $("#fecha_emision_01").val();
    $("#fecha_emision").val(fecha_emision_01);
    //estyo en pos tmb  no va yas a presioanr ocntrol z

    var numero_boleta = $("#numero_boleta").val();
    $("#numero_factura").val(numero_boleta);

    // var idcliente = $("#idcliente").val();
    // $("#idpersona").val(idcliente);

    // var numero_documento = $("#numero_documento").val();
    // $("#numero_documento2").val(numero_documento);

    // var razon_social = $("#razon_social").val();
    // $("#razon_social2").val(razon_social);

    // var domicilio_fiscal = $("#domicilio_fiscal").val();
    // $("#domicilio_fiscal2").val(domicilio_fiscal);

    var guia_remision_25 = $("#guia_remision_25").val();
    $("#guia_remision_29_2").val(guia_remision_25);

    var codigo_tributo_18_3 = $("#codigo_tributo_18_3").val();
    $("#nombre_trixbuto_4_p").val(codigo_tributo_18_3);

    var descripcion_leyenda_26_2 = $("#descripcion_leyenda_26_2").val();
    $("#descripcion_leyenda_2").val(descripcion_leyenda_26_2);

    var subtotal_boleta = $("#subtotal_boleta").val();
    $("#subtotal_factura").val(subtotal_boleta);


    // Si es Factura
    url_pago = "../ajax/factura.php?op=guardaryeditarFactura2";

  } else if (select_tipocomp == 2) {
    $("#idcliente").val("N");
    $("#tipo_documento_06").val(50);
    // Si es Nota de Pedido
    url_pago = "../ajax/notapedido.php?op=guardaryeditarBoleta";

  } else if (select_tipocomp == 4) {

    // ============================================================================
    // NOTA DE CRÉDITO: Validaciones y configuración
    // ============================================================================
    var tipoDocCliente = $('#tipo_doc_ide').val();
    var numero_doc = $('#numero_documento').val();

    // Validar que se haya seleccionado un cliente
    if (!$("#idcliente").val() || $("#idcliente").val() == "N") {

        // Determinar el mensaje específico según el estado del formulario
        var mensajeDetallado = "";

        if (!numero_doc || numero_doc === "" || numero_doc === "-") {
            // Caso 1: No se ingresó ningún número de documento
            mensajeDetallado = "<strong>No ha ingresado un número de documento.</strong><br><br>" +
                              "Para emitir una Nota de Crédito, debe:<br>" +
                              "1. Seleccionar el tipo de documento (DNI, RUC, etc.)<br>" +
                              "2. Ingresar el número de documento del cliente<br>" +
                              "3. El sistema buscará automáticamente los datos del cliente";
        } else {
            // Caso 2: Se ingresó documento pero no se buscó (usuario no presionó Enter ni salió del campo)
            mensajeDetallado = "<strong>Debe buscar los datos del cliente.</strong><br><br>" +
                              "Ha ingresado el número de documento: <strong>" + numero_doc + "</strong><br><br>" +
                              "Por favor, presione <strong>ENTER</strong> o haga clic fuera del campo " +
                              "para que el sistema busque automáticamente los datos del cliente.";
        }

        Swal.fire({
            title: "Cliente no seleccionado",
            html: mensajeDetallado,
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#3085d6",
            customClass: {
                popup: 'swal-wide'
            }
        });

        // Enfocar el campo de número de documento para que el usuario corrija
        $("#numero_documento").focus();
        return false;
    }

    // ============================================================================
    // VALIDAR CAMPOS ESPECÍFICOS DE NOTA DE CRÉDITO
    // ============================================================================

    // Validación 1: Verificar que se haya seleccionado un comprobante de referencia
    var nc_idcomprobante = $("#nc_idcomprobante").val();
    if (!nc_idcomprobante || nc_idcomprobante === "") {
        Swal.fire({
            title: "Comprobante no seleccionado",
            html: "<strong>Debe seleccionar el comprobante a acreditar.</strong><br><br>" +
                  "Para emitir una Nota de Crédito debe:<br>" +
                  "1. Hacer clic en el botón <strong>'Buscar Comprobante a Acreditar'</strong><br>" +
                  "2. Seleccionar el comprobante (Factura o Boleta) que desea acreditar<br>" +
                  "3. El sistema cargará automáticamente los datos del comprobante",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#3085d6",
            customClass: {
                popup: 'swal-wide'
            }
        });
        $("#btn_buscar_comprobante_nc").focus();
        return false;
    }

    // Validación 2: Verificar que se haya seleccionado un motivo (Catálogo 09 SUNAT)
    var nc_motivo = $("#nc_motivo").val();
    if (!nc_motivo || nc_motivo === "") {
        Swal.fire({
            title: "Motivo no seleccionado",
            html: "<strong>Debe seleccionar el motivo de la Nota de Crédito.</strong><br><br>" +
                  "Seleccione uno de los motivos del Catálogo 09 SUNAT:<br>" +
                  "• 01 - Anulación de la operación<br>" +
                  "• 02 - Anulación por error en el RUC<br>" +
                  "• 06 - Devolución total<br>" +
                  "• Etc.",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#3085d6",
            customClass: {
                popup: 'swal-wide'
            }
        });
        $("#nc_motivo").focus();
        return false;
    }

    // Validación 3: Verificar que haya al menos un item
    var itemsCount = $(".items-order .item-order").length;
    if (itemsCount === 0) {
        Swal.fire({
            title: "Sin items",
            html: "<strong>No hay items en la Nota de Crédito.</strong><br><br>" +
                  "La Nota de Crédito debe tener al menos un item del comprobante original.<br>" +
                  "Los items se cargan automáticamente al seleccionar el comprobante.",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#3085d6"
        });
        return false;
    }

    console.log("Validaciones NC completadas exitosamente:", {
        comprobante: nc_idcomprobante,
        motivo: nc_motivo,
        items: itemsCount
    });

    // ============================================================================
    // FIN VALIDACIONES NC
    // ============================================================================

    // Configurar tipo de documento según el tipo de cliente
    // Esto se usa para determinar el tipo de comprobante modificado (01 o 03)
    if (tipoDocCliente == "6") {
        // Cliente con RUC → NC para Factura (tipo_doc_mod = 01)
        $("#tipo_documento_06").val("01"); // Factura
    } else {
        // Cliente sin RUC → NC para Boleta (tipo_doc_mod = 03)
        $("#tipo_documento_06").val("03"); // Boleta
    }

    var numero_boleta = $("#numero_boleta").val();
    $("#numero_factura").val(numero_boleta);

    // Si es Nota de Crédito
    // NOTA: notacd.php maneja tanto Notas de Crédito como Notas de Débito
    // El tipo se determina por el parámetro tipodo en la URL
    url_pago = "../ajax/notacd.php?op=guardaryeditarnc&tipodo=" + $("#tipo_documento_06").val();
    // ============================================================================

  } else if (select_tipocomp == 5) {

    // ============================================================================
    // NOTA DE DÉBITO: Validaciones y configuración
    // ============================================================================
    var tipoDocCliente = $('#tipo_doc_ide').val();
    var numero_doc = $('#numero_documento').val();

    // Validar que se haya seleccionado un cliente
    if (!$("#idcliente").val() || $("#idcliente").val() == "N") {

        // Determinar el mensaje específico según el estado del formulario
        var mensajeDetallado = "";

        if (!numero_doc || numero_doc === "" || numero_doc === "-") {
            // Caso 1: No se ingresó ningún número de documento
            mensajeDetallado = "<strong>No ha ingresado un número de documento.</strong><br><br>" +
                              "Para emitir una Nota de Débito, debe:<br>" +
                              "1. Seleccionar el tipo de documento (DNI, RUC, etc.)<br>" +
                              "2. Ingresar el número de documento del cliente<br>" +
                              "3. El sistema buscará automáticamente los datos del cliente";
        } else {
            // Caso 2: Se ingresó documento pero no se buscó (usuario no presionó Enter ni salió del campo)
            mensajeDetallado = "<strong>Debe buscar los datos del cliente.</strong><br><br>" +
                              "Ha ingresado el número de documento: <strong>" + numero_doc + "</strong><br><br>" +
                              "Por favor, presione <strong>ENTER</strong> o haga clic fuera del campo " +
                              "para que el sistema busque automáticamente los datos del cliente.";
        }

        Swal.fire({
            title: "Cliente no seleccionado",
            html: mensajeDetallado,
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#dc3545",
            customClass: {
                popup: 'swal-wide'
            }
        });

        // Enfocar el campo de número de documento para que el usuario corrija
        $("#numero_documento").focus();
        return false;
    }

    // ============================================================================
    // VALIDAR CAMPOS ESPECÍFICOS DE NOTA DE DÉBITO
    // ============================================================================

    // Validación 1: Verificar que se haya seleccionado un comprobante de referencia
    var nd_idcomprobante = $("#nd_idcomprobante").val();
    if (!nd_idcomprobante || nd_idcomprobante === "") {
        Swal.fire({
            title: "Comprobante no seleccionado",
            html: "<strong>Debe seleccionar el comprobante a debitar.</strong><br><br>" +
                  "Para emitir una Nota de Débito debe:<br>" +
                  "1. Hacer clic en el botón <strong>'Buscar Comprobante a Debitar'</strong><br>" +
                  "2. Seleccionar el comprobante (Factura o Boleta) que desea debitar<br>" +
                  "3. El sistema cargará automáticamente los datos del comprobante",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#dc3545",
            customClass: {
                popup: 'swal-wide'
            }
        });
        $("#btn_buscar_comprobante_nd").focus();
        return false;
    }

    // Validación 2: Verificar que se haya seleccionado un motivo (Catálogo 10 SUNAT)
    var nd_motivo = $("#nd_motivo").val();
    if (!nd_motivo || nd_motivo === "") {
        Swal.fire({
            title: "Motivo no seleccionado",
            html: "<strong>Debe seleccionar el motivo de la Nota de Débito.</strong><br><br>" +
                  "Seleccione uno de los motivos del Catálogo 10 SUNAT:<br>" +
                  "• 01 - Intereses por mora<br>" +
                  "• 02 - Aumento en el valor<br>" +
                  "• 03 - Penalidades<br>" +
                  "• 04 - Otros conceptos",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#dc3545",
            customClass: {
                popup: 'swal-wide'
            }
        });
        $("#nd_motivo").focus();
        return false;
    }

    // Validación 3: Verificar que haya al menos un item
    var itemsCount = $(".items-order .item-order").length;
    if (itemsCount === 0) {
        Swal.fire({
            title: "Sin items",
            html: "<strong>No hay items en la Nota de Débito.</strong><br><br>" +
                  "La Nota de Débito debe tener al menos un item del comprobante original.<br>" +
                  "Los items se cargan automáticamente al seleccionar el comprobante.",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#dc3545"
        });
        return false;
    }

    console.log("Validaciones ND completadas exitosamente:", {
        comprobante: nd_idcomprobante,
        motivo: nd_motivo,
        items: itemsCount
    });

    // ============================================================================
    // FIN VALIDACIONES ND
    // ============================================================================

    // Configurar tipo de documento según el tipo de cliente
    // Esto se usa para determinar el tipo de comprobante modificado (01 o 03)
    if (tipoDocCliente == "6") {
        // Cliente con RUC → ND para Factura (tipo_doc_mod = 01)
        $("#tipo_documento_06").val("01"); // Factura
    } else {
        // Cliente sin RUC → ND para Boleta (tipo_doc_mod = 03)
        $("#tipo_documento_06").val("03"); // Boleta
    }

    var numero_boleta = $("#numero_boleta").val();
    $("#numero_factura").val(numero_boleta);

    // Si es Nota de Débito
    // NOTA: notacd.php maneja tanto Notas de Crédito como Notas de Débito
    // El tipo se determina por el parámetro tipodo en la URL
    url_pago = "../ajax/notacd.php?op=guardaryeditarnd&tipodo=" + $("#tipo_documento_06").val();
    // ============================================================================

  }

  Swal.fire({
    title: "¿Desea realizar el pedido?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, realizar pedido",
    cancelButtonText: "Cancelar",

  }).then((result) => {
    if (result.value) {
      capturarhora();

      //// SE ENVIO COMO TEXTO LOS MEDIOS DE PAGO !!!

      if (select_tipocomp == 1) {
        // var formData = new FormData();
        var formData = new FormData($("#formulario")[0]);

        // formData.append("serie", $('#serie').val());
        // formData.append("idnumeracion", $('#idnumeracion').val());
        // formData.append("SerieReal", $('input[name="SerieReal"]').val());

        // formData.append("numero_factura", $('input[name="numero_boleta"]').val());

        // formData.append("tipofactura", $('#tipoboleta').val());

        // formData.append("idfactura", $('input[name="idboleta"]').val());
        // formData.append("firma_digital", $('input[name="firma_digital_36"]').val());

        // formData.append("idempresa", $('input[name="idempresa"]').val());

        // formData.append("tipo_documento", '01');
        // formData.append("numeracion", $('input[name="numeracion_07"]').val());
        // formData.append("idpersona", $('input[name="idcliente"]').val());

        // formData.append("tipo_documento_cliente", $('input[name="tipo_documento_cliente"]').val());

        // formData.append("total_operaciones_gravadas_codigo", $('input[name="codigo_tipo_15_1"]').val());

        // formData.append("codigo_tributo_h", $('input[name="codigo_tributo_h"]').val());
        // formData.append("nombre_tributo_h", $('input[name="nombre_tributo_h"]').val());
        // formData.append("codigo_internacional_5", $('input[name="codigo_internacional_5"]').val());

        // formData.append("tipo_documento_guia", $('input[name="tipo_documento_25_1"]').val());
        // formData.append("codigo_leyenda_1", $('input[name="codigo_leyenda_26_1"]').val());
        // formData.append("version_ubl", $('input[name="version_ubl_37"]').val());
        // formData.append("version_estructura", $('input[name="version_estructura_38"]').val());

        // formData.append("tasa_igv", $('input[name="tasa_igv"]').val());

        // formData.append("codigo_precio", $('input[name="codigo_precio_14_1"]').val());

        // formData.append("hora", $('input[name="hora"]').val());

        // formData.append("fecha_emision", $('input[name="fecha_emision_01"]').val());

        // formData.append("fechavenc", $('input[name="fechavenc"]').val());

        // formData.append("tipo_moneda", $('#tipo_moneda_24').val());

        // formData.append("tcambio", $('input[name="tcambio"]').val());

        // formData.append("numero_documento2", $('input[name="numero_documento"]').val());
        // formData.append("razon_social2", $('input[name="razon_social"]').val());
        // formData.append("domicilio_fiscal2", $('input[name="domicilio_fiscal"]').val());

        // formData.append("vendedorsitio", $('#vendedorsitio').val());

        // formData.append("guia_remision_29_2", $('input[name="guia_remision_25"]').val());

        // formData.append("nombre_trixbuto_4_p", $('#codigo_tributo_18_3').val());
        // formData.append("descripcion_leyenda_2", $('#descripcion_leyenda_26_2').val());

        // formData.append("tipopago", $('#tipopago').val());

        // formData.append("ccuotas", $('input[name="ccuotas"]').val());
        // formData.append("tadc", $('input[name="tadc"]').val());
        // formData.append("trans", $('input[name="trans"]').val());
        // formData.append("itemno", $('input[name="itemno"]').val());

        // formData.append("codigob", $('input[name="codigob"]').val());

        // formData.append("correo", '');
        // formData.append("unidadMedida", 'original');
        // formData.append("afectacion_igv_3", '');
        // formData.append("afectacion_igv_4", '');
        // formData.append("afectacion_igv_5", '');
        // formData.append("afectacion_igv_6", '');
        // formData.append("iglobal", 18.00);
        // formData.append("correocli", '');



        // formData.append("numero_orden_item[]", $('input[name="numero_orden_item_29[]"]').val());

        // formData.append("idarticulo[]", $('input[name="idarticulo[]"]').val());
        // formData.append("codigotributo[]", $('input[name="codigotributo[]"]').val());
        // formData.append("afectacionigv[]", $('input[name="afectacionigv[]"]').val());

        // formData.append("cantidad[]", $('input[name="cantidad_item_12[]"]').val());

        // formData.append("descuento[]", $('input[name="descuento[]"]').val());
        // formData.append("sumadcto[]", $('input[name="sumadcto[]"]').val());

        // formData.append("codigo[]", $('input[name="codigo[]"]').val());
        // formData.append("unidad_medida[]", $('input[name="unidad_medida[]"]').val());

        // formData.append("valor_unitario[]", $('input[name="precio_unitario[]"]').val());
        // formData.append("valor_unitario2[]", $('input[name="valor_unitario[]"]').val());

        // formData.append("subtotalBD[]", $('input[name="subtotalBD[]"]').val());
        // formData.append("igvBD[]", $('input[name="igvBD[]"]').val());
        // formData.append("igvBD2[]", $('input[name="igvBD2[]"]').val());

        // formData.append("pvt[]", $('input[name="pvt[]"]').val());

        // formData.append("cicbper[]", $('input[name="cicbper[]"]').val());
        // formData.append("mticbperu[]", $('input[name="mticbperu[]"]').val());
        // formData.append("factorc[]", $('input[name="factorc[]"]').val());
        // formData.append("cantidadreal[]", $('input[name="cantidadreal[]"]').val());

        // formData.append("descdet_[]", '');
        // formData.append("descdet[]", 'DELICADO');


        // formData.append("subtotal_factura", $('input[name="subtotal_boleta"]').val());

        // formData.append("total_igv", $('input[name="total_igv"]').val());
        // formData.append("ipagado", $('input[name="ipagado"]').val());
        // formData.append("total_final", $('input[name="total_final"]').val());

        // formData.append("pre_v_u", $('input[name="pre_v_u"]').val());
        // formData.append("total_icbper", $('input[name="total_icbper"]').val());
        // formData.append("total_dcto", $('input[name="total_dcto"]').val());
        // formData.append("ipagado_final", $('input[name="ipagado_final"]').val());
        // formData.append("saldo_final", $('input[name="saldo_final"]').val());

        // formData.append("efectivo", $('input[name="efectivo"]').val());
        // formData.append("visa", $('input[name="visa"]').val());
        // formData.append("yape", $('input[name="yape"]').val());
        // formData.append("plin", $('input[name="plin"]').val());
        // formData.append("mastercard", $('input[name="mastercard"]').val());
        // formData.append("deposito", $('input[name="deposito"]').val());


      } else {
        var formData = new FormData($("#formulario")[0]);

      }

      console.log('formdata', formData);
      for (var pair of formData.entries()) {
        console.log(pair[0] + ', ' + pair[1]);
      }

      Swal.fire({
        title: 'Enviando',
        html: 'Espere un momento.',
        timer: 2000,
        timerProgressBar: true,
        didOpen: () => {
          Swal.showLoading()
        },
      })

      $.ajax({
        url: url_pago,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        timeout: 30000, // Timeout de 30 segundos

        success: function (response) {
          Swal.close();

          // VALIDACIÓN: Verificar que la respuesta indica éxito
          console.log('Respuesta del servidor:', response);

          // Verificar si la respuesta contiene palabras clave de error
          const responseStr = String(response).toLowerCase();
          const esError = responseStr.includes('error') ||
                         responseStr.includes('no se') ||
                         responseStr.includes('falló') ||
                         responseStr.includes('fallido') ||
                         responseStr.includes('token') ||
                         responseStr.includes('inválido');

          if (esError) {
            // La respuesta indica un error, mostrar como error
            console.error('Error detectado en respuesta del servidor:', response);

            Swal.fire({
              title: "Error al procesar la venta",
              html: `<div style="text-align: left;">
                      <p><strong>Mensaje del servidor:</strong></p>
                      <p>${response}</p>
                      <hr>
                      <p><small><strong>Detalles técnicos:</strong></small></p>
                      <ul style="font-size: 12px;">
                        <li>Tipo de comprobante: ${select_tipocomp == 0 ? 'Boleta' : select_tipocomp == 1 ? 'Factura' : 'Nota de Pedido'}</li>
                        <li>URL: ${url_pago}</li>
                        <li>Hora: ${new Date().toLocaleString()}</li>
                      </ul>
                    </div>`,
              icon: "error",
              confirmButtonText: "Entendido",
              allowOutsideClick: false
            });

            // NO limpiar formulario ni mostrar comprobante en caso de error
            return;
          }

          // Si llegamos aquí, la venta fue exitosa
          console.log('Venta procesada exitosamente');

          Swal.fire({
            title: "¡Venta Exitosa!",
            text: response,
            icon: "success",
            timer: 2000,
            showConfirmButton: false
          });

          // Cerrar modal de pago
          $('#modal_metodopago').modal('hide');

          // Limpiar formulario
          limpiarFormulario();

          // Mostrar comprobante SOLO si la venta fue exitosa
          tipoimpresion();

          // Actualizar lista de productos
          busqueda = '';
          listarProductos(busqueda);
        },

        error: function (xhr, status, error) {
          Swal.close();

          console.error('Error AJAX al procesar venta:', {
            status: status,
            error: error,
            xhr: xhr,
            url: url_pago,
            tipoComprobante: select_tipocomp,
            responseText: xhr.responseText
          });

          // Determinar mensaje de error según el tipo de fallo
          let mensajeError = '';
          let detallesTecnicos = '';

          if (status === 'timeout') {
            mensajeError = 'La solicitud tardó demasiado tiempo. El servidor no respondió en 30 segundos.';
            detallesTecnicos = '<li>Posible causa: Servidor lento o sobrecargado</li><li>Solución: Verifique su conexión a internet e intente nuevamente</li>';
          } else if (status === 'abort') {
            mensajeError = 'La solicitud fue cancelada.';
            detallesTecnicos = '<li>La operación fue interrumpida</li>';
          } else if (xhr.status === 0) {
            mensajeError = 'No se pudo conectar con el servidor.';
            detallesTecnicos = '<li>Posible causa: Sin conexión a internet</li><li>Posible causa: Servidor caído</li><li>Posible causa: Firewall bloqueando la conexión</li>';
          } else if (xhr.status === 404) {
            mensajeError = 'No se encontró el archivo del servidor (Error 404).';
            detallesTecnicos = `<li>URL solicitada: ${url_pago}</li><li>Verifique que el archivo PHP existe</li>`;
          } else if (xhr.status === 500) {
            mensajeError = 'Error interno del servidor (Error 500).';
            detallesTecnicos = '<li>Posible causa: Error de PHP en el servidor</li><li>Revise los logs del servidor</li>';
          } else if (xhr.status === 403) {
            mensajeError = 'Acceso denegado (Error 403).';
            detallesTecnicos = '<li>Posible causa: Sin permisos</li><li>Posible causa: Token CSRF inválido</li>';
          } else {
            mensajeError = `Error desconocido (Código ${xhr.status}).`;
            detallesTecnicos = `<li>Mensaje: ${error}</li><li>Estado: ${status}</li>`;
          }

          // Mostrar error detallado al usuario
          Swal.fire({
            title: "❌ Error al Guardar la Venta",
            html: `<div style="text-align: left;">
                    <p><strong>No se pudo completar la operación:</strong></p>
                    <p>${mensajeError}</p>
                    <hr>
                    <p><small><strong>Detalles técnicos:</strong></small></p>
                    <ul style="font-size: 12px;">
                      ${detallesTecnicos}
                      <li>Tipo de comprobante: ${select_tipocomp == 0 ? 'Boleta' : select_tipocomp == 1 ? 'Factura' : 'Nota de Pedido'}</li>
                      <li>URL: ${url_pago}</li>
                      <li>Hora: ${new Date().toLocaleString()}</li>
                    </ul>
                    <hr>
                    <p style="color: #d33;"><strong>⚠️ LA VENTA NO SE GUARDÓ</strong></p>
                    <p><small>Por favor, verifique los datos e intente nuevamente.</small></p>
                  </div>`,
            icon: "error",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#d33",
            allowOutsideClick: false,
            width: '600px'
          });

          // NO limpiar formulario para que el usuario pueda intentar nuevamente
          // NO cerrar modal de pago
          // NO mostrar comprobante porque la venta falló
        }
      });
    }
  });
})

/* ---------------------------------------------------------------- */
//                       FUNCION TIPO IMPRESION

function tipoimpresion() {

  if (select_tipocomp == 0) {

    // Si es boleta
    $.post(
      "../ajax/boleta.php?op=mostrarultimocomprobanteId",
      function (data, status) {
        data = JSON.parse(data);
        if (data != null) {
          $("#idultimocom").val(data.idboleta);
        } else {
          $("#idultimocom").val("");
        }

        if (data.tipoimpresion == "00") {
          var rutacarpeta = "../reportes/exTicketBoleta.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        } else if (data.tipoimpresion == "01") {
          var rutacarpeta = "../reportes/exBoleta.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        } else {
          var rutacarpeta =
            "../reportes/exBoletaCompleto.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        }
      }
    );

  } else if (select_tipocomp == 1) {

    // Si es Factura
    $.post(
      "../ajax/factura.php?op=mostrarultimocomprobanteId",
      function (data, status) {
        data = JSON.parse(data);
        if (data != null) {
          $("#idultimocom").val(data.idfactura);
        } else {
          $("#idultimocom").val("");
        }

        if (data.tipoimpresion == "00") {
          var rutacarpeta =
            "../reportes/exTicketFactura.php?id=" + data.idfactura;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        } else if (data.tipoimpresion == "01") {
          var rutacarpeta = "../reportes/exFactura.php?id=" + data.idfactura;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        } else {
          var rutacarpeta =
            "../reportes/exFacturaCompleto.php?id=" + data.idfactura;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        }
      }
    );

  } else if (select_tipocomp == 2) {

    // Si es Nota de Pedido
    $.post(
      "../ajax/notapedido.php?op=mostrarultimocomprobanteId",
      function (data, status) {
        data = JSON.parse(data);
        if (data != null) {
          $("#idultimocom").val(data.idboleta);
        } else {
          $("#idultimocom").val("");
        }

        if (data.tipoimpresion == "00") {
          var rutacarpeta =
            "../reportes/exNotapedidoTicket.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreviewXml").modal("show");
        } else if (data.tipoimpresion == "01") {
          var rutacarpeta = "../reportes/exNotapedido.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreviewXml").modal("show");
        } else {
          var rutacarpeta =
            "../reportes/exNotapedidocompleto.php?id=" + data.idboleta;
          $("#modalCom").attr("src", rutacarpeta);
          $("#modalPreview2").modal("show");
        }
      }
    );

  }


}


/* ---------------------------------------------------------------- */
//                    FUNCION LIMPIAR FORMULARIO

function limpiarFormulario() {
  // $('#d_tipocomprobante').val(0);
  listarTodosProductos();
  $('#search_product').val('');

  $('.items-order').html('');

  $("#idcliente").val("N");
  $("#idpersona").val("");

  // $("#tipo_doc_ide").val(0);
  // focusI()
  $("#numero_documento").val('');
  $("#numero_documento2").val('');

  $("#razon_social").val('');
  $("#razon_social2").val('');
  $("#domicilio_fiscal").val('');
  $("#domicilio_fiscal2").val('');

  $('#descripcion_leyenda_26_2').val('');
  $('#descripcion_leyenda_2').val('');
  obtenerSerie();

  modificarSubtotales();

  numeroOrden = 1;

  $('#efectivo').val();
  $('#visa').val();
  $('#yape').val();
  $('#plin').val();
  $('#mastercard').val();
  $('#deposito').val();

  sessionStorage.removeItem('miContenidoHTML');
}

/********************************************************************************/
/*                                  PRODUCTOS                                   */
/********************************************************************************/

$('#btn_modalproducto').click(function (e) {
  e.preventDefault();

  obtenerAlmacenProd();
  obtenerCategoriaProd();
  obtenerUnidMedidaProd();

  $('#modal_agregarproducto').modal('show');
})

/* ---------------------------------------------------------------- */
//               OBTENER ALMACEN (idalmacennarticulo)

function obtenerAlmacenProd() {

  $.post(
    "../ajax/articulo.php?op=selectAlmacen&idempresa=" + $idempresa,
    function (r) {
      $("#idalmacennarticulo").html(r);
      //$('#idalmacennarticulo').selectpicker('refresh');
    }
  );
}

/* ---------------------------------------------------------------- */
//               OBTENER CATEGORIA (idfamilianarticulo)
function obtenerCategoriaProd() {

  $.post("../ajax/articulo.php?op=selectFamilia", function (r) {
    $("#idfamilianarticulo").html(r);
    //$('#idfamilianarticulo').selectpicker('refresh');
  });

}

/* ---------------------------------------------------------------- */
//               OBTENER UNIDAD MEDIDA (umedidanp)

function obtenerUnidMedidaProd() {

  $.post("../ajax/factura.php?op=selectunidadmedidanuevopro", function (r) {
    $("#umedidanp").html(r);
    //$('#umedidanp').selectpicker('refresh');
  });

}

/* ---------------------------------------------------------------- */
//     FUNCION GENERAR CODIGO INTERNO (codigonarticulonarticulo)

function generarcodigonarti() {
  //alert("asdasdas");

  var caracteres1 = $("#nombrenarticulo").val();

  var codale = "";

  codale = caracteres1.substring(-3, 3);

  var caracteres2 = "ABCDEFGHJKMNPQRTUVWXYZ012346789";

  codale2 = "";

  for (i = 0; i < 3; i++) {
    var autocodigo = "";

    codale2 += caracteres2.charAt(
      Math.floor(Math.random() * caracteres2.length)
    );
  }

  $("#codigonarticulonarticulo").val(codale + codale2);
}



/* ---------------------------------------------------------------- */
//            FUNCION ACEPTAR NUM CON DOS DECIMALES

function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which;

  // if (e.keyCode === 13 && !e.shiftKey) {
  //   document.getElementById("precio_unitario[]").focus();
  // }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {
    if ($(field).val() === "") return true;

    var existePto = /[.]/.test($(field).val());

    if (existePto === false) {
      regexp = /.[0-9]{10}$/;
    } else {
      regexp = /.[0-9]{2}$/;
    }

    return !regexp.test($(field).val());
  }

  if (key == 46) {
    if (field.val() === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.val());
  }

  return false;
}

/* ---------------------------------------------------------------- */
//                EVENTO CLICK GUARDAR ARTICULO

$('#btn_savearticulo').click(function (e) {
  e.preventDefault();

  var formData = new FormData($("#formularionarticulo")[0]);

  $.ajax({
    url: "../ajax/articulo.php?op=guardarnuevoarticulo",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (datos) {
      Swal.fire({
        title: "Resultado",
        text: datos,
        icon: "success",
        confirmButtonText: "Aceptar",
      });

      $('#modal_agregarproducto').modal('hide');
      limpiarArticulo();
    },
  });
})

/* ---------------------------------------------------------------- */
//                  FUNCION LIMPIAR ARTICULO MODAL

function limpiarArticulo() {

  $("#nombrenarticulo").val("");
  $("#stocknarticulo").val("");
  $("#precioventanarticulo").val("");
  $("#codigonarticulonarticulo").val("");
  $("#descripcionnarticulo").val("");

}

$('#btn_preview').click(function () {
  // $('#modalPreview').modal('show');
  // PRUEBAS
})



/* ---------------------------------------------------------------- */
//                        Funcion redondeo

function redondeo(numero, decimales) {
  var flotante = parseFloat(numero);

  var resultado =
    Math.round(flotante * Math.pow(10, decimales)) / Math.pow(10, decimales);

  return resultado;
}


/********************************************************************************/
/*                            VENTAS COMPROBANTES                               */
/********************************************************************************/

function listarComprobante() {

  var tipocomprobante = $('#tipoComprobante').val();

  var tipo;

  if (tipocomprobante == 'recibo') {
    tipo = 'Boleta';
  } else if (tipocomprobante == 'factura') {
    tipo = 'Factura';
  } else if (tipocomprobante == 'nota') {
    tipo = 'NotaPedido';
  } else {
    tipo = 'Todos';

  }

  var table = $('#tbllistado').DataTable({
    bDestroy: true,
    iDisplayLength: 10, //Paginación
    order: [[0, "desc"]], //Ordenar (columna,orden)

    "ajax": {
      "url": urlconsumo + "pos.php?action=listarComprobantesVarios",
      "type": "POST",
      "headers": {
        "Content-Type": "application/json"
      },
      "data": function (d) {
        return JSON.stringify({
          "idempresa": $idempresa,
          "fechainicio": $('#fechaDesde').val(),
          "fechafinal": $('#fechaHasta').val(),
          "tipocomprobante": tipo
        });
      },
      "dataSrc": "ListaComprobantes"
    },
    "columns": [
      { "data": "id", "visible": false },
      { "data": "fecha" },
      { "data": "cliente" },
      {
        "data": "estado",
        "render": function (data, type, row) {
          if (type === 'display') {
            var displayText = '';
            var color = '';
            data = parseInt(data); // Convertimos data a número

            switch (data) {
              case 5:
                displayText = 'Aceptado';
                color = 'green';
                break;
              case 4:
                displayText = 'Enviando a sunat';
                color = 'orange';
                break;
              case 3:
                displayText = 'Anulado';
                color = 'red';
                break;
              case 0:
                displayText = 'Error Anular y Volverlo hacer';
                color = 'red';
                break;
              default:
                displayText = 'Otro Estado';
                break;
            }

            return '<span style="color:' + color + '">' + displayText + '</span>';
          } else {
            return data;  // En caso de ordenación, filtrado, etc., regresamos el dato original
          }
        }
      },
      { "data": "tipo_comprobante" },
      { "data": "producto" },
      { "data": "unidades_vendidas" },
      { "data": "total" }
    ]

  });


}


// $.ajax(settings).done(function (response) {
//   console.log(response);
// });

$('#btn_modalventas').click(function (e) {
  e.preventDefault();

  $('#tipoComprobante').val('recibo');
  $('#fechaDesde').val(today);
  $('#fechaHasta').val(today);

  listarComprobante();

})


$('#fechaDesde').change(function () {
  listarComprobante();
})
$('#fechaHasta').change(function () {
  listarComprobante();
})
$('#tipoComprobante').change(function () {
  listarComprobante();
})


/********************************************************************************/
/*                                 CLIENTES                                     */
/********************************************************************************/

function focusRsocial(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("razon_social").focus();
  }
}

function focusDomi(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("domicilio_fiscal").focus();
  }
}

function focustel(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("telefono1").focus();
  }
}

function focusemail(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("email").focus();
  }
}

function focusguardar(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("btnguardarncliente").focus();
  }
}

function focusemail(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("email").focus();
  }
}

function focusguardar(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("btnguardarncliente").focus();
  }
}

$.post("../ajax/persona.php?op=selectDepartamento", function (r) {
  $("#iddepartamento").html(r);
  //$('#iddepartamento').selectpicker('refresh');
});

function llenarCiudad() {
  var iddepartamento = $("#iddepartamento option:selected").val();
  $.post(
    "../ajax/persona.php?op=selectCiudad&id=" + iddepartamento,
    function (r) {
      $("#idciudad").html(r);
      //$('#idciudad').selectpicker('refresh');
      $("#idciudad").val("");
    }
  );
}

function llenarDistrito() {
  var idciudad = $("#idciudad option:selected").val();
  $.post("../ajax/persona.php?op=selectDistrito&id=" + idciudad, function (r) {
    $("#iddistrito").html(r);
    //$('#iddistrito').selectpicker('refresh');
  });
}

$("#formularioncliente").on("submit", function (e) {
  guardaryeditarcliente(e);
});

function guardaryeditarcliente(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento
  //$("#btnGuardarcliente").prop("disabled",true);
  var formData = new FormData($("#formularioncliente")[0]);

  $.ajax({
    url: "../ajax/persona.php?op=guardaryeditarNcliente",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      //bootbox.alert(datos);
      if (datos) {
        toastr.success("Cliente nuevo registrado");
      } else {
        toastr.danger("Problema al registrar");
      }

      limpiarcliente();
      agregarClientexRucNuevo();
    },
  });

  $('#container_datos').slideToggle();
  $("#ModalNcliente").modal("hide");
}

function agregarClientexRucNuevo() {
  $.post(
    "../ajax/factura.php?op=listarClientesfacturaxDocNuevos",
    function (data, status) {
      data = JSON.parse(data);

      if (data != null) {
        $("#numero_documento2").val(data.numero_documento);
        $("#idpersona").val(data.idpersona);
        $("#razon_social2").val(data.razon_social);
        $("#domicilio_fiscal2").val(data.domicilio_fiscal);
        $("#correocli").val(data.email);
        $("#tipo_documento_cliente").val(data.tipo_documento);
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#367fa9";
        document.getElementById("btnAgregarArt").focus();
      } else {
        $("#idpersona").val("");
        $("#razon_social2").val("No existe");
        $("#domicilio_fiscal2").val("No existe");
        $("#tipo_documento_cliente").val("");
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#35770c";
        document.getElementById("btnAgregarCli").focus();
      }
    }
  );
}

function limpiarcliente() {
  //NUEVO CLIENTE

  $("#numero_documento2").val("");
  $("#razon_social3").val("");
  $("#domicilio_fiscal3").val("");
  $("#iddepartamento").val("");
  $("#idciudad").val("");
  $("#iddistrito").val("");
  $("#telefono1").val("");
  $("#email").val("");
  $("#nruc").val("");
  $("#numero_documento3").val("");
  //=========================
}


/********************************************************************************/
/*           FUNCIONES PARA NOTA DE CRÉDITO - BÚSQUEDA Y CARGA DE COMPROBANTE   */
/********************************************************************************/

/**
 * Carga la lista de comprobantes disponibles para NC en el modal
 */
function cargarComprobantesNC() {
  console.log("Cargando comprobantes para NC...");

  var tipoFiltro = $("#filtro_tipo_comp_nc").val() || "";
  var fechaDesde = $("#filtro_fecha_desde_nc").val() || "";
  var fechaHasta = $("#filtro_fecha_hasta_nc").val() || "";

  // Mostrar spinner de carga
  $("#tbody_comprobantes_nc").html(`
    <tr>
      <td colspan="8" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Cargando comprobantes disponibles...</p>
      </td>
    </tr>
  `);

  $.ajax({
    url: "../ajax/pos.php?op=listarComprobantesParaNC",
    type: "POST",
    data: {
      tipo: tipoFiltro,
      fecha_desde: fechaDesde,
      fecha_hasta: fechaHasta
    },
    dataType: "json",
    success: function (response) {
      console.log("Comprobantes recibidos:", response);

      if (response.length === 0) {
        $("#tbody_comprobantes_nc").html(`
          <tr>
            <td colspan="8" class="text-center">
              <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
              <p>No se encontraron comprobantes disponibles</p>
              <small class="text-muted">Intente cambiar los filtros de búsqueda</small>
            </td>
          </tr>
        `);
        return;
      }

      var html = "";
      $.each(response, function (index, comp) {
        var tipoNombre = comp.tipo_comprobante == "01" ? "Factura" : "Boleta";
        var estadoBadge = comp.estado == "Aceptado"
          ? '<span class="badge bg-success">Aceptado</span>'
          : '<span class="badge bg-warning">Pendiente</span>';

        html += `
          <tr>
            <td>
              <button type="button" class="btn btn-sm btn-primary"
                      onclick="seleccionarComprobanteNC('${comp.idboleta || comp.idfactura}', '${comp.tipo_comprobante}')">
                <i class="fa fa-check"></i> Seleccionar
              </button>
            </td>
            <td>${tipoNombre}</td>
            <td>${comp.serie}-${comp.numero}</td>
            <td>${comp.fecha_emision}</td>
            <td>${comp.cliente}</td>
            <td>${comp.num_documento}</td>
            <td>S/ ${parseFloat(comp.total).toFixed(2)}</td>
            <td>${estadoBadge}</td>
          </tr>
        `;
      });

      $("#tbody_comprobantes_nc").html(html);
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar comprobantes:", error);
      Swal.fire({
        icon: "error",
        title: "Error al cargar comprobantes",
        text: "No se pudieron cargar los comprobantes. Por favor, intente nuevamente.",
        confirmButtonColor: "#3085d6"
      });

      $("#tbody_comprobantes_nc").html(`
        <tr>
          <td colspan="8" class="text-center text-danger">
            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
            <p>Error al cargar los comprobantes</p>
            <small>${error}</small>
          </td>
        </tr>
      `);
    }
  });
}

/**
 * Selecciona un comprobante y carga sus datos
 */
function seleccionarComprobanteNC(idcomprobante, tipo) {
  console.log("Seleccionando comprobante:", idcomprobante, "tipo:", tipo);

  // Mostrar indicador de carga
  Swal.fire({
    title: "Cargando datos del comprobante...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: "../ajax/pos.php?op=obtenerDatosComprobanteNC",
    type: "POST",
    data: {
      idcomprobante: idcomprobante,
      tipo_comprobante: tipo
    },
    dataType: "json",
    success: function (data) {
      console.log("Datos del comprobante recibidos:", data);

      if (data.error) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.error,
          confirmButtonColor: "#3085d6"
        });
        return;
      }

      // Cargar datos del comprobante
      cargarDatosComprobanteNC(data);

      // Cerrar modal de búsqueda
      $("#modalBuscarComprobante").modal("hide");

      // Mensaje de éxito
      Swal.fire({
        icon: "success",
        title: "Comprobante cargado",
        text: `Se cargó el comprobante ${data.serie}-${data.numero}`,
        timer: 2000,
        showConfirmButton: false
      });
    },
    error: function (xhr, status, error) {
      console.error("Error al obtener datos del comprobante:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron cargar los datos del comprobante. Por favor, intente nuevamente.",
        confirmButtonColor: "#3085d6"
      });
    }
  });
}

/**
 * Carga los datos del comprobante seleccionado en el formulario
 */
function cargarDatosComprobanteNC(data) {
  console.log("Cargando datos en el formulario...", data);

  // ============================================================================
  // 1. CARGAR DATOS DEL COMPROBANTE DE REFERENCIA
  // ============================================================================
  $("#nc_idcomprobante").val(data.idcomprobante);
  $("#nc_tipo_comprobante_mod").val(data.tipo_comprobante);
  $("#nc_serie_comprobante").val(data.serie);
  $("#nc_numero_comprobante").val(data.numero);
  $("#nc_comprobante_ref").val(data.serie + "-" + data.numero);
  $("#nc_fecha_comprobante").val(data.fecha_emision);

  // ============================================================================
  // 2. CARGAR DATOS DEL CLIENTE
  // ============================================================================
  $("#idcliente").val(data.idcliente);
  $("#tipo_doc_ide").val(data.tipo_documento);

  // Actualizar campos según tipo de documento
  if (data.tipo_documento == "6") {
    // RUC
    $("#numero_documento2").val(data.numero_documento);
    $("#razon_social2").val(data.razon_social);
    $("#domicilio_fiscal2").val(data.domicilio_fiscal);
  } else {
    // DNI u otros
    $("#numero_documento").val(data.numero_documento);
    $("#razon_social").val(data.razon_social);
    $("#domicilio_fiscal").val(data.domicilio_fiscal);
  }

  console.log("Cliente cargado:", data.razon_social);

  // ============================================================================
  // 3. CARGAR ITEMS DEL COMPROBANTE
  // ============================================================================
  if (data.items && data.items.length > 0) {
    // Limpiar área de items actual
    $(".items-order").html("");
    cont = 0; // Reiniciar contador

    $.each(data.items, function (index, item) {
      console.log("Cargando item:", item);

      // Construir URL de la imagen real del producto
      var productImage = "../../assets/images/no_img_avaliable.jpg"; // Imagen por defecto
      if (item.imagen && item.imagen.trim() !== "") {
        // Construir URL completa de la imagen del artículo
        productImage = "../files/articulos/" + item.imagen;
      }

      // Agregar item usando la función existente del POS
      // Nota: agregarProductPedido() recibe 9 parámetros posicionales
      agregarProductPedido(
        productImage,                                 // productImage (imagen real del artículo)
        item.descripcion,                             // productName
        parseFloat(item.precio_unitario),             // productPrice
        999,                                          // productStock (ficticio para NC)
        item.idarticulo,                              // productId
        item.codigo,                                  // productCod
        item.codigo,                                  // productCodProv (mismo que código)
        item.unidad_medida || "NIU",                  // productUM
        1                                             // productFactC (factor de conversión)
      );

      // Actualizar la cantidad del item recién agregado
      var cantidad = parseFloat(item.cantidad);
      var $lastCard = $('.items-order .card').last();

      if (cantidad !== 1) {
        $lastCard.find('input[name="cantidad_item_12[]"]').val(cantidad);
        $lastCard.find('input[name="cantidadreal[]"]').val(cantidad);
      }

      // Siempre recalcular totales para evitar valores undefined/NaN
      setTimeout(function() {
        modificarSubtotales();
      }, 100);
    });

    console.log("Items cargados:", data.items.length);
  }

  // ============================================================================
  // 4. ACTUALIZAR TOTALES
  // ============================================================================
  modificarSubtotales();

  console.log("Datos del comprobante cargados exitosamente");
}

// ============================================================================
// EVENT LISTENERS PARA MODAL Y BOTONES DE NC
// ============================================================================

// Cargar comprobantes cuando se abre el modal
$("#modalBuscarComprobante").on("show.bs.modal", function () {
  console.log("Modal de búsqueda de comprobantes abierto");
  cargarComprobantesNC();
});

// Botón de filtrar en el modal
$("#btn_filtrar_comprobantes_nc").on("click", function () {
  console.log("Aplicando filtros de búsqueda");
  cargarComprobantesNC();
});

// Permitir filtrar con Enter en los campos de fecha
$("#filtro_fecha_desde_nc, #filtro_fecha_hasta_nc").on("keypress", function (e) {
  if (e.which === 13) {
    e.preventDefault();
    cargarComprobantesNC();
  }
});

console.log("Funciones de Nota de Crédito cargadas correctamente");

/********************************************************************************/
/*           FUNCIONES PARA NOTA DE DÉBITO - BÚSQUEDA Y CARGA DE COMPROBANTE    */
/********************************************************************************/

/**
 * Carga la lista de comprobantes disponibles para ND en el modal
 */
function cargarComprobantesND() {
  console.log("Cargando comprobantes para ND...");

  var tipoFiltro = $("#filtro_tipo_comp_nd").val() || "";
  var fechaDesde = $("#filtro_fecha_desde_nd").val() || "";
  var fechaHasta = $("#filtro_fecha_hasta_nd").val() || "";

  // Mostrar spinner de carga
  $("#tbody_comprobantes_nd").html(`
    <tr>
      <td colspan="8" class="text-center">
        <div class="spinner-border text-danger" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Cargando comprobantes disponibles...</p>
      </td>
    </tr>
  `);

  $.ajax({
    url: "../ajax/pos.php?op=listarComprobantesParaND",
    type: "POST",
    data: {
      tipo: tipoFiltro,
      fecha_desde: fechaDesde,
      fecha_hasta: fechaHasta
    },
    dataType: "json",
    success: function (response) {
      console.log("Comprobantes recibidos:", response);

      if (response.length === 0) {
        $("#tbody_comprobantes_nd").html(`
          <tr>
            <td colspan="8" class="text-center">
              <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
              <p>No se encontraron comprobantes disponibles</p>
              <small class="text-muted">Intente cambiar los filtros de búsqueda</small>
            </td>
          </tr>
        `);
        return;
      }

      var html = "";
      $.each(response, function (index, comp) {
        var tipoNombre = comp.tipo_comprobante == "01" ? "Factura" : "Boleta";
        var estadoBadge = comp.estado == "Aceptado"
          ? '<span class="badge bg-success">Aceptado</span>'
          : '<span class="badge bg-warning">Pendiente</span>';

        html += `
          <tr>
            <td>
              <button type="button" class="btn btn-sm btn-danger"
                      onclick="seleccionarComprobanteND('${comp.idboleta || comp.idfactura}', '${comp.tipo_comprobante}')">
                <i class="fa fa-check"></i> Seleccionar
              </button>
            </td>
            <td>${tipoNombre}</td>
            <td>${comp.serie}-${comp.numero}</td>
            <td>${comp.fecha_emision}</td>
            <td>${comp.cliente}</td>
            <td>${comp.num_documento}</td>
            <td>S/ ${parseFloat(comp.total).toFixed(2)}</td>
            <td>${estadoBadge}</td>
          </tr>
        `;
      });

      $("#tbody_comprobantes_nd").html(html);
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar comprobantes:", error);
      Swal.fire({
        icon: "error",
        title: "Error al cargar comprobantes",
        text: "No se pudieron cargar los comprobantes. Por favor, intente nuevamente.",
        confirmButtonColor: "#dc3545"
      });

      $("#tbody_comprobantes_nd").html(`
        <tr>
          <td colspan="8" class="text-center text-danger">
            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
            <p>Error al cargar los comprobantes</p>
            <small>${error}</small>
          </td>
        </tr>
      `);
    }
  });
}

/**
 * Selecciona un comprobante y carga sus datos
 */
function seleccionarComprobanteND(idcomprobante, tipo) {
  console.log("Seleccionando comprobante:", idcomprobante, "tipo:", tipo);

  // Mostrar indicador de carga
  Swal.fire({
    title: "Cargando datos del comprobante...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: "../ajax/pos.php?op=obtenerDatosComprobanteND",
    type: "POST",
    data: {
      idcomprobante: idcomprobante,
      tipo_comprobante: tipo
    },
    dataType: "json",
    success: function (data) {
      console.log("Datos del comprobante recibidos:", data);

      if (data.error) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.error,
          confirmButtonColor: "#dc3545"
        });
        return;
      }

      // Cargar datos del comprobante
      cargarDatosComprobanteND(data);

      // Cerrar modal de búsqueda
      $("#modalBuscarComprobanteND").modal("hide");

      // Mensaje de éxito
      Swal.fire({
        icon: "success",
        title: "Comprobante cargado",
        text: `Se cargó el comprobante ${data.serie}-${data.numero}`,
        timer: 2000,
        showConfirmButton: false
      });
    },
    error: function (xhr, status, error) {
      console.error("Error al obtener datos del comprobante:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron cargar los datos del comprobante. Por favor, intente nuevamente.",
        confirmButtonColor: "#dc3545"
      });
    }
  });
}

/**
 * Carga los datos del comprobante seleccionado en el formulario
 */
function cargarDatosComprobanteND(data) {
  console.log("Cargando datos en el formulario...", data);

  // ============================================================================
  // 1. CARGAR DATOS DEL COMPROBANTE DE REFERENCIA
  // ============================================================================
  $("#nd_idcomprobante").val(data.idcomprobante);
  $("#nd_tipo_comprobante_mod").val(data.tipo_comprobante);
  $("#nd_serie_comprobante").val(data.serie);
  $("#nd_numero_comprobante").val(data.numero);
  $("#nd_comprobante_ref").val(data.serie + "-" + data.numero);
  $("#nd_fecha_comprobante").val(data.fecha_emision);

  // ============================================================================
  // 2. CARGAR DATOS DEL CLIENTE
  // ============================================================================
  $("#idcliente").val(data.idcliente);
  $("#tipo_doc_ide").val(data.tipo_documento);

  // Actualizar campos según tipo de documento
  if (data.tipo_documento == "6") {
    // RUC
    $("#numero_documento2").val(data.numero_documento);
    $("#razon_social2").val(data.razon_social);
    $("#domicilio_fiscal2").val(data.domicilio_fiscal);
  } else {
    // DNI u otros
    $("#numero_documento").val(data.numero_documento);
    $("#razon_social").val(data.razon_social);
    $("#domicilio_fiscal").val(data.domicilio_fiscal);
  }

  console.log("Cliente cargado:", data.razon_social);

  // ============================================================================
  // 3. CARGAR ITEMS DEL COMPROBANTE
  // ============================================================================
  if (data.items && data.items.length > 0) {
    // Limpiar área de items actual
    $(".items-order").html("");
    cont = 0; // Reiniciar contador

    $.each(data.items, function (index, item) {
      console.log("Cargando item:", item);

      // Agregar item usando la función existente del POS
      // Nota: agregarProductPedido() recibe 9 parámetros posicionales
      agregarProductPedido(
        "../../assets/images/no_img_avaliable.jpg",  // productImage (imagen por defecto que sí existe)
        item.descripcion,                             // productName
        parseFloat(item.precio_unitario),             // productPrice
        999,                                          // productStock (ficticio para ND)
        item.idarticulo,                              // productId
        item.codigo,                                  // productCod
        item.codigo,                                  // productCodProv (mismo que código)
        item.unidad_medida || "NIU",                  // productUM
        1                                             // productFactC (factor de conversión)
      );

      // Actualizar la cantidad del item recién agregado
      var cantidad = parseFloat(item.cantidad);
      var $lastCard = $('.items-order .card').last();

      if (cantidad !== 1) {
        $lastCard.find('input[name="cantidad_item_12[]"]').val(cantidad);
        $lastCard.find('input[name="cantidadreal[]"]').val(cantidad);
      }

      // Siempre recalcular totales para evitar valores undefined/NaN
      setTimeout(function() {
        modificarSubtotales();
      }, 100);
    });

    console.log("Items cargados:", data.items.length);
  }

  // ============================================================================
  // 4. ACTUALIZAR TOTALES
  // ============================================================================
  modificarSubtotales();

  console.log("Datos del comprobante cargados exitosamente");
}

// ============================================================================
// EVENT LISTENERS PARA MODAL Y BOTONES DE ND
// ============================================================================

// Cargar comprobantes cuando se abre el modal
$("#modalBuscarComprobanteND").on("show.bs.modal", function () {
  console.log("Modal de búsqueda de comprobantes ND abierto");
  cargarComprobantesND();
});

// Botón de filtrar en el modal
$("#btn_filtrar_comprobantes_nd").on("click", function () {
  console.log("Aplicando filtros de búsqueda para ND");
  cargarComprobantesND();
});

// Permitir filtrar con Enter en los campos de fecha
$("#filtro_fecha_desde_nd, #filtro_fecha_hasta_nd").on("keypress", function (e) {
  if (e.which === 13) {
    e.preventDefault();
    cargarComprobantesND();
  }
});

console.log("Funciones de Nota de Débito cargadas correctamente");

/********************************************************************************/
/*                   GUARDAR NOTA DE CRÉDITO Y NOTA DE DÉBITO                   */
/********************************************************************************/

/**
 * Guardar Nota de Crédito
 * Valida y envía los datos de la NC al servidor
 */
function guardarNotaCredito() {
  console.log("=== INICIANDO GUARDADO DE NOTA DE CRÉDITO ===");

  // Validar que haya un comprobante afectado seleccionado
  const idcomprobanteAfectado = $("#nc_idcomprobante").val();
  const tipoComprobanteAfectado = $("#nc_tipo_comprobante_mod").val();

  if (!idcomprobanteAfectado || !tipoComprobanteAfectado) {
    Swal.fire({
      icon: "warning",
      title: "Comprobante no seleccionado",
      text: "Por favor, seleccione un comprobante para generar la Nota de Crédito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Validar que haya items en el pedido
  if ($(".items-order .card").length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Sin items",
      text: "Debe haber al menos un producto en la Nota de Crédito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Validar motivo
  const codigoMotivo = $("#nc_motivo").val();
  const descripcionMotivo = $("#nc_descripcion").val();

  if (!codigoMotivo) {
    Swal.fire({
      icon: "warning",
      title: "Motivo requerido",
      text: "Por favor, seleccione un motivo para la Nota de Crédito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  if (!descripcionMotivo || descripcionMotivo.trim() === "") {
    Swal.fire({
      icon: "warning",
      title: "Descripción requerida",
      text: "Por favor, ingrese una descripción del motivo de la Nota de Crédito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Recolectar datos de items
  const idarticulo = [];
  const cantidad = [];
  const valorUnitario = [];
  const precioVenta = [];
  const afectacionIgv = [];
  const valorVenta = [];
  const igvItem = [];
  const totalItem = [];
  const unidadMedida = [];
  const codigoProducto = [];
  const descripcionItem = [];

  $(".items-order .card").each(function () {
    const $item = $(this);

    // Obtener valores de los campos del item
    idarticulo.push($item.find('input[name="idarticulo[]"]').val());
    cantidad.push($item.find('input[name="cantidad_item_12[]"]').val());

    // Calcular valores
    const precio = parseFloat($item.find('input[name="precio_unitario[]"]').val()) || 0;
    const cant = parseFloat($item.find('input[name="cantidad_item_12[]"]').val()) || 0;
    const igv = parseFloat($("#iva").val()) || 18;

    const valorUnit = precio / (1 + igv / 100);
    const valorVent = valorUnit * cant;
    const igvIt = valorVent * (igv / 100);
    const totalIt = valorVent + igvIt;

    valorUnitario.push(valorUnit.toFixed(5));
    precioVenta.push(precio.toFixed(5));
    afectacionIgv.push("10"); // 10 = Gravado - Operación Onerosa
    valorVenta.push(valorVent.toFixed(2));
    igvItem.push(igvIt.toFixed(2));
    totalItem.push(totalIt.toFixed(2));

    unidadMedida.push($item.find('input[name="unidad_medida[]"]').val() || "NIU");
    codigoProducto.push($item.find('input[name="codigo[]"]').val());
    descripcionItem.push($item.find('label[id="ped_name"]').text());
  });

  // Obtener totales del pedido desde los inputs hidden
  const totalOpGravadas = parseFloat($("#subtotal_factura").val()) || parseFloat($("#subtotal_boleta").val()) || 0;
  const sumatoriaIgv = parseFloat($("#total_igv").val()) || 0;
  const importeTotal = parseFloat($("#totalpagar").val()) || 0;

  // Preparar datos para enviar
  const formData = {
    idcomprobante_afectado: idcomprobanteAfectado,
    tipo_comprobante_afectado: tipoComprobanteAfectado,
    motivo_nota: descripcionMotivo,
    codigo_motivo: codigoMotivo,
    total_operaciones_gravadas: totalOpGravadas.toFixed(2),
    sumatoria_igv: sumatoriaIgv.toFixed(2),
    importe_total: importeTotal.toFixed(2),
    idarticulo: idarticulo,
    cantidad_item_12: cantidad,
    valor_uni_item_14: valorUnitario,
    precio_venta_item_15_2: precioVenta,
    afectacion_igv_item_16_1: afectacionIgv,
    valor_venta_item_32: valorVenta,
    igv_item: igvItem,
    total_item: totalItem,
    unidad_medida_item_13: unidadMedida,
    codigo_producto: codigoProducto,
    descripcion_item: descripcionItem
  };

  console.log("Datos a enviar:", formData);

  // Mostrar confirmación antes de guardar
  Swal.fire({
    title: "¿Confirmar Nota de Crédito?",
    html: `
      <div class="text-left">
        <p><strong>Comprobante afectado:</strong> ${$("#nc_serie_comprobante").val()}-${$("#nc_numero_comprobante").val()}</p>
        <p><strong>Motivo:</strong> ${descripcionMotivo}</p>
        <p><strong>Total:</strong> S/ ${importeTotal.toFixed(2)}</p>
        <p><strong>Items:</strong> ${idarticulo.length}</p>
      </div>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, guardar NC",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar loading
      Swal.fire({
        title: "Guardando Nota de Crédito...",
        text: "Por favor espere",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Enviar datos al servidor
      $.ajax({
        url: "../ajax/pos.php?op=guardarNotaCredito",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          console.log("Respuesta del servidor:", response);

          if (response.success) {
            // Mostrar PDF en modal (igual que facturas/boletas) con parámetro tipodoc
            var tipodoc = $("#nc_tipo_comprobante_afectado").val(); // '01' = Factura, '03' = Boleta
            var rutacarpeta = "../reportes/exNcredito_new.php?id=" + response.idnota_credito + "&tipodoc=" + tipodoc;
            $("#modalCom").attr("src", rutacarpeta);
            $("#modalPreview2").modal("show");

            Swal.fire({
              icon: "success",
              title: "¡Nota de Crédito guardada!",
              html: `
                <p>La Nota de Crédito se registró correctamente</p>
                <p><strong>Número:</strong> ${response.numeracion}</p>
              `,
              confirmButtonColor: "#28a745"
            }).then(() => {
              // Limpiar formulario y recargar
              limpiarFormularioNC();
              location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error al guardar",
              text: response.message || "No se pudo guardar la Nota de Crédito",
              confirmButtonColor: "#dc3545"
            });
          }
        },
        error: function (xhr, status, error) {
          console.error("Error AJAX:", error);
          console.error("Response:", xhr.responseText);

          Swal.fire({
            icon: "error",
            title: "Error de comunicación",
            text: "No se pudo conectar con el servidor. Por favor, intente nuevamente.",
            confirmButtonColor: "#dc3545"
          });
        }
      });
    }
  });
}

/**
 * Guardar Nota de Débito
 * Valida y envía los datos de la ND al servidor
 */
function guardarNotaDebito() {
  console.log("=== INICIANDO GUARDADO DE NOTA DE DÉBITO ===");

  // Validar que haya un comprobante afectado seleccionado
  const idcomprobanteAfectado = $("#nd_idcomprobante").val();
  const tipoComprobanteAfectado = $("#nd_tipo_comprobante_mod").val();

  if (!idcomprobanteAfectado || !tipoComprobanteAfectado) {
    Swal.fire({
      icon: "warning",
      title: "Comprobante no seleccionado",
      text: "Por favor, seleccione un comprobante para generar la Nota de Débito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Validar que haya items en el pedido
  if ($(".items-order .card").length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Sin items",
      text: "Debe haber al menos un concepto en la Nota de Débito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Validar motivo
  const codigoMotivo = $("#nd_motivo").val();
  const descripcionMotivo = $("#nd_descripcion").val();

  if (!codigoMotivo) {
    Swal.fire({
      icon: "warning",
      title: "Motivo requerido",
      text: "Por favor, seleccione un motivo para la Nota de Débito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  if (!descripcionMotivo || descripcionMotivo.trim() === "") {
    Swal.fire({
      icon: "warning",
      title: "Descripción requerida",
      text: "Por favor, ingrese una descripción del motivo de la Nota de Débito",
      confirmButtonColor: "#3085d6"
    });
    return;
  }

  // Recolectar datos de items
  const idarticulo = [];
  const cantidad = [];
  const valorUnitario = [];
  const precioVenta = [];
  const afectacionIgv = [];
  const valorVenta = [];
  const igvItem = [];
  const totalItem = [];
  const unidadMedida = [];
  const codigoProducto = [];
  const descripcionItem = [];

  $(".items-order .card").each(function () {
    const $item = $(this);

    // Obtener valores de los campos del item
    idarticulo.push($item.find('input[name="idarticulo[]"]').val());
    cantidad.push($item.find('input[name="cantidad_item_12[]"]').val());

    // Calcular valores
    const precio = parseFloat($item.find('input[name="precio_unitario[]"]').val()) || 0;
    const cant = parseFloat($item.find('input[name="cantidad_item_12[]"]').val()) || 0;
    const igv = parseFloat($("#iva").val()) || 18;

    const valorUnit = precio / (1 + igv / 100);
    const valorVent = valorUnit * cant;
    const igvIt = valorVent * (igv / 100);
    const totalIt = valorVent + igvIt;

    valorUnitario.push(valorUnit.toFixed(5));
    precioVenta.push(precio.toFixed(5));
    afectacionIgv.push("10"); // 10 = Gravado - Operación Onerosa
    valorVenta.push(valorVent.toFixed(2));
    igvItem.push(igvIt.toFixed(2));
    totalItem.push(totalIt.toFixed(2));

    unidadMedida.push($item.find('input[name="unidad_medida[]"]').val() || "NIU");
    codigoProducto.push($item.find('input[name="codigo[]"]').val());
    descripcionItem.push($item.find('label[id="ped_name"]').text());
  });

  // Obtener totales del pedido desde los inputs hidden
  const totalOpGravadas = parseFloat($("#subtotal_factura").val()) || parseFloat($("#subtotal_boleta").val()) || 0;
  const sumatoriaIgv = parseFloat($("#total_igv").val()) || 0;
  const importeTotal = parseFloat($("#totalpagar").val()) || 0;

  // Preparar datos para enviar
  const formData = {
    idcomprobante_afectado: idcomprobanteAfectado,
    tipo_comprobante_afectado: tipoComprobanteAfectado,
    motivo_nota: descripcionMotivo,
    codigo_motivo: codigoMotivo,
    total_operaciones_gravadas: totalOpGravadas.toFixed(2),
    sumatoria_igv: sumatoriaIgv.toFixed(2),
    importe_total: importeTotal.toFixed(2),
    idarticulo: idarticulo,
    cantidad_item_12: cantidad,
    valor_uni_item_14: valorUnitario,
    precio_venta_item_15_2: precioVenta,
    afectacion_igv_item_16_1: afectacionIgv,
    valor_venta_item_32: valorVenta,
    igv_item: igvItem,
    total_item: totalItem,
    unidad_medida_item_13: unidadMedida,
    codigo_producto: codigoProducto,
    descripcion_item: descripcionItem
  };

  console.log("Datos a enviar:", formData);

  // Mostrar confirmación antes de guardar
  Swal.fire({
    title: "¿Confirmar Nota de Débito?",
    html: `
      <div class="text-left">
        <p><strong>Comprobante afectado:</strong> ${$("#nd_serie_comprobante").val()}-${$("#nd_numero_comprobante").val()}</p>
        <p><strong>Motivo:</strong> ${descripcionMotivo}</p>
        <p><strong>Total:</strong> S/ ${importeTotal.toFixed(2)}</p>
        <p><strong>Items:</strong> ${idarticulo.length}</p>
      </div>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, guardar ND",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar loading
      Swal.fire({
        title: "Guardando Nota de Débito...",
        text: "Por favor espere",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Enviar datos al servidor
      $.ajax({
        url: "../ajax/pos.php?op=guardarNotaDebito",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          console.log("Respuesta del servidor:", response);

          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "¡Nota de Débito guardada!",
              html: `
                <p>La Nota de Débito se registró correctamente</p>
                <p><strong>Número:</strong> ${response.numeracion}</p>
              `,
              confirmButtonColor: "#dc3545"
            }).then(() => {
              // Limpiar formulario y recargar
              limpiarFormularioND();
              location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error al guardar",
              text: response.message || "No se pudo guardar la Nota de Débito",
              confirmButtonColor: "#dc3545"
            });
          }
        },
        error: function (xhr, status, error) {
          console.error("Error AJAX:", error);
          console.error("Response:", xhr.responseText);

          Swal.fire({
            icon: "error",
            title: "Error de comunicación",
            text: "No se pudo conectar con el servidor. Por favor, intente nuevamente.",
            confirmButtonColor: "#dc3545"
          });
        }
      });
    }
  });
}

/**
 * Limpiar formulario de Nota de Crédito
 */
function limpiarFormularioNC() {
  $("#nc_idcomprobante").val("");
  $("#nc_tipo_comprobante_mod").val("");
  $("#nc_serie_comprobante").val("");
  $("#nc_numero_comprobante").val("");
  $("#nc_comprobante_ref").val("");
  $("#nc_fecha_comprobante").val("");
  $("#nc_motivo").val("");
  $("#nc_descripcion").val("");
  $(".items-order").html("");
  updateTotals(); // Función correcta para recalcular totales
}

/**
 * Limpiar formulario de Nota de Débito
 */
function limpiarFormularioND() {
  $("#nd_idcomprobante").val("");
  $("#nd_tipo_comprobante_mod").val("");
  $("#nd_serie_comprobante").val("");
  $("#nd_numero_comprobante").val("");
  $("#nd_comprobante_ref").val("");
  $("#nd_fecha_comprobante").val("");
  $("#nd_motivo").val("");
  $("#nd_descripcion").val("");
  $(".items-order").html("");
  updateTotals(); // Función correcta para recalcular totales
}

console.log("Funciones de guardado NC/ND cargadas correctamente");

/********************************************************************************/
/*                        FUNCIONES RECARGA DE PAGINA                           */
/********************************************************************************/

// window.addEventListener("beforeunload", function (e) {
//   // Mensaje de confirmación personalizado
//   var confirmationMessage = "¿Estás seguro de que deseas recargar la página?";

//   // Activa la alerta solo si se cumple cierta condición
//   if ($('.items-order').html() !== '') {
//     (e || window.event).returnValue = confirmationMessage;
//     return confirmationMessage;
//   }
// });

// // Detección de recarga en dispositivos móviles
// window.addEventListener("pagehide", function (event) {
//   // Mensaje de advertencia
//   var warningMessage = "Recargar la página podría causar pérdida de datos.";

//   if ($('.items-order').html() !== '') {

//     // Muestra un mensaje en la página
//     alert(warningMessage);
//   }

// });