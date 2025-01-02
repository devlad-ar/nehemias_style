<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<?php 
    include_once __DIR__ . '/../templates/barra.php';
?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">Servicios</button>
        <button type="button" data-paso="2">Información Cita</button>
        <button type="button" data-paso="3">Resumen</button>
    </nav>

    <div id="paso-1" class="seccion mostrar">
        <h2>Servicios</h2>
        <p class="text-center">Elige tus servicios a continuación</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>
    <div id="paso-2" class="seccion">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Coloca la fecha (recuerda que solo los domingos y lunes se trabaja con turnos) 
            y selecciona la hora de tu cita</p>
        <div class="aclaracion_horas">
            <div class="aclaracion_1"></div><span>Ocupado | </span>
            <div class="aclaracion_2"></div><span>Disponible | </span>
            <div class="aclaracion_3"></div><span>Seleccionado</span>
        </div>
        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input
                    id="nombre"
                    type="text"
                    placeholder="Tu Nombre"
                    value="<?php echo $nombre; ?>"
                    disabled
                />
            </div>

            <div class="campo">
                <label for="fecha">Fecha</label>
                <input
                    id="fecha"
                    type="date"
                    min="<?php echo date('Y-m-d', strtotime('+1 day') ); ?>"
                />
            </div>

            <div class="campo">
                <label for="hora">Hora</label>
                <div id="schedule" class="horarios">
                    <!-- Los horarios se generarán dinámicamente aquí -->
                </div>
            </div>

            <input type="hidden" id="id" value="<?php echo $id; ?>" />
        </form>
    </div>
    <div id="paso-3" class="seccion contenido-resumen">
        <h2>Resumen</h2>
        <p class="text-center">Verifica que la información sea correcta</p>
    </div>

    <div class="paginacion">
        <button id="anterior" class="boton ocultar">&laquo; Anterior</button>
        <button id="siguiente" class="boton">Siguiente &raquo;</button>
    </div>
</div>

<?php 
    $script = "
        <script src='https://sdk.mercadopago.com/js/v2'></script>
        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='/public/build/js/app.js'></script>

    ";
?>
