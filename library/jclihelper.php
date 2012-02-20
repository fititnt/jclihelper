<?php

/**
 * @package     JCliHelper
 * @author      Emerson Rocha Luiz - emerson at webdesign.eng.br - http://fititnt.org
 * @copyright   Copyright (C) 2011 Webdesign Assessoria em Tecniligia da Informacao. All rights reserved.
 * @license     GNU General Public License version 3. See license-gpl3.txt
 */
abstract class JCliHelper extends JCli {

	/**
	 *
	 * @var array 
	 */
	protected $args;

	/**
	 *
	 * @deprecated
	 * 
	 * @var string 
	 */
	private $cursor;

	/**
	 * Tasks that can be excecuted by end user
	 * Are all public methods from class that extends this class
	 * 
	 * @var object 
	 */
	protected $tasks;

	/**
	 * Informatou about this application
	 * 
	 * $this->environment->name : Name of this application
	 * $this->environment->status: 'input' or 'output'. Internal use.
	 * $this->environment->classname
	 * 
	 * @var object 
	 */
	protected $environment;

	/**
	 *
	 * @var boolean 
	 */
	protected $exit;

	/**
	 * Witch method is active now
	 * 
	 * @var string 
	 */
	protected $method;

	/**
	 * Last Error message
	 * 
	 * @var string 
	 */
	protected $lastError;

	/**
	 * Last Result
	 * 
	 * @var mixed 
	 */
	protected $lastResult;

	/**
	 *
	 * @var instance 
	 */
	protected $logger;

	/**
	 * 
	 * @todo Is really need asks children class name on this construct param? I'm not sure
	 *
	 * @param type $refletion 
	 */
	function __construct($refletion) {

		//Before call this construct, you can define app name. If empty, will...
		if (!isset($this->environment->name)) {
			$this->environment->name = 'jcli';
		}
		$this->environment->status = 'input';


		$this->exit = false;

		$this->method = null;

		$this->tasks = array();

		$this->log('teste');

		$reflection = new ReflectionClass($refletion);
		$this->environment->classname = $reflection->name;

		foreach ($reflection->getMethods() AS $method) {
			if ($method->class == 'JCli' || $method->class == 'JCliHelper') {
				continue;
			} else if ($method->name == '__construct') {
				continue;
			}
			if ($method->isPublic()) {
				///Itinerate for each param
				foreach ($method->getParameters() AS $param) {
					if ($param->isOptional()) {//&& $param->isDefaultValueAvailable()
						$this->tasks[$method->name]['args'][$param->name]['default'] = $param->getDefaultValue();
						$this->tasks[$method->name]['args'][$param->name]['optional'] = TRUE;
					} else {
						$this->tasks[$method->name]['args'][$param->name]['default'] = NULL;
						$this->tasks[$method->name]['args'][$param->name]['optional'] = FALSE;
					}
				}
				///Get RAW document from method and also try parse each method
				$this->tasks[$method->name]['doc'] = $method->getDocComment();
				$this->tasks[$method->name]['help'] = $this->parseHelp($this->tasks[$method->name]['doc']);
			}
		}
		parent::__construct();
	}

	/**
	 * Return one Array of parsed result
	 * 
	 * @todo Still not finished
	 * 
	 * @param type $DocComment 
	 */
	protected function executeMethod($name) {
		///Parse method arguments...
		//foreach ($this->input->args AS $value) {
		//	$response .= $name . PHP_EOL;
		//}

		$result = $this->$name($this->input->args[1]);
		return $result;
	}

	/**
	 * Turn CLI interative, based on Docbloc of each cli method
	 * 
	 */
	protected function interative($options = NULL) {
		$i = 0;
		do {
			$this->parseInput();
			if ($this->method) {
				$this->callMethod();
				echo $this->parseCursor();
				echo $this->parseOutput();
			}
			echo $this->parseCursor();
			$this->in();//To avoid infinite loop
//			if ($this->input->get('help')) {
//				$this->getHelp($this->method);
//			} else if (!$this->method) {//Not inside a method
//				if (!isset($this->input->args[0]) || !$this->input->args[0]) {
//					$this->lastError = JText::_('No method selected');
//					$this->out($this->lastError);
//					$this->out($this->getHelp($this->method));
//				} else {
//					$task = $this->searchMethod($this->input->args[0]);
//					if (!$task) {
//						$this->lastError = JText::_('Task not found');
//					} else {
//						$this->executeMethod($task);
//					}
//				}
//			}
			$this->log('interative ' . ++$i, $this->args, 'NOTICE');
//			$this->cursor();
			$this->input = new JInputCli(); //Reset input
		} while (!$this->exit);
	}

