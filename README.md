# Deli Backend

Laravel backend API for the Deli delivery management system. Handles package management, rider assignments, status tracking, and real-time notifications.

## Features

- 📦 Package management (registration, tracking, status updates)
- 🚴 Rider assignment and tracking
- 🏢 Office web interface for package management
- 📱 RESTful API for mobile applications (Rider & Merchant apps)
- 🔔 Real-time notifications via WebSocket
- 💰 COD (Cash on Delivery) collection tracking
- 📍 Location tracking for riders
- 🔐 Authentication and authorization

## Tech Stack

- **Framework**: Laravel 11
- **Database**: MySQL
- **Real-time**: WebSocket (Pusher/Broadcasting)
- **Storage**: Supabase Storage (for package images with auto-cleanup)
- **Deployment**: Docker on Render

## Project Structure

```
deli_backend/
├── app/
│   ├── Http/Controllers/    # API and Web controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Events/              # Event classes
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
├── resources/views/         # Blade templates
└── public/                  # Public assets
```

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js & NPM (for frontend assets)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd deli_backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=deli_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class=OfficeUserSeeder
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

## Default Admin Credentials

After seeding:
- **Email**: `erickboyle@superadmin.com`
- **Password**: `erick2004`

⚠️ **Change these credentials in production!**

## API Documentation

### Base URL
- **Local**: `http://localhost:8000/api`
- **Production**: `https://your-domain.com/api`

### Authentication
Most endpoints require authentication via Bearer token (Sanctum).

### Key Endpoints

#### Office Web App
- `GET /office/packages` - List packages
- `POST /office/packages/assign` - Assign package to rider
- `POST /office/packages/bulk-assign` - Bulk assign packages

#### Rider API
- `GET /api/rider/packages` - Get rider's assignments
- `POST /api/rider/packages/{id}/status` - Update package status
- `POST /api/rider/packages/{id}/receive` - Receive package from office
- `POST /api/rider/packages/{id}/start-delivery` - Start delivery
- `POST /api/rider/packages/{id}/collect-cod` - Collect COD payment

#### Merchant API
- `POST /api/merchant/packages` - Register new package
- `GET /api/merchant/packages` - List merchant's packages

## Package Status Flow

```
registered → assigned_to_rider → picked_up → on_the_way → delivered
                ↓
         ready_for_delivery → on_the_way → delivered
                ↓
         cancelled → return_to_office → returned_to_merchant
                ↓
         contact_failed → arrived_at_office (auto-reassign)
```

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for complete deployment guide to Render.

Quick steps:
1. Push code to GitHub
2. Create MySQL database on Render (or external)
3. Create Web Service on Render (Docker runtime)
4. Configure environment variables
5. Deploy!

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/phpcs
```

### Database Migrations
```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback
```

## Environment Variables

Key environment variables:

```env
APP_NAME=Deli
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deli_db
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## License

MIT License

## Support

For issues or questions, please check:
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment guide
- Render logs for production issues
- Laravel documentation: https://laravel.com/docs
