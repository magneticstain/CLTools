<?php
namespace CLTools\CLData;

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
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'].'/'.str_replace('\\', '/', __NAMESPACE__);
	require $BASE_URL.'/lib/Autoloader.php';
	require $BASE_URL.'/conf/db.php';

	// gather identifiers, fields, and options
	// listing ID
	$listingID = $_GET['lid'];
//	if(empty($listingID))
//	{
//		echo json_encode([
//			'success'	=>	false,
//			'error' => 'no listing ID supplied'
//		]);
//
//		exit();
//	}
	// field name
	if(isset($_GET['f']) && !empty($_GET['f']))
	{
		$field = $_GET['f'];
	}
	else
	{
		$field = '*';
	}
	$options = array();
	// TODO: options

	// start data engine
	try {
		$data = new Data($DB_CONFIG_OPTIONS, $listingID, $field);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData :: [ SEV: FATAL ] :: [ LID: '.$listingID.' ] :: could not start data engine :: [ FIELD: '.$field.' ] :: [ MSG: '.$e->getMessage().' ]');

		echo json_encode([
			'success'	=>	false,
			'error' => 'could not start data engine'
		]);

		exit(1);
	}

	// fetch data from db (stored in private variable in Data() obj)
	try {
		$data->retrieveListingFromDb();
	} catch(\Exception $e) {
		error_log('CLTools :: CLData :: [ SEV: ERROR ] :: [ LID: '.$listingID.' ] :: could not query database for listing :: [ FIELD: '.$field.' ] :: [ MSG: '.$e->getMessage().' ]');

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
