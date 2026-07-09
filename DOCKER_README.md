# Containerizing the AMU Inventory System

This document explains how to build, configure, and run the production-ready Docker containers for the Laravel-based Asset Management Unit (AMU) Inventory System.

---

## Prerequisites
Ensure the target server has the following installed:
- [Docker Engine](https://docs.docker.com/engine/install/) (v20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2.0 or higher)

---

## 1. Setup Environment Configuration

1. Copy the production environment template to `.env` on your host:
   ```bash
   cp .env.production.example .env
   ```
2. Open `.env` and fill in the production secrets, database credentials, domain name, and ports.

> [!IMPORTANT]
> The `REVERB_HOST` and `VITE_REVERB_HOST` must match the public IP address or domain name of the server so that clients can connect to the WebSockets.

---

## 2. Build and Run the Stack

Build the images and run the services in the background:
```bash
docker compose up -d --build
```
This builds and starts the full multi-service stack:
- **`amu_db`**: MySQL database server.
- **`amu_redis`**: Redis server for fast caching/queues.
- **`amu_app`**: PHP-FPM application process.
- **`amu_reverb`**: WebSocket server for real-time dashboard updates.
- **`amu_queue`**: Queue worker for processing system notifications.
- **`amu_nginx`**: Nginx web server routing traffic and proxying WebSocket upgrades.

---

## 3. Post-Deployment Setup

Upon first launch, generate the application encryption key:
```bash
docker compose exec app php artisan key:generate
```
*(This will automatically update the `.env` file mounted inside the container, but you should also record the generated key for backup.)*

---

## 4. Operational Commands

### View Logs
To view logs for all services or for a specific container:
```bash
# View logs for all services
docker compose logs -f

# View logs for the app service only
docker compose logs -f app

# View logs for the reverb service only
docker compose logs -f reverb
```

### Running Artisan Commands
To execute standard Laravel Artisan commands within the running application container:
```bash
# Run database migrations manually
docker compose exec app php artisan migrate

# View Laravel routes
docker compose exec app php artisan route:list

# Clear application cache
docker compose exec app php artisan cache:clear

# Open interactive PHP shell (Tinker)
docker compose exec app php artisan tinker
```

### Safely Applying Migrations on Deploy
By default, the `RUN_MIGRATIONS=true` environment variable in `docker-compose.yml` ensures migrations run automatically on container startup.
- **Production Risk Warning**: Automatic migrations can introduce deployment risks.
- **To make migrations manual**:
  1. Edit `docker-compose.yml` and set `RUN_MIGRATIONS: "false"` on the `app` service.
  2. Deploy containers with `docker compose up -d`.
  3. Manually run:
     ```bash
     docker compose exec app php artisan migrate --force
     ```

---

## 5. Port Mapping and Firewall Configuration

Ensure your host/server firewall is configured to open the following ports:
- **`80`**: Standard HTTP (handled by Nginx web server)
- **`8080`**: WebSocket port (handled by Laravel Reverb)

If you are using Nginx to reverse proxy SSL/TLS on port `443` (recommended for production), configure SSL certificate mounting on the `nginx` service and map port `443:443` to host.
