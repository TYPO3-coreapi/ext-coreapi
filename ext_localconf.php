<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
		// Register commands
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\DatabaseApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\CacheApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\SiteApiCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Etobi\CoreAPI\Command\ExtensionApiCommandController';
}

// Register the CLI dispatcher
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
	'EXT:' . $_EXTKEY . '/Scripts/Cli.php', '_CLI_lowlevel'
);