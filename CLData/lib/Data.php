<?php
namespace CLTools\CLData;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  Data.php - class used for easily retriving and providing various data and its characteristics
	 */

	class Data
	{
		public $dbConn = '';
		private $dbConnConfig = array();

		public function __construct($dbConnConfig)
		{
			// no sanatizing is really needed because mysql and its drivers/libraries should do all the sanatizing we need
			$this->dbConnConfig = $dbConnConfig;
		}

		# OTHER FUNCTIONS
		public function connectToDb()
		{
			/*
			 *  Purpose: create a connection to a given database and return an object representing that
			 *
			 *  Params: NONE
			 *
			 *  Returns: PDO object
			 */

			// generate DSN
			$dsn = 'mysql:host='.$this->dbConnConfig['dbHost'].';dbname='.$this->dbConnConfig['dbName'].';port='.$this->dbConnConfig['dbPort'].';charset=utf8';
			$pdoOpts = [
				\PDO::ATTR_ERRMODE              =>  \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE   =>  \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES		=>	false
			];

			// create PDO object using db config data and return it to the user
			return new \PDO($dsn, $this->dbConnConfig['dbUser'], $this->dbConnConfig['dbPass'], $pdoOpts);
		}
	}

?>