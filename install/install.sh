#!/bin/bash

#
# CLTools Installation Script v1.0.0
#   - guides the user through installing CLTools in their environment
#

LG_LINE_BRK="===================================================="

echo "Beginning CLTools installation..."
echo ""
echo "If you'd like to continue with the default value, press enter at the prompt."
echo ""

# create application folder if it doesn't exist
read -p "Directory to install application files? [Default: /opt/cltools/]: " APP_DIR
if [ -z "$APP_DIR" ]
then
    # use default value
    APP_DIR="/opt/cltools/"
fi

echo "Creating application directory..."
mkdir $APP_DIR > /dev/null 2>&1

echo "$LG_LINE_BRK"

# create log folder if it doesn't exist
read -p "Directory to write application logs to? [Default: /var/log/cltools/]: " LOG_DIR
if [ -z "$LOG_DIR" ]
then
    # use default value
    LOG_DIR="/var/log/cltools/"
fi

echo "Creating log directory..."
mkdir $LOG_DIR > /dev/null 2>&1

echo "$LG_LINE_BRK"

# move application files
echo "Moving files..."
rsync -av ../* $APP_DIR

echo "$LG_LINE_BRK"

# configure file permissions
echo "Setting permissions..."
# app files
chown -R root:www-data "${APP_DIR}CLData" "${APP_DIR}CLWeb"
chmod -R 775 $APP_DIR
# log files
chown -R root:adm "$LOG_DIR"
chmod 644 "$LOG_DIR"

echo "$LG_LINE_BRK"

echo "Configuring database..."
echo "Please enter the password for the MySQL root user below whenever prompted."
echo "Creating database..."
mysql -u root -p -e "create database cltools"
echo "Import table schema..."
mysql -u root -p cltools < cltools.sql

echo "$LG_LINE_BRK"

echo "Installation complete!"