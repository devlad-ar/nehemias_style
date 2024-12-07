<?php
namespace Model;
class ActiveRecord {

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Alertas y Mensajes
    protected static $alertas = [];
    
    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database) {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Validación
    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria
    public static function consultarSQL($query, $params = []) {
        $stmt = self::$db->prepare($query);
    
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . self::$db->error);
        }
    
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Asume que todos los parámetros son strings
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows === 0) {
            return []; // Retorna un array vacío si no hay resultados
        }
    
        $datos = [];
        while ($row = $resultado->fetch_assoc()) {
            $datos[] = $row;
        }
    
        $stmt->close();
    
        return $datos;
    }
    
    

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro) {
        $objeto = new static;

        foreach($registro as $key => $value ) {
            if(property_exists( $objeto, $key  )) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach($atributos as $key => $value ) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }

    // Sincroniza BD con Objetos en memoria
    public function sincronizar($args=[]) { 
        foreach($args as $key => $value) {
          if(property_exists($this, $key) && !is_null($value)) {
            $this->$key = $value;
          }
        }
    }

    // Registros - CRUD
    public function guardar() {
        if (!is_null($this->id)) {
            // Si el objeto tiene un ID, actualiza el registro existente
            return $this->actualizar();
        } else {
            // Si no tiene un ID, crea un nuevo registro
            return $this->crear();
        }
    }
    
    protected function crear() {
        $atributos = $this->sanitizarAtributos();
        $columnas = join(', ', array_keys($atributos));
        $placeholders = join(', ', array_fill(0, count($atributos), '?'));
        $valores = array_values($atributos);
    
        $query = "INSERT INTO " . static::$tabla . " ($columnas) VALUES ($placeholders)";
        
        $stmt = self::$db->prepare($query);
    
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . self::$db->error);
        }
    
        $types = str_repeat('s', count($valores));
        $stmt->bind_param($types, ...$valores);
    
        $resultado = $stmt->execute();
    
        if (!$resultado) {
            die("Error al ejecutar la consulta: " . self::$db->error);
        }
    
        return $resultado;
    }

    protected function actualizar() {
        $atributos = $this->sanitizarAtributos();
        $valores = [];
        $set = [];
    
        foreach ($atributos as $key => $value) {
            $set[] = "{$key} = ?";
            $valores[] = $value;
        }
    
        $valores[] = $this->id; // Agregar el ID para la cláusula WHERE
    
        $query = "UPDATE " . static::$tabla . " SET " . join(', ', $set) . " WHERE id = ? LIMIT 1";
    
        $stmt = self::$db->prepare($query);
    
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . self::$db->error);
        }
    
        $types = str_repeat('s', count($valores));
        $stmt->bind_param($types, ...$valores);
    
        $resultado = $stmt->execute();
    
        if (!$resultado) {
            die("Error al ejecutar la consulta: " . self::$db->error);
        }
    
        return $resultado;
    }
    
    
    

    // Todos los registros
    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busca un registro por su id
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id} LIMIT 1";
        $resultado = self::$db->query($query);
    
        if ($resultado->num_rows) {
            $registro = $resultado->fetch_assoc();
            return new static($registro); // Devuelve una instancia del modelo
        }
    
        return null; // Si no encuentra el registro
    }
    

    // Obtener Registros con cierta cantidad
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT {$limite}";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    // Busca un registro por su id
    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = ? LIMIT 1";
    
        // Preparar la consulta
        $stmt = self::$db->prepare($query);
        $stmt->bind_param('s', $valor); // 's' indica que el parámetro es un string
        $stmt->execute();
    
        // Obtener el resultado
        $resultado = $stmt->get_result();
        $registro = $resultado->fetch_assoc(); // Obtiene una fila como array asociativo
    
        // Convertir a un objeto del modelo actual
        return $registro ? static::crearObjeto($registro) : null;
    }
    
    

    // Consulta Plana de SQL (Utilizar cuando los métodos del modelo no son suficientes)
    public static function SQL($query) {
        $resultado = self::$db->query($query);
    
        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = new static($registro); // Crear una instancia del modelo por cada fila
        }
    
        return $array;
    }
    

    // crea un nuevo registro
    // public function crear() {
    //     // Sanitizar los datos
    //     $atributos = $this->sanitizarAtributos();

    //     // Insertar en la base de datos
    //     $query = " INSERT INTO " . static::$tabla . " ( ";
    //     $query .= join(', ', array_keys($atributos));
    //     $query .= " ) VALUES (' "; 
    //     $query .= join("', '", array_values($atributos));
    //     $query .= " ') ";
        
    //     // Resultado de la consulta
    //     $resultado = self::$db->query($query);
    //     return [
    //        'resultado' =>  $resultado,
    //        'id' => self::$db->insert_id
    //     ];
    // }

    // Actualizar el registro
    // public function actualizar() {
    //     // Sanitizar los datos
    //     $atributos = $this->sanitizarAtributos();

    //     // Iterar para ir agregando cada campo de la BD
    //     $valores = [];
    //     foreach($atributos as $key => $value) {
    //         $valores[] = "{$key}='{$value}'";
    //     }

    //     // Consulta SQL
    //     $query = "UPDATE " . static::$tabla ." SET ";
    //     $query .=  join(', ', $valores );
    //     $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    //     $query .= " LIMIT 1 "; 

    //     // Actualizar BD
    //     $resultado = self::$db->query($query);
    //     return $resultado;
    // }

    // // Eliminar un Registro por su ID
    public function eliminar() {
        $query = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        return self::$db->query($query);
    }
}