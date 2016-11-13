#!/usr/bin/python3

"""
CLListing.py

A Python library for handling CraigsList listings
"""

# MODULES
# | Native
from os import sys,path
from time import sleep
from MySQLdb import Error as mysqldb_error
import MySQLdb

# | Third-Party

# | Custom

# METADATA
__author__ = 'Joshua Carlson-Purcell'
__copyright__ = 'Copyright 2016, CarlsoNet'
__license__ = 'MIT'
__version__ = '1.0.0-alpha'
__maintainer__ = 'Joshua Carlson-Purcell'
__email__ = 'jcarlson@carlso.net'
__status__ = 'Development'

class CLListing:
	"""
	Library for downloading, parsing, storing, and retrieving CraigsList listings
	"""

	dbConn = None
	CLL = None
	CLH = None
	numNewResultsSinceStartup = 0

	def __init__(self, dbConfig, CLL, CLH):
		self.dbConn = self.initializeDbConnection(dbConfig['host'], dbConfig['user'], dbConfig['pass'], dbConfig['name'], dbConfig['port'])
		self.CLL = CLL
		self.CLH = CLH

	# DATABASE
	# initialize db connection
	def initializeDbConnection(self, dbHost, dbUser, dbPass, dbName, dbPort=3306):
		"""
		Purpose: Create a new db connection to be used by the application

		Parameters:
			* dbHost :: string :: hostname or IP of database server
			* dbUser :: string :: db username
			* dbPass :: string :: db user password
			* dbName :: string :: name of database to connect to
			* dbPort :: string :: network port that client should connect to on db server

		Returns: MySQLdb object
		"""

		return MySQLdb.connect(host=dbHost, port=int(dbPort), user=dbUser, passwd=dbPass, db=dbName)

	# LISTINGS
	def checkForListingInDB(self, listingID):
		"""
		Purpose: check if a record for the given listing ID is present in the database

		Parameters:
			* listingID :: int :: Craigslist ID of listing

		Returns: Bool
		"""

		# prepare SQL
		sql = """SELECT
					id
				FROM
					listings
				WHERE
					listing_id = %d
				LIMIT 1""" % int(listingID)

		try:
			# create db cursor
			dbCursor = self.dbConn.cursor()

			# try running query
			dbCursor.execute(sql)

			if dbCursor.rowcount == 1:
				# listing already exists
				return True
			else:
				return False
		except mysqldb_error, e:
			# log error
			self.CLL.logMsg(
				'checkForListingInDB() :: failed to run SQL query to check if listing already exists in db :: [ %s ] :: [ %s ]' % (
				e.message, sql), 'ERROR')

	def sendCLResultToDB(self, CLResult):
		"""
		Purpose: take a CL result and add/update it in the database

		Parameters:
			* clResult :: CL result obj

		Returns: NONE
		"""

		if not self.checkForListingInDB(CLResult['id']):
			# listing is not in db yet, let's insert it
			# prepare SQL
			sql = """INSERT INTO
						listings(listing_id, post_date, url, location, price, name, geotag)
					VALUES('%d', '%s', '%s', '%s', '%d', '%s', '%s')""" \
				  % (int(CLResult['id']), CLResult['datetime'], CLResult['url'], CLResult['where'],
					 int(CLResult['price'].replace('$', '')), CLResult['name'], CLResult['geotag'])

			try:
				# create db cursor
				dbCursor = self.dbConn.cursor()

				# try running query
				dbCursor.execute(sql)

				# commit the transaction
				self.dbConn.commit()

				# increase counter for number of total new results
				self.numNewResultsSinceStartup += 1
			except mysqldb_error, e:
				# rollback transaction
				self.dbConn.rollback()

				# log and return error
				self.CLL.logMsg('sendCLResultToDB() :: failed to run SQL query for CL result :: [ %s ] :: [ %s ]' % (
				e.message, sql), 'ERROR')
		else:
			# log message of attempt
			self.CLL.logMsg('sendCLResultToDB() :: listing already present in db :: [ ID: %s ]' % CLResult['id'], 'DEBUG')

	def startQueryFetcher(self, searchSort, searchResultCnt, searchSleepTime):
		"""
		Purpose: query CL, scrape listings, and store them

		Parameters:
			* searchSort :: string :: sort method for search results
			* searchResultCnt :: string :: number of search results to query for and retrieve
			* searchSleepTime :: int :: number of seconds to sleep between CL search queries

		Returns: NONE
		"""

		# start search query loop
		try:
			while True:
				# scrape listing
				# log start message
				self.CLL.logMsg('Fetching listings...', 'INFO')
				CL_results = self.CLH.get_results(
					sort_by=searchSort,
					limit=searchResultCnt,
					geotagged=True
				)

				# log status message
				self.CLL.logMsg('Finished fetching [ %d ] result(s)' % searchResultCnt, 'INFO')

				# iterate through the results
				resultCount = 0
				for result in CL_results:

					# print 'RESULT: %s' % result

					resultCount += 1

					# log result
					resultLogMsg = '[ Result # %d ] | [ ID: %s ] :: [ Posted: %s ] :: [ URL: %s ] :: [ Location: %s ] :: [ Price: %s ] - %s - [ geotag: { %s } ]' \
								   % (resultCount, result['id'], result['datetime'], result['url'], result['where'],
									  result['price'], result['name'], result['geotag'])
					self.CLL.logMsg(resultLogMsg, 'DEBUG')

					# send result to db
					try:
						self.sendCLResultToDB(result)
					except Exception as e:
						self.CLL.logMsg('startQueryFetcher() :: error sending CL result to database :: %s' % e.message, 'ERROR')

				# log metrics
				self.CLL.logMsg('[ METRICS ] - [ LISTINGS ] - number of new listings added since startup: [ ' + str(self.numNewResultsSinceStartup) + ' ]', 'INFO')

				# sleep for desired number of seconds
				# log sleep message
				self.CLL.logMsg('Sleeping for [ %ds ]...' % searchSleepTime, 'INFO')

				sleep(searchSleepTime)
		# except KeyboardInterrupt:
		# 	# close db connection
		# 	self.dbConn.close()
		#
		# 	# quit
		# 	sys.exit()
		except Exception as e:
			print '[LISTING ERROR] :: could not retrieve CraigsList listings :: [ %s ]' % e.message

			sys.exit(1)
