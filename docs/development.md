# Excusator 3000 - Development Guide

## Prerequisites
- Docker & Docker Compose
- Node.js & NPM (for local development tools if needed)

## Setup
1.  **Clone the repository**.
2.  **Start Docker Containers**:
    ```bash
    docker compose up -d
    ```
3.  **Install Backend Dependencies**:
    ```bash
    docker compose exec excusator_php-fpm composer install
    ```
4.  **Install Frontend Dependencies**:
    ```bash
    docker compose exec excusator_node npm install
    ```
5.  **Setup Database**:
    - Run migrations:
        ```bash
        docker compose exec excusator_php-fpm php bin/console doctrine:migrations:migrate
        ```
    - Seed the database (Optional but recommended):
        ```bash
        docker compose exec excusator_php-fpm php bin/console doctrine:fixtures:load
        ```

## Running the Application
- **Frontend**: Accessed via `http://front.executor.local` (Traefik Proxy).
  - Dev server runs on port `5173` inside the container.
  - To start dev server: `docker compose exec excusator_node npm run dev -- --host`
- **Backend API**: Accessed via `http://back.executor.local`.
- **Database**: Port 3306 (internal).

## Common Commands

### Backend (Symfony)
- **Clear Cache**:
    ```bash
    docker compose exec excusator_php-fpm php bin/console cache:clear
    ```
- **Make Migration**:
    ```bash
    docker compose exec excusator_php-fpm php bin/console make:migration
    ```
- **Run Migrations**:
    ```bash
    docker compose exec excusator_php-fpm php bin/console doctrine:migrations:migrate
    ```

### Frontend (Vue.js)
- **Install Package**:
    ```bash
    docker compose exec excusator_node npm install <package_name>
    ```
- **Run Dev Server**:
    ```bash
    docker compose exec excusator_node npm run dev -- --host
    ```

## Project Structure
- `backend/`: Symfony Application code.
- `frontend/`: Vue.js Application code.
- `docker/`: Docker configuration files.
- `docs/`: Project documentation.
