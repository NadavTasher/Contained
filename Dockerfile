# Select the base image
FROM php:7.4.1-apache
# Enable modifications
RUN a2enmod headers
# Change working directory
WORKDIR /home
# Create contents directory
RUN mkdir /home/contents
# Copy interface
COPY src/interface /home/interface
# Change ownership & permissions of /var/www
RUN chown www-data /var/www/ -R && chmod 775 /var/www/ -R