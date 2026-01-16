# Excusator 3000 - Architecture Documentation

## Overview
Excusator 3000 is a full-stack web application designed to generate random excuses based on categories. It consists of a decoupled Backend (Symfony) and Frontend (Vue.js).

## Backend (Symfony)
REST API built with Symfony 7.4/PHP 8.2.

- **Entities**: 
    - `Category`: Represents a grouping for excuses. Properties: `id`, `name`, `active`, `deletedAt` (SoftDelete).
    - `Excuse`: Represents the excuse text. Properties: `id`, `content`, `category`, `deletedAt` (SoftDelete).
- **Architecture Pattern**: 
    - **Controller**: Single action (`__invoke`) controllers for strict responsibility separation.
    - **Service Layer**: Business logic resides in `CategoryService` and `ExcuseService`.
    - **DTOs**: Data Transfer Objects (`CategoryDto`, `ExcuseDto`) used for type-safe data handling and validation.
    - **Repository**: Custom methods (e.g., `findValidCategories`) in repositories.
- **Database**: 
    - MySQL for persistence.
    - Doctrine ORM for data mapping.
    - SoftDeletes implemented manually via `deletedAt` column handling.
- **API**:
    - Returns JSON responses.
    - Uses `NelmioCorsBundle` for CORS handling.
    - Uses `Symfony Serializer` and `Validator`.

## Frontend (Vue.js)
Single Page Application (SPA) built with Vue 3 (Composition API) and Vite.

- **Routing**: `vue-router` handles navigation between Home and Admin sections.
- **State/API**: `axios` instance configured with base URL for backend communication.
- **Components**:
    - `Wheel.vue`: Interactive SVG-based spinning wheel. Handles logic to valid categories (>5 excuses) and fetching random excuses.
    - `ExcuseModal.vue`: Displays the generated excuse.
- **Views**:
    - `Home.vue`: Main user interface.
    - `Admin/Categories/*`: CRUD for Categories.
    - `Admin/Excuses/*`: CRUD for Excuses.
- **Styling**: Vanilla CSS with scoped styles, featuring a "Premium" aesthetic with gradients and animations.

## Infrastructure
Docker-based microservices architecture.
- **Containers**:
    - `excusator_php-fpm`: PHP Backend.
    - `excusator_nginx-backend`: Nginx for Backend.
    - `excusator_node`: Nodejs/Vite Dev Server for Frontend.
    - `excusator_nginx-frontend`: Nginx for Frontend (Production/Proxy).
    - `excusator_db`: MySQL Database.
    - `traefik`: Reverse proxy handling routing (`front.executor.local`, `back.executor.local`).
