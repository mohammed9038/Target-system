# Target Management System

A professional Laravel-based Target Management System for sales target tracking and matrix management.

## 🚀 Features

- **Multi-User Support**: Admin and Manager roles with granular permissions
- **Target Matrix Management**: Interactive matrix view for target setting and monitoring
- **Regional & Channel Filtering**: Scope-based access control for different organizational levels
- **Import/Export**: Excel/CSV import and export capabilities
- **Real-time Performance**: Optimized queries with caching for sub-second response times
- **Professional Architecture**: Service layer, Repository pattern, and comprehensive testing

## 🏗️ Architecture

### Professional Structure
```
app/
├── Console/           # Artisan commands
├── Events/           # Domain events
├── Exceptions/       # Custom exceptions
├── Exports/          # Excel export classes
├── Http/
│   ├── Controllers/  # MVC controllers
│   ├── Middleware/   # Custom middleware
│   ├── Requests/     # Form request validation
│   └── Resources/    # API resource transformers
├── Jobs/             # Background job processing
├── Listeners/        # Event listeners
├── Models/           # Eloquent models
├── Policies/         # Authorization policies
├── Providers/        # Service providers
├── Repositories/     # Data access layer
└── Services/         # Business logic layer
```

### Key Components

- **Service Layer**: `TargetService` - Handles business logic and orchestration
- **Repository Pattern**: `TargetRepository` - Manages data access and queries
- **Form Requests**: Validation and authorization in dedicated classes
- **API Resources**: Consistent data formatting for API responses
- **Event System**: Decoupled business logic with events and listeners
- **Background Jobs**: Asynchronous processing for heavy operations

## 🛠️ Installation

### Requirements
- PHP 8.2+
- Laravel 12.x
- MySQL 5.7+
- Composer

### Setup
```bash
# Clone the repository
git clone <repository-url>
cd target-system

# Install dependencies
composer install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
php artisan serve
```

## 📊 Database Schema

### Core Tables
- `users` - System users with role-based access
- `regions` - Geographic regions
- `channels` - Sales channels
- `suppliers` - Product suppliers
- `categories` - Product categories
- `salesmen` - Sales representatives
- `sales_targets` - Target data matrix
- `active_month_years` - Period management

### Performance Optimizations
- Composite indexes on frequently queried columns
- Query optimization with raw SQL for matrix operations
- Redis caching for heavy computations
- Database connection pooling

## �️ Clean Project Structure

The application now has a clean, production-ready structure:

### Root Directory
```
Target/
├── .env                    # Environment configuration
├── .htaccess              # Apache configuration
├── artisan                # Laravel CLI tool
├── composer.json          # PHP dependencies
├── docker-compose.yml     # Docker orchestration
├── Dockerfile             # Container configuration
├── index.php              # Legacy entry point
├── README_PROFESSIONAL.md # Documentation
├── app/                   # Application code
├── bootstrap/             # Laravel bootstrap
├── config/                # Configuration files
├── database/              # Migrations & seeders
├── deploy/                # Deployment scripts
├── public/                # Web-accessible files
├── resources/             # Views & assets
├── routes/                # Route definitions
├── storage/               # File storage & logs
├── tests/                 # Test suite
└── vendor/                # Composer dependencies
```

### Removed Files (Cleanup)
The following unnecessary files have been removed to maintain a clean codebase:
- **Backup files**: `.env.backup`, `.env.broken*`, `web.php.backup`
- **Development scripts**: `test_*.php`, `fix_*.php`, `create_*.php`
- **Deployment debris**: `hostinger_*.php`, `deploy_to_hostinger.php`
- **Documentation clutter**: Old `.md` files replaced by this professional README
- **Error files**: `error_output.html`, `full_error.html`
- **Temporary files**: `cookies.txt`, `vendor.zip`

## �🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME="Target Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

## 🔐 Security Features

- **Authentication**: Laravel Sanctum for API authentication
- **Authorization**: Policy-based permissions with role checking
- **CSRF Protection**: Built-in CSRF token validation
- **Input Validation**: Comprehensive form request validation
- **SQL Injection Prevention**: Eloquent ORM and prepared statements
- **XSS Protection**: Blade template escaping

## 📈 Performance Features

- **Query Optimization**: Raw SQL for complex matrix operations
- **Caching Strategy**: Multi-layer caching (Redis, file, application)
- **Database Indexing**: Strategic indexes for optimal query performance
- **Lazy Loading**: Efficient relationship loading
- **Response Compression**: Gzip compression for API responses

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Structure
- **Unit Tests**: Service and repository layer testing
- **Feature Tests**: End-to-end API testing
- **Database Testing**: Factory-based test data generation

## 📚 API Documentation

### Authentication
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "username": "admin",
    "password": "password"
}
```

### Target Management
```http
# Get targets
GET /api/v1/targets?year=2024&month=1

# Create target
POST /api/v1/targets
{
    "salesman_id": 1,
    "supplier_id": 1,
    "category_id": 1,
    "year": 2024,
    "month": 1,
    "target_amount": 15000.00
}

# Get matrix data
GET /api/v1/targets/matrix?year=2024&month=1
```

## 🚀 Deployment

### Production Deployment
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Set up queue workers
php artisan queue:work --daemon --queue=high,default
```

### Docker Support
```dockerfile
FROM php:8.2-fpm
# ... Docker configuration
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🆘 Support

For support, please contact the development team or create an issue in the repository.

## 🔄 Version History

- **v2.0.0** - Professional architecture refactor with service layer
- **v1.5.0** - Performance optimization and caching
- **v1.0.0** - Initial release with basic functionality

---

Built with ❤️ using Laravel 12
