# Examen_1

Proyecto PHP ligero para generación de QR, acortador de URL y utilidades de contraseña.

## Descripción

Este repositorio contiene una pequeña aplicación PHP que incluye: generación de códigos QR, servicio de acortador de URL y utilidades relacionadas con contraseñas. La estructura es modular, organizada por dominios en la carpeta `domains/` y con recursos/servicios reutilizables.

## Requisitos

- PHP 7.4+ (o versión compatible)
- Composer

## Instalación

1. Clonar el repositorio.
2. Instalar dependencias con Composer:

```bash
composer install
composer dump-autoload -o
```

3. Asegurar permisos de escritura para `storage/qr` si vas a generar imágenes QR.

## Configuración

- Revisa `config/database.php` para la configuración de base de datos (si aplica).
- Ajusta cualquier otro archivo en `config/` según tu entorno.

## Ejecutar en desarrollo

Desde la raíz del proyecto puedes iniciar un servidor embebido de PHP apuntando a la carpeta `public/`:

```bash
php -S localhost:8080 -t public
```

Luego abre `http://localhost:8080` en tu navegador.

## Rutas y uso

Las rutas se definen en la carpeta `routes/` y el punto de entrada es `public/index.php`.

Ejemplos de uso (rutas de ejemplo — verifica las rutas reales en `routes/`):

- Generar QR (ejemplo):

```bash
curl "http://localhost:8080/qr?text=Hola%20mundo" -o qr.png
```

- Acortar URL (ejemplo):

```bash
curl -X POST -H "Content-Type: application/json" -d '{"url":"https://example.com"}' http://localhost:8080/shorten
```

- Generar contraseña (ejemplo):

```bash
curl "http://localhost:8080/password/generate?length=12"
```

> Nota: Ajusta los endpoints y parámetros según la definición en `routes/`.

## Estructura del proyecto (resumen)

- `public/` — Punto de entrada (`index.php`).
- `config/` — Configuraciones de la aplicación.
- `core/` — Núcleo (p. ej. `Router.php`).
- `domains/` — Lógica por dominios (QR, ShortURL, password).
- `storage/qr` — Carpeta para imágenes QR generadas.
- `vendor/` — Dependencias gestionadas por Composer.
