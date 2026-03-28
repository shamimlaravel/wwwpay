# WwwPay Development Environment

## Quick Start

### Using Docker

```bash
# Start all services
docker-compose -f docker/docker-compose.yml up -d

# Run tests
docker-compose -f docker/docker-compose.yml exec app ./vendor/bin/phpunit

# Stop services
docker-compose -f docker/docker-compose.yml down
```

### Manual Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

## Services

| Service | Port | Description |
|---------|------|-------------|
| App | 8000 | Laravel application |
| MySQL | 3306 | Database |
| Redis | 6379 | Cache/Queue |

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

## Development

```bash
# Start development server
php artisan serve

# Watch tests
./vendor/bin/phpunit --watch
```
