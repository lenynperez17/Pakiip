const wrapper = document.querySelector('.wrapper');
const registerLink = document.querySelector('.register-link');
const loginLink = document.querySelector('.login-link');

// Validar que los elementos existan antes de asignar eventos
if (registerLink) {
    registerLink.onclick = () => {
        wrapper.classList.add('active');
    }
}

if (loginLink) {
    loginLink.onclick = () => {
        wrapper.classList.remove('active');
    }
}