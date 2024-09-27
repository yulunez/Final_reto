// Inicializar variable para el texto del CAPTCHA
let captchaText = '';

// Función para generar el CAPTCHA
function generateCaptcha() {
    const canvas = document.getElementById('captcha-canvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Generar el código CAPTCHA
    captchaText = Math.random().toString(36).substring(2, 8).toUpperCase();

    // Dibujar el texto CAPTCHA
    ctx.font = '30px Arial';
    ctx.fillStyle = '#333';
    ctx.fillText(captchaText, 40, 40);

    // Añadir ruido al CAPTCHA
    for (let i = 0; i < 100; i++) {
        ctx.fillStyle = `rgba(${Math.random() * 255},${Math.random() * 255},${Math.random() * 255},0.3)`;
        ctx.fillRect(Math.random() * canvas.width, Math.random() * canvas.height, 2, 2);
    }
}

// Función para validar el CAPTCHA y el formulario
function validateForm() {
    const userInput = document.getElementById('captcha-input').value.toUpperCase();

    if (userInput === captchaText) {
        // Mostrar imagen de carga
        const loadingImg = document.getElementById('loadingImg');
        loadingImg.style.display = 'block';

        // Simular espera de 3 segundos antes de enviar
        setTimeout(function() {
            loadingImg.style.display = 'none'; // Ocultar la imagen de carga
            alert('Formulario enviado con éxito');
            return true;
        }, 3000);

        return true; // Enviar el formulario
    } else {
        alert('CAPTCHA incorrecto. Por favor, inténtelo de nuevo.');
        generateCaptcha(); // Regenerar CAPTCHA si es incorrecto
        return false; // No enviar el formulario
    }
}

// Generar el CAPTCHA inicial cuando la página cargue
window.onload = function() {
    generateCaptcha();
}