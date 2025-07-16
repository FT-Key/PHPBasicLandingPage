<?php

require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/EmailTemplates.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
  private $config;

  public function __construct(array $config)
  {
    $this->config = $config;
  }

  private function configureMailer(): PHPMailer
  {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host = $this->config['smtp_host'] ?? '';
      $mail->SMTPAuth = true;
      $mail->Username = $this->config['smtp_user'] ?? '';
      $mail->Password = $this->config['smtp_pass'] ?? '';
      $mail->SMTPSecure = $this->config['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $this->config['smtp_port'] ?? 587;

      $from_email = $this->config['from_email'] ?? '';
      $from_name = $this->config['from_name'] ?? '';

      if (!$from_email) {
        throw new Exception("La configuración 'from_email' es requerida.");
      }

      $mail->setFrom($from_email, $from_name);

      // Configuramos HTML y Charset
      $mail->isHTML(true);
      $mail->CharSet = 'UTF-8';

      return $mail;
    } catch (Exception $e) {
      Logger::error('mailer', 'Error configurando PHPMailer', ['error' => $e->getMessage()]);
      throw $e;
    }
  }

  public function sendContactMail(array $datos): bool
  {
    try {
      $mail = $this->configureMailer();

      $to_email = $this->config['to_email'] ?? '';
      $to_name = $this->config['to_name'] ?? '';

      if (!$to_email) {
        throw new Exception("La configuración 'to_email' es requerida.");
      }

      $mail->addAddress($to_email, $to_name);
      $mail->addReplyTo($datos['email'], $datos['nombre']);
      $mail->Subject = "Nuevo contacto desde TechSolutions - {$datos['nombre']}";
      $mail->Body = EmailTemplates::generateContactTemplate($datos);

      $mail->send();

      Logger::success('email', 'Correo de contacto enviado', ['to' => $to_email]);

      return true;
    } catch (Exception $e) {
      Logger::error('email', 'Error enviando correo de contacto', ['error' => $e->getMessage()]);
      return false;
    }
  }

  public function sendConfirmation(array $datos): bool
  {
    try {
      $mail = $this->configureMailer();

      $mail->addAddress($datos['email'], $datos['nombre']);
      $reply_email = $this->config['reply_email'] ?? ($this->config['from_email'] ?? '');
      $reply_name = $this->config['reply_name'] ?? ($this->config['from_name'] ?? '');

      $mail->addReplyTo($reply_email, $reply_name);
      $mail->Subject = "Confirmación - Hemos recibido tu mensaje";
      $mail->Body = EmailTemplates::generateConfirmationTemplate($datos, $this->config);
      $mail->addCustomHeader('List-Unsubscribe', "<mailto:{$this->config['from_email']}?subject=Unsubscribe>");

      $mail->send();

      Logger::success('email', 'Correo de confirmación enviado', ['to' => $datos['email']]);

      return true;
    } catch (Exception $e) {
      Logger::error('email', 'Error enviando correo de confirmación', ['error' => $e->getMessage()]);
      return false;
    }
  }
}
