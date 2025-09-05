# Smart Ticket Triage & Dashboard

This project is a production-style single-page application for help-desk teams.  
It allows submitting support tickets, queueing AI classification jobs, viewing/filtering tickets, and basic analytics.

---

## Setup Instructions

Follow these steps (≤ 10) to get started:

1. Clone the repository  
   ```bash
   git clone https://github.com/your-username/smart-ticket-triage.git
   cd smart-ticket-triage
   ```

2. Install PHP dependencies  
   ```bash
   composer install
   ```

3. Install Node.js dependencies  
   ```bash
   npm install
   ```

4. Copy environment file  
   ```bash
   cp .env.example .env
   ```

5. Generate app key  
   ```bash
   php artisan key:generate
   ```

6. Run migrations & seed database  
   ```bash
   php artisan migrate --seed
   ```

7. Start Redis (for queues)  
   ```bash
   redis-server
   ```

8. Run the Laravel queue worker  
   ```bash
   php artisan queue:work
   ```

9. Build and serve frontend assets  
   ```bash
   npm run dev
   ```

10. Start the Laravel server  
    ```bash
    php artisan serve
    ```

---

## ⚙️ .env Example

```env
APP_NAME="SmartTicketTriage"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_tickets
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
SESSION_LIFETIME=120

# OpenAI settings
OPENAI_API_KEY=sk-xxxxx
OPENAI_CLASSIFY_ENABLED=true
OPENAI_RATE_LIMIT_PER_MINUTE=10

# Sentry DSN (optional)
SENTRY_LARAVEL_DSN=

```

---

## Assumptions & Trade-offs

- **AI Classification**: Uses OpenAI if enabled, otherwise generates dummy results for local development.  
- **Polling**: The frontend polls the ticket detail API until classification results are ready.  
- **Categories**: Extracted dynamically from tickets and cached in the store.  
- **Queue**: Redis is required for job dispatching and classification.  
- **Error Monitoring**: Sentry integrated for backend error tracking.  
- **Styling**: Plain CSS with BEM conventions, no CSS frameworks.  

---

##  What I’d Do With More Time

- Add CSV export of tickets list.  
- Improve dashboard with multiple charts and filtering.  
- Add authentication & role-based access control.  
- Dockerize for easy setup (Laravel, Redis, MySQL, Node).  
- More robust unit and feature tests.  

---

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2, Redis (queues), MySQL/Postgres  
- **Frontend**: Vue 3 (Options API), Vite, Chart.js  
- **AI**: openai-php/laravel  
- **Monitoring**: Sentry  

---

## Bulk Classification Command

Run classification for multiple tickets in one go:

```bash
php artisan tickets:bulk-classify
```

---
