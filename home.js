function redirigir() {
    window.location.href = "index.php";
}
function redirigir2() {
    window.location.href = "order.php";
}

// Función para agregar una nueva fila a la tabla de órdenes
function addOrder() {
    var table = document.getElementById("orderTable");
    var row = table.insertRow(-1);

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    var cell5 = row.insertCell(4);
    var cell6 = row.insertCell(5);

    cell1.textContent = "XXXXXXXXXX";
    cell2.textContent = "";
    cell3.textContent = "XXXXXXXXXX";
    cell4.textContent = "XXXXXXXXXX";
    cell5.textContent = "XXXXXXXXXX";
    cell6.innerHTML = '<button class="btn btn-danger" onclick="deleteOrder(this)">Eliminar</button>';
  }

  // Función para eliminar una fila de la tabla de órdenes
  function deleteOrder(button) {
    var row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);
  }


  function realizarAccion() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;

    // Verifica si ambos campos tienen valores
    if (fechaInicio && fechaFin) {
        // Acción a realizar
        alert(`Fecha de inicio: ${fechaInicio}\nFecha de fin: ${fechaFin}`);
        // Aquí puedes poner la acción que deseas, como hacer una llamada AJAX, filtrar datos, etc.
    }
}

// Event listener para los campos de fecha
document.addEventListener('DOMContentLoaded', () => {
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');

    fechaInicio.addEventListener('input', realizarAccion);
    fechaFin.addEventListener('input', realizarAccion);
});

let paginaActual = 1;

// Cargar las órdenes de la página actual usando AJAX
function cargarOrdenes(pagina) {
    // Crear una solicitud XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `cargar_ordenes.php?pagina=${pagina}`, true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            // Actualizar el contenido de la tabla con las nuevas órdenes
            document.getElementById('tabla-ordenes').innerHTML = this.responseText;
        }
    };
    
    xhr.send();
}

// Event listeners para la paginación
document.getElementById('numeroPagina').addEventListener('change', function() {
    paginaActual = this.value;
    cargarOrdenes(paginaActual);
});

document.getElementById('prevPage').addEventListener('click', function() {
    if (paginaActual > 1) {
        paginaActual--;
        document.getElementById('numeroPagina').value = paginaActual;
        cargarOrdenes(paginaActual);
    }
});

document.getElementById('nextPage').addEventListener('click', function() {
    paginaActual++;
    document.getElementById('numeroPagina').value = paginaActual;
    cargarOrdenes(paginaActual);
});

// Cargar la primera página de órdenes al cargar la página
cargarOrdenes(paginaActual);