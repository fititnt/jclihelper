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

class Estagiario extends JCliHelper {

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
	
		
	private function doTask($args){
		
	}

	/**
	 * 
	 *
	 * @param array $options 
	 */
	private function parseArgs($options) {
		foreach ($options AS $key => $item) {
			$this->args[$key] = $item;
		}
	}

	/**
	 *
	 * @param array $args 
	 */
	public function dumpArgs($args = NULL) {
		if (!$args) {
			$args = $this->args;
		}
		if (is_null($args)) {
			return null;
		}
		foreach ($args AS $key => $item) {
			$this->out($key . ' => ' . $item);
		}
	}

	/**
	 *
	 * @param string $local
	 * @param boolean $imprime
	 * @return array $arquivos
	 */
	public function listaDiretorio($local = null, $imprime = false) {
		if (!$local) {
			$local = $this->fonte;
		}
		$arquivos = array();
		JFolder::makeSafe($local);
		$arquivos = JFolder::files($local);

		if ($imprime) {
			foreach ($arquivos AS $item) {
				$this->out($item);
			}
		}
		return $arquivos;
	}
}

$cli = JCli::getInstance('Estagiario');


//$cli->argsDump( getopt('a:b:c:d:e:f:g:h:i:j:k:l:m:n:o:p:q:r:s:t:u:v:x:z:') );

$cli->load(getopt('f:fonte:d:destino'));

//$cli->listaDiretorio(__DIR__);
