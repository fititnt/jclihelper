<?php

/**
 * @package     CLIArgumentsDump
 * @author      Emerson Rocha Luiz - @fititnt ( http://fititnt.org )
 * @copyright   Copyright (C) Joomla! Coders Brazil @JCoderBR. All rights reserved.
 * @license     GNU General Public License version 3
 */
abstract class JCliHelper extends JCli {

	/**
	 *
	 * @var array 
	 */
	private $args;

	/**
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

		$this->exit = false;

		$this->method = null;

		$this->tasks = array();

		$this->log('teste');

		$reflection = new ReflectionClass($refletion);
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
			if ($this->input->get('help')) {
				$this->getHelp($this->method);
			} else if (!$this->method) {//Not inside a method
				if (!isset($this->input->args[0]) || !$this->input->args[0]) {
					$this->lastError = JText::_('No method selected');
					$this->out($this->lastError);
					$this->out($this->getHelp($this->method));
				} else {
					$task = $this->searchMethod($this->input->args[0]);
					if (!$task) {
						$this->lastError = JText::_('Task not found');
					} else {
						$this->executeMethod($task);
					}
				}
			}
			$this->log('interative ' . ++$i, $this->args, 6);
			$this->cursor();
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

	/**
	 * Search for avalible tasks (Case insensitive)
	 * 
	 * @param string $method
	 * @return string Method name if true, false if not found 
	 */
	protected function searchMethod($method) {
		foreach ($this->tasks AS $name => $item) {
			if (strtolower($name) == strtolower($method)) {
				$this->method = $name;
				return $name;
			}
		}
		$this->method = NULL;

		return FALSE;
	}

	/**
	 *
	 * @param type $options 
	 */
	protected function cursor($options = NULL) {
		if ($this->method) {
			$this->cursor = $this->method . '>';
		} else {
			$this->cursor = 'jcli' . '>';
		}
		echo $this->cursor;
		$this->in();
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
	 * Level 9: BREAKPOINT. Alias for DEBUG.
	 * Level 8: DEBUG. Debugging message.
	 * Level 7: INFO. Informational message.
	 * Level 6: NOTICE. Normal, but significant condition.
	 * Level 5: WARNING. Warning conditions.
	 * Level 4: ERROR. Error conditions.
	 * Level 3: CRITICAL. Critical conditions.
	 * Level 2: ALERT. Action must be taken immediately.
	 * Level 1: EMERGENCY. The system is unusable.
	 * Level 0: No error report
	 * 
	 * @param string $message
	 * @param mixed $aditionalInfo
	 * @param int $level
	 * @return void
	 */
	protected function log($message, $aditionalInfo = "\e", $level = 5) {
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
