<?php

/**
 * @package     CLIArgumentsDump
 * @author      Emerson Rocha Luiz - @fititnt ( http://fititnt.org )
 * @copyright   Copyright (C) Joomla! Coders Brazil @JCoderBR. All rights reserved.
 * @license     GNU General Public License version 3
 */

/**
 * Dump params vars using PHP getopt function ( http://php.net/manual/en/function.getopt.php )
 * On windows, PHP 5.3+ is requerid
 */
class JCliHelper extends JCli {

	/**
	 *
	 * @var array 
	 */
	private $args;

	/**
	 *
	 * @var type 
	 */
	private $fonte;

	/**
	 *
	 * @var type 
	 */
	private $destino;

	function __contruct() {
		$this->fonte = __DIR__;
		$this->destino = __DIR__;
	}

	public function load($options) {
		$this->parseArgs($options);
		$this->dumpArgs();
		//$this->out('PHP getopt() output: ');
	}

	private function doTask($args) {
		
	}
	
	/**
	 * Dump informatou to debug
	 */
	protected function jcliDebug(){
		
	}

	/**
	 * 
	 * @param array $options 
	 */
	private function parseArgs($options) {
		foreach ($options AS $key => $item) {
			$this->args[$key] = $item;
		}
	}

	/**
	 * Delete (set to NULL) generic variable
	 * 
	 * @param String $name: name of var do delete
	 * @return Object $this
	 */
	public function del($name) {
		$this->$name = NULL;
		return $this;
	}

	/**
	 * Return generic variable
	 * 
	 * @param String $name: name of var to return
	 * @return Mixed this->$name: value of var
	 */
	public function get($name) {
		return $this->$name;
	}

	/**
	 * Set one generic variable the desired value
	 * 
	 * @param String $name: name of var to set value
	 * @param Mixed $value: value to set to desired variable
	 * @return Object $this
	 */
	public function set($name, $value) {
		$this->$name = $value;
		return $this;
	}

}
