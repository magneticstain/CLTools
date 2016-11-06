<?php
namespace CLTools\CLWeb;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  Autoloader.php - logic for autoloading classes
	 */

	class AutoLoader
	{
		public static function loadClass($className)
		{
			// extract class name
			// - it is the last string delimited by '\'
			// - we also must get last string of the array separately to comply with php strict coding standards (cannot pass reference as variable)
			$explodedClassName = explode('\\',$className);
			$extractedClassName = end($explodedClassName);
			$baseUrl = $_SERVER['DOCUMENT_ROOT'].'/'.str_replace('\\', '/', __NAMESPACE__);

			// load class from file
			require $baseUrl.'/lib/'.$extractedClassName.'.php';
		}
	}

	// set autoload function in AutoLoad() class
	spl_autoload_register(__NAMESPACE__.'\\AutoLoader::loadClass');
?>
