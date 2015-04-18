<?php
namespace Etobi\CoreAPI\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Site API service
 *
 * @author Tobias Liebig <tobias.liebig@typo3.org>
 * @package Etobi\CoreAPI\Service\ConfigurationApiService
 */
class ConfigurationApiService {

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getValue($key) {
		return ObjectAccess::getPropertyPath($this->getConfigurationArray(), $key);
	}

	/**
	 * Returns the configuration array
	 *
	 * @return array
	 */
	public function getConfigurationArray() {
		return $GLOBALS['TYPO3_CONF_VARS'];
	}
}
