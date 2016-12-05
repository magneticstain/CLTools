<?php
namespace CLTools\CLWeb;

/**
 *  CLTools
 *  Author: Josh Carlson
 *  Email: jcarlson(at)carlso(dot)net
 */

/*
 *  WebTest.php - PHP unit test for Web() library
 */

require('./CLWeb/lib/Web.php');

class WebTest extends \PHPUnit_Framework_TestCase
{
	protected $web;
	
	public function setUp()
	{
		$this->web = new Web('');
	}
	
	public function testGenerateHTMLIsNonEmptyString()
	{
		$html = $this->web->generateHTML();
		$this->assertFalse(empty($html));
	}
}
