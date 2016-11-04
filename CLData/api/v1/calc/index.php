<?php
namespace CLTools\CLData;

	/*
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  calc/index.php - main script for calculation portion of API
	 */

	// import config(s) and libraries
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'].'/'.str_replace('\\', '/', __NAMESPACE__);
	require $BASE_URL.'/lib/Autoloader.php';
	require $BASE_URL.'/conf/db.php';

	// set content-type
	header('Content-Type: application/json');

	function generateOptions()
	{
		// convert GET vars to API query options
		
		// initialize option variables
		$measurement = 'max';
		$dataField = 'price';
		$searchString = '';
		$order = 'asc';
		$limit = 0;
		
		// set from GET vars
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
		
		return [
			'measurement'	=>	$measurement,
			'field'			=>	$dataField,
			'search_string'	=>	$searchString,
			'order'			=>	$order,
			'limit'			=>	$limit
		];
	}

	// start calc engine
	try {
		// generate options
		$options = generateOptions();
		
		$calc = new Calc(
			$DB_CONFIG_OPTIONS,
			$options['measurement'],
			$options['field'],
			$options['search_string']
		);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - CALC :: [ SEV: FATAL ] :: could not start calculation engine :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 503 status
		http_response_code(503);

		exit(1);
	}

	// attempt to perform calculation
	try {
		$calc->calculate($options['order'], $options['limit']);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData - CALC :: [ SEV: CRIT ] :: could not perform calculation :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 400 status
		http_response_code(400);
		
		exit(1);
	}

	// return data as json
	echo json_encode([
		'success'	=>	true,
		'result'	=>	$calc->getData(true)
	]);
?>
