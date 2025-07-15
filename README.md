# 💻 TechSolutions — Innovación Digital

![TechSolutions Banner](./assets/images/heroImage.svg)

Transformamos tu **visión digital** en realidad.
Con TechSolutions, llevamos tu negocio al siguiente nivel mediante soluciones tecnológicas innovadoras, personalizadas y de alto impacto.

---

## 🎨 Paleta de colores

| Color           | Hex       |
| --------------- | --------- |
| Primary Matte   | `#5D7C8A` |
| Secondary Matte | `#7A9299` |
| Dark Matte      | `#2C3E50` |
| Light Matte     | `#F8F9FA` |
| Accent Matte    | `#E74C3C` |
| Gray Matte      | `#95A5A6` |
| Success Matte   | `#27AE60` |
| Warning Matte   | `#F39C12` |
| Info Matte      | `#3498DB` |

---

## 🚀 Funcionalidades

✅ Sitio web completamente responsive y moderno.
✅ Formulario de contacto con validación y protección contra bots (reCAPTCHA).
✅ Conexión segura a base de datos MySQL para almacenar mensajes.
✅ Envío automático de correos mediante **PHPMailer**.
✅ Código modular y organizado, con middlewares y lógica de seguridad separada.
✅ Uso de **Logger** para seguimiento y trazabilidad de eventos importantes.

---

## 🏗️ Tecnologías usadas

* **Frontend:**

  * HTML5
  * CSS3 (con Bootstrap 5 + estilos personalizados)
  * JavaScript

* **Backend:**

  * PHP
  * MySQL
  * PHPMailer

---

## ✉️ Formulario de contacto

El formulario permite a los usuarios enviar sus datos, que son:

* Validados y verificados con reCAPTCHA.
* Guardados en una base de datos MySQL.
* Enviados automáticamente al administrador vía email usando PHPMailer.
* Registrados en logs para auditoría y seguridad.

---

## 🗂️ Estructura del proyecto

```
📁 assets/
   ├── css/
   │    └── index.css
   ├── images/
   │    ├── heroImage.svg
   │    ├── aboutImage.svg
   │    ├── imageMariaGonzalez.png
   │    ├── imageJuanPerez.png
   │    └── imageAnaRodriguez.png
   └── js/
        └── script.js
📁 php/
   ├── security/
   │    ├── block_direct_access.php
   │    ├── check_payload_size.php
   │    ├── headers.php
   │    ├── rate_limit.php
   │    └── validate_content_type.php
   ├── middleware/
   │    └── verify_recaptcha.php
   ├── process_form.php
   └── Logger.php
📄 index.html
```

---

## ⚡ Instalación y uso

1. Clona este repositorio:

```bash
git clone https://github.com/FT-Key/PHPBasicLandingPage.git
```

2. Configura tu base de datos MySQL y actualiza las credenciales en el archivo `process_form.php`.

3. Configura PHPMailer con tus datos SMTP en el mismo archivo o mediante variables de entorno.

4. Configura tu clave de Google reCAPTCHA en `middleware/verify_recaptcha.php`.

5. Sube el proyecto a tu servidor o ejecútalo en tu entorno local.

6. ¡Listo! Tu página estará lista para recibir y gestionar mensajes de forma segura.

---

## 💬 Testimonios

> ⭐️⭐️⭐️⭐️⭐️
> "Excelente trabajo, cumplieron todos los plazos y superaron nuestras expectativas. Muy recomendado."
> — **María González**, CEO de StartupTech

> ⭐️⭐️⭐️⭐️⭐️
> "El equipo de TechSolutions transformó completamente nuestra presencia digital. Resultados increíbles."
> — **Juan Pérez**, Director de Marketing

> ⭐️⭐️⭐️⭐️⭐️
> "Profesionales, creativos y muy eficientes. La mejor inversión que hemos hecho para nuestro negocio."
> — **Ana Rodríguez**, Fundadora de EcoShop

---

## 🌟 Contribuye

¡Las contribuciones son bienvenidas!
Si quieres mejorar el proyecto, crea un *fork*, haz tus cambios y envía un *pull request*.

---

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).

---

## 🤝 Contacto

📧 **[fr4nc0t2@gmail.com](mailto:fr4nc0t2@gmail.com)**
🌐 [TechSolutions](https://phpbasiclandingpage.onrender.com/)

---

### 💙 Hecho con pasión por el equipo de TechSolutions

> "Transformando ideas en soluciones digitales innovadoras."

---

## 💡 Bonus: beneficios de la estructura modular

Separar la lógica en carpetas `security` y `middleware` permite:

✅ Mejor mantenibilidad.
✅ Código más legible y seguro.
✅ Reutilización de funciones de seguridad en otros formularios o endpoints.
✅ Escalabilidad futura sin reescribir toda la lógica.