<h1 class="nombre-pagina">Panel de Administración</h1>

<?php 
    include_once __DIR__ . '/../templates/barra.php';
?>

<h2>Buscar Citas</h2>
<div class="busqueda">
    <form class="formulario">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input 
                type="date"
                id="fecha"
                name="fecha"
                value="<?php echo $fecha; ?>"
            />
        </div>
    </form> 
</div>

    <?php if (empty($citas)) { ?>
        <h2 class="sin-citas">No hay citas solicitadas</h2>
    <?php } ?>

    <?php 
    $idCita = 0;
    foreach ($citas as $key => $cita) {

        if ($idCita !== $cita->id) {
            $total = 0;
    ?>
    <li>
        <p>ID: <span><?= $cita->id ?? 'Sin ID' ?></span></p>
        <p>Hora: <span><?= $cita->hora ?? 'Sin Hora' ?></span></p>
        <p>Cliente: <span><?= $cita->cliente ?? 'Sin Cliente' ?></span></p>
        <p>Email: <span><?= $cita->email ?? 'Sin Email' ?></span></p>
        <p>Teléfono: <span><?= $cita->telefono ?? 'Sin Teléfono' ?></span></p>

        <h3>Servicios</h3>
    <?php 
            $idCita = $cita->id;
        } 
        $total += is_numeric($cita->precio) ? (float)$cita->precio : 0;
    ?>
        <p class="servicio"><?= $cita->servicio . " $" . $cita->precio ?></p>
    <?php 
        $actual = $cita->id;
        $proximo = $citas[$key + 1]->id ?? null;

        if (esUltimo($actual, $proximo)) { ?>
            <p class="total">Total: <span>$ <?= $total ?></span></p>

            <form action="/api/eliminar" method="POST">
                <input type="hidden" name="id" value="<?= $cita->id ?>">
                <input type="submit" class="boton-eliminar" value="Eliminar">
            </form>
    <?php } 
    } ?>

     </ul>
</div>

<?php
    $script = "<script src='build/js/buscador.js'></script>"
?>