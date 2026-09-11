# Antique Furniture Workshop

A full-stack PHP/MySQL portfolio project for an antique-furniture restoration and custom-workshop concept. It demonstrates responsive UI engineering, database-backed content management, authentication, application security, and containerized deployment.

> **Portfolio/demo note:** The business history, client names, testimonials, statistics, contact details, and catalogue content in this repository are demonstration data for a portfolio project. They should not be interpreted as claims about a real client or operating business unless the data is replaced.

## What the project demonstrates

- Responsive public site with home, about, gallery, services, FAQ, and contact pages
- Database-driven gallery, services, testimonials, FAQs, and site settings
- Protected admin area for managing portfolio content and reporting
- Contact workflow with server-side validation and reCAPTCHA support
- CSRF protection, prepared SQL statements, hardened sessions, and login-rate limiting
- Environment-based database and reCAPTCHA configuration
- Custom JavaScript motion effects using GSAP and lightweight visual effects using Three.js
- Docker-based deployment support with a small PHP router for the application server

## Tech stack

- PHP 8.2
- MySQL / MariaDB
- HTML, CSS, vanilla JavaScript
- GSAP
- Three.js
- Apache/XAMPP for local development
- Docker for containerized deployment

## Local development

1. Install XAMPP with Apache, PHP, and MySQL.
2. Clone this repository into `htdocs/`.
3. Import `database.sql` into MySQL using phpMyAdmin or the MySQL client.
4. Configure the database with environment variables, or use the local XAMPP defaults in `config/database.php`.
5. Open the project through Apache using the folder name you chose under `htdocs/`.

There is intentionally no web-accessible installer in the repository. Database initialization is performed explicitly from `database.sql` instead of exposing a database-creation endpoint.

## Configuration

Production credentials must come from the hosting environment. Do not commit `.env` files, database passwords, SMTP passwords, API credentials, or production reCAPTCHA secrets.

Typical variables:

```text
MYSQLHOST
MYSQLDATABASE
MYSQLUSER
MYSQLPASSWORD
MYSQLPORT
RECAPTCHA_SITE_KEY
RECAPTCHA_SECRET_KEY
```

`.env.example` is a reference template only; this project does not require a dotenv loader.

## Admin bootstrap

The public `database.sql` contains the administrator table structure but **does not seed administrator credentials**. This prevents a reusable password or known password hash from being published in source control.

After importing the schema, create an administrator locally and generate the password hash with PHP:

```bash
php -r 'echo password_hash("YOUR_PASSWORD", PASSWORD_DEFAULT), PHP_EOL;'
```

Insert the resulting hash into `admin_users.password_hash` together with your chosen username. Use a unique password for every deployment.

## Repository layout

```text
admin/       Authentication and content management
config/      Database configuration and application initialization
css/         Public stylesheet and design system
images/      Project imagery and icons
includes/    Shared header/footer components
js/          Front-end behavior and visual effects
database.sql Database schema and demonstration seed data
Dockerfile   Container configuration
router.php   PHP development/deployment router
```

## Security notes

- Secrets are supplied through environment variables rather than source control.
- State-changing forms use CSRF protection.
- Database access uses PDO prepared statements with emulated prepares disabled.
- Sessions use hardened cookie settings.
- Login attempts are rate limited and runtime lockout data stays outside version control.
- User-supplied output is escaped before rendering.
- `router.php` blocks direct access to configuration, SQL, log, and documentation files when using the built-in PHP server.

## Testing

No automated test suite is currently included. The project is intended to be evaluated through local smoke testing of the public pages, forms, authentication flow, and admin CRUD operations.

## Portfolio context

This repository is a portfolio artifact demonstrating full-stack PHP/MySQL development, responsive interface work, database-backed administration, authentication, and security hardening.