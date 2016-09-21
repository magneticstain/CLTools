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

	// start data engine
	try {
		$data = new Data($DB_CONFIG_OPTIONS);
	} catch(\Exception $e) {
		error_log('CLTools :: CLData :: [ FATAL ] :: could not start data engine :: '.$e->getMessage());

		echo "[ FATAL ERROR ] :: CLData DATA ENGINE UNAVAILABLE";
	}

	// gather fields and options
	$field = '';
	$options = array();
	if(isset($_GET['field']) && !empty($_GET['field']))
	{
		$field = $_GET['field'];
	}
	// TODO: options

	// retrieve data

	// return data as json
?>
