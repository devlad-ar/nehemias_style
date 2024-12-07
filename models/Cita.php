<?php

namespace Model;

class Cita extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora', 'usuarioId'];

    public $id;
    public $fecha;
    public $hora;
    public $usuarioId;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

    public static function obtenerHorasPorFecha($fecha) {
        $query = "SELECT hora FROM citas WHERE fecha = ?";
        $resultado = self::consultarSQL($query, [$fecha]);
    
        // Validar si la consulta no devuelve resultados
        if (!$resultado) {
            return [];
        }
    
        return $resultado;
    }

    public function guardar() {
        $atributos = $this->sanitizarAtributos();
        $columnas = implode(', ', array_keys($atributos));
        $valores = implode("', '", array_values($atributos));
    
        $query = "INSERT INTO " . static::$tabla . " ({$columnas}) VALUES ('{$valores}')";
    
        $resultado = self::$db->query($query);
    
        if ($resultado) {
            return ['id' => self::$db->insert_id]; // Devolver el ID de la última inserción
        } else {
            return false;
        }
    }
    public function eliminar() {
        // Eliminar servicios asociados
        $queryServicios = "DELETE FROM citasServicios WHERE citaId = " . self::$db->escape_string($this->id);
        self::$db->query($queryServicios);
    
        // Eliminar la cita
        $queryCita = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        return self::$db->query($queryCita);
    }
    
    
    

}