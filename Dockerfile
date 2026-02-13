FROM php:8.3-cli

# Install dependencies sistem (Minimalis)
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpng-dev \
  libonig-dev \
  libxml2-dev \
  zip \
  unzip \
  git \
  curl \
  && rm -rf /var/lib/apt/lists/*

# Install Node.js 24 (Tanpa rekomendasi tambahan)
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
  && apt-get install -y --no-install-recommends nodejs \
  && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Ambil Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Port 8000 untuk Laravel, 5173 untuk Vite
EXPOSE 8000 5173

# ... setelah COPY code ...
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Standby mode
CMD ["tail", "-f", "/dev/null"]