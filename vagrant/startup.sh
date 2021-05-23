sudo yum -y update
sudo yum -y install nano wget

# apache
sudo yum -y install httpd
sudo systemctl start httpd
sudo systemctl enable httpd
#   systemctl status httpd

# mariadb
sudo yum -y install mariadb-server mariadb
sudo systemctl start mariadb
sudo systemctl enable mariadb
#   sudo mysql_secure_installation
#   Enter, pwd, y, y, y, y
#   mysql -u root -p
      # CREATE DATABASE prophpmvc;
      # CREATE USER 'prophpmvc'@localhost IDENTIFIED BY 'prophpmvc';
      # GRANT ALL PRIVILEGES ON prophpmvc.* TO 'prophpmvc'@localhost;
      # FLUSH PRIVILEGES;
#   (Optional)
#   SET GLOBAL general_log_file='/var/log/mariadb/general_queries.log';
#   SET GLOBAL general_log=1;

# php
sudo yum -y install php8.0
sudo yum install libapache2-mod-php8.0 php8.0-fpm libapache2-mod-fcgid php8.0-curl php8.0-dev php8.0-gd php8.0-mbstring php8.0-zip php8.0-mysql php8.0-xml php-simplexml
sudo yum -y install php php-mysql
sudo yum -y install epel-release
sudo yum-config-manager --enable remi-php74
sudo yum -y update
sudo yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
sudo yum -y install yum-utils
sudo yum -y install php7.4
sudo yum -y install zip unzip php7.4-mysql php7.4-curl php7.4-ctype php7.4-uuid php7.4-iconv php7.4-json php7.4-mbstring php7.4-gd php7.4-intl php7.4-xml php7.4-zip php7.4-gettext php7.4-pgsql php7.4-bcmath php7.4-redis php-memcached php7.4-zip
update-alternatives --set php /usr/bin/php7.4
#   || update-alternatives --set php /usr/bin/php8.0
#         a2enmod php8.0

#   sudo vi /etc/httpd/conf/httpd.conf
#     LoadModule php7_module modules/libphp7.so
#     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#     <IfModule dir_module>
#       DirectoryIndex index.html index.php
#     </IfModule>
#   sudo systemctl restart httpd
#
#   sudo vi /etc/httpd/conf/httpd.conf
#     User vagrant
#     Group vagrant
#   chown -R vagrant:vagrant /tmp/
#   chown -R vagrant:vagrant /var/lib/php/session

#   (Optional)
#   sudo vi /etc/php.ini
#     log_errors = On
#     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#     error_log  = /var/log/php_errors.log
#
sudo systemctl restart httpd

# Permission denied?
#     sudo setenforce 0
#     check you can access files
#     sudo vi /etc/selinux/config
#         SELINUX=disabled
#     vagrant reload

# composer
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
HASH="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
mkdir -p ~/.composer
sudo chown -R $USER ~/.composer
# cd /var/www/html/
# composer init
# composer require --dev vimeo/psalm
# composer require ext-memcache

# memcached
sudo yum -y install memcached
# sudo vi /etc/sysconfig/memcached
#     PORT="11211"
#     USER="memcached"
#     MAXCONN="1024"
#     CACHESIZE="64"
#     OPTIONS="-l 127.0.0.1 -U 0"
# systemctl restart memcached
systemctl start memcached
systemctl enable memcached
sudo yum install php-memcache
sudo yum install libmemcached

# cli apps
sudo yum install net-tools