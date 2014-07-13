<?php

namespace Etobi\CoreApi\Tests\Unit\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Stefano Kowalke <blueduck@gmx.net>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
 
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class SiteApiServiceTest
 * 
 * @package Etobi\CoreApi\Tests\Unit\Service
 * @author  Stefano Kowalke <blueduck@gmx.net>
 * @coversDefaultClass \Etobi\CoreAPI\Service\SiteApiService
 */
class SiteApiServiceTest extends UnitTestCase {

	/**
	 * @var \Etobi\CoreApi\Service\SiteApiService|\PHPUnit_Framework_MockObject_MockObject $subject
	 */
	protected $subject;

	/**
	 * Setup the test
	 */
	public function setUp() {
		$this->subject = $this->getMock('Etobi\\CoreApi\\Service\\SiteApiService', array('dummy'));
	}

	/**
	 * Tears the test down
	 */
	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 * @covers ::getSiteInfo
	 */
	public function getSiteInfoReturnsSiteInfo() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = 'CoreApi Testpage';

		$expectedData = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
			'Combined disk usage' => '42M',
			'Database size' => '23M',
			'Count local installed extensions' => 4
		);

		$data1 = array(
				'TYPO3 version' => TYPO3_version,
				'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
		);

		$data2 = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
			'Combined disk usage' => '42M',
		);

		$data3 = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
			'Combined disk usage' => '42M',
			'Database size' => '23M',

		);

		$data4 = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
			'Combined disk usage' => '42M',
			'Database size' => '23M',
			'Count local installed extensions' => 4
		);

		/** @var \Etobi\CoreApi\Service\SiteApiService|\PHPUnit_Framework_MockObject_MockObject $subject */
		$subject = $this->getMock('Etobi\\CoreApi\\Service\\SiteApiService', array('getDiskUsage', 'getDatabaseSize', 'getCountOfExtensions'));

		$subject->expects($this->once())->method('getDiskUsage')->with($data1)->will($this->returnValue($data2));
		$subject->expects($this->once())->method('getDatabaseSize')->with($data2)->will($this->returnValue($data3));
		$subject->expects($this->once())->method('getCountOfExtensions')->with($data3)->will($this->returnValue($data4));
		$calculatedData = $subject->getSiteInfo();
		$this->assertEquals($expectedData, $calculatedData);
	}

	/**
	 * @test
	 * @covers ::createSysNews
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage No header given
	 */
	public function createSysNewsNoHeaderGivenThrowsException() {
		$this->subject->createSysNews('', 'Foo');
	}

	/**
	 * @test
	 * @covers ::createSysNews
	 */
	public function createSysNewsCreateNewsEntry() {
		$databaseConnectionMock = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_INSERTquery'), array(), '', FALSE);
		/** @var \Etobi\CoreApi\Service\SiteApiService|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject */
		$subject = $this->getAccessibleMock('Etobi\\CoreApi\\Service\\SiteApiService', array('getDatabaseHandler'));

		$databaseConnectionMock->expects($this->once())->method('exec_INSERTquery')->with('sys_news', array('title' => 'Foo', 'content' => 'Bar', 'tstamp' => $GLOBALS['EXEC_TIME'], 'crdate' => $GLOBALS['EXEC_TIME'], 'cruser_id' => $GLOBALS['BE_USER']->user['uid']));
		$subject->expects($this->once())->method('getDatabaseHandler')->will($this->returnValue($databaseConnectionMock));

		$subject->createSysNews('Foo', 'Bar');
	}

	/**
	 * @test
	 * @covers ::getDiskUsage
	 */
	public function getDiskUsageCalculatesDiskUsageWhenOsIsNotWindows() {
		if (TYPO3_OS === 'WIN') {
			$this->markTestSkipped('Test is only valid for Unix based platforms');
		}
		$data = array();
		$diskUsage = trim(array_shift(explode("\t", shell_exec('du -sh ' . PATH_site))));

		$this->assertEquals(array('Combined disk usage' => $diskUsage), $this->subject->getDiskUsage($data));
	}

	/**
	 * @test
	 * @covers ::getDiskUsage
	 */
	public function getDiskUsageCalculatesNoDiskUsageWhenOsIsWindows() {
		if (TYPO3_OS === 'WIN') {
			$data = array();
			$this->assertEquals(array(), $this->subject->getDiskUsage($data));
		} else {
			$this->markTestSkipped('Test is only valid for Windows based platforms');
		}
	}

	/**
	 * @test
	 * @covers ::getDatabaseSize
	 */
	public function getDatabaseSizeReturnsDatabaseSize() {
		$data = array();

		$databaseConnectionMock = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('sql_query', 'sql_fetch_assoc'), array(), '', FALSE);
		/** @var \Etobi\CoreApi\Service\SiteApiService|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject */
		$subject = $this->getAccessibleMock('Etobi\\CoreApi\\Service\\SiteApiService', array('getDatabaseHandler'));

		$databaseConnectionMock->expects($this->once())->method('sql_query')->with("SELECT SUM( data_length + index_length ) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . TYPO3_db . "'");
		$databaseConnectionMock->expects($this->once())->method('sql_fetch_assoc')->will($this->returnValue(array('size' => 30.06250000)));
		$subject->expects($this->once())->method('getDatabaseHandler')->will($this->returnValue($databaseConnectionMock));

		$data = $subject->getDatabaseSize($data);
		$this->assertEquals('30M', $data['Database size']);
	}

	/**
	 * @test
	 * @covers ::getCountOfExtensions
	 */
	public function getCountOfExtensionsCountsLocalInstalledExtensions() {
		$data = array();
		$extensionApiServiceMock = $this->getMock('Etobi\\CoreAPI\\Service\\ExtensionApiService', array('listExtensions'));
		$extensionApiServiceMock->expects($this->once())->method('listExtensions')->with('Local')->will($this->returnValue($this->getExtensionArrayForCreateUploadFolders()));
		$objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', array('get'));
		$objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($extensionApiServiceMock));
		$this->subject->injectObjectManager($objectManagerMock);
		$data = $this->subject->getCountOfExtensions($data);
		$this->assertEquals(4, $data['Count local installed extensions']);
	}

	/**
	 * Provides the array which is return value from ExtensionServiceApi::getInstalledExtensions()
	 *
	 * @return array
	 */
	public function getExtensionArrayForCreateUploadFolders() {
		return array(
			'about' => array(
				'title' => 'Help>About',
				'description' => 'Shows info about TYPO3 and installed extensions',
				'category' => 'module',
				'shy' => 1,
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'module' => 'mod',
				'state' => 'stable',
				'internal' => 0,
				'uploadfolder' => 0,
				'createDirs' => '',
				'modify_tables' => '',
				'clearCacheOnLoad' => '',
				'lockType' => '',
				'author' => 'Kasper Skaarhoj',
				'author_email' => 'kasperYYYY@typo3.com',
				'author_company' => 'Curby Soft Multimedia',
				'CGLcompilance' => '',
				'CGLcompilance_note' => '',
				'version' => '6.2.0',
				'_md5_values_when_last_written' => '',
				'constraints' => array(),
				'suggests' => array(),
				'key' => 'about'
			),
			'backend' => array(
				'title' => 'TYPO3 Backend',
				'description' => 'Classes for the TYPO3 backend.',
				'category' => 'be',
				'shy' => 1,
				'dependencies' => '',
				'conflicts' => '',
				'priority' => 'top',
				'loadOrder' => '',
				'module' => '',
				'state' => 'stable',
				'internal' => 1,
				'uploadfolder' => 0,
				'createDirs' => '',
				'modify_tables' => '',
				'clearCacheOnLoad' => 0,
				'lockType' => 'S',
				'author' => 'Kasper Skaarhoj',
				'author_email' => 'kasperYYYY@typo3.com',
				'author_company' => 'Curby Soft Multimedia',
				'CGLcompilance' => '',
				'CGLcompilance_note' => '',
				'version' => '6.2.0',
				'_md5_values_when_last_written' => '',
				'constraints' => array(),
				'suggests' => array(),
				'key' => 'about'
			),
			'core' => array(
				'title' => 'TYPO3 Core',
				'description' => 'Classes for the TYPO3 backend.',
				'category' => 'be',
				'shy' => 1,
				'dependencies' => '',
				'conflicts' => '',
				'priority' => 'top',
				'loadOrder' => '',
				'module' => '',
				'state' => 'stable',
				'internal' => 1,
				'uploadfolder' => 0,
				'createDirs' => '',
				'modify_tables' => '',
				'clearCacheOnLoad' => 0,
				'lockType' => 'S',
				'author' => 'Kasper Skaarhoj',
				'author_email' => 'kasperYYYY@typo3.com',
				'author_company' => 'Curby Soft Multimedia',
				'CGLcompilance' => '',
				'CGLcompilance_note' => '',
				'version' => '6.2.0',
				'_md5_values_when_last_written' => '',
				'constraints' => array(),
				'suggests' => array(),
				'key' => 'about'
			),
			'dummy' => array(
				'title' => 'Dummy Extension for testing',
				'description' => 'This is just a dummy extension',
				'category' => 'experimental',
				'shy' => 1,
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'module' => 'mod',
				'state' => 'stable',
				'internal' => 0,
				'uploadfolder' => 0,
				'createDirs' => '',
				'modify_tables' => '',
				'clearCacheOnLoad' => '',
				'lockType' => '',
				'author' => 'Stefano Kowalke',
				'author_email' => 'blueduck@gmx.net',
				'author_company' => 'Arroba IT',
				'CGLcompilance' => '',
				'CGLcompilance_note' => '',
				'version' => '6.2.0',
				'_md5_values_when_last_written' => '',
				'constraints' => array(),
				'suggests' => array(),
				'key' => 'about'
			)
		);
	}
}

