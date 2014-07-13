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
 * Class DatabaseApiServiceTest
 * 
 * @package Etobi\CoreApi\Tests\Unit\Service
 * @author  Stefano Kowalke <blueduck@gmx.net>
 * @coversDefaultClass \Etobi\CoreAPI\Service\DatabaseApiService
 */
class DatabaseApiServiceTest extends UnitTestCase {

	/**
	 * @var \Etobi\CoreApi\Service\DatabaseApiService|\PHPUnit_Framework_MockObject_MockObject $subject
	 */
	protected $subject;

	/**
	 * Setup the test
	 */
	public function setup() {
		$this->subject = $this->getMock('Etobi\\CoreApi\\Service\\DatabaseApiService', array('dummy'));
	}

	/**
	 * Tears the test down
	 */
	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 * @covers ::databaseCompare
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage No compare modes defined
	 */
	public function databaseCompareNoCompareModesDefinedThrowsException() {
		$objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', array('get'));
		$classReflectionMock = $this->getMock('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', array(), array(new \Etobi\CoreAPI\Service\DatabaseApiService()));

		$classReflectionMock->expects($this->once())->method('getConstants')->will($this->returnValue($this->getAvailableActions()));
		$objectManagerMock->expects($this->once())->method('get')->with('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseApiService')->will($this->returnValue($classReflectionMock));
		$this->subject->injectObjectManager($objectManagerMock);

		$this->subject->databaseCompare('');
	}

	/**
	 * @test
	 * @covers ::databaseCompare
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Action "10" is not available!
	 */
	public function databaseCompareActionsNoDefinedThrowsException() {
		$objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', array('get'));
		$classReflectionMock = $this->getMock('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', array(), array(new \Etobi\CoreAPI\Service\DatabaseApiService()));
		$this->subject = $this->getMock('Etobi\\CoreApi\\Service\\DatabaseApiService', array('trimExplode'));

		$classReflectionMock->expects($this->once())->method('getConstants')->will($this->returnValue($this->getAvailableActions()));
		$objectManagerMock->expects($this->once())->method('get')->with('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseApiService')->will($this->returnValue($classReflectionMock));
		$this->subject->expects($this->once())->method('trimExplode')->with('10')->will($this->returnValue(array('10')));
		$this->subject->injectObjectManager($objectManagerMock);

		$this->subject->databaseCompare('10');
	}

	/**
	 * @test
	 * @covers ::databaseCompare
	 */
	public function databaseCompareOneAction() {
		$this->markTestIncomplete();
		$objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', array('get'));
		$classReflectionMock = $this->getMock('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', array(), array(new \Etobi\CoreAPI\Service\DatabaseApiService()));
		$this->subject = $this->getMock('Etobi\\CoreApi\\Service\\DatabaseApiService', array('trimExplode'));

		$classReflectionMock->expects($this->once())->method('getConstants')->will($this->returnValue($this->getAvailableActions()));
		$objectManagerMock->expects($this->once())->method('get')->with('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseApiService')->will($this->returnValue($classReflectionMock));
		$this->subject->expects($this->once())->method('trimExplode')->with('1')->will($this->returnValue(array('1')));
		$this->subject->injectObjectManager($objectManagerMock);

		$this->subject->databaseCompare('1');
	}

	/**
	 * Returns the complete available actions
	 *
	 * @return array
	 */
	protected function getAvailableActions() {
		return array(
			'ACTION_UPDATE_CLEAR_TABLE' => 1,
			'ACTION_UPDATE_ADD' => 2,
			'ACTION_UPDATE_CHANGE' => 3,
			'ACTION_UPDATE_CREATE_TABLE' => 4,
			'ACTION_REMOVE_CHANGE' => 5,
			'ACTION_REMOVE_DROP' => 6,
			'ACTION_REMOVE_CHANGE_TABLE' => 7,
			'ACTION_REMOVE_DROP_TABLE' => 8
		);
	}

	protected function getLoadedExtensions() {
		return array(
			'core' => array(
				'type' => 'S',
				'siteRelPath' => 'typo3/sysext/core/',
				'typo3RelPath' => 'sysext/core/',
				'ext_localconf.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/core/ext_localconf.php',
				'ext_tables.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/core/ext_tables.php',
				'ext_tables.sql' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/core/ext_tables.sql',
				'ext_icon' => 'ext_icon.png'
			),
			'backend' => array(
				'type' => 'S',
				'siteRelPath' => 'typo3/sysext/backend/',
				'typo3RelPath' => 'sysext/backend/',
				'ext_localconf.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/backend/ext_localconf.php',
				'ext_tables.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/backend/ext_tables.php',
				'ext_icon' => 'ext_icon.png'
			),
			'extbase' => array(
				'type' => 'S',
				'siteRelPath' => 'typo3/sysext/extbase/',
				'typo3RelPath' => 'sysext/extbase/',
				'ext_localconf.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/extbase/ext_localconf.php',
				'ext_tables.php' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/extbase/ext_tables.php',
				'ext_tables.sql' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/extbase/ext_tables.sql',
				'ext_typoscript_setup.txt' => '/Volumes/HDD/Users/sok/Sites/TYPO3/www.coreapi.dev/http/typo3/sysext/extbase/ext_typoscript_setup.txt',
				'ext_icon' => 'ext_icon.png'
			),
		);
	}
}

