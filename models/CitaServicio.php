<?php

namespace Model;

class CitaServicio extends ActiveRecord {
    protected static $tabla = 'citasServicios';
    protected static $columnasDB = ['id', 'citaId', 'servicioId'];

    public $id;
    public $citaId;
    public $servicioId;

    public function __construct($args = [])
    {
       $this->id = $args['id'] ?? null;
       $this->citaId = $args['citaId'] ?? '';
       $this->servicioId = $args['servicioId'] ?? ''; 
    }
    public function guardar() {
        $atributos = $this->sanitizarAtributos();
        $columnas = implode(', ', array_keys($atributos));
        $valores = implode("', '", array_values($atributos));
    
        $query = "INSERT INTO " . static::$tabla . " ({$columnas}) VALUES ('{$valores}')";
    
        return self::$db->query($query);
    }
    
}