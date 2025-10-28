// const multipleEvents = (element, eventNames, listener) => {
//     const events = eventNames.split(' ');

//     events.forEach(event => {
//         element.addEventListener(event, listener, false);
//     });
// };

// const fileUpload = () => {
//     const INPUT_FILE = document.querySelector('#upload-files');
//     const INPUT_CONTAINER = document.querySelector('#upload-container');

//     multipleEvents(INPUT_FILE, 'click dragstart dragover', () => {
//         INPUT_CONTAINER.classList.add('active');
//     });

//     multipleEvents(INPUT_FILE, 'dragleave dragend drop change', () => {
//         INPUT_CONTAINER.classList.remove('active');
//     });

//     INPUT_FILE.addEventListener('change', () => {
//         const files = [...INPUT_FILE.files];

//         if (files.length > 0) {
//             const file = files[0];
//             const fileName = file.name;
//             const fileExtension = fileName.split(".").pop().toLowerCase();

//             if (['xls', 'xlsx', 'xlsm', 'csv'].includes(fileExtension)) {
//                 INPUT_CONTAINER.textContent = "";
//                 const iconHTML = `<img src="../files/iconos/excel.png" alt="Icono Excel" class="icon-excel">`;
//                 const content = `
//                     <div class="form__files-container">
//                         ${iconHTML}
//                         <span class="form__text">${fileName}</span>
//                         <div class="barra-cargado"></div>
//                     </div>
//                 `;

//                 INPUT_CONTAINER.insertAdjacentHTML('beforeEnd', content);
//             } else {
//                 Swal.fire({
//                     icon: 'error',
//                     title: 'Error',
//                     text: 'Por favor, selecciona un archivo válido de tipo Excel.'
//                 });
//                 INPUT_FILE.value = '';
//             }
//         } else {
//             INPUT_CONTAINER.textContent = "Elija o Arrastre su Archivo";
//         }
//     });
// };

// fileUpload();

// // Manejo del envío del formulario
// document.getElementById('formularioImportar').addEventListener('submit', function(e) {
//     e.preventDefault();

//     const form = e.target; // Obtener una referencia al formulario
//     const data = new FormData(form);

//     fetch(form.action, {
//         method: 'POST',
//         body: data
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             Swal.fire({
//                 icon: 'success',
//                 title: '¡Éxito!',
//                 text: 'Los datos se han importado correctamente.'
//             });
//         } else {
//             Swal.fire({
//                 icon: 'error',
//                 title: 'Error',
//                 text: data.message || 'Ocurrió un error al importar los datos.'
//             });
//         }
//     })
//     .catch(err => {
//         Swal.fire({
//             icon: 'error',
//             title: 'Error',
//             text: 'Ocurrió un error al enviar el archivo.'
//         });
//     });
// });
