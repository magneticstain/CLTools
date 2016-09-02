#!/usr/bin/python

"""
CL_Store

Scrapes and stores Craigslist data
"""

# MODULES
# | Native
import json
from time import sleep

# | Third-Party
from craigslist import CraigslistHousing

# | Custom
import config

# METADATA
__author__ = 'Joshua Carlson-Purcell'
__copyright__ = 'Copyright 2016, CarlsoNet'
__license__ = 'MIT'
__version__ = '1.0.0-alpha'
__maintainer__ = 'Joshua Carlson-Purcell'
__email__ = 'jcarlson@carlso.net'
__status__ = 'Prototype'

# FUNCTIONS

def main():
	print '==================='
	print '   -= CLStore =-   '
	print '==================='

	print 'Starting CL_Store...'

	# create CL housing data structure
	CLH = CraigslistHousing(site='worcester', category='apa', filters={'max_price': config.SEARCH_MAX_PRICE, 'cats_ok': True})

	# start search query loop
	while True:
		# scrape listing
		print ''
		print 'Fetching listings...'
		print ''
		CL_results = CLH.get_results(sort_by=config.SEARCH_SORT, limit=config.SEARCH_RESULT_COUNT, geotagged=True)

		# iterate through the results
		resultCount = 0
		for result in CL_results:
			resultCount += 1

			print '[ %d ] %s' % (resultCount, result['name'])

		# sleep for desired number of seconds
		print ''
		print 'Sleeping for [ %ds ]...' % config.QUERY_SLEEP_TIME
		sleep(config.QUERY_SLEEP_TIME)

if __name__ == '__main__':
	main()