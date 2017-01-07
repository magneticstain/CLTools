#!/usr/bin/python

"""
CLLog

Main class for CLTools logging components
"""

# MODULES
# | Native
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
__status__ = 'Development'

class CLLogging:
	""""
	Main class for CLTools logging components
	"""

	name = 'CLLogging'
	config = ''
	logger = ''
	verbosityLvl = 0

	def __init__(self, logFile='/var/log/cltools/cltools.log', logVerbosityLvl='INFO',
				 logFormat='%(asctime)s [ %(levelname)s ] %(message)s', dateFormat='%m/%d/%Y %I:%M:%S %p'):
		"""
		Constructor for CLLogging class
		"""

		# create logger
		self.initializeLogger(logFile, logVerbosityLvl, logFormat, dateFormat)

	def initializeLogger(self, logFile='/var/log/cltools/cltools.log', logVerbosityLvl='INFO', logFormat='%(asctime)s [ %(levelname)s ] %(message)s', dateFormat='%m/%d/%Y %I:%M:%S %p'):
		"""
		Purpose: Define a logger object to be used for sending logs

		Parameters:
			* logFile :: string :: file to log to :: [OPTIONAL]
			* logVerbosityLvl :: string :: minimum log level threshold :: [OPTIONAL]
			* logFormat :: string :: formatting that logs message should be in :: [OPTIONAL]
			* dateFormat :: string :: formatting of log timestamp, if applicable :: [OPTIONAL]

		Returns: NONE
		"""

		# create log formatter obj
		logFormatter = logging.Formatter(fmt=logFormat, datefmt=dateFormat)

		# set file handler
		logFileHandler = logging.FileHandler(filename=logFile)

		# associate file handler with Formatter
		logFileHandler.setFormatter(logFormatter)

		# initialize logger
		self.logger = logging.getLogger(__name__)

		# associate handler with logger
		self.logger.addHandler(logFileHandler)

		# Verbosity Level
		# normalize log verbosity level to all uppercase
		logVerbosityLvl = logVerbosityLvl.upper()
		# set numerical log level of logger based on logLevel string
		numericalLogLevel = getattr(logging, logVerbosityLvl)
		self.logger.setLevel(numericalLogLevel)

	def logMsg(self, log, logLevel = 'INFO'):
		"""
		Purpose: Logging strings to a given log file

		Parameters:
		* log :: string :: log message to be written to log file
		* logLevel :: string :: the severity level of the log to be written
			( DEBUG | INFO | WARNING | ERROR | CRITICAL )

		Returns: NONE
		"""

		# write log using appropriate function based on log severity level
		getattr(self.logger, logLevel.lower())(log)
