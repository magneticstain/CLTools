# CLTools
A set of python scripts that use Craigslist to help find the perfect apartment.

## Inspired By
* https://www.dataquest.io/blog/apartment-finding-slackbot/

## Requirements
* https://github.com/juliomalegria/python-craigslist
* python mysqldb
  * `pip install MySQL-python`
* Debian
  * libmysqlclient-dev

## Usage
```
usage: clstore.py [-h] -c CONFIGFILE [-s SEARCHSLEEPTIME]
```

### Example
```
./clstore.py -c /opt/CLTools/conf/ca_search.cfg -s 15
```

This example starts CLStore using the config file `/opt/CLTools/conf/ca_search.cfg` and a search query sleep time of `15` seconds.

## Services
### CLStore
CLStore is a script that scrapes Craigslist postings based on our defined parameters and stores them 
for further analysis.