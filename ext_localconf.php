<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
		// Register commands
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\BackendApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\DatabaseApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\CacheApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\SiteApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\ExtensionApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\ConfigurationApiCommandController';
}
