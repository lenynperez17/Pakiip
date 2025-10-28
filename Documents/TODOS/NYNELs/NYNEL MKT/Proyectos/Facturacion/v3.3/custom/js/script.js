// preloader
$(document).ready(function () {
    setInterval(function () {
        $(".loader").hide();
        $(".loader-overlay").hide();
    }, 700);

    //sidebar toggle
    $("#sidebar-toggle, .sidebar-overlay").click(function () {
        $(".sidebar").toggleClass("sidebar-show");
        $(".sidebar-overlay").toggleClass("d-block");
    });
});

// Guarda la posición de la barra de desplazamiento en el almacenamiento local antes de recargar la página
window.addEventListener("beforeunload", () => {
	const sidebar = document.querySelector(".sidebar");
	localStorage.setItem("scrollPos", sidebar.scrollTop);
  });
  
  // Después de recargar la página, restablece la posición de la barra de desplazamiento al valor guardado
  window.addEventListener("load", () => {
	const sidebar = document.querySelector(".sidebar");
	const savedScrollPos = localStorage.getItem("scrollPos");
  
	if (savedScrollPos) {
	  sidebar.scrollTop = savedScrollPos;
	  localStorage.removeItem("scrollPos");  // Borra el valor guardado después de usarlo
	}
  });
  

// sidebar menu dropdown
const allDropdown = document.querySelectorAll('#sidebar .side-dropdown');
const allMenuLinks = Array.from(document.querySelectorAll('#sidebar .side-menu a'));
const sidebar = document.getElementById('sidebar');
const currentURL = window.location.pathname.split("/").pop();

// Check if the current URL matches any link in the main menu
allMenuLinks.forEach(link => {
    if (link.getAttribute('href') === currentURL) {
        link.classList.add('active');
    }
});

allDropdown.forEach(item => {
    const a = item.parentElement.querySelector('a:first-child');
    const dropdownLinks = Array.from(item.querySelectorAll('a'));

    // Check if the current URL matches any link in the dropdown
    const isCurrentPageInDropdown = dropdownLinks.some(link => link.getAttribute('href') === currentURL);

    // If the current page is in the dropdown, expand it and set the link to active
    if (isCurrentPageInDropdown) {
        a.classList.add('active');
        item.classList.add('show');

        // Find the active link in the dropdown and set it to active
        dropdownLinks.forEach(link => {
            if (link.getAttribute('href') === currentURL) {
                link.classList.add('active');
            }
        });
    }

    a.addEventListener('click', function (e) {
        e.preventDefault();

        // Close all other dropdowns
        allDropdown.forEach(i => {
            if (i !== item) {
                const aLink = i.parentElement.querySelector('a:first-child');
                aLink.classList.remove('active');
                i.classList.remove('show');
            }
        });

        // Toggle the clicked dropdown
        this.classList.toggle('active');
        item.classList.toggle('show');
    });
});