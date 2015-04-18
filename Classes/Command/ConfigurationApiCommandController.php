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

/**
 * Configuration API Command Controller
 *
 * @package TYPO3
 * @subpackage coreapi
 */
class ConfigurationApiCommandController extends CommandController {

	/**
	 * @var \Etobi\CoreAPI\Service\ConfigurationApiService
	 * @inject
	 */
	protected $configurationApiService;

	/**
	 * List all configurations
	 *
	 * @return string
	 */
	public function listCommand() {
		$this->outputLine(json_encode($this->configurationApiService->getConfigurationArray()));
	}

	/**
	 * Get configuration value for given key
	 *
	 * @param string $key
	 * @return string
	 */
	public function showCommand($key) {
		$this->outputLine(json_encode($this->configurationApiService->getValue($key)));
	}
}
