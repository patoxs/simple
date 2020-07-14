#!/bin/bash
sed -i "s/pm.max_children = 5/pm.max_children = 10/" /usr/local/etc/php-fpm.d/www.conf
sed -i "s/pm.start_servers = 2/pm.start_servers = 4/" /usr/local/etc/php-fpm.d/www.conf
sed -i "s/pm.min_spare_servers = 1/pm.min_spare_servers = 1/" /usr/local/etc/php-fpm.d/www.conf
sed -i "s/pm.max_spare_servers = 3/pm.max_spare_servers = 4/" /usr/local/etc/php-fpm.d/www.conf
sed -i "s/;pm.max_requests = 500/pm.max_requests = 250/" /usr/local/etc/php-fpm.d/www.conf

# Update the application name
sed -i "s/newrelic.appname = \"PHP Application\"/newrelic.appname = \"${NR_APP_NAME}\"/" /usr/local/etc/php/conf.d/newrelic.ini
sed -i "s/newrelic.license = \"REPLACE_WITH_REAL_KEY\"/newrelic.license = \"${NR_INSTALL_KEY}\"/" /usr/local/etc/php/conf.d/newrelic.ini

sed -i "s/newrelic.appname = \"\"/newrelic.appname = \"${NR_APP_NAME}\"/" /usr/local/etc/php/conf.d/newrelic.ini
sed -i "s/newrelic.license = \"\"/newrelic.license = \"${NR_INSTALL_KEY}\"/" /usr/local/etc/php/conf.d/newrelic.ini

### se desactivando modulo browser
### sed -i "s/;newrelic.browser_monitoring.auto_instrument = true/newrelic.browser_monitoring.auto_instrument = false" /usr/local/etc/php/conf.d/newrelic.ini
echo 'newrelic.browser_monitoring.auto_instrument = false' >> /usr/local/etc/php/conf.d/newrelic.ini

echo "Start deamon New Relic"
/etc/init.d/newrelic-daemon start
 
echo "Launch fpm-php"
php-fpm