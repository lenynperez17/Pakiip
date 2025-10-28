
"use strict";
(() => {
  var navbar = document.getElementById("sidebar");

  // Solo ejecutar si el elemento sidebar existe
  if (navbar) {
    var sticky = navbar.offsetTop;

    function stickyFn() {
      if (window.scrollY >= 75) {
        navbar.classList.add("sticky-pin")
      } else {
        navbar.classList.remove("sticky-pin");
      }
    }

    window.addEventListener('scroll', stickyFn);
    window.addEventListener('DOMContentLoaded', stickyFn);
  }
})();

