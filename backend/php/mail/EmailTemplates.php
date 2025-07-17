<?php
// php/email/EmailTemplates.php

require_once __DIR__ . '/../core/Logger.php';

class EmailTemplates
{
    public static function generateContactTemplate($datos)
    {
        Logger::info('email_template', 'Generando plantilla de email');

        $servicios = [
            'desarrollo-web' => 'Desarrollo Web',
            'apps-moviles' => 'Apps Móviles',
            'cloud-solutions' => 'Cloud Solutions',
            'consultoria' => 'Consultoría'
        ];

        $servicioNombre = $servicios[$datos['servicio']] ?? 'No especificado';

        $plantilla = "
<html>
  <head>
    <meta charset='UTF-8'>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
      }
      .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }
      .header {
        text-align: center;
        margin-bottom: 30px;
      }
      .header h1 {
        color: #2c3e50;
        margin: 0;
      }
      .content {
        margin-bottom: 20px;
      }
      .field {
        margin-bottom: 15px;
      }
      .field strong {
        color: #34495e;
      }
      .mensaje-box {
        background: #f8f9fa;
        padding: 15px;
        border-left: 4px solid #3498db;
        margin: 10px 0;
      }
      .footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        color: #666;
      }
    </style>
  </head>
  <body>
    <div class='container'>
      <div class='header'>
        <h1>🚀 TechSolutions</h1>
        <p>Nuevo mensaje de contacto</p>
      </div>

      <div class='content'>
        <div class='field'>
          <strong>Nombre:</strong> {$datos['nombre']}
        </div>
        <div class='field'>
          <strong>Email:</strong> {$datos['email']}
        </div>
        <div class='field'>
          <strong>Empresa:</strong> " . (!empty($datos['empresa']) ? $datos['empresa'] : 'No especificada') . "
        </div>
        <div class='field'>
          <strong>Servicio de interés:</strong> {$servicioNombre}
        </div>
        <div class='field'>
          <strong>Mensaje:</strong><br>
          <p class='mensaje-box'>
            " . nl2br(htmlspecialchars($datos['mensaje'])) . "
          </p>
        </div>
        <div class='field'>
          <strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "
        </div>
      </div>

      <div class='footer'>
        <p>Este mensaje fue enviado desde el formulario de contacto de TechSolutions</p>
      </div>
    </div>
  </body>
</html>
    ";

        Logger::success('email_template', 'Plantilla de email generada exitosamente');
        return $plantilla;
    }

    public static function generateConfirmationTemplate($datos, $config)
    {
        Logger::info('email_template', 'Generando plantilla de confirmación');

        $mensaje = "
<html>
  <head>
    <meta charset='UTF-8'>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
      }
      .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }
      .header {
        text-align: center;
        margin-bottom: 30px;
      }
      .header h1 {
        color: #2c3e50;
        margin: 0;
      }
      .content {
        color: #333;
        line-height: 1.6;
      }
      .footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        color: #666;
      }
      .unsubscribe {
        font-size: 12px;
        color: #999;
        margin-top: 20px;
      }
    </style>
  </head>
  <body>
    <div class='container'>
      <div class='header'>
        <h1>🚀 TechSolutions</h1>
        <p>¡Gracias por contactarnos!</p>
      </div>
      <div class='content'>
        <p>Hola <strong>{$datos['nombre']}</strong>,</p>
        <p>Hemos recibido tu mensaje y te agradecemos por contactarnos. Nuestro equipo revisará tu solicitud y te responderá en un plazo máximo de 24 horas.</p>
        <p>Mientras tanto, puedes:</p>
        <ul>
          <li>Visitar nuestro portafolio en línea</li>
          <li>Seguirnos en redes sociales</li>
          <li>Explorar nuestros servicios</li>
        </ul>
        <p>Si tienes alguna pregunta urgente, no dudes en llamarnos al <strong>+54 (381) 615-2377</strong>.</p>
        <p>¡Esperamos trabajar contigo pronto!</p>
        <p>Saludos,<br>El equipo de TechSolutions</p>
        <p class='unsubscribe'>
          Si no deseas recibir futuros correos de confirmación, por favor
          <a href='mailto:{$config['from_email']}?subject=Cancelar%20confirmaciones'>haz clic aquí</a>
          para solicitarnos la baja.
        </p>
      </div>
      <div class='footer'>
        <p>TechSolutions - Transformando ideas en soluciones digitales</p>
      </div>
    </div>
  </body>
</html>
    ";

        Logger::success('email_template', 'Plantilla de confirmación generada exitosamente');
        return $mensaje;
    }
}
