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

echo "$LG_LINE_BRK"

echo "Creating directory..."
mkdir $APP_DIR > /dev/null 2>&1

echo "$LG_LINE_BRK"

echo "Moving files..."
rsync -anv ./ $APP_DIR

echo "$LG_LINE_BRK"

echo "Setting permissions..."
chgrp -R www-data "$APP_DIR/CLData" "$APP_DIR/CLWeb"
chmod -R 775 $APP_DIR

echo "$LG_LINE_BRK"

echo "Configuring database..."
echo "Please enter the password for the MySQL root user below whenever prompted:"
mysql -u root -p cltools < cltools.sql

echo "$LG_LINE_BRK"

echo "Installation complete!"