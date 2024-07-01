#!/bin/sh
echo "#!/bin/sh"
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
echo ""
echo "***********************************************************"
echo "   Starting LARAVEL PHP-FPM Container                      "
echo "***********************************************************"

# check if $ADMIN_PASSWORD is not "password" or missing and exit if it is
if [ "$ADMIN_PASSWORD" = "password" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "${Red} ADMIN_PASSWORD is set to default password, please change it"
    exit 1
fi
# check if $ADMIN_EMAIL is not "admin@example.com" and exit if it is
if [ "$ADMIN_EMAIL" = "admin@example.com"|| [ -z "$ADMIN_EMAIL" ]; then
    echo "${Red} ADMIN_EMAIL is set to default email, please change it"
    exit 1
fi

set -e

php artisan storage:link
php artisan config:cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

php artisan key:generate --force

# printenv > /etc/environment

php artisan migrate --force

## Check if the supervisor config file exists
if [ -f /var/www/html/docker/php-fpm/supervisord.conf ]; then
    echo "additional supervisor config found. copying to /etc/supervisor/conf.d/supervisord.conf"
    cp /var/www/html/docker/php-fpm/supervisord.conf /etc/supervisor/supervisord.conf
else
    echo "${Red} Supervisor.conf not found"
    echo "${Green} If you want to add more supervisor configs, create config file in /var/www/html/docker/php-fpm/supervisord.conf"
    echo "${Green} Start supervisor with default config..."
fi

## Check if php.ini file exists
if [ -f /var/www/html/docker/php-fpm/php.ini ]; then
    cp /var/www/html/docker/php-fpm/php.ini $PHP_INI_DIR/conf.d/
    echo "Custom php.ini file found and copied to $PHP_INI_DIR/conf.d/"
else
    echo "Custom php.ini file not found"
    echo "If you want to add a custom php.ini file, you add it in /var/www/html/docker/php-fpm/php.ini"
fi

echo ""
echo "**********************************"
echo "     displaying CRONTABS ...     "
echo "***********************************"
echo "root"
crontab -u root -l


echo ""
echo "**********************************"
echo "     Starting Supervisord...     "
echo "***********************************"
supervisord -c /etc/supervisor/supervisord.conf
