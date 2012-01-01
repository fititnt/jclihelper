<?php

/**
 * @package     CLIArgumentsDump
 * @author      Emerson Rocha Luiz - @fititnt ( http://fititnt.org )
 * @copyright   Copyright (C) Joomla! Coders Brazil @JCoderBR. All rights reserved.
 * @license     GNU General Public License version 3
 */

define('_JEXEC', 1); // You MUST define it. Or Joomla Framework will not load
define('JPATH_BASE', dirname(__FILE__)); // Setup the base path related constant.
define('JPATH_SITE', dirname(__FILE__)); //JFolder
include_once dirname(__FILE__) . "/../../../joomla/joomla-platform/libraries/import.php"; //path to Joomla-platform
jimport('joomla.application.cli');
include_once dirname(__FILE__) . "/../library/jclihelper.php"; //Path to JClihelper


jimport('joomla.filesystem.folder');

class Test1 extends JCliHelper {


	
	/**
	 * 
	 */
	function __construct() {
		parent::__construct(get_class($this));
		$this->interative();
	}	

	
	/**
	 * Deescription of my class
	 * 
	 * @param string $param1
	 * @param string $param2
	 * @return string 
	 */
	public function myTask($param1, $param2 = ''){
		$result = $param1 . ' ' . $param2;
		echo $result;
		return $result;
	}
	
	/**
	 *
	 * @param mixed $args
	 * @param mixed $canBeNull 
	 */
	private function doTask($args, $canBeNull = NULL){
		
	}

	/**
	 * 
	 *
	 * @param array $options 
	 */
	public function parseArgs($options) {
		foreach ($options AS $key => $item) {
			$this->args[$key] = $item;
		}
	}
	
	/**
	 * Dump informatou to debug
	 */
	protected function jcliDebug(){
		//print_r($this);
	}
}

$cli = JCli::getInstance('Test1');

//print_r($cli);

$oClassReflect = new ReflectionClass("Test1");
$sDocComment = $oClassReflect->getDocComment();

//print_r($sDocComment);

//print_r($oClassReflect->getMethods());


//print_r($oClassReflect->getMethod('myTask')->getDocComment());