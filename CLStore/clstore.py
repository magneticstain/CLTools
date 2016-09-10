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

def main():
	# parse CLI args
	cliParams = readCLIParameters()

	# read in app config
	runningConfig = CLConfig()
	runningConfig.readConfig(cliParams.configfile)

	print '==================='
	print '   -= CLStore =-   '
	print '==================='

	print 'Starting CLStore...'

	# initialize components
	CLT = CLTool()
	logger = CLT.initializeLogger(runningConfig.config.get('logging', 'log_file'))
	db_conn = CLT.initializeDbConnection(runningConfig.config.get('database', 'db_host', 1),
										 runningConfig.config.get('database', 'db_user', 1),
										 runningConfig.config.get('database', 'db_pass', 1),
										 runningConfig.config.get('database', 'db_name', 1),
										 runningConfig.config.get('database', 'db_port', 1)
	)

	# create CL housing data structure
	CLH = CraigslistHousing(
		site='worcester',
		category='apa',
		filters={
			'max_price': runningConfig.config.get('search', 'search_max_price'),
			'cats_ok': True
		})

	# start search query loop
	resultCount = 0
	while True:
		# scrape listing
		# log start message
		CLTool.logMsg(logger, 'Fetching listings...', 'INFO')
		CL_results = CLH.get_results(sort_by=runningConfig.config('search', 'search_sort'), limit=runningConfig.config('search', 'search_result_count'), geotagged=True)

		# log status message
		CLTool.logMsg(logger, 'Finished fetching [ %d ] result(s)' % runningConfig.config('search', 'search_result_count'), 'INFO')

		# iterate through the results
		for result in CL_results:
			resultCount += 1

			# log result
			resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s' \
				% (resultCount, result['id'], result['datetime'], result['where'], result['price'], result['name'])
			CLTool.logMsg(logger, resultLogMsg, 'DEBUG')

		# sleep for desired number of seconds
		# log sleep message
		CLTool.logMsg(logger, 'Sleeping for [ %ds ]...' % runningConfig.config('search', 'search_query_sleep_time'), 'INFO')

		sleep(runningConfig.config('search', 'search_query_sleep_time'))

if __name__ == '__main__':
	main()
