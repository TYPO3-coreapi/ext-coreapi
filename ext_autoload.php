<?php

$extensionPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('coreapi');
$extensionClassesPath = $extensionPath . 'Classes/';

return array(
	'Etobi\CoreAPI\Command\DatabaseApiCommandController' => $extensionClassesPath . 'Command/DatabaseApiCommandController.php',
	'Etobi\CoreAPI\Command\SiteApiCommandController' => $extensionClassesPath . 'Command/SiteApiCommandController.php',
	'Etobi\CoreAPI\Command\CacheApiCommandController' => $extensionClassesPath . 'Command/CacheApiCommandController.php',
	'Etobi\CoreAPI\Service\CacheApiService' => $extensionClassesPath . 'Service/CacheApiService.php',
	'Etobi\CoreAPI\Service\SiteApiService' => $extensionClassesPath . 'Service/SiteApiService.php',
	'Etobi\CoreAPI\Service\DatabaseApiService' => $extensionClassesPath . 'Service/DatabaseApiService.php',
	'Etobi\CoreAPI\Service\ExtensionApiService' => $extensionClassesPath . 'Service/ExtensionApiService.php'
);