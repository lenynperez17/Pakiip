// function checkFullscreen() {
//   if (sessionStorage.getItem('fullscreen') === 'true') {
//     toggleFullscreen();
//   }
// }



// window.onload = checkFullscreen;


// function toggleFullscreen(elem) {
//   elem = elem || document.documentElement;
//   if (!document.fullscreenElement && !document.mozFullScreenElement &&
//     !document.webkitFullscreenElement && !document.msFullscreenElement) {
//     if (elem.requestFullscreen) {
//       elem.requestFullscreen();
//     } else if (elem.msRequestFullscreen) {
//       elem.msRequestFullscreen();
//     } else if (elem.mozRequestFullScreen) {
//       elem.mozRequestFullScreen();
//     } else if (elem.webkitRequestFullscreen) {
//       elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
//     }
//     fullscreen = true;
//   } else {
//     if (document.exitFullscreen) {
//       document.exitFullscreen();
//     } else if (document.msExitFullscreen) {
//       document.msExitFullscreen();
//     } else if (document.mozCancelFullScreen) {
//       document.mozCancelFullScreen();
//     } else if (document.webkitExitFullscreen) {
//       document.webkitExitFullscreen();
//     }
//     fullscreen = false;
//   }
//   sessionStorage.setItem('fullscreen', fullscreen);
// }
