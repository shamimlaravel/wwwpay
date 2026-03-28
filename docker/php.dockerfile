FROM php:8.2-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libbcmath-dev \
    zip \
    unzip \
    && docker-php-ext-install bcmath json

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# Expose port
EXPOSE 8000

# Start server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
