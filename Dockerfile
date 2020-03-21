# Select the base image
FROM php:7.4.1-apache
# Copy configurations
COPY src/configuration/virtualhosts.conf /etc/apache2/sites-available/000-default.conf
# Enable modifications
RUN a2enmod headers
# Change working directory
WORKDIR /home
# Copy contents directory
COPY src/contents /home/contents
# Copy interface
COPY src/contained /home/contained
# Change ownership & permissions of /home
RUN chown www-data /home -R && chmod 775 /home -R