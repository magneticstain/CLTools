#!/usr/bin/python

"""
CLStore

Scrapes and stores Craigslist listings/data
"""

# MODULES
# | Native
from time import sleep
from os import sys, path
from MySQLdb import Error as mysqldb_error

# | Third-Party
from craigslist import CraigslistHousing
import argparse

# | Custom
# update sys.path
sys.path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.clconfig import CLConfig
from lib.cllogging import CLLogging

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
	cliParser.add_argument(
		'-s', '--searchsleeptime', help='Length to wait between search queries in seconds (default: 600)',
		required=False, type=int)

	# parse arguments and return them
	return cliParser.parse_args()


def checkForListingInDB(CLL, dbConn, listingID):
	#
	# Purpose: check if a record for the given listing ID is present in the database
	#
	# Parameters:
	#	* CLL :: CLLogging obj for logging
	#	* dbConn :: database connection obj
	#	* listingID :: int :: Craigslist ID of listing
	#
	# Returns: Bool
	#

	# create db cursor
	dbCursor = dbConn.cursor()

	# prepare SQL
	sql = """SELECT
				id
			FROM
				listings
			WHERE
				listing_id = %d
			LIMIT 1""" % int(listingID)

	# try running query
	try:
		dbCursor.execute(sql)

		if dbCursor.rowcount == 1:
			# listing already exists
			return True
		else:
			return False
	except mysqldb_error, e:
		# log error
		CLL.logMsg('checkForListingInDB() :: failed to run SQL query to check if listing already exists in db :: [ %s ] :: [ %s ]' % (e.message, sql), 'ERROR')


def sendCLResultToDB(CLL, dbConn, CLResult):
	#
	# Purpose: take a CL result and add/update it in the database
	#
	# Parameters:
	#	* CLL :: CLLogging obj for logging
	#	* dbConn :: database connection obj
	#	* clResult :: CL result obj
	#
	# Returns: NONE
	#

	if not checkForListingInDB(CLL, dbConn, CLResult['id']):
		# listing is not in db yet, let's insert it
		# create db cursor
		dbCursor = dbConn.cursor()

		# prepare SQL
		sql = """INSERT INTO
					listings(listing_id, post_date, url, location, price, name, geotag)
				VALUES('%d', '%s', '%s', '%s', '%d', '%s', '%s')""" \
			  % (int(CLResult['id']), CLResult['datetime'], CLResult['url'], CLResult['where'],
				 int(CLResult['price'].replace('$', '')), CLResult['name'], CLResult['geotag'])

		# try running query
		try:
			dbCursor.execute(sql)

			# commit the transaction
			dbConn.commit()
		except mysqldb_error, e:
			# rollback transaction
			dbConn.rollback()

			# log and return error
			CLL.logMsg('sendCLResultToDB() :: failed to run SQL query for CL result :: [ %s ] :: [ %s ]' % (e.message, sql), 'ERROR')
	else:
		# log message of attempt
		CLL.logMsg('sendCLResultToDB() :: listing already present in db :: [ ID: %s ]' % CLResult['id'], 'DEBUG')


def startQueryFetcher(dbConn, CLH, CLL, searchSort, searchResultCnt, searchSleepTime):
	#
	# Purpose: query CL, scrape listings, and store them
	#
	# Parameters:
	#	* dbConn :: string :: database connection object
	#	* CLH :: CraigslistHousing handler
	#	* CLL :: CLLogging obj for logging
	#	* searchSort :: string :: sort method for search results
	#	* searchResultCnt :: string :: number of search results to query for and retrieve
	#	* searchSleepTime :: int :: number of seconds to sleep between CL search queries
	#
	# Returns: NONE
	#

	# start search query loop
	try:
		while True:
			# scrape listing
			# log start message
			CLL.logMsg('Fetching listings...', 'INFO')
			CL_results = CLH.get_results(sort_by=searchSort, limit=searchResultCnt, geotagged=True)

			# log status message
			CLL.logMsg('Finished fetching [ %d ] result(s)' % searchResultCnt, 'INFO')

			# iterate through the results
			resultCount = 0
			for result in CL_results:
				resultCount += 1

				# log result
				resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ URL: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s - [ geotag: { %s } ]' \
						% (resultCount, result['id'], result['datetime'], result['url'], result['where'], result['price'], result['name'], result['geotag'])
				CLL.logMsg(resultLogMsg, 'DEBUG')

				# send result to db
				try:
					sendCLResultToDB(CLL, dbConn, result)
				except Exception as e:
					CLL.logMsg('startQueryFetcher() :: error sending CL result to database :: %s' % e.message, 'ERROR')

			# sleep for desired number of seconds
			# log sleep message
			CLL.logMsg('Sleeping for [ %ds ]...' % searchSleepTime, 'INFO')

			sleep(searchSleepTime)
	except KeyboardInterrupt:
		# close db connection
		dbConn.close()

		# quit
		sys.exit()


def main():
	# parse CLI params
	cliParams = readCLIParameters()

	# print display heading
	print '==================='
	print '   -= CLStore =-   '
	print '==================='
	print 'Starting CLStore...'

	try:
		# read in app config
		runningConfig = CLConfig()
		runningConfig.readConfig(cliParams.configfile)

		# normalize config data
		searchSort = runningConfig.config.get('search', 'search_sort')
		searchResultCnt = int(runningConfig.config.get('search', 'search_result_count'))
		searchSleepTime = float(runningConfig.config.get('search', 'search_query_sleep_time'))
		listingRegion = runningConfig.config.get('listing', 'region')
		listingCatg = runningConfig.config.get('listing', 'category')
		listingMinPrice = runningConfig.config.get('listing', 'min_price')
		listingMaxPrice = runningConfig.config.get('listing', 'max_price')
		dbHost = runningConfig.config.get('database', 'db_host', 1)
		dbUser = runningConfig.config.get('database', 'db_user', 1)
		dbPass = runningConfig.config.get('database', 'db_pass', 1)
		dbName = runningConfig.config.get('database', 'db_name', 1)
		dbPort = runningConfig.config.get('database', 'db_port', 1)
		logFile = runningConfig.config.get('logging', 'log_file')
	except Exception as e:
		print '[CONFIG ERROR] :: could not read in configuration option :: [ %s ]' % e.message

		sys.exit(1)

	# check if certain CLI params were specified
	if cliParams.searchsleeptime:
		# search sleep time specified as CLI variable and should override the conf value
		searchSleepTime = cliParams.searchsleeptime

	# initialize components
	# CL Housing handler
	CLH = CraigslistHousing(
		site=listingRegion,
		category=listingCatg,
		filters={
			# uncomment below if you're a heathen who doesn't have a cat :3
			'cats_ok': True,
			'has_image': True,
			'min_price': listingMinPrice,
			'max_price': listingMaxPrice
		}
	)

	# Logging
	CLL = CLLogging()
	CLL.initializeLogger(logFile)

	# DB
	dbConn = CLL.initializeDbConnection(dbHost, dbUser, dbPass, dbName, dbPort)

	# start fetching listings
	startQueryFetcher(dbConn, CLH, CLL, searchSort, searchResultCnt, searchSleepTime)

	# close db connection before ending program
	dbConn.close()

if __name__ == '__main__':
	main()
