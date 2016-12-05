#!/bin/bash

#
# CLTools Build Script
#   - express build CLTools for a given server
#

APP_DIR="/opt/cltools/"
LOG_DIR="/var/log/cltools/"

# create directories
echo "Creating application directory..."
mkdir $APP_DIR > /dev/null 2>&1

echo "Creating log directory..."
mkdir $LOG_DIR > /dev/null 2>&1

# setup database
echo "Creating database..."
mysql -u root -e "create database cltools"
echo "Creating service account..."
mysql -u root -e "GRANT SELECT,INSERT,UPDATE,DELETE ON cltools.* TO cltools@'localhost' IDENTIFIED BY ''"
mysql -u root -e "FLUSH PRIVILEGES"
echo "Import table schema..."
mysql -u root cltools < install/cltools.sql

# run any tests
# PHPUnit
phpunit

echo "Build complete!"

exit 0