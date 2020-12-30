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

# php
sudo yum -y install php8.0
sudo yum install libapache2-mod-php8.0 php8.0-fpm libapache2-mod-fcgid php8.0-curl php8.0-dev php8.0-gd php8.0-mbstring php8.0-zip php8.0-mysql php8.0-xml
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
service httpd restart

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