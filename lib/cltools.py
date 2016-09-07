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

	def __init__(self, toolName):
		"""
		Constructor for CLTools class
		:param toolName:
		"""

		if toolName != '' or toolName == None:
			raise ValueError('tool name not defined')
		else:
			self.name = toolName

	# LOGGING
	def initializeLogger(logFormat='%(asctime)s [ %(levelname)s ] %(message)s', dateFormat='%m/%d/%Y %I:%M:%S %p'):
		#
		# Purpose: Define a logger object to be used for sending logs
		#
		# Parameters:
		#	* logFormat :: string :: formatting that logs message should be in :: [OPTIONAL]
		#	* dateFormat :: string :: formatting of log timestamp, if applicable :: [OPTIONAL]
		#
		# Returns: Logger()
		#

		# create log formatter obj
		logFormatter = logging.Formatter(fmt=logFormat, datefmt=dateFormat)

		# set file handler
		logFileHandler = logging.FileHandler(filename=config.LOG_FILE)

		# associate file handler with Formatter
		logFileHandler.setFormatter(logFormatter)

		# initialize logger
		logger = logging.getLogger(__name__)

		# associate handler with logger
		logger.addHandler(logFileHandler)

		return logger

	def logMsg(logger, log, logLevel):
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
	def initializeDbConnection():
		#
		# Purpose: Create a new db connection to be used by the application
		#
		# Parameters:
		#	* log :: string :: log message to be written to log file
		#
		# Returns: MySQLdb object
		#

		return MySQLdb.connect(host=config.DB_HOST, port=config.DB_PORT,
							   user=config.DB_USER, passwd=config.DB_PASS,
							   db=config.DB_NAME)
