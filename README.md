[![Stories in Ready](https://badge.waffle.io/magneticstain/CLTools.png?label=ready&title=Ready)](https://waffle.io/magneticstain/CLTools)
[![Coverage Status](https://coveralls.io/repos/github/magneticstain/CLTools/badge.svg?branch=master)](https://coveralls.io/github/magneticstain/CLTools?branch=master)
[![Build Status](https://travis-ci.org/magneticstain/CLTools.svg?branch=master)](https://travis-ci.org/magneticstain/CLTools)

# CLTools
A set of python and PHP scripts that use Craigslist to help find the perfect apartment.

## Inspired By
* https://www.dataquest.io/blog/apartment-finding-slackbot/

## Requirements
### OS
* Debian 6<=
* RHEL/CentOS 6<=

### Software
* Apache
* MySQL/MariaDB
* PHP 5.4<=

### Misc. Software/Libraries/Plugins
#### PHP
* PHP PEAR
* php5-mysql

#### Python
* https://github.com/juliomalegria/python-craigslist
  * `pip install python-craigslist`
* python mysqldb
  * `pip install MySQL-python`
* Debian
  * libmysqlclient-dev
  
## Getting Started
### Install
To install CLTools, run the install script `install.sh`. This script will run through each step of installing CLTools:

* copying application files
* configuring file permissions
* configuring database

### Configuration
#### Apache/nginx
When setting up a site in Apache/nginx for CLTools, you will need to make the root directory the directory that the `CLTools` directory is in.

For example: if CLTools, and the contents of the `CLTools` directory, are deployed to `/opt`, `/opt` would need to be the Apache site's root directory.

Here is an example Apache config that can be based off of:
```apacheconfig
<IfModule mod_ssl.c>
    <VirtualHost _default_:443>
	ServerName dev.carlso.net

        DocumentRoot /var/www/html

        <Directory /var/www/html/>
            Options Indexes FollowSymLinks
            AllowOverride All
            Order allow,deny
            Allow from all
            Require all granted
        </Directory>

        LogLevel warn

        ErrorLog /var/log/cltools/clweb-errors.log
        CustomLog /var/log/cltools/clweb-access_log combined

        SSLEngine on

        # Replace these with your own TLS certificates
        SSLCertificateFile  <SSL_CERT_FILE>
        SSLCertificateKeyFile <SSL_KEY_FILE>

        <FilesMatch "\.(cgi|shtml|phtml|php)$">
            SSLOptions +StdEnvVars
        </FilesMatch>

        BrowserMatch "MSIE [2-6]" \
            nokeepalive ssl-unclean-shutdown \
            downgrade-1.0 force-response-1.0
        # MSIE 7 and newer should be able to use keepalive
        BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown

        # Cipherlist [ via cipherli.st ]
        SSLCipherSuite EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
        SSLProtocol All -SSLv2 -SSLv3
        SSLHonorCipherOrder On
        # Requires Apache >= 2.4
        SSLCompression off

	# compress text, html, javascript, css, xml, json:
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
	AddOutputFilterByType DEFLATE application/json
    </VirtualHost>
</IfModule>
```

#### CLTools Database Settings
After setting up CLTools for your web server software, the next thing we will need to do to complete the configuration is update the database settings to work with your environment.
To do that, update the respective variables in each of the config files before with the correct values:

* `CLData/conf/db.php`
* `CLStore/conf/example.cfg` (or your customized config for CLStore)

## Services
### CLStore
CLStore is a set of python scripts and libraries that scrapes CraigsList postings based on our defined parameters and stores them 
for further analysis.

#### Usage
```
usage: clstore.py [-h] -c CONFIGFILE [-s SEARCHSLEEPTIME] [-v|--verbosity (DEBUG|INFO|WARNING|ERROR|CRITICAL)
```

##### Example
```
./clstore.py -c /opt/CLTools/conf/ca_search.cfg -s 15 -v INFO
```

This example starts CLStore using the config file `/opt/CLTools/conf/ca_search.cfg` and a search query sleep time of `15` seconds.
The logs will contain any log messages generated with a severity level of `INFO` and above.

### CLData
CLData is a backend service written in PHP that provides calculations, metrics, and any other raw data that's needed by the web application.
This data can be accessed directly by the user using REST calls, and is also utilized by CLWeb - the CLTools analysis front-end.

#### API
For instructions on using the CLTools API, see our [CLData API doc](https://github.com/magneticstain/CLTools/wiki/CLData-API-Guide).

### CLWeb
CLWeb is the front-end web application for fetching and displaying the listing analysis data to the user in an elegant and easy-to-use way.
This webapp utilizes PHP to provide an HTML5/JS webpage.

## Contributing
If you're interested in contributing to CLTools, please see our [Developer's Guide](https://github.com/magneticstain/CLTools/wiki/Developer's-Guide) in the project wiki.