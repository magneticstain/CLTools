#!/usr/bin/python

"""
CLStore

Scrapes and stores Craigslist data
"""

# MODULES
# | Native
from time import sleep
from os import sys, path

# | Third-Party
from craigslist import CraigslistHousing

# | Custom
# update sys.path
sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.CLConfig.config import CLConfig
from lib.cltools import CLTool

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
	# read in app config
	CLConfig.readConfig()

	print '==================='
	print '   -= CLStore =-   '
	print '==================='

	print 'Starting CLStore...'

	# initialize components
	logger = CLTool.initializeLogger()
	db_conn = CLTool.initializeDbConnection()

	# create CL housing data structure
	CLH = CraigslistHousing(
		site='worcester',
		category='apa',
		filters={
			'max_price': CLConfig.config['SEARCH_MAX_PRICE'],
			'cats_ok': True
		})

	# start search query loop
	resultCount = 0
	while True:
		# scrape listing
		# log start message
		CLTool.logMsg(logger, 'Fetching listings...', 'INFO')
		CL_results = CLH.get_results(sort_by=CLConfig.config['SEARCH_SORT'], limit=CLConfig.config['SEARCH_RESULT_COUNT'], geotagged=True)

		# log status message
		CLTool.logMsg(logger, 'Finished fetching [ %d ] result(s)' % CLConfig.config['SEARCH_RESULT_COUNT'], 'INFO')

		# iterate through the results
		for result in CL_results:
			resultCount += 1

			# log result
			resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s' \
				% (resultCount, result['id'], result['datetime'], result['where'], result['price'], result['name'])
			CLTool.logMsg(logger, resultLogMsg, 'DEBUG')

		# sleep for desired number of seconds
		# log sleep message
		CLTool.logMsg(logger, 'Sleeping for [ %ds ]...' % CLConfig.config['SEARCH_QUERY_SLEEP_TIME'], 'INFO')

		sleep(CLConfig.config['SEARCH_QUERY_SLEEP_TIME'])

if __name__ == '__main__':
	main()
