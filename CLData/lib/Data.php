<?php
namespace CLTools\CLData;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  Data.php - class used for easily retrieving and providing various data and its characteristics
	 */

	class Data
	{
		public $dbConn = '';
		protected $dbConnConfig = array();
		public $listingID = 0;
		public $dataField = '';
		protected $data = array();

		public function __construct($dbConnConfig, $listingID = 0, $dataField = '*')
		{
			// no sanatizing is really needed because mysql and its drivers/libraries should do all the sanitizing we need
			$this->dbConnConfig = $dbConnConfig;

			// start db connection
			$this->connectToDb();

			// set local vars
			$this->listingID = $listingID;
			$this->dataField = $dataField;
		}
		
		// SETTERS
		public function setData($data)
		{
			/*
			 *  Purpose: set private data var
			 *
			 *  Params: NONE
			 *
			 *  Returns: bool
			 */
			
			$this->data = $data;
			
			return true;
		}

		// GETTERS
		public function getData($rawData = false, $key = 0)
		{
			/*
			 *  Purpose: getter for private variable data
			 *
			 *  Params:
			 * 		$rawData :: bool :: return raw or post-processed data
			 * 		$key :: string :: index key of data to return if data is array
			 *
			 *  Returns: various data types
			 */
			
			// check if raw data should be returned and stop there if so
			if($rawData)
			{
				return $this->data;
			}

			$dataSet = $this->data;
			
			// check if key is specified; if so, return data indexed at that key in $data
			if(!empty($key))
			{
				if(isset($this->data[$key]))
				{
					return $this->data[$key];
				}
				else
				{
					return [];
				}
			}

			// strip primary key (id) from dataset (it's preferred not to leak this information from a security standpoint)
			if(isset($dataSet['id']))
			{
				unset($dataSet['id']);
			}
			elseif(isset($dataSet[0]['id']))
			{
				// multiple listings included; traverse over each one separately and remove its ID field from the dataset
				foreach($dataSet as $dataSetKey => $listing)
				{
					unset($dataSet[$dataSetKey]['id']);
				}
			}

			// return all info if a blank or general field name is specified and there's a single record
//			var_dump($dataSet);
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

		// OTHER FUNCTIONS
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
		
		public function getTotalNumberOfListings()
		{
			/*
			 *  Purpose: get the total number of listings currently being stored in the db
			 *
			 *  Params: NONE
			 *
			 *  Returns: int
			 */
			
			$numListings = 0;
			
			// generate sql
			$sql = '
					SELECT
						COUNT(*) as count
					FROM
						listings
			';
			
			// prepare query
			$sqlStmt = $this->dbConn->prepare($sql);
			
			// execute query
			$sqlStmt->execute();
			
			// fetch results
			$results = $sqlStmt->fetch(\PDO::FETCH_OBJ);
			
			// set count if set
			if(isset($results->count))
			{
				$numListings = $results->count;
			}
			
			return $numListings;
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
			 * 		* $sortOpt :: string :: field to sort results by
			 * 		* $sortOrder :: string :: order to sort results by
			 * 		* $resultLimit :: int :: max number of records to return
			 * 		* $resultFormat :: PDO format val :: format to set fetched data in
			 *
			 *  Returns: NONE
			 */
			
			// check if db is empty
			if($this->getTotalNumberOfListings() === 0)
			{
				// no listings available, don't bother doing anything else and set data as blank array and exit the function
				$this->data = [];
				return;
			}

			// list and set approved sort and sort order options
			// user can also sort by field numbers
			$approvedSortOpts = ['listing_id', 'post_date', 'location', 'price'];
			$approvedSortOrderOpts = ['asc', 'desc'];
			$sortSqlClause = '';
			if(in_array($sortOpt, $approvedSortOpts, true) || (0 < $sortOpt && $sortOpt <= 50))
			{
				$sortSqlClause = 'ORDER BY '.$sortOpt;

				// check for sort order option
				if(in_array(strtolower($sortOrder), $approvedSortOrderOpts, true))
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
			// check if all listings were requested or a specific one
			if(strtolower($this->listingID === 'all'))
			{
				// all listings requested
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

				// prepare query
				$sqlStmt = $this->dbConn->prepare($sql);

				// execute query
				$sqlStmt->execute();

				// fetch results
				$this->data = $sqlStmt->fetchAll($resultFormat);
			}
			elseif(isset($this->listingID) && !empty($this->listingID) && $this->listingID !== 0)
			{
				// fetch all data for specific listing
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
				// no listing ID set, retrieve default: last 5 listings by date
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