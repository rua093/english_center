FROM php:8.2-apache

# Cài đặt các extension để PHP kết nối được với MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Nới giới hạn upload để ảnh đại diện thực tế không bị chặn quá sớm
RUN { \
	echo 'upload_max_filesize=10M'; \
	echo 'post_max_size=12M'; \
	echo 'max_file_uploads=20'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Bật mod_rewrite của Apache để file .htaccess (điều hướng MVC) hoạt động
RUN a2enmod rewrite