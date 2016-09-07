#!/usr/bin/python

"""
CLConfig.py

Global configuration settings and related functions
"""

# MODULES
# | Native
import ConfigParser
import os

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

class CLConfig:
	"""
	The global configuration module

	Addl. Info:
		* https://docs.python.org/2/library/configparser.html
	"""

	config = ''

	def __init__(self):
		"""
		Create new config parser object
		"""

		# create ConfigParser() obj
		self.config = ConfigParser.ConfigParser()

	def readConfig(self, configFile='/opt/CLTools/conf/main.cfg'):
		"""
		Read in configuration settings if present
		"""

		# see if config file exists
		if os.path.isfile(configFile):
			# read in config
			self.config.read(configFile)

	def writeConfig(self, configFile='/opt/CLTools/conf/main.cfg'):
		"""
		Write configuration options to disk
		"""

		# open log file for writing
		with open(configFile, 'wb') as configFileHandle:
			self.config.write(configFileHandle)