	/**
	 * Return one Array of parsed result
	 * 
	 * @todo Still not finished
	 * 
	 * @param type $DocComment 
	 */
	protected function parseDocComment($DocComment) {
		$data = trim(preg_replace('/\r?\n *\* */', ' ', $DocComment));
		preg_match_all('/@([a-z]+)\s+(.*?)\s*(?=$|@[a-z]+\s)/s', $data, $matches);
		$result = array_combine($matches[1], $matches[2]);
		print_r($result);
		return $result;
	}

	protected function parseInput() {
		if (is_array($this->input->args) && empty($this->input->args)) {
			$this->method = NULL; //Reset method
		} else {
			$method = $this->searchMethod($this->input->args[0]);
			if ($method) {//If method exist
				$this->method = $method; //Set method
				unset($this->args); //Reset old args
				$this->args = array(); //Initialize new args
				foreach ($this->input->args AS $k => $v) {
					if (strtolower($method) === strtolower($v)) {
						continue; //Skip method name
					}
					$this->args[$k] = $v;
				}
			}
		}
	}

	/**
	 * Return one string with result from a PHP DocBlock string input
	 * 
	 * @todo Still not finished. Need fist finish parseDocComment() to inprove this one
	 * 
	 * @param type $DocComment 
	 */
	protected function parseHelp($DocComment) {
		$result = str_replace(array('/**', '* ', '*/'), '', $DocComment);
		return $result;
	}
	
	protected function parseOutput(){
		$this->environment->status = 'input';
		if(is_string($this->lastResult)){
			$output = $this->lastResult;
		} else {
			//@todo improve this output (fititnt, 2012-02-20 06:41)
			$output = PHP_EOL . json_encode($this->lastResult);
		}
		return $output; 
	}

	/**
	 * Search for avalible tasks (Case insensitive)
	 * 
	 * @param string $method
	 * @return string Method name if true, false if not found 
	 */
	protected function searchMethod($method) {
		foreach ($this->tasks AS $name => $item) {
			if (strtolower($name) == strtolower($method)) {
				//$this->method = $name;
				return $name;
			}
		}
		$this->method = NULL;

		return FALSE;
	}

	/**
	 * 
	 */
	protected function callMethod() {
		switch (strtolower($this->method)) {
			case 'help':
				//...
				break;
			case 'exit':
				$this->exit = true;
			default:
				$method = $this->method;
				if (empty($this->args)) {
					$this->lastResult = $this->$method();
				} else {
					//var_dump($this->args);die;
					$this->lastResult = call_user_func_array(array($this->environment->classname, $this->method), $this->args);
				}
				break;
		}
		$this->environment->status = 'output';
		return $this->lastResult;
	}

	/**
	 *
	 * @param type $options 
	 */
	protected function parseCursor($options = NULL) {
		if ($this->environment->status === 'input') {
			$separator = '>';
		} else {
			$separator = ':';
		}

		if ($this->method) {
			$this->cursor = $this->method . $separator;
		} else {
			$this->cursor = $this->environment->name . $separator;
		}
		return $this->cursor;
	}

	/**
	 * Return one string with desired Help
	 * 
	 * @param string $scope Scope of help. NULL for all methods
	 * @return string Desired help
	 */
	protected function getHelp($scope = NULL) {
		$response = PHP_EOL;
		if (!$scope) {
			$response .= JTEXT::_('Avalible functions') . PHP_EOL;
			foreach ($this->tasks AS $name => $item) {
				$response .= '    ' . $name . PHP_EOL;
			}
		}
		return $response;
	}

	/**
	 * Error level
	 * BREAKPOINT: Alias for DEBUG.
	 * DEBUG: Debugging message.
	 * INFO: Informational message.
	 * NOTICE: Normal, but significant condition.
	 * WARNING: Warning conditions.
	 * ERROR: Error conditions.
	 * CRITICAL: Critical conditions.
	 * ALERT: Action must be taken immediately.
	 * EMERGENCY: The system is unusable.
	 * Level '': No error report
	 * 
	 * @param string $message
	 * @param mixed $aditionalInfo
	 * @param int $level
	 * @return void
	 */
	protected function log($message, $aditionalInfo = "\e", $level = 'NOTICE') {
		defined('JDEBUG') or define('JDEBUG', 1);

		//Load loggger
		if (!$this->logger) {
			jimport('joomla.log.log');
			$this->logger = JLog::getInstance('error.log', NULL, JPATH_BASE);
		}

		//Parse adicitional info, if is need
		if ($aditionalInfo != "\e") {
			if (strpos($message, '%s') === FALSE) {
				$message = $message . ' ' . json_encode($aditionalInfo);
			} else {
				$message = str_replace('%s', json_encode($aditionalInfo), $message);
			}
		}
		$this->logger->addEntry(array('priority' => $level, 'comment' => $message));
	}

}
