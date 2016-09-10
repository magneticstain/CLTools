#!/usr/bin/python

"""
CLTools

Main class for CLTools components
"""

# MODULES
# | Native
import MySQLdb
import logging

# | Third-Party

# | Custom

# METADATA
__author__ = 'Joshua Carlson-Purcell'
__copyright__ = 'Copyright 2016, CarlsoNet'
__license__ = 'MIT'
__version__ = '1.0.0-alpha'
__maintainer__ = 'Joshua Carlson-Purcell'
__email__ = 'jcarlson@carlso.net'
__status__ = 'Prototype'

class CLTool:
	""""
	Base class for components of the CLTool suite
	"""

	name = 'CLTools'
	config = ''

	def __init__(self):
		"""
		Constructor for CLTools class
		:param toolName:
		"""

		pass

	# LOGGING
	def initializeLogger(self, logFile='/opt/CLTools/logs/cltools.log', logFormat='%(asctime)s [ %(levelname)s ] %(message)s', dateFormat='%m/%d/%Y %I:%M:%S %p'):
		#
		# Purpose: Define a logger object to be used for sending logs
		#
		# Parameters:
		#	* logFile :: string :: file to log to :: [OPTIONAL]
		#	* logFormat :: string :: formatting that logs message should be in :: [OPTIONAL]
		#	* dateFormat :: string :: formatting of log timestamp, if applicable :: [OPTIONAL]
		#
		# Returns: Logger()
		#

		# create log formatter obj
		logFormatter = logging.Formatter(fmt=logFormat, datefmt=dateFormat)

		# set file handler
		logFileHandler = logging.FileHandler(filename=logFile)

		# associate file handler with Formatter
		logFileHandler.setFormatter(logFormatter)

		# initialize logger
		logger = logging.getLogger(__name__)

		# associate handler with logger
		logger.addHandler(logFileHandler)

		return logger

	def logMsg(self, log, logLevel):
		#
		# Purpose: Logging strings to a given log file
		#
		# Parameters:
		#	* log :: string :: log message to be written to log file
		#	* logLevel :: string :: the severity level of the log to be written
		#
		# Returns: NONE
		#

		# initialize logger
		logger = logging.getLogger(__name__)

		# Verbosity Level
		# normalize log verbosity level to all uppercase
		logLevel.upper()
		# set numerical log level of logger based on logLevel string
		numericalLogLevel = getattr(logging, logLevel)
		logger.setLevel(numericalLogLevel)

		# write log using appropriate function based on log severity level
		getattr(logger, logLevel.lower())(log)

	# DATABASE
	# initialize db connection
	def initializeDbConnection(self, dbHost, dbUser, dbPass, dbName, dbPort=3306):
		#
		# Purpose: Create a new db connection to be used by the application
		#
		# Parameters:
		#	* dbHost :: string :: hostname or IP of database server
		#	* dbUser :: string :: db username
		#	* dbPass :: string :: db user password
		#	* dbName :: string :: name of database to connect to
		#	* dbPort :: string :: network port that client should connect to on db server
		#
		# Returns: MySQLdb object
		#

		return MySQLdb.connect(host=dbHost, port=int(dbPort), user=dbUser, passwd=dbPass, db=dbName)
