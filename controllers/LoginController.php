<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {
        $alertas = [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();
    
            if (empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);
    
                if (!$usuario) {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                } else {
                    if ($usuario->comprobarPasswordAndVerificado($auth->password)) {
                        session_start();
    
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;
    
                        if ((int)$usuario->admin === 1) {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                        return;
                    } else {
                        Usuario::setAlerta('error', 'Contraseña incorrecta o cuenta no confirmada');
                    }
                }
            }
        }
    
        $alertas = Usuario::getAlertas();
    
        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }
    
    

    public static function logout() {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function olvide(Router $router) {
        $alertas = [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();
    
            if (empty($alertas)) {
            
                $usuario = Usuario::where('email', $auth->email);
    
                if ($usuario && (int) $usuario->confirmado === 1) {
                    
                    $usuario->crearToken();
    
                    if ($usuario->guardar()) {
                        
                        try {
                            $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                            $email->enviarInstrucciones();
                            
                            Usuario::setAlerta('exito', 'Revisa tu email para las instrucciones');
                        } catch (\Exception $e) {
                        
                            Usuario::setAlerta('error', 'No se pudo enviar el correo. Inténtalo más tarde.');
                        }
                    } else {
                        Usuario::setAlerta('error', 'Error al guardar el token.');
                    }
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado.');
                }
            }
        }
    
        $alertas = Usuario::getAlertas();
    
        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }
    
    public static function recuperar(Router $router) {
        $alertas = [];
        $error = false;

        $token = s($_GET['token']);

        // Buscar usuario por su token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token No Válido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Leer el nuevo password y guardarlo

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if(empty($alertas)) {
                $usuario->password = null;

                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas, 
            'error' => $error
        ]);
    }

    public static function crear(Router $router) {
        $usuario = new Usuario;

        // Alertas vacias
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            // Revisar que alerta este vacio
            if(empty($alertas)) {
                // Verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el Password
                    $usuario->hashPassword();

                    // Generar un Token único
                    $usuario->crearToken();

                    // Enviar el Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();


                    // Crear el usuario
                    $resultado = $usuario->guardar();
                    
                    // debuguear($usuario);
                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }
        
        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router) {
        $alertas = [];
        $token = trim(s($_GET['token'])); // Limpia el token recibido
    
        $usuario = Usuario::where('token', $token);
    
        if (!$usuario) {
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            $usuario->confirmado = "1";
            $usuario->token = null;
            $resultado = $usuario->guardar();
    
            if ($resultado) {
                Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');
            } else {
                Usuario::setAlerta('error', 'Error al confirmar la cuenta.');
            }
        }
    
        $alertas = Usuario::getAlertas();
    
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
}