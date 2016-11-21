<?php
namespace CLTools;

	/*
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  data/index.php - main script for raw data queries sent to API
	 */

	/*
	 *  QUERY STRUCTURE (URL)
	 *      /{VERSION}/data/{FIELD}/?{OPTIONS}
	 */

	// import config(s) and libraries
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'];
	require $BASE_URL.'/CLTools/lib/Autoloader.php';
	require $BASE_URL.'/CLTools/CLData/conf/db.php';

	// set http headers
	// cache time is currently set to 120 seconds in order to balance caching w/ listing freshness
	CLWeb\Web::setHTTPHeaders(120, 'Content-Type: application/json');

	// gather identifiers, fields, and options
	// listing ID
	$listingID = $_GET['lid'];
	// field name
	if(isset($_GET['f']) && !empty($_GET['f']))
	{
		$field = $_GET['f'];
	}
	else
	{
		$field = '*';
	}
	// order by field
	if(isset($_GET['o']))
	{
		$sortOrderOpt = $_GET['o'];
	}
	else
	{
		$sortOrderOpt = '';
	}
	// sort order of dataset
	if(isset($_GET['s']))
	{
		$sortOrder = $_GET['s'];
	}
	else
	{
		$sortOrder = 'asc';
	}
	// result limit
	if(isset($_GET['l']))
	{
		$limit = $_GET['l'];
	}
	else
	{
		$limit = 0;
	}

	// start data engine
	try {
		$data = new CLData\Data($DB_CONFIG_OPTIONS, $listingID, $field);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData :: [ SEV: FATAL ] :: [ LID: '.$listingID.' ] :: could not start data engine :: [ FIELD: '.$field.' ] :: [ MSG: '.$e->getMessage().' ]');

		// throw 503 status
		http_response_code(503);

		exit(1);
	}

	// fetch data from db (stored in private variable in Data() obj)
	try {
		$data->retrieveListingFromDb($sortOrderOpt, $sortOrder, $limit);

		// verify data was received
		if($data->getData() === false)
		{
			// data not found, return 404 status
			http_response_code(404);

			exit();
		}
	} catch(\Exception $e) {
		error_log('CLTools :: CLData :: [ SEV: CRIT ] :: [ LID: '.$listingID.' ] :: could not query database for listing :: [ FIELD: '.$field.' ] :: [ MSG: '.$e->getMessage().' ]');

		echo json_encode([
			'success'	=>	false,
			'error' => 'could not query database for provided field'
		]);

		exit();
	}

	// return data as json
	echo json_encode([
		'success'	=>	true,
		'data'		=>	$data->getData()
	]);
?>
