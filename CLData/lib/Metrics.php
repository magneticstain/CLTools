<?php
namespace CLTools\CLData;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  Metrics.php - class containing logic for generating metrics based on collected data
	 */

	class Metrics extends Data
	{
		private $field = '';
		private $timespan = 'monthly';
		private $operator = 'count';
		
		public function __construct(
			$dbConfig,
			$field = 'location',
			$timespan = 'monthly',
			$operator = 'count'
		)
		{
			// generate db connection from Data()
			parent::__construct($dbConfig);
			
			// set local class vars
			$this->setField($field);
			$this->setTimespan($timespan);
			$this->setOperator($operator);
		}
		
		// SETTERS
		public function setField($field)
		{
			/*
			 * 	Purpose: set given field if it matches one of the approved fields
			 *
			 * 	Params:
			 * 		* $field :: string :: data field to measure metrics of
			 *
			 * 	Returns: bool
			 */
			
			// initialize group of approved data fields
			// NOTE: blank string indicates listing records in general
			$approvedDataFields = [
				'',
				'location',
				'price'
			];
			
			// normalize field
			$field = strtolower($field);
			
			// check if given field is approved for use with metrics
			if(in_array($field, $approvedDataFields, true))
			{
				// approved ❣◕ ‿ ◕❣
				$this->field = $field;
				
				return true;
			}
			
			// not approved (ಥ﹏ಥ)
			throw new \Exception('invalid data field provided for metric generation :: [ '.$field.' ]');
		}
		
		public function setTimespan($timespan)
		{
			/*
			 * 	Purpose: set given timespan if it matches one of the approved timespans
			 *
			 * 	Params:
			 * 		* $timespan :: string :: timespan to measure metrics in
			 *
			 * 	Returns: bool
			 *
			 * 	Dev Notes:
			 * 		* [ 2016-10-31 ] - [JCP] - If you update the approved timespans in this function, you must also
			 * 			update generateMetrics accordingly
			 */
			
			$approvedTimespans = [
				'daily',
				'monthly',
				'yearly'
			];
			
			// normalize timespan param
			$timespan = strtolower($timespan);
			
			// check if timespan is approved
			if(in_array($timespan, $approvedTimespans, true))
			{
				// approved ^_^
				$this->timespan = $timespan;
				
				return true;
			}
			
			// not approved V_V
			throw new \Exception('invalid timespan provided :: [ '.$timespan.' ]');
		}
		
		public function setOperator($operator)
		{
			/*
			 * 	Purpose: set given operator if it matches one of the approved SQL operators
			 *
			 * 	Params:
			 * 		* $operator :: string :: operator to perform metrics on
			 *
			 * 	Returns: bool
			 */
			
			$availableSQLOperators = [
				'COUNT',
				'AVG'
			];
			
			// normalize operator param
			$operator = strtoupper($operator);
			
			// check if operator is approved
			if(in_array($operator, $availableSQLOperators, true))
			{
				// approved ^_^
				$this->operator = $operator;
				
				return true;
			}
			
			// not approved V_V
			throw new \Exception('invalid SQL operator provided :: [ '.$operator.' ]');
		}
		
		// OTHER FUNCTIONS
		public function translateCanonicalTimespanToFieldNum()
		{
			/*
			 * 	Purpose: calculate the number of fields of the time value to take into account based on the canonical timespan
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: int
			 */
			
			// make determination
			switch($this->timespan)
			{
				case 'daily':
					$fieldNum = 10;
					
					break;
				case 'monthly':
					$fieldNum = 7;
					
					break;
				case 'yearly':
					$fieldNum = 4;
					
					break;
				default:
					throw new \Exception('invalid timespan provided for metrics generation :: [ '.$this->timespan.' ]');
			}
			
			return $fieldNum;
		}
		
		public function generateDistinctTimespans($returnDataDirectly = false)
		{
			/*
			 * 	Purpose: calculate a list of distinct timespans available in our data
			 *
			 * 	Params:
			 * 		* $returnDataDirectly :: bool :: if set to true, return all data instead of settings it inside parent obj
			 *
			 * 	Returns: array
			 */
			
			// calculate field count from canonical timespan
			$fieldCnt = $this->translateCanonicalTimespanToFieldNum();
			
			// generate sql to scan the database for all timespans
			$sql = '
					SELECT
						left(post_date, ?),
						count(*) as count_'.$this->timespan.'
					FROM
						listings
					GROUP BY 1
					ORDER BY 1';
			
			// init stmt and execute
			$stmt = $this->dbConn->prepare($sql);
			$stmt->execute([$fieldCnt]);
			
			// fetch result
			if(!$result = $stmt->fetchAll(\PDO::FETCH_NUM))
			{
				// request is bad
				$pdoError = $stmt->errorInfo();
				throw new \Exception('query could not be completed [ '.$pdoError[2].' ]');
			}
			
			// return data directly or set as local data obj var
			if($returnDataDirectly)
			{
				return $result;
			}
			else
			{
				// set result array as data
				parent::setData($result);
				
				return [];
			}
		}
		
		private function generateMetricsSQL()
		{
			/*
			 * 	Purpose: generate specific SQL query for metrics based on incoming data
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: string
			 */
			
			/*
			 * check what kind of operator we have so we can generate the correct select and order by SQL clauses, and
			 * determine whether to unwrap the sql results (usually done with single-record results in order to remove unnecessary arrays)
			 */
			if($this->operator === 'AVG')
			{
				$sqlSelectClause = $this->operator.'('.$this->field.')';
				
				$sqlOptions = '
						ORDER BY 1';
			}
			else
			{
				$sqlSelectClause  = $this->field.',
							'.$this->operator.'(*) as value';
				
				$sqlOptions = '
						GROUP BY 1
						ORDER BY 2 DESC';
			}
			
			// concatonate full sql query
			return '
						SELECT
							'.$sqlSelectClause.'
						FROM
							listings
						WHERE
							post_date LIKE ?
						'.$sqlOptions;
		}
		
		public function generateMetrics()
		{
			/*
			 * 	Purpose: generate metrics based on the set $field and $timespan
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: NONE
			 */
			
			$result = [];
			
			// check if db is empty
			if(0 < $this->getTotalNumberOfListings())
			{
				// listings are available in the db, continue with metrics
				// get distinct timespan values
				$timespanVals = $this->generateDistinctTimespans(true);
				
				// check if field name is set and multiple queries are needed
				if(!empty($this->field))
				{
					// in this case, we will need to cycle through each timespan and get the count by field for each one
					// generate sql query
					$sql = $this->generateMetricsSQL();
					
					// init stmt
					$stmt = $this->dbConn->prepare($sql);
					
					// iterate through timespans
					foreach($timespanVals as $timespan)
					{
						// initialize current results array w/ timespan
						$currentResults = [
							$timespan[0]
						];
						
						// get field counts for timespan
						// execute sql querys
						$stmt->execute([
							$timespan[0].'%'
						]);
						
						// fetch results
						if(!$sqlResults = $stmt->fetchAll(\PDO::FETCH_NUM))
						{
							// request is bad
							$pdoError = $stmt->errorInfo();
							throw new \Exception('query could not be completed [ PDOERR: { '.$pdoError[2].' } ]');
						} else
						{
							// add sql results to our current results array
							// check if single-record result was returned
							if(count($sqlResults[0]) === 1)
							{
								// single-record result, add as single raw value
								array_push($currentResults, $sqlResults[0][0]);
							} else
							{
								// multiple records, add all sql results
								array_push($currentResults, $sqlResults);
							}
							
							// add current set of results to results collection
							array_push($result, $currentResults);
						}
					}
				} else
				{
					// metrics request is for listings in general
					// this means we can just return the count by distinct timespans
					$result = $timespanVals;
				}
			}
			
			// by default, set result array as data
			parent::setData($result);
		}
	}

?>