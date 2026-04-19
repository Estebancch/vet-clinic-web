# 🐾 Peluditos y Amorositos - Website

Página web desarrollada para una tienda de mascotas y veterinaria. 
El objetivo fue crear un sitio funcional, claro y adaptable a dispositivos móviles, donde los clientes puedan ver productos y agendar citas fácilmente.

## 🚀 Tecnologías utilizadas
- Frontend: HTML5, CSS3 (Flexbox, Grid, animaciones), JavaScript
- Backend: PHP
- Base de datos: JSON (`citas.json`)

## ✨ Funcionalidades
- Catálogo de productos
- Información de servicios veterinarios
- Sistema de agendamiento de citas
- Envío de correos automáticos
- Botón de contacto por WhatsApp
- Diseño responsive (adaptado a celular)

## 📁 Estructura del proyecto

/public_html  
├── index.html  
├── /images  
└── /api  
  ├── citas.php  
  ├── citas.json  
  └── .htaccess  

## 🔒 Seguridad
- Protección del archivo `citas.json` mediante `.htaccess`
- Validación y sanitización de datos en PHP
- Validación de correos para evitar spam

## 🌐 Demo
https://peluditosyamorositos.com/

## 🎓 Sobre el Desarrollador (Perfil Junior / Freelance)
Soy estudiante de **7mo semestre de Ingeniería en Sistemas y Telecomunicaciones**. Desarrollé este proyecto desde cero como un **trabajo freelance para un cliente real**, llevándolo a producción exitosamente (alojado en Hostinger).

Decidí no usar frameworks pesados para el MVP y construirlo con tecnologías base (Vanilla JS, CSS nativo, PHP) con el propósito de entregar un sitio muy rápido y al mismo tiempo demostrar bases sólidas de programación en un entorno comercial real:

- **Arquitectura Cliente-Servidor:** Consumo asíncrono de API REST (Fetch) interactuando con el backend.
- **Seguridad y Producción:** Sanitización de inputs y validación estricta en el servidor para evitar inyecciones, junto con despliegue en un entorno real.
- **Solución a medida:** Diseño responsivo adaptado a las necesidades específicas del cliente (catálogo dinámico y motor de agendamiento propio sin dependencias extra).

*¡Este proyecto refleja mi capacidad para entender requerimientos de negocio y llevarlos a código limpio en producción! Siempre dispuesto a seguir aprendiendo en mi próxima oportunidad laboral.*