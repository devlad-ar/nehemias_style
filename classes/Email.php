<?php 
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;
    
    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {
        // Crear un nuevo objeto PHPMailer
        $phpmailer = new PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = $_ENV['EMAIL_HOST'];
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = $_ENV['EMAIL_PORT'];
        $phpmailer->Username = $_ENV['EMAIL_USER'];
        $phpmailer->Password = $_ENV['EMAIL_PASS'];
     
        $phpmailer->setFrom('cuentas@appsalon.com', 'AppSalon');
        $phpmailer->addAddress($this->email, $this->nombre);
        $phpmailer->Subject = 'Confirma tu Cuenta';

        // Configurar HTML
        $phpmailer->isHTML(TRUE);
        $phpmailer->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre .  "</strong> Has Creado tu cuenta en App Salón, solo debes confirmarla presionando el siguiente enlace:</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL'] . "/confirmar-cuenta?token=" . $this->token . "'>Confirmar Cuenta</a></p>";        
        $contenido .= "<p>Si tú no solicitaste este cambio, puedes ignorar este mensaje.</p>";
        $contenido .= '</html>';
        $phpmailer->Body = $contenido;

        // Enviar el mail
        if (!$phpmailer->send()) {
            throw new \Exception('Error al enviar el correo: ' . $phpmailer->ErrorInfo);
        }
    }

    public function enviarInstrucciones() {
        // Crear un nuevo objeto PHPMailer
        $phpmailer = new PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = $_ENV['EMAIL_HOST'];
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = $_ENV['EMAIL_PORT'];
        $phpmailer->Username = $_ENV['EMAIL_USER'];
        $phpmailer->Password = $_ENV['EMAIL_PASS'];
    
        $phpmailer->setFrom('cuentas@appsalon.com', 'AppSalon');
        $phpmailer->addAddress($this->email, $this->nombre);
        $phpmailer->Subject = 'Reestablece tu Password';

        // Configurar HTML
        $phpmailer->isHTML(TRUE);
        $phpmailer->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre .  "</strong> Has solicitado reestablecer tu password, sigue el siguiente enlace para hacerlo:</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL'] . "/recuperar?token=" . $this->token . "'>Reestablecer Password</a></p>";        
        $contenido .= "<p>Si tú no solicitaste este cambio, puedes ignorar este mensaje.</p>";
        $contenido .= '</html>';
        $phpmailer->Body = $contenido;

        // Enviar el mail
        if (!$phpmailer->send()) {
            throw new \Exception('Error al enviar el correo: ' . $phpmailer->ErrorInfo);
        }
    }
}
