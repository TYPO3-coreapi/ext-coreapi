<?php
namespace Etobi\CoreAPI\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Achim Fritz <af@achimfritz.de>
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

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Configuration API Command Controller
 *
 * @package TYPO3
 * @subpackage coreapi
 */
class ConfigurationApiCommandController extends CommandController {

	/**
	 * listCommand
	 *
	 * @return string
	 */
	public function listCommand() {
		$typo3ConfVars = $GLOBALS['TYPO3_CONF_VARS'];
		$this->outputLine(json_encode($typo3ConfVars));
	}

	/**
	 * showCommand
	 *
	 * @param string $param
	 * @return string
	 */
	public function showCommand($key) {
		$typo3ConfVars = ObjectAccess::getPropertyPath($GLOBALS['TYPO3_CONF_VARS'], $key);
		$this->outputLine(json_encode($typo3ConfVars));
	}
}
