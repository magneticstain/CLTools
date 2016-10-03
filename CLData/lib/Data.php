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

//			throw new \Exception('testing');
		}

		# GETTERS
		public function getData()
		{
			/*
			 *  Purpose: getter for private data variable
			 *
			 *  Params: NONE
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
			if($this->dataField === '' || $this->dataField === '*' || 1 < count($dataSet))
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

		public function retrieveListingFromDb(
			$sortOpt = 'post_date',
			$sortOrder = 'desc',
			$resultLimit = 10,
			$resultFormat = \PDO::FETCH_ASSOC)
		{
			/*
			 *  Purpose: retrieve listing data from database
			 *
			 *  Params:
			 * 		* $sortOpt :: field to sort results by
			 * 		* $sortOrder :: order to sort results by
			 * 		* $resultLimit :: max number of records to return
			 * 		* $resultFormat :: format to set fetched data in
			 *
			 *  Returns: array
			 */

			// list and set approved sort and sort order options
			$sortOpts = ['listing_id', 'post_date', 'location', 'price'];
			$sortOrderOpts = ['asc', 'desc'];
			$sortSqlClause = '';
			if(in_array($sortOpt, $sortOpts, true) || (0 < $sortOpt && $sortOpt <= 50))
			{
				$sortSqlClause = 'ORDER BY '.$sortOpt;

				// check for sort order option
				if(in_array(strtolower($sortOrder), $sortOrderOpts, true))
				{
					$sortSqlClause .= ' '.strtoupper($sortOrder);
				}
			}
			// validate given limit
			$limitSqlClause = '';
			if(0 < $resultLimit)
			{
				$limitSqlClause = 'LIMIT '.$resultLimit;
			}

			// fetch listings
			if(strtolower($this->listingID === 'all'))
			{
				// check if all fields were requested or a specific one
				if(!empty($this->dataField) && $this->dataField !== '*' && $this->dataField !== 'listing_id')
				{
					$sqlSelectClause = 'listing_id, '.$this->dataField;
				}
				else
				{
					$sqlSelectClause = '*';
				}

				// fetch all (max 5k) listings
				$sql = '
						SELECT
							'.$sqlSelectClause.'
						FROM
							listings
						'.$sortSqlClause.'
						'.$limitSqlClause.'
				';
//				echo $sql;

				// prepare query
				$sqlStmt = $this->dbConn->prepare($sql);

				// execute query
				$sqlStmt->execute();

				// fetch results
				$this->data = $sqlStmt->fetchAll($resultFormat);
			}
			elseif(isset($this->listingID) && !empty($this->listingID) && $this->listingID !== 0)
			{
				// fetch specific listing
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