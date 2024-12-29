let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); // Muestra y oculta las secciones
    tabs(); // Cambia la sección cuando se presionen los tabs
    botonesPaginador(); // Agrega o quita los botones del paginador
    paginaSiguiente(); 
    paginaAnterior();

    consultarAPI(); // Consulta la API en el backend de PHP

    idCliente();
    nombreCliente(); // Añade el nombre del cliente al objeto de cita
    seleccionarFecha(); // Añade la fecha de la cita en el objeto
    manejarHorarios(); // Maneja horarios disponibles y ocupados

    mostrarResumen(); // Muestra el resumen de la cita
}

function mostrarSeccion() {
    // Ocultar la sección que tenga la clase de mostrar
    const seccionAnterior = document.querySelector('.mostrar');
    if (seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // Seleccionar la sección con el paso actual
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // Quitar la clase de actual al tab anterior
    const tabAnterior = document.querySelector('.actual');
    if (tabAnterior) {
        tabAnterior.classList.remove('actual');
    }

    // Resaltar el tab actual
    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            paso = parseInt(e.target.dataset.paso);
            mostrarSeccion();
            botonesPaginador(); 
        });
    });
}

function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguiente = document.querySelector('#siguiente');

    if (paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    } else if (paso === 3) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
        mostrarResumen();
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }

    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');
    paginaAnterior.addEventListener('click', function() {
        if (paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');
    paginaSiguiente.addEventListener('click', function() {
        if (paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {
    try {
        const url = '/api/servicios';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
    } catch (error) {
        console.error(error);
    }
}

function mostrarServicios(servicios) {
    servicios.forEach(servicio => {
        const { id, nombre, precio } = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function() {
            seleccionarServicio(servicio);
        }

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        document.querySelector('#servicios').appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const { servicios } = cita;

    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    if (servicios.some(agregado => agregado.id === id)) {
        cita.servicios = servicios.filter(agregado => agregado.id !== id);
        divServicio.classList.remove('seleccionado');
    } else {
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }
}

function idCliente() {
    cita.id = document.querySelector('#id').value;
}

function nombreCliente() {
    cita.nombre = document.querySelector('#nombre').value;
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    inputFecha.addEventListener('input', function(e) {
        const dia = new Date(e.target.value).getUTCDay();

        if ([2, 3, 4, 5, 6].includes(dia)) {
            e.target.value = '';
            mostrarAlerta('Los turnos solo son para domingos y lunes', 'error', '.formulario');
        } else {
            cita.fecha = e.target.value;
            manejarHorarios(); // Actualizar horarios al seleccionar fecha
        }
    });
}

async function manejarHorarios() {
    const fechaSeleccionada = cita.fecha;
    if (!fechaSeleccionada) return; // Si no hay fecha, no continúa

    const horasOcupadas = await obtenerHorasOcupadas(fechaSeleccionada); // Llama a la API
    mostrarHorarios(horasOcupadas); // Renderiza las horas en la vista
}


async function obtenerHorasOcupadas(fecha) {
    try {
        const response = await fetch(`/api/horas-ocupadas?fecha=${fecha}`);
        const data = await response.json();
        console.log("Respuesta del servidor:", data); // Depuración
        return data.horasOcupadas || [];
    } catch (error) {
        console.error("Error al obtener las horas ocupadas:", error);
        return [];
    }
}


function mostrarHorarios(horasOcupadas) {
    const scheduleContainer = document.getElementById('schedule');
    scheduleContainer.innerHTML = ''; // Limpia horarios anteriores

    const timeSlots = [
        "10am-11am", "11am-12pm", "12pm-1pm", "1pm-2pm",
        "2pm-3pm", "3pm-4pm", "4pm-5pm", "5pm-6pm",
        "6pm-7pm", "7pm-8pm"
    ];

    // Asegurarse de que horasOcupadas es un array
    if (!Array.isArray(horasOcupadas)) {
        console.error("horasOcupadas no es un array:", horasOcupadas);
        return;
    }

    timeSlots.forEach(slot => {
        const button = document.createElement('div');
        button.className = 'time-slot';
        button.textContent = slot;

        if (horasOcupadas.includes(slot)) {
            button.classList.add('taken'); // Marcar como ocupado
        } else {
            button.addEventListener('click', () => {
                const seleccionado = document.querySelector('.time-slot.selected');
                if (seleccionado) seleccionado.classList.remove('selected');

                button.classList.add('selected');
                cita.hora = slot;
            });
        }

        scheduleContainer.appendChild(button);
    });
}

function formatearFecha(fecha) {
    const [year, month, day] = fecha.split('-'); // Divide la fecha en partes
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fechaObj = new Date(year, month - 1, day); // Crear fecha usando partes
    return fechaObj.toLocaleDateString('es-ES', opciones); // Formatear la fecha
}


function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    resumen.innerHTML = '';

    if (!cita.nombre || !cita.fecha || !cita.hora || cita.servicios.length === 0) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora.', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    servicios.forEach(servicio => {
        const { nombre, precio } = servicio;
        const contenedorServicio = document.createElement('DIV');
        contenedorServicio.classList.add('contenedor-servicio');

        const textoServicio = document.createElement('P');
        textoServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.innerHTML = `<span>Precio:</span> $${precio}`;

        contenedorServicio.appendChild(textoServicio);
        contenedorServicio.appendChild(precioServicio);

        resumen.appendChild(contenedorServicio);
    });

    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaFormateada = formatearFecha(fecha); // Usar la función corregida
    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora}`;

    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(botonReservar);
    
    
}

async function reservarCita() {
    const { id, fecha, hora, servicios } = cita;

    if (!id || !fecha || !hora || servicios.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Faltan datos obligatorios para reservar la cita.'
        });
        return;
    }

    const idServicios = servicios.map(servicio => servicio.id);
    const datos = new FormData();
    datos.append('usuarioId', id);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('servicios', idServicios.join(','));

    try {
        const url = '/api/citas';
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        const resultado = await respuesta.json();

        if (resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: 'Tu cita fue creada correctamente!',
                button: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.mensaje || 'Hubo un error al guardar la cita'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo conectar con el servidor. Inténtalo más tarde.'
        });
        console.error('Error al guardar la cita:', error);
    }
}


function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if (alertaPrevia) {
        alertaPrevia.remove();
    }

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    if (desaparece) {
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}