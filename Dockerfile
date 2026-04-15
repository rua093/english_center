FROM php:8.2-apache

# Cài đặt các extension để PHP kết nối được với MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Bật mod_rewrite của Apache để file .htaccess (điều hướng MVC) hoạt động
RUN a2enmod rewrite