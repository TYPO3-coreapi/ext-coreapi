<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Georg Ringer <georg.ringer@cyberhouse.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * API Command Controller
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Command_DatabaseApiCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Database compare
	 *
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 */
	public function databaseCompareCommand($actions = '') {
		/** @var $service Tx_Coreapi_Service_DatabaseApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_DatabaseApiService');

		if ($actions === 'help' || strlen($actions) === 0) {
			$actions = $service->databaseCompareAvailableActions();
			foreach ($actions as $number => $action) {
				$this->outputLine('  - ' . $action . ' => ' . $number);
			}
			$this->quit();
		}

		$result = $service->databaseCompare($actions);
		if (empty($result)) {
			$this->outputLine('DB has been compared');
		} else {
			$this->outputLine('DB could not be compared, Error(s): %s', array(LF . implode(LF, $result)));
			$this->quit();
		}
	}
}

?>