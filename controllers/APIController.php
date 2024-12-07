<?php

namespace Controllers;

use Model\Cita;
use Model\CitaServicio;
use Model\Servicio;

class APIController {
    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar() {
        header('Content-Type: application/json');
    
        try {
            if (empty($_POST['fecha']) || empty($_POST['hora']) || empty($_POST['usuarioId']) || empty($_POST['servicios'])) {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'Faltan datos obligatorios para guardar la cita.'
                ]);
                return;
            }
    
            // Guardar la cita y obtener el ID
            $cita = new Cita([
                'usuarioId' => $_POST['usuarioId'],
                'fecha' => $_POST['fecha'],
                'hora' => $_POST['hora'],
                'estado_pago' => 'pendiente', // Estado inicial
                'referencia_pago' => null     // Se actualizará después del pago
            ]);
            
            $resultado = $cita->guardar();
            
    
            if (!$resultado || !isset($resultado['id'])) {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'Error al guardar la cita en la base de datos.'
                ]);
                return;
            }
    
            $citaId = $resultado['id']; // Obtener el ID de la cita guardada
    
            // Guardar los servicios relacionados
            $idServicios = explode(',', $_POST['servicios']);
            foreach ($idServicios as $idServicio) {
                $citaServicio = new CitaServicio([
                    'citaId' => $citaId,
                    'servicioId' => intval($idServicio)
                ]);
    
                if (!$citaServicio->guardar()) {
                    echo json_encode([
                        'resultado' => false,
                        'mensaje' => 'Error al guardar los servicios de la cita.'
                    ]);
                    return;
                }
            }
    
            // Respuesta exitosa
            echo json_encode([
                'resultado' => true,
                'mensaje' => 'La cita fue creada correctamente.'
            ]);
    
        } catch (Exception $e) {
            echo json_encode([
                'resultado' => false,
                'mensaje' => 'Error inesperado: ' . $e->getMessage()
            ]);
        }
    }
    
    
    

    public static function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
    
            // Buscar la cita
            $cita = Cita::find($id);
    
            if ($cita) {
                // Eliminar la cita si existe
                $resultado = $cita->eliminar();
    
                if ($resultado) {
                    header('Location:' . $_SERVER['HTTP_REFERER']);
                } else {
                    echo json_encode([
                        'resultado' => false,
                        'mensaje' => 'Error al eliminar la cita.'
                    ]);
                }
            } else {
                echo json_encode([
                    'resultado' => false,
                    'mensaje' => 'La cita no existe o ya fue eliminada.'
                ]);
            }
        }
    }
    
    public static function obtenerHorasOcupadas($router) {
        $fecha = $_GET['fecha'] ?? null;
    
        if (!$fecha) {
            echo json_encode(['error' => 'Fecha no proporcionada']);
            return;
        }
    
        try {
            $horas = Cita::obtenerHorasPorFecha($fecha);
    
            // Si $horas es null o vacío, retorna un array vacío
            $horasOcupadas = !empty($horas) ? array_column($horas, 'hora') : [];
    
            header('Content-Type: application/json');
            echo json_encode(['horasOcupadas' => $horasOcupadas]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    
    
}