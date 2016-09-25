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
		public $listingID = 0;
		public $dataField = '';
		private $data = array();

		public function __construct($dbConnConfig, $listingID = 0, $dataField = '*')
		{
			// no sanatizing is really needed because mysql and its drivers/libraries should do all the sanatizing we need
			$this->dbConnConfig = $dbConnConfig;

			// start db connection
			$this->connectToDb();

			// set local vars
			$this->listingID = $listingID;
			$this->dataField = $dataField;
		}

		# GETTERS
		public function getData()
		{
			/*
			 *  Purpose: getter for private data variable
			 *
			 *  Params:
			 * 		* $field :: field name of data to specifically return :: [ OPT ]
			 *
			 *  Returns: private obj var
			 */

			if($this->dataField === '' || $this->dataField === '*')
			{
				return $this->data;
			}

			// if a field name is set, return only that
			if(!array_key_exists($this->dataField, $this->data))
			{
				return [];
			}

			return $this->data[$this->dataField];
		}

		# OTHER FUNCTIONS
		public function connectToDb()
		{
			/*
			 *  Purpose: create a connection to a given database and return an object representing that
			 *
			 *  Params: NONE
			 *
			 *  Returns: NONE
			 */

			// generate DSN
			$dsn = 'mysql:host='.$this->dbConnConfig['dbHost'].';dbname='.$this->dbConnConfig['dbName'].';port='.$this->dbConnConfig['dbPort'].';charset=utf8';

			// set PDO options
			$pdoOpts = [
				\PDO::ATTR_ERRMODE              =>  \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE   =>  \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES		=>	false
			];

			// create PDO object using db config data and return it to the user
			$this->dbConn = new \PDO($dsn, $this->dbConnConfig['dbUser'], $this->dbConnConfig['dbPass'], $pdoOpts);
		}

		public function disconnectFromDb()
		{
			/*
			 *  Purpose: destroys connection to CLTools db
			 *
			 *  Params: NONE
			 *
			 *  Returns: bool
			 */

			// set connection variable to NULL, per PHP docs - http://php.net/manual/en/pdo.connections.php
			$this->dbConn = null;
		}

		public function retrieveListingFromDb($resultFormat = \PDO::FETCH_ASSOC)
		{
			/*
			 *  Purpose: retrieve listing data from database
			 *
			 *  Params:
			 * 		* $resultFormat :: format to set fetched data in :: [ OPT ]
			 *
			 *  Returns: array
			 */

			// craft sql query
			$sql = '
					SELECT
						*
					FROM
						listings
					WHERE
						listing_id = :listingID
					LIMIT 1
			';

			// prepare query
			$sqlStmt = $this->dbConn->prepare($sql);

			// bind listing ID param
			$sqlStmt->bindValue(':listingID', $this->listingID, \PDO::PARAM_INT);

			// execute query
			$sqlStmt->execute();

			// fetch results
			$this->data = $sqlStmt->fetch($resultFormat);
		}
	}

?>