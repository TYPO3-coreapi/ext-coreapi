<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'coreapi',
	'description' => 'coreapi',
	'category' => 'plugin',
	'author' => 'Tobias Liebig,Georg Ringer,Stefano Kowalke',
	'author_email' => 'tobias.liebig@typo3.org,georg.ringer@cyberhouse.at,blueduck@gmx.net',
	'author_company' => '',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
			'extbase' => '6.2.0-6.2.99',
			'fluid' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);