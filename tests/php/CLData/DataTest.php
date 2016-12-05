<?php
namespace CLTools\CLData;

/**
 *  CLTools
 *  Author: Josh Carlson
 *  Email: jcarlson(at)carlso(dot)net
 */

/*
 *  DataTest.php - PHP unit test for Data() library
 */

require('./CLData/lib/Data.php');

class DataTest extends \PHPUnit_Framework_TestCase
{
	protected $data;
	
	public function setUp()
	{
		require('./CLData/conf/db.php');
		$this->data = new Data($DB_CONFIG_OPTIONS);
	}
	
	// SETTERS
	/**
	 * @param $rawData
	 *
	 * @dataProvider providerTestSetDataValid
	 */
	public function testSetDataValid($rawData)
	{
		$this->assertTrue($this->data->setData($rawData));
	}
	
	public function providerTestSetDataValid()
	{
		return array(
			array('asdfahjnsglnalsdn425294i50jsdfogn'),
			array(''),
			array(1),
			array(-1),
			array(9999999999999),
			array(-9999999999999)
		);
	}
	
	// GETTERS
	public function testGetDataRaw()
	{
		$testData = array(
			array(
				'test',
				'data'
			)
		);
		
		// set data
		$this->data->setData($testData);
		
		// get raw data back and make sure it's equal to the original data
		$this->assertEquals($testData, $this->data->getData(true));
	}
	
	public function testGetDataSpecificKey()
	{
		// test to make sure getData() returns the correct data when requested with a specific array key
		$testData = array(
			'test',
			'data'
		);
		
		// set data
		$this->data->setData($testData);
		
		$this->assertEquals($testData[1], $this->data->getData(false, 1));
	}
	
	public function testGetDataSpecificKeyWithKeyFailure()
	{
		// test to make sure getData() returns a blank array when requesting data at a key from the data array that doesn't exist
		$testData = array(
			'test',
			'data'
		);
		
		// set data
		$this->data->setData($testData);
		
		$this->assertEquals([], $this->data->getData(false, 5));
	}
	
	public function testGetDataWhenFieldDoesNotExist()
	{
		// test for when the data field is not in the data array
		$testData = array(
			'test' => array('a', 'b', 'c')
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = 'invalidDataField';
		
		$this->assertEquals([], $this->data->getData(false));
	}
	
	public function testGetDataNoSpecificDataField()
	{
		// test to make sure getData() returns the entire dataset if no data field is specified and there's only a single record
		$testData = array(
			'test'
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = '';
		
		$this->assertEquals($testData, $this->data->getData(false));
	}
	
	public function testGetDataMultipleRecordsWithNoSpecificDataField()
	{
		// test to make sure getData() returns the entire dataset if no data field is specified, but there are multiple records
		$testData = array(
			'test',
			'data'
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = '';
		
		$this->assertEquals($testData, $this->data->getData(false));
	}
	
	public function testGetDataInvalidSpecificDataField()
	{
		// test to make sure getData() returns a blank array if a data field is specified and there is a single record
		$testData = array(
			'test' => array('a', 'b', 'c')
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = 'randomDataField';
		
		$this->assertEquals([], $this->data->getData(false));
	}
	
	public function testGetDataMultipleRecordsSpecificDataField()
	{
		// test to make sure getData() returns a the original dataset if a data field is specified and there are multiple records
		$testData = array(
			'test',
			'data'
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = 'randomDataField';
		
		$this->assertEquals($testData, $this->data->getData(false));
	}
	
	public function testGetDataValidSpecificDataField()
	{
		// test to make sure getData() returns a the correct subset of the original dataset if a valid data field is specified and a single record
		$testData = array(
			'test' => array('a', 'b', 'c')
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = 'test';
		
		$this->assertEquals($testData['test'], $this->data->getData(false));
	}
	
	public function testGetDataMultipleRecordsValidSpecificDataField()
	{
		// test to make sure getData() returns the original dataset if a valid data field is specified and there are multiple records
		$testData = array(
			'test' => array('a', 'b', 'c'),
			'data' => array('e', 'f', 'g')
		);
		
		// set data
		$this->data->setData($testData);
		
		// set data field
		$this->data->dataField = 'test';
		
		$this->assertEquals($testData, $this->data->getData(false));
	}
	
	// OTHER FUNCTIONS
	public function testGetTotalNumberOfListingsValidCount()
	{
		$this->assertGreaterThanOrEqual(0, $this->data->getTotalNumberOfListings());
	}
}
