<?php
namespace CLTools\CLData;

	/*
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  metrics/index.php - main script for calculations portion of API (i.e. what is the best month to rent? or Rent by Month of Year)
	 */

	// import config(s) and libraries
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'].'/'.str_replace('\\', '/', __NAMESPACE__);
	require $BASE_URL.'/lib/Autoloader.php';
	require $BASE_URL.'/conf/db.php';
	
	// set content-type
	header('Content-Type: application/json');
	
	function setOptions()
	{
		// convert GET vars to API query options
		
		// initialize option variables
		$dataField = '';
		$timespan = 'monthly';
		$operator = 'count';
		
		// set from GET vars
		// field (OPT)
		if(isset($_GET['f']))
		{
			$dataField = $_GET['f'];
		}
		
		// timespan
		if(isset($_GET['t']) && !empty($_GET['t']))
		{
			$timespan = $_GET['t'];
		}
		
		// operator
		if(isset($_GET['o']))
		{
			$operator = $_GET['o'];
		}
		
		return [
			'field'		=>	$dataField,
			'timespan'	=>	$timespan,
			'operator'	=>	$operator
		];
	}

	// start metrics engine
	try {
		// set options
		$options = setOptions();
		
		$metrics = new Metrics(
			$DB_CONFIG_OPTIONS,
			$options['field'],
			$options['timespan'],
			$options['operator']
		);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - METRICS :: [ SEV: FATAL ] :: could not start metrics engine :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 503 status
		http_response_code(503);
		
		exit(1);
	}
	
	// generate requested metrics
	try {
		$metrics->generateMetrics();
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - METRICS :: [ SEV: CRIT ] :: could not generate metrics :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 400 status
		http_response_code(400);
		
		exit(1);
	}
	
	// return data as json
	echo json_encode([
		'success'	=>	true,
		'result'	=>	$metrics->getData(true)
	]);
?>
