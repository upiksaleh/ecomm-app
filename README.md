# Multi-Tenant eCommerce Platform

Multi-tenant eCommerce platform built with **Laravel**, **Vue.js**, and **Inertia.js**, using **stancl/tenancy** with multi-database architecture to ensure strict data isolation between tenants.

---

# Features

## Core Features

- Multi-tenant architecture (database per tenant)
- Central & Tenant authentication
- Tenant onboarding system
- Product management (CRUD)
- Shopping cart system
- Tenant specific frontend
- Database isolation per store

## Technical Features

- Laravel 11+
- Vue 3
- Inertia.js
- stancl/tenancy
- TailwindCSS
- Multi database connection switching

---

# Setup

## Development

Using [laravel/sail](https://laravel.com/docs/master/sail) for development.

- Clone Repository

    ```
    git clone https://github.com/upiksaleh/ecomm-app.git

    cd ecomm-app
    ```

- Setup Environment

    ```
    cp .env.example.sail .env
    ```
    Update .env if needed.

- Install Composer Dependencies

    ```
    composer install
    ```

- Start Laravel Sail

    ```
    ./vendor/bin/sail up -d
    ```

- Sail alias (optional but recommended):

    ```
    alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
    ```

- Generate Application Key

    ```
    sail artisan key:generate
    ```

- Run Migration

    ```
    sail artisan migrate
    ```

- Dev Database Seed 

    ```
    sail artisan db:seed
    # dev tenant seed
    sail artisan tenants:seed DevTenantSeeder
    ```

- Install Frontend Dependencies

    ```
    sail npm install
    ```

- Run Frontend Dev

    ```
    sail npm run dev
    ```

---

# Dev

- [Requirements](./dev/requirements.md)
- [Todo](./TODO.md)
