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
from MySQLdb import Error as mysqldb_error

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
	cliParser.add_argument(
		'-s', '--searchsleeptime', help='Length to wait between search queries in seconds (default: 600)',
		required=False, type=int)

	# parse arguments and return them
	return cliParser.parse_args()


def checkForListingInDB(CLT, dbConn, listingID):
	#
	# Purpose: check if a record for the given listing ID is present in the database
	#
	# Parameters:
	#	* CLT :: CLTools obj for logging
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
		CLT.logMsg('checkForListingInDB() :: failed to run SQL query to check if listing already exists in db :: [ %s ] :: [ %s ]' % (e.message, sql), 'ERROR')

def sendCLResultToDB(CLT, dbConn, CLResult):
	#
	# Purpose: take a CL result and add/update it in the database
	#
	# Parameters:
	#	* CLT :: CLTools obj for logging
	#	* dbConn :: database connection obj
	#	* clResult :: CL result obj
	#
	# Returns: NONE
	#

	if not checkForListingInDB(CLT, dbConn, CLResult['id']):
		# listing is not in db yet, let's insert it
		# create db cursor
		dbCursor = dbConn.cursor()


		# normalize result values
		listingID = int(CLResult['id'].encode('ascii', 'ignore'))
		listingPrice = int(CLResult['price'].replace('$', '').encode('ascii', 'ignore'))

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
			CLT.logMsg('sendCLResultToDB() :: failed to run SQL query for CL result :: [ %s ] :: [ %s ]' % (e.message, sql), 'ERROR')
	else:
		# log message of attempt
		CLT.logMsg('sendCLResultToDB() :: listing already present in db :: [ ID: %s ]' % CLResult['id'], 'DEBUG')


def startQueryFetcher(dbConn, CLT, searchMaxPrice, searchSort, searchResultCnt, searchSleepTime):
	#
	# Purpose: query CL, scrape listings, and store them
	#
	# Parameters:
	#
	# Returns: NONE
	#

	# create CL Housing obj
	CLH = CraigslistHousing(
		site='boston',
		category='nfa',
		filters={
			'has_image': True,
			'max_price': searchMaxPrice,
			'cats_ok': True
		})

	# start search query loop
	while True:
		# scrape listing
		# log start message
		CLT.logMsg('Fetching listings...', 'INFO')
		# print "%s" % runningConfig.config.get('search', 'search_result_count')
		CL_results = CLH.get_results(sort_by=searchSort, limit=searchResultCnt, geotagged=True)

		# log status message
		CLT.logMsg('Finished fetching [ %d ] result(s)' % searchResultCnt, 'INFO')

		# iterate through the results
		resultCount = 0
		for result in CL_results:
			resultCount += 1

			# log result
			resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ URL: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s - [ geotag: { %s } ]' \
					% (resultCount, result['id'], result['datetime'], result['url'], result['where'], result['price'], result['name'], result['geotag'])
			CLT.logMsg(resultLogMsg, 'DEBUG')

			# send result to db
			try:
				sendCLResultToDB(CLT, dbConn, result)
			except Exception as e:
				CLT.logMsg('startQueryFetcher() :: error sending CL result to database :: %s' % e.message, 'ERROR')

		# sleep for desired number of seconds
		# log sleep message
		CLT.logMsg('Sleeping for [ %ds ]...' % searchSleepTime, 'INFO')

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

	# check if CLI params were specified
	if cliParams.searchsleeptime:
		# Search sleep time specified as CLI variable and should override the conf value
		searchSleepTime = cliParams.searchsleeptime

	print '==================='
	print '   -= CLStore =-   '
	print '==================='

	print 'Starting CLStore...'

	# initialize components
	CLT = CLTool()
	CLT.initializeLogger(runningConfig.config.get('logging', 'log_file'))
	dbConn = CLT.initializeDbConnection(runningConfig.config.get('database', 'db_host', 1),
										 runningConfig.config.get('database', 'db_user', 1),
										 runningConfig.config.get('database', 'db_pass', 1),
										 runningConfig.config.get('database', 'db_name', 1),
										 runningConfig.config.get('database', 'db_port', 1)
	)

	startQueryFetcher(dbConn, CLT, searchMaxPrice, searchSort, searchResultCnt, searchSleepTime)

if __name__ == '__main__':
	main()
