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
 * Site API Command Controller
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Command_SiteApiCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Site info
	 *
	 * Basic information about the system
	 *
	 * @return void
	 */
	public function infoCommand() {
		/** @var $service Tx_Coreapi_Service_SiteApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_SiteApiService');
		$data = $service->getSiteInfo();

		foreach($data as $key => $value) {
			$line = wordwrap($value, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $key, $line));
		}
	}

	/**
	 * Create a sys news
	 *
	 * Sys news record is displayed at the login page
	 *
	 * @param string $header Header text
	 * @param string $text Basic text
	 * @return void
	 */
	public function createSysNewsCommand($header, $text = '') {
		/** @var $service Tx_Coreapi_Service_SiteApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_SiteApiService');
		$service->createSysNews($header, $text);
	}

}

?>