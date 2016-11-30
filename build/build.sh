#!/bin/bash

#
# CLTools Build Script
#   - express build CLTools for a given server
#

APP_DIR="/opt/cltools/"
LOG_DIR="/var/log/cltools/"

echo "Creating application directory..."
mkdir $APP_DIR > /dev/null 2>&1


echo "Creating log directory..."
mkdir $LOG_DIR > /dev/null 2>&1