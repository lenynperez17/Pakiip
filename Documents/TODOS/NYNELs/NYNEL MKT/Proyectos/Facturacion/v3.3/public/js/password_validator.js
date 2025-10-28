/**
 * VALIDADOR DE CONTRASEÑAS EN FRONTEND
 * Sistema de Facturación v3.3
 *
 * Valida contraseñas en tiempo real y muestra retroalimentación visual
 */

// Configuración de la política (debe coincidir con password_policy.php)
const PASSWORD_POLICY = {
    minLength: 12,
    requireUppercase: true,
    requireLowercase: true,
    requireNumbers: true,
    requireSymbols: true
};

// Contraseñas comunes prohibidas (lista reducida para frontend)
const COMMON_PASSWORDS = [
    'password', 'password123', '12345678', 'qwerty123', 'abc12345',
    'admin123', 'letmein', 'welcome123', 'factura123', 'sistema123'
];

/**
 * Valida una contraseña según la política
 * @param {string} password - Contraseña a validar
 * @returns {Object} Resultado de validación
 */
function validatePassword(password) {
    const errors = [];
    let strength = 0;

    // 1. Validar longitud mínima
    if (password.length < PASSWORD_POLICY.minLength) {
        errors.push(`Debe tener al menos ${PASSWORD_POLICY.minLength} caracteres`);
    } else {
        strength += 20;
    }

    // 2. Validar mayúsculas
    if (PASSWORD_POLICY.requireUppercase && !/[A-Z]/.test(password)) {
        errors.push('Debe contener al menos una letra mayúscula');
    } else {
        strength += 15;
    }

    // 3. Validar minúsculas
    if (PASSWORD_POLICY.requireLowercase && !/[a-z]/.test(password)) {
        errors.push('Debe contener al menos una letra minúscula');
    } else {
        strength += 15;
    }

    // 4. Validar números
    if (PASSWORD_POLICY.requireNumbers && !/[0-9]/.test(password)) {
        errors.push('Debe contener al menos un número');
    } else {
        strength += 15;
    }

    // 5. Validar símbolos
    if (PASSWORD_POLICY.requireSymbols && !/[^a-zA-Z0-9]/.test(password)) {
        errors.push('Debe contener al menos un símbolo especial (!@#$%^&*)');
    } else {
        strength += 15;
    }

    // 6. Verificar contraseñas comunes
    const passwordLower = password.toLowerCase();
    for (const common of COMMON_PASSWORDS) {
        if (passwordLower.includes(common.toLowerCase())) {
            errors.push('La contraseña es demasiado común');
            strength = Math.max(0, strength - 30);
            break;
        }
    }

    // 7. Detectar patrones secuenciales
    if (hasSequentialPatterns(password)) {
        errors.push('Evita patrones secuenciales (123, abc, etc.)');
        strength = Math.max(0, strength - 20);
    }

    // 8. Bonificación por longitud extra
    if (password.length >= 16) strength += 10;
    if (password.length >= 20) strength += 10;

    // Limitar fortaleza a 100
    strength = Math.min(100, strength);

    // Clasificar nivel de fortaleza
    let strengthLevel = 'Muy débil';
    let strengthColor = '#dc3545'; // Rojo

    if (strength >= 80) {
        strengthLevel = 'Muy fuerte';
        strengthColor = '#28a745'; // Verde
    } else if (strength >= 60) {
        strengthLevel = 'Fuerte';
        strengthColor = '#5cb85c'; // Verde claro
    } else if (strength >= 40) {
        strengthLevel = 'Media';
        strengthColor = '#ffc107'; // Amarillo
    } else if (strength >= 20) {
        strengthLevel = 'Débil';
        strengthColor = '#fd7e14'; // Naranja
    }

    return {
        valid: errors.length === 0,
        errors: errors,
        strength: strength,
        strengthLevel: strengthLevel,
        strengthColor: strengthColor
    };
}

/**
 * Detecta patrones secuenciales en la contraseña
 * @param {string} password - Contraseña a analizar
 * @returns {boolean} True si contiene patrones secuenciales
 */
function hasSequentialPatterns(password) {
    const passwordLower = password.toLowerCase();
    const patterns = [
        '123', '234', '345', '456', '567', '678', '789',
        'abc', 'bcd', 'cde', 'def', 'efg', 'fgh',
        'qwe', 'wer', 'ert', 'rty', 'tyu', 'yui',
        'asd', 'sdf', 'dfg', 'fgh', 'ghj', 'hjk'
    ];

    for (const pattern of patterns) {
        if (passwordLower.includes(pattern)) {
            return true;
        }
    }

    return false;
}

/**
 * Genera una contraseña aleatoria segura
 * @param {number} length - Longitud de la contraseña (default: 12)
 * @returns {string} Contraseña generada
 */
function generateSecurePassword(length = PASSWORD_POLICY.minLength) {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    let password = '';
    const allChars = uppercase + lowercase + numbers + symbols;

    // Garantizar al menos un carácter de cada tipo
    if (PASSWORD_POLICY.requireUppercase) {
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
    }
    if (PASSWORD_POLICY.requireLowercase) {
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
    }
    if (PASSWORD_POLICY.requireNumbers) {
        password += numbers[Math.floor(Math.random() * numbers.length)];
    }
    if (PASSWORD_POLICY.requireSymbols) {
        password += symbols[Math.floor(Math.random() * symbols.length)];
    }

    // Completar el resto
    const remaining = length - password.length;
    for (let i = 0; i < remaining; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }

    // Mezclar caracteres
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

/**
 * Inicializa el validador en un campo de contraseña
 * @param {string} inputId - ID del input de contraseña
 * @param {string} feedbackId - ID del contenedor de retroalimentación
 */
function initPasswordValidator(inputId, feedbackId) {
    const input = document.getElementById(inputId);
    const feedback = document.getElementById(feedbackId);

    if (!input || !feedback) {
        console.error('No se encontraron los elementos de validación');
        return;
    }

    // Validar en tiempo real
    input.addEventListener('input', function() {
        const password = this.value;
        const result = validatePassword(password);

        // Mostrar errores
        if (password.length === 0) {
            feedback.innerHTML = '';
            feedback.className = '';
            return;
        }

        let html = '';

        // Barra de fortaleza
        html += `
            <div class="password-strength-bar mb-2">
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar"
                         style="width: ${result.strength}%; background-color: ${result.strengthColor};"
                         aria-valuenow="${result.strength}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <small class="text-muted">Fortaleza: <strong style="color: ${result.strengthColor};">${result.strengthLevel}</strong> (${result.strength}/100)</small>
            </div>
        `;

        // Mostrar errores
        if (result.errors.length > 0) {
            html += '<ul class="list-unstyled mb-0">';
            result.errors.forEach(error => {
                html += `<li class="text-danger"><i class="fa fa-times-circle"></i> ${error}</li>`;
            });
            html += '</ul>';
        } else {
            html += '<p class="text-success mb-0"><i class="fa fa-check-circle"></i> ¡Contraseña válida!</p>';
        }

        feedback.innerHTML = html;
        feedback.className = 'password-feedback mt-2';
    });

    // Botón para generar contraseña (si existe)
    const generateBtn = document.getElementById(inputId + '_generate');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            input.value = generateSecurePassword();
            input.dispatchEvent(new Event('input'));

            // Mostrar contraseña generada
            if (input.type === 'password') {
                input.type = 'text';
                setTimeout(() => {
                    if (confirm('¿Copiar contraseña al portapapeles?')) {
                        navigator.clipboard.writeText(input.value).then(() => {
                            alert('Contraseña copiada al portapapeles');
                        });
                    }
                    input.type = 'password';
                }, 3000);
            }
        });
    }

    // Botón para mostrar/ocultar contraseña
    const toggleBtn = document.getElementById(inputId + '_toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            this.innerHTML = type === 'password' ?
                '<i class="fa fa-eye"></i>' :
                '<i class="fa fa-eye-slash"></i>';
        });
    }
}

/**
 * Obtiene requisitos de contraseña como HTML
 * @returns {string} HTML con lista de requisitos
 */
function getPasswordRequirementsHTML() {
    let html = '<h6 class="mb-2">Requisitos de contraseña:</h6><ul class="small">';

    html += `<li>Mínimo ${PASSWORD_POLICY.minLength} caracteres</li>`;

    if (PASSWORD_POLICY.requireUppercase) {
        html += '<li>Al menos una letra mayúscula (A-Z)</li>';
    }
    if (PASSWORD_POLICY.requireLowercase) {
        html += '<li>Al menos una letra minúscula (a-z)</li>';
    }
    if (PASSWORD_POLICY.requireNumbers) {
        html += '<li>Al menos un número (0-9)</li>';
    }
    if (PASSWORD_POLICY.requireSymbols) {
        html += '<li>Al menos un símbolo especial (!@#$%^&*)</li>';
    }

    html += '<li>No usar contraseñas comunes</li>';
    html += '<li>Evitar patrones secuenciales (123, abc, etc.)</li>';
    html += '</ul>';

    return html;
}

// Exportar funciones para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validatePassword,
        generateSecurePassword,
        initPasswordValidator,
        getPasswordRequirementsHTML
    };
}
