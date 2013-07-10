<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'coreapi',
	'description' => 'coreapi',
	'category' => 'plugin',
	'author' => 'Tobias Liebig,Georg Ringer',
	'author_email' => 'tobias.liebig@typo3.org,georg.ringer@cyberhouse.at',
	'author_company' => '',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.1.99',
			'extbase' => '1.3.0-6.1.99',
			'fluid' => '1.3.0-6.1.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>