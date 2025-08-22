# Fjordloop

Fjordloop is a collaborative travel planning application that centralizes all aspects of a trip into one platform: messages, budget tracking, bookings, calendar, maps, and shared content.

## Requirements

- Docker & Docker Compose
- Composer
- Node.js (only for optional local builds outside Docker)
- A `.env` file (ask a developer for a pre-configured `.env`)

## Local Installation

Clone the repository

```bash
git clone git@github.com:romainsilvy/fjordloop.git
cd fjordloop
```

Install dependencies : 
```bash
composer install --ignore-platform-reqs
```

Start environement 
```bash
./vendor/bin/sail up -d
```

Install front-end assets : 
```bash
./vendor/bin/sail npm i && ./vendor/bin/sail npm run dev
```

Note: For CSP (Content Security Policy) to allow Vite dev server, add the following line to your .env:

VITE_DEV_SERVER=http://localhost:5173

Run database migrations with seeders
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Access the app : go to 
```bash
http://localhost
```

    
Mailpit (for local email testing): http://localhost:8025


## Additional Notes

    The project uses Laravel Sail for consistent dev environment.

    Assets are built with Vite and styled using TailwindCSS.

    CI/CD is handled via GitHub Actions with tests, linting, and auto-deploy.
