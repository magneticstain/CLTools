#!/usr/bin/python

"""
CLStore

Scrapes and stores Craigslist listings/data
"""

# MODULES
# | Native
from os import sys, path

# | Third-Party
from craigslist import CraigslistHousing
import argparse

# | Custom
# update sys.path
sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.clconfig import CLConfig
from lib.cllogging import CLLogging
from lib.cllisting import CLListing

# METADATA
__author__ = 'Joshua Carlson-Purcell'
__copyright__ = 'Copyright 2016, CarlsoNet'
__license__ = 'MIT'
__version__ = '1.0.0-alpha'
__maintainer__ = 'Joshua Carlson-Purcell'
__email__ = 'jcarlson@carlso.net'
__status__ = 'Development'

# FUNCTIONS
def readCLIParameters():
	"""
	Purpose: parse CLI arguments/parameters

	Parameters: NONE

	Returns: Args() object
	"""

	# read in CLI arguments using argparse
	cliParser = argparse.ArgumentParser(description='A script for retriving and storing Craigslist listings for analysis.')
	cliParser.add_argument('-c', '--configfile', help='Application Configuration File', required=True)
	cliParser.add_argument(
		'-s', '--searchsleeptime', help='Length to wait between search queries in seconds (default: 600)',
		required=False, type=int)
	cliParser.add_argument('-v', '--verbosity', help='How verbose the logs will be // CRITICAL : ERROR : WARNING : INFO (DEFAULT) : DEBUG', required=False)

	# parse arguments and return them
	return cliParser.parse_args()

def main():
	# parse CLI params
	cliParams = readCLIParameters()

	# print display heading
	print '==================='
	print '   -= CLStore =-   '
	print '==================='
	print 'Starting CLStore...'

	# read in app config
	try:
		runningConfig = CLConfig()
		runningConfig.readConfig(cliParams.configfile)

		# normalize config data
		# DB
		dbHost = runningConfig.config.get('database', 'db_host', 1)
		dbUser = runningConfig.config.get('database', 'db_user', 1)
		dbPass = runningConfig.config.get('database', 'db_pass', 1)
		dbName = runningConfig.config.get('database', 'db_name', 1)
		dbPort = runningConfig.config.get('database', 'db_port', 1)
		# logging
		logFile = runningConfig.config.get('logging', 'log_file')
		logVerbosityLvl = runningConfig.config.get('logging', 'log_verbosity_lvl')
		# search
		searchSort = runningConfig.config.get('search', 'search_sort')
		searchResultCnt = int(runningConfig.config.get('search', 'search_result_count'))
		searchSleepTime = float(runningConfig.config.get('search', 'search_query_sleep_time'))
		# listings
		listingRegion = runningConfig.config.get('listing', 'region')
		listingCatg = runningConfig.config.get('listing', 'category')
		listingMinPrice = runningConfig.config.get('listing', 'min_price')
		listingMaxPrice = runningConfig.config.get('listing', 'max_price')
	except Exception as e:
		print '[CONFIG ERROR] :: could not read in configuration option :: [ %s ]' % e.message

		sys.exit(1)

	# check if certain CLI params were specified
	if cliParams.searchsleeptime:
		# search sleep time specified as CLI variable and should override the conf value
		searchSleepTime = cliParams.searchsleeptime

	if cliParams.verbosity:
		# log verbosity level specified, override config file value
		logVerbosityLvl = cliParams.verbosity

	# initialize components
	# CL Housing handler
	CLHousing = CraigslistHousing(
		site=listingRegion,
		category=listingCatg,
		filters={
			# comment option below if you're a heathen who doesn't have a cat :3
			'cats_ok': True,
			'has_image': True,
			'min_price': listingMinPrice,
			'max_price': listingMaxPrice
		}
	)

	# Logging
	CLLogger = CLLogging(logFile, logVerbosityLvl)

	# Listing Engine
	dbConfig = {'host': dbHost, 'user': dbUser, 'pass': dbPass, 'name': dbName, 'port': dbPort}
	CLListingEngine = CLListing(dbConfig, CLLogger, CLHousing)

	# start fetching listings
	CLListingEngine.startQueryFetcher(searchSort, searchResultCnt, searchSleepTime)

	# close db connection before ending program
	CLListingEngine.dbConn.close()

if __name__ == '__main__':
	main()
