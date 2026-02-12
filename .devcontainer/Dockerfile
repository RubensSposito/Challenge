FROM php:8.2-cli

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    curl \
    && docker-php-ext-install zip intl \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer de forma explícita (mais robusta)
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Xdebug (opcional, pode comentar se quiser)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /workspace
