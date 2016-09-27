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

			$dataSet = $this->data;

			// strip primary key (id) from dataset (it's preferred not to leak this information from a security standpoint)
			if(isset($dataSet['id']))
			{
				unset($dataSet['id']);
			}
			elseif(isset($dataSet[0]['id']))
			{
				// multiple listings included; traverse over each one separately and remove its ID field from the dataset
				foreach($dataSet as $key => $listing)
				{
					unset($dataSet[$key]['id']);
				}
			}

			// return all info if no field name is specified
			if($this->dataField === '' || $this->dataField === '*')
			{
				return $dataSet;
			}

			// if a field name is set, but doesn't exist, return a blank array
			if(!array_key_exists($this->dataField, $dataSet))
			{
				return [];
			}

			// the default option is to return the most specific data possible
			return $dataSet[$this->dataField];
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
			if(isset($this->listingID) && !empty($this->listingID) && $this->listingID !== 0)
			{
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
			else
			{
				// no listing ID set, retrieve last 5 listings by date
				$sql = '
						SELECT
							*
						FROM
							listings
						ORDER BY post_date DESC
						LIMIT 5
				';

				// prepare query
				$sqlStmt = $this->dbConn->prepare($sql);

				// execute query
				$sqlStmt->execute();

				// fetch results
				$this->data = $sqlStmt->fetchAll($resultFormat);
			}
		}
	}

?>