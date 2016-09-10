#!/usr/bin/python

"""
CLStore

Scrapes and stores Craigslist data
"""

# MODULES
# | Native
from time import sleep
from os import sys, path
import argparse

# | Third-Party
from craigslist import CraigslistHousing

# | Custom
# update sys.path
sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.clconfig import CLConfig
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
def readCLIParameters():
	#
	# Purpose: parse CLI arguments/parameters
	#
	# Parameters: NONE
	#
	# Returns: Args() object
	#

	# read in CLI arguments using argparse
	cliParser = argparse.ArgumentParser(description='A script for retriving and storing Craigslist listings for analysis.')
	cliParser.add_argument('-c', '--configfile', help='Application Configuration File', required=True)

	# parse arguments and return them
	return cliParser.parse_args()

def startQueryFetcher(CLT, searchMaxPrice, searchSort, searchResultCnt, searchSleepTime):
	#
	# Purpose: query CL, scrape listings, and store them
	#
	# Parameters:
	#
	# Returns: NONE
	#

	# create CL Housing obj
	CLH = CraigslistHousing(
		site='worcester',
		category='apa',
		filters={
			'max_price': searchMaxPrice,
			'cats_ok': True
		})

	# start search query loop
	resultCount = 0
	while True:
		# scrape listing
		# log start message
		CLT.logMsg('Fetching listings...', 'INFO')
		# print "%s" % runningConfig.config.get('search', 'search_result_count')
		CL_results = CLH.get_results(sort_by=searchSort, limit=searchResultCnt, geotagged=True)

		# log status message
		CLT.logMsg('Finished fetching [ %d ] result(s)' % searchResultCnt, 'INFO')

		# iterate through the results
		for result in CL_results:
			resultCount += 1

			# log result
			resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s' \
				% (resultCount, result['id'], result['datetime'], result['where'], result['price'], result['name'])
			CLT.logMsg(resultLogMsg, 'DEBUG')

			# send result to db
			# TODO

		# sleep for desired number of seconds
		# log sleep message
		CLT.logMsg('Sleeping for [ %d ]...' % searchSleepTime, 'INFO')

		sleep(searchSleepTime)

def main():
	# parse CLI params
	cliParams = readCLIParameters()

	# read in app config
	runningConfig = CLConfig()
	runningConfig.readConfig(cliParams.configfile)

	# normalize config data
	searchSort = runningConfig.config.get('search', 'search_sort')
	searchMaxPrice = float(runningConfig.config.get('search', 'search_max_price'))
	searchResultCnt = int(runningConfig.config.get('search', 'search_result_count'))
	searchSleepTime = float(runningConfig.config.get('search', 'search_query_sleep_time'))

	print '==================='
	print '   -= CLStore =-   '
	print '==================='

	print 'Starting CLStore...'

	# initialize components
	CLT = CLTool()
	CLT.initializeLogger(runningConfig.config.get('logging', 'log_file'))
	db_conn = CLT.initializeDbConnection(runningConfig.config.get('database', 'db_host', 1),
										 runningConfig.config.get('database', 'db_user', 1),
										 runningConfig.config.get('database', 'db_pass', 1),
										 runningConfig.config.get('database', 'db_name', 1),
										 runningConfig.config.get('database', 'db_port', 1)
	)

	startQueryFetcher(CLT, searchMaxPrice, searchSort, searchResultCnt, searchSleepTime)

if __name__ == '__main__':
	main()
