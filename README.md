# Tickets – Laravel Support Ticket System

![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Tests](https://img.shields.io/badge/tests-passing-success)
![License](https://img.shields.io/badge/license-MIT-green)

Tickets is a Laravel-based support ticket system demonstrating a clean,
maintainable and idiomatic Laravel application.

The project focuses on clarity, structure and reliability, intentionally
avoiding unnecessary complexity or overengineering.

---

## Project Purpose

This repository serves as a reference implementation for:

- classic CRUD workflows
- authenticated user interactions
- state-based ticket handling
- clean Laravel conventions and project structure

It is designed to show how a simple business problem can be implemented
in a clear and maintainable way.

---

## Core Features

- Create and manage support tickets
- Ticket status handling (open / closed)
- User authentication
- Authorization for ticket access
- Clean separation of concerns

---

## Tech Stack

- Framework: Laravel 12
- Language: PHP 8.2
- Database: MySQL / SQLite
- Authentication: Laravel Auth
- Testing: Pest PHP
- Styling: Blade + Tailwind CSS

---

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 16
- MySQL or SQLite
- Git

---

## Installation

1) Clone the repository

git clone https://github.com/TorbenSt/tickets.git
cd tickets

2) Install dependencies

composer install
npm install

3) Environment setup

cp .env.example .env
php artisan key:generate

4) Configure database

Edit .env and set your database connection, for example:

DB_CONNECTION=sqlite

5) Run migrations and seed demo data

php artisan migrate --seed

6) Build frontend assets

npm run build
npm run dev

7) Start the development server

php artisan serve

The application is now available at:
http://localhost:8000

---

## Standard Users (after seeding)

After running the database seeders, the following demo users are available:

- Admin: admin@example.com / password
- User: user@example.com / password

Note: These accounts exist for local development only.

---

## Tests

This project uses Pest PHP for automated testing.

./vendor/bin/pest

---

## Project Structure

app/
├── Http/Controllers/
├── Http/Requests/
├── Models/
└── Providers/

resources/
├── views/
└── css/

database/
├── migrations/
├── factories/
└── seeders/

tests/
├── Feature/
└── Unit/

---

## Architectural Notes

- Follows standard Laravel conventions
- Clear separation between controllers, models and views
- Predictable and readable codebase
- No unnecessary abstractions

This repository intentionally demonstrates that not every problem requires
a complex architecture.

---

## Why this project exists

While other repositories showcase more advanced architecture or AI integration,
this project highlights solid Laravel fundamentals and clean implementation
of a common business use case.

---

## License

This project is licensed under the MIT License.
