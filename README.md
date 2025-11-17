<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed on your system:

-   **PHP** >= 8.2
-   **Composer** (PHP dependency manager)
-   **Node.js** and **npm** (for frontend assets)
-   **MySQL/PostgreSQL** or **SQLite** (database)
-   **Git**

### Installation Steps

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd CCS_BACKEND
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node.js dependencies**

    ```bash
    npm install
    ```

4. **Environment Configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Edit the `.env` file and configure your database settings:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

5. **Run Database Migrations**

    ```bash
    php artisan migrate
    ```

6. **Generate Swagger API Documentation** (if using l5-swagger)
    ```bash
    php artisan l5-swagger:generate
    ```

### Running the Project

#### Option 1: Using Laravel's Built-in Server (Development)

1. **Start the Laravel development server**

    ```bash
php artisan serve --host=0.0.0.0 --port=8000
    ```

    The application will be available at `http://localhost:8000`

2. **Start Vite for frontend assets** (in a separate terminal)
    ```bash
    npm run dev
    ```

#### Option 2: Using the Dev Script (All Services)

Run all services concurrently (server, queue, logs, and vite):

```bash
composer run dev
```

This will start:

-   Laravel development server
-   Queue worker
-   Log viewer (Pail)
-   Vite dev server

#### Option 3: Using Laravel Sail (Docker)

If you prefer using Docker:

1. **Start Sail**

    ```bash
    ./vendor/bin/sail up -d
    ```

2. **Run migrations**

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

3. **Access the application**
   The application will be available at `http://localhost`

### API Documentation

Once the application is running, you can access the Swagger API documentation at:

```
http://localhost:8000/api/documentation
```

### Additional Commands

-   **Run tests**

    ```bash
    composer run test
    ```

    or

    ```bash
    php artisan test
    ```

-   **Clear cache**

    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

-   **Build frontend assets for production**
    ```bash
    npm run build
    ```

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
