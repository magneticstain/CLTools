<?php
namespace CLTools\CLData;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  Metrics.php - class containing metrics based on collected data
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
			 * 		* $field :: str :: data field to measure metrics of
			 *
			 * 	Returns: bool
			 */
			
			// NOTE: blank string indicates listing records in general
			$availableDataFields = [
				'',
				'location',
				'price'
			];
			
			// normalize data field param
			$field = strtolower($field);
			
			// check if given field is approved for use with metrics
			if(in_array($field, $availableDataFields, true))
			{
				// approved ❣◕ ‿ ◕❣
				$this->field = $field;
				
				return true;
			}
			
			// not approved (ಥ﹏ಥ)
			throw new \Exception('invalid data field provided for metric generation!');
		}
		
		public function setTimespan($timespan)
		{
			/*
			 * 	Purpose: set given timespan if it matches one of the approved timespans
			 *
			 * 	Params:
			 * 		* $timespan :: str :: timespan to measure metrics in
			 *
			 * 	Returns: bool
			 *
			 * 	Dev Notes:
			 * 		* [ 2016-10-31 ] - [JCP] - If you update the available timespans in this function, you must also
			 * 			update generateMetrics accordingly
			 */
			
			$availableTimespans = [
				'daily',
				'monthly',
				'yearly'
			];
			
			// normalize timespan param
			$timespan = strtolower($timespan);
			
			// check if timespan is approved
			if(in_array($timespan, $availableTimespans, true))
			{
				// approved ^_^
				$this->timespan = $timespan;
				
				return true;
			}
			
			// not approved V_V
			throw new \Exception('invalid timespan provided!');
		}
		
		public function setOperator($operator)
		{
			/*
			 * 	Purpose: set given operator if it matches one of the approved SQL operators
			 *
			 * 	Params:
			 * 		* $operator :: str :: operator to perform metrics on
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
			throw new \Exception('invalid SQL operator provided!');
		}
		
		// OTHER FUNCTIONS
		public function translateCanonicalTimespanToFieldNum()
		{
			/*
			 * 	Purpose: calculate a list of distinct timespan when a field is set, based on the canonical timespan
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: array
			 */
			
			// make determination
			$fieldNum = 0;
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
					throw new \Exception('invalid timespan provided for metrics generation!');
			}
			
			return $fieldNum;
		}
		
		public function generateDistinctTimespans($returnDataDirectly = false)
		{
			/*
			 * 	Purpose: calculate a list of distinct timespan when a field is set, based on the canonical timespan
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: array
			 */
			
			// calculate field count
			$fieldCnt = $this->translateCanonicalTimespanToFieldNum();
			
			// generate sql to scan the database
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
		
		public function generateMetrics()
		{
			/*
			 * 	Purpose: generate metrics based on the set $field and $timespan
			 *
			 * 	Params: NONE
			 *
			 * 	Returns: array
			 */
			
			$unwrapResults = false;
			$result = [];
			
			// get distict timespan values
			$timespanVals = $this->generateDistinctTimespans(true);
			
			// check if field name is set and multiple queries are needed
			if(!empty($this->field))
			{
				// in this case, we will need to cycle through each timespan and get the count by field for each one
				// check what kind of operator we have so we can generate correct select and order by SQL clauses, and
				// determine whether to unwrap the sql results (usually done with single-record results in order to remove unnecessary arrays)
				if($this->operator === 'AVG')
				{
					$sqlSelectClause = $this->operator.'('.$this->field.')';
					
					$sqlOptions = '
						ORDER BY 1';
					
					$unwrapResults = true;
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
				$sql  = '
						SELECT
							'.$sqlSelectClause.'
						FROM
							listings
						WHERE
							post_date LIKE ?
						'.$sqlOptions;
				
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
					}
					else
					{
						// add sql results to our current results array
						if($unwrapResults)
						{
							// add results of first sql record only
							array_push($currentResults, $sqlResults[0][0]);
						}
						else
						{
							// add all sql results
							array_push($currentResults, $sqlResults);
						}
						
						// add current results to results collection
						array_push($result, $currentResults);
					}
				}
			}
			else
			{
				// metrics request is for listings in general
				// this means we can just return the count by distinct timespans
				$result = $timespanVals;
			}
			
			// set result array as data
			if($wrapResults)
			{
				// set results in a wrapping array
				parent::setData([
					$result
				]);
			}
			else
			{
				// set results in raw form
				parent::setData($result);
			}
		}
	}

?>