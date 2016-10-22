<?php
namespace CLTools\CLData;

	/*
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  metrics/index.php - main script for metrics portion of API
	 */

	// import config(s) and libraries
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'].'/'.str_replace('\\', '/', __NAMESPACE__);
	require $BASE_URL.'/lib/Autoloader.php';
	require $BASE_URL.'/conf/db.php';

	// set content-type
	header('Content-Type: application/json');

	// set get params
	$measurement = 'max';
	$dataField = 'price';
	$searchString = '';
	$order = 'asc';
	$limit = 0;
	// measurement
	if(isset($_GET['m']))
	{
		$measurement = $_GET['m'];
	}
	
	// data field
	if(isset($_GET['f']))
	{
		$dataField = $_GET['f'];
	}
	
	// search string
	if(isset($_GET['s']))
	{
		$searchString = $_GET['s'];
	}
	
	// order
	if(isset($_GET['o']))
	{
		$order = $_GET['o'];
	}

	// result limit
	if(isset($_GET['l']))
	{
		$limit = $_GET['l'];
	}

	// start metrics engine
	try {
		$metrics = new Metrics(
			$DB_CONFIG_OPTIONS,
			$measurement,
			$dataField,
			$searchString
		);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - METRICS :: [ SEV: FATAL ] :: could not start data engine :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 503 status
		http_response_code(503);

		exit(1);
	}

	// attempt to perform calculation
	try {
		$metrics->calculate($order, $limit);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - METRICS :: [ SEV: INFO ] :: could not perform calculation :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 400 status
		http_response_code(400);
		
		exit(1);
	}

	// return data as json
//	var_dump($metrics);
	echo json_encode([
		'success'	=>	true,
		'metrics'	=>	$metrics->getData(true)
	]);
?>
