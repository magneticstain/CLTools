<?php
namespace CLTools;

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
			$baseUrl = $_SERVER['DOCUMENT_ROOT'].'/';
			
			// format full class name as directory structure
			$classDirStructure = str_replace('\\', '/', $className);
			
			// separate dir structure & class name
			$dirStructure = substr($classDirStructure, 0, strrpos($classDirStructure, '/'));
			$class = strrchr($classDirStructure, '/');

			// concatonate everything and load class from file
			require $baseUrl.$dirStructure.'/lib/'.$class.'.php';
		}
	}

	// set autoload function in AutoLoad() class
	spl_autoload_register(__NAMESPACE__.'\\AutoLoader::loadClass');
?>
