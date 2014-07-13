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

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ExtensionApiServiceTest
 * 
 * @package Etobi\CoreApi\Tests\Unit\Service
 * @author  Stefano Kowalke <blueduck@gmx.net>
 * @coversDefaultClass \Etobi\CoreAPI\Service\ExtensionApiService
 */
class ExtensionApiServiceTest extends UnitTestCase {

	/**
	 * @var \Etobi\CoreApi\Service\ExtensionApiService|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject
	 */
	protected $subject;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Repository\Helper|\PHPUnit_Framework_MockObject_MockObject $repositoryHelperMock
	 */
	protected $repositoryHelperMock;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Model\Mirrors|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mirrorsMock;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Model\Extension|\PHPUnit_Framework_MockObject_MockObject $extensionMock
	 */
	protected $extensionMock;

	/**
	 * @var |\PHPUnit_Framework_MockObject_MockObject $extensionRepositoryMock
	 */
	protected $extensionRepositoryMock;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository|\PHPUnit_Framework_MockObject_MockObject $repositoryRepositoryMock
	 */
	protected $repositoryRepositoryMock;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility|\PHPUnit_Framework_MockObject_MockObject $repositoryRepositoryMock
	 */
	protected $configurationMock;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService $extensionManagementService
	 */
	protected $extensionManagementServiceMock;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManagerMock
	 */
	protected $objectManagerMock;

	/**
	 * @var string $installPath
	 */
	protected $installPath = 'root/coreapi/';

	/**
	 * Set the test up
	 */
	public function setup() {
		$this->subject = $this->getAccessibleMock('Etobi\\CoreApi\\Service\\ExtensionApiService', array('dummy'));
		$this->objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', array('get'));
		$this->extensionMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Extension', array('dummy'));

		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$this->repositoryHelperMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Repository\\Helper', array(), array(), '', FALSE);
		$this->mirrorsMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Mirrors');

		$this->subject->injectFileHandlingUtility($fileHandlingUtility);
	}

	//
	// Tests for importExtension()
	//

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage File "vfs://root/path/to/importfolder/dummy.t3x" does not exist!
	 */
	public function importExtensionDemandedFileNotExistsThrowsException() {
		vfsStream::setup('root');
		vfsStream::create(array(
			'path' => array(
				'to' => array(
					'importfolder' => array(
					)
				)
			)
		));

		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/dummy.t3x'));
	}

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage System installation is not allowed!
	 */
	public function importExtensionDemandedLocationNotAllowed() {
		vfsStream::setup('root');
		vfsStream::create(array(
			'path' => array(
				'to' => array(
					'importfolder' => array(
						'dummy.t3x' => 'File exists'
					)
				)
			)
		));

		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/dummy.t3x'), 'System');
	}

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException \TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage File had no or wrong content.
	 */
	public function importExtensionDemandedFileIsEmpty() {
		vfsStream::setup('root');
		vfsStream::create(array(
			'path' => array(
				'to' => array(
					'importfolder' => array(
						'dummy.t3x' => ''
					)
				)
			)
		));

		$uploadExtensionFileController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController');

		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);

		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/dummy.t3x'), 'Local');
	}

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException \TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage Decoding the file went wrong. No extension key found
	 */
	public function importExtensionDemandedFileDataIsNotAnArray() {
		vfsStream::setup('root');
		vfsStream::copyFromFileSystem(PATH_site . 'typo3conf/ext/coreapi/Tests/Unit/Resources/vfsStream/importCommand/');

		$importFile = 'root/path/to/importfolder/realurl_1.12.8.t3x';
		$fetchData = '';

		$uploadExtensionFileController = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController', array('dummy'));
		$terUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Connection\\TerUtility', array('decodeExchangeData'));

		$terUtilityMock->expects($this->once())->method('decodeExchangeData')->will($this->returnValue($fetchData));

		$uploadExtensionFileController->_set('terUtility', $terUtilityMock);
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/realurl_1.12.8.t3x'), 'Local');
	}

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException \TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage Decoding the file went wrong. No extension key found
	 */
	public function importExtensionDemandedFileDataMissesKey() {
		vfsStream::setup('root');
		vfsStream::copyFromFileSystem(PATH_site . 'typo3conf/ext/coreapi/Tests/Unit/Resources/vfsStream/importCommand/');

		$importFile = 'root/path/to/importfolder/realurl_1.12.8.t3x';
		$fetchData = array(
			'EM_CONF' => array(
				'title' => 'RealURL: speaking paths for TYPO3',
				'description' => 'Creates nice looking URLs for TYPO3 pages: converts http://example.com/index.phpid=12345&L=2 to http://example.com/path/to/your/page/. Please, ask for free support in TYPO3 mailing lists or contact the maintainer for paid support.',
				'category' => 'fe',
				'shy' => 0,
				'version' => '1.12.8',
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'TYPO3_version' => '4.5.0-6.2.999',
				'PHP_version' => '5.3.2-5.999.999',
				'module' => '',
				'state' => 'stable',
				'uploadfolder' => 0,
				'createDirs' => '',
				'modifiy_tables' => 'pages,sys_domain,pages_language_overlay,sys_template',
				'clearcacheonload' => 1,
				'lockType' => '',
				'author' => 'Dmitry Dulepov',
				'author_email' => 'dmitry.dulepov@gmail.com',
				'author_company' => '',
				'CGLcompliance' => NULL,
				'CGLcompliance_note' => NULL
			),
			'misc' => array(),
			'techInfo' => array(),
			'FILES' => array()
		);

		$uploadExtensionFileController = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController', array('dummy'));
		$terUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Connection\\TerUtility', array('decodeExchangeData'));

		$terUtilityMock->expects($this->once())->method('decodeExchangeData')->will($this->returnValue($fetchData));

		$uploadExtensionFileController->_set('terUtility', $terUtilityMock);
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/realurl_1.12.8.t3x'), 'Local');
	}

	/**
	 * @test
	 * @covers ::importExtension
	 * @expectedException \TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 */
	public function importExtensionOptionOverrideFalseExtensionAlreadyExists() {
		vfsStream::setup('root');
		vfsStream::copyFromFileSystem(PATH_site . 'typo3conf/ext/coreapi/Tests/Unit/Resources/vfsStream/importCommand/');
		vfsStream::create(array('typo3conf' => array('ext' => array('realurl' => array()))));

		$importFile = 'root/path/to/importfolder/realurl_1.12.8.t3x';
		$fetchData = array(
			'extKey' => 'realurl',
			'EM_CONF' => array(
				'title' => 'RealURL: speaking paths for TYPO3',
				'description' => 'Creates nice looking URLs for TYPO3 pages: converts http://example.com/index.phpid=12345&L=2 to http://example.com/path/to/your/page/. Please, ask for free support in TYPO3 mailing lists or contact the maintainer for paid support.',
				'category' => 'fe',
				'shy' => 0,
				'version' => '1.12.8',
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'TYPO3_version' => '4.5.0-6.2.999',
				'PHP_version' => '5.3.2-5.999.999',
				'module' => '',
				'state' => 'stable',
				'uploadfolder' => 0,
				'createDirs' => '',
				'modifiy_tables' => 'pages,sys_domain,pages_language_overlay,sys_template',
				'clearcacheonload' => 1,
				'lockType' => '',
				'author' => 'Dmitry Dulepov',
				'author_email' => 'dmitry.dulepov@gmail.com',
				'author_company' => '',
				'CGLcompliance' => NULL,
				'CGLcompliance_note' => NULL
			),
			'misc' => array(),
			'techInfo' => array(),
			'FILES' => array()
		);

		$uploadExtensionFileController = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController', array('translate'));
		$terUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Connection\\TerUtility', array('decodeExchangeData'));
		$installUtilityMock = $this->getMock('TYPO3\\CMS\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));

		$terUtilityMock->expects($this->once())->method('decodeExchangeData')->will($this->returnValue($fetchData));
		$installUtilityMock->expects($this->once())->method('isAvailable')->will($this->returnValue(TRUE));

		$uploadExtensionFileController->_set('terUtility', $terUtilityMock);
		$uploadExtensionFileController->_set('installUtility', $installUtilityMock);
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/realurl_1.12.8.t3x'), 'Local');
	}

	/**
	 * @test
	 * @covers ::importExtension
	 */
	public function importExtensionOptionOverrideTrueExtensionAlreadyExists() {
		vfsStream::setup('root');
		vfsStream::copyFromFileSystem(PATH_site . 'typo3conf/ext/coreapi/Tests/Unit/Resources/vfsStream/importCommand/');
		vfsStream::create(array('typo3conf' => array('ext' => array('realurl' => array()))));

		$importFile = 'root/path/to/importfolder/realurl_1.12.8.t3x';
		$fetchData = array(
			'extKey' => 'realurl',
			'EM_CONF' => array(
				'title' => 'RealURL: speaking paths for TYPO3',
				'description' => 'Creates nice looking URLs for TYPO3 pages: converts http://example.com/index.phpid=12345&L=2 to http://example.com/path/to/your/page/. Please, ask for free support in TYPO3 mailing lists or contact the maintainer for paid support.',
				'category' => 'fe',
				'shy' => 0,
				'version' => '1.12.8',
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'TYPO3_version' => '4.5.0-6.2.999',
				'PHP_version' => '5.3.2-5.999.999',
				'module' => '',
				'state' => 'stable',
				'uploadfolder' => 0,
				'createDirs' => '',
				'modifiy_tables' => 'pages,sys_domain,pages_language_overlay,sys_template',
				'clearcacheonload' => 1,
				'lockType' => '',
				'author' => 'Dmitry Dulepov',
				'author_email' => 'dmitry.dulepov@gmail.com',
				'author_company' => '',
				'CGLcompliance' => NULL,
				'CGLcompliance_note' => NULL
			),
			'misc' => array(),
			'techInfo' => array(),
			'FILES' => array()
		);

		$uploadExtensionFileController = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController', array('translate', 'copyExtensionFolderToTempFolder'));
		$terUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Connection\\TerUtility', array('decodeExchangeData'));
		$installUtilityMock = $this->getMock('TYPO3\\CMS\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility', array('unpackExtensionFromExtensionDataArray'));

		$terUtilityMock->expects($this->once())->method('decodeExchangeData')->will($this->returnValue($fetchData));
		$installUtilityMock->expects($this->once())->method('isAvailable')->will($this->returnValue(TRUE));
		$fileHandlingUtility->expects($this->once())->method('unpackExtensionFromExtensionDataArray')->with($fetchData);
		$uploadExtensionFileController->expects($this->once())->method('copyExtensionFolderToTempFolder')->with('realurl');

		$uploadExtensionFileController->_set('terUtility', $terUtilityMock);
		$uploadExtensionFileController->_set('installUtility', $installUtilityMock);
		$uploadExtensionFileController->_set('fileHandlingUtility', $fileHandlingUtility);
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/realurl_1.12.8.t3x'), 'Local', TRUE);
	}
	/**
	 * @test
	 * @covers ::importExtension
	 */
	public function importExtensionOptionOverrideFalseExtensionNotExists() {
		vfsStream::setup('root');
		vfsStream::copyFromFileSystem(PATH_site . 'typo3conf/ext/coreapi/Tests/Unit/Resources/vfsStream/importCommand/');
		vfsStream::create(array('typo3conf' => array('ext' => array('realurl' => array()))));

		$importFile = 'root/path/to/importfolder/realurl_1.12.8.t3x';
		$fetchData = array(
			'extKey' => 'realurl',
			'EM_CONF' => array(
				'title' => 'RealURL: speaking paths for TYPO3',
				'description' => 'Creates nice looking URLs for TYPO3 pages: converts http://example.com/index.phpid=12345&L=2 to http://example.com/path/to/your/page/. Please, ask for free support in TYPO3 mailing lists or contact the maintainer for paid support.',
				'category' => 'fe',
				'shy' => 0,
				'version' => '1.12.8',
				'dependencies' => '',
				'conflicts' => '',
				'priority' => '',
				'loadOrder' => '',
				'TYPO3_version' => '4.5.0-6.2.999',
				'PHP_version' => '5.3.2-5.999.999',
				'module' => '',
				'state' => 'stable',
				'uploadfolder' => 0,
				'createDirs' => '',
				'modifiy_tables' => 'pages,sys_domain,pages_language_overlay,sys_template',
				'clearcacheonload' => 1,
				'lockType' => '',
				'author' => 'Dmitry Dulepov',
				'author_email' => 'dmitry.dulepov@gmail.com',
				'author_company' => '',
				'CGLcompliance' => NULL,
				'CGLcompliance_note' => NULL
			),
			'misc' => array(),
			'techInfo' => array(),
			'FILES' => array()
		);

		$uploadExtensionFileController = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController', array('translate', 'copyExtensionFolderToTempFolder'));
		$terUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Connection\\TerUtility', array('decodeExchangeData'));
		$installUtilityMock = $this->getMock('TYPO3\\CMS\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility', array('unpackExtensionFromExtensionDataArray'));

		$terUtilityMock->expects($this->once())->method('decodeExchangeData')->will($this->returnValue($fetchData));
		$installUtilityMock->expects($this->once())->method('isAvailable')->will($this->returnValue(FALSE));
		$fileHandlingUtility->expects($this->once())->method('unpackExtensionFromExtensionDataArray')->with($fetchData);
		$uploadExtensionFileController->expects($this->never())->method('translate');
		$uploadExtensionFileController->expects($this->never())->method('copyExtensionFolderToTempFolder')->with('realurl');

		$uploadExtensionFileController->_set('terUtility', $terUtilityMock);
		$uploadExtensionFileController->_set('installUtility', $installUtilityMock);
		$uploadExtensionFileController->_set('fileHandlingUtility', $fileHandlingUtility);
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($uploadExtensionFileController));

		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->importExtension(vfsStream::url('root/path/to/importfolder/realurl_1.12.8.t3x'), 'Local');
	}

	//
	// Tests for getExtensionInformation()
	//

	/**
	 * @test
	 * @covers ::getExtensionInformation
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage No extension key given!
	 */
	public function getExtensionInformationNoExtensionKeyGivenThrowsException(){
		$this->subject->getExtensionInformation('');
	}

	/**
	 * @test
	 * @covers ::getExtensionInformation
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" does not exist!
	 */
	public function getExtensionInformationExtensionNotFoundThrowsException() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(FALSE));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->getExtensionInformation('dummy');
	}

	/**
	 * @test
	 * @covers ::getExtensionInformation
	 */
	public function getExtensionInformationReturnsInformation() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));
		$this->subject = $this->getAccessibleMock('Etobi\\CoreApi\\Service\\ExtensionApiService', array('listExtensions'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));
		$this->subject->expects($this->once())->method('listExtensions')->will($this->returnValue($this->getExtensionArrayForCreateUploadFolders()));

		$this->subject->injectInstallUtility($installUtilityMock);

		$currentExtensionInformation = $this->subject->getExtensionInformation('dummy');

		$this->assertArrayHasKey('em_conf', $currentExtensionInformation);
		$this->assertArrayHasKey('is_installed', $currentExtensionInformation);
		$this->assertSame($currentExtensionInformation['em_conf']['title'], 'Dummy Extension for testing');
		$this->assertSame($currentExtensionInformation['is_installed'], TRUE);
	}

	/**
	 * @test
	 * @covers ::listExtensions
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Only "Local", "System", "Global" and "" (all) are supported as type
	 */
	public function getInstalledExtensionWrongTypeGivenThrowsException() {
		$this->subject->listExtensions('42');
	}

	/**
	 * @test
	 * @covers ::listExtensions
	 */
	public function getInstalledExtensionReturnsListOfLocalExtensions() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isLoaded'));
		$listUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility', array('getAvailableExtensions'));
		$emConfUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\EmConfUtility', array('includeEmConf'));

		$installUtilityMock->expects($this->any())->method('isLoaded')->will($this->returnValue(TRUE));
		$extensions = $this->getFakeInstalledExtensionArray();
		$listUtility->expects($this->exactly(2))->method('getAvailableExtensions')->will($this->returnValue($extensions));
		$emConfUtility->expects($this->at(0))->method('includeEmConf');
		$this->objectManagerMock->expects($this->at(0))->method('get')->will($this->returnValue($listUtility));
		$this->objectManagerMock->expects($this->at(1))->method('get')->will($this->returnValue($emConfUtility));
		$this->objectManagerMock->expects($this->at(2))->method('get')->will($this->returnValue($listUtility));
		$emConfUtility->expects($this->at(2))->method('includeEmConf')->will($this->returnValue($extensions['coreapi']));
		$this->objectManagerMock->expects($this->at(3))->method('get')->will($this->returnValue($emConfUtility));

		$this->subject->injectInstallUtility($installUtilityMock);
		$this->subject->injectObjectManager($this->objectManagerMock);

		$listLocal = $this->subject->listExtensions('Local');
		$this->assertTrue(count($listLocal) === 0);
		$listLocal = $this->subject->listExtensions('Local');
		$this->assertTrue(count($listLocal) === 1);
	}

	/**
	 * @test
	 * @covers ::listExtensions
	 */
	public function getInstalledExtensionReturnsListOfGlobalExtensions() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isLoaded'));
		$listUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility', array('getAvailableExtensions'));
		$emConfUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\EmConfUtility', array('includeEmConf'));

		$installUtilityMock->expects($this->any())->method('isLoaded')->will($this->returnValue(TRUE));
		$extensions = $this->getFakeInstalledExtensionArray();
		$listUtility->expects($this->exactly(2))->method('getAvailableExtensions')->will($this->returnValue($extensions));
		$emConfUtility->expects($this->at(0))->method('includeEmConf');
		$this->objectManagerMock->expects($this->at(0))->method('get')->will($this->returnValue($listUtility));
		$this->objectManagerMock->expects($this->at(1))->method('get')->will($this->returnValue($emConfUtility));
		$this->objectManagerMock->expects($this->at(2))->method('get')->will($this->returnValue($listUtility));
		$emConfUtility->expects($this->at(1))->method('includeEmConf')->will($this->returnValue($extensions['coreapi']));
		$this->objectManagerMock->expects($this->at(3))->method('get')->will($this->returnValue($emConfUtility));

		$this->subject->injectInstallUtility($installUtilityMock);
		$this->subject->injectObjectManager($this->objectManagerMock);

		$listGlobal = $this->subject->listExtensions('Global');
		$this->assertTrue(count($listGlobal) === 0);
		$listGlobal = $this->subject->listExtensions('Global');
		$this->assertTrue(count($listGlobal) === 1);
	}

	/**
	 * @test
	 * @covers ::listExtensions
	 */
	public function getInstalledExtensionReturnsListOfSystemExtensions() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isLoaded'));
		$listUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility', array('getAvailableExtensions'));
		$emConfUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\EmConfUtility', array('includeEmConf'));

		$installUtilityMock->expects($this->any())->method('isLoaded')->will($this->returnValue(TRUE));
		$listUtility->expects($this->exactly(2))->method('getAvailableExtensions')->will($this->returnValue($this->getFakeInstalledExtensionArray()));
		$this->objectManagerMock->expects($this->at(0))->method('get')->will($this->returnValue($listUtility));
		$this->objectManagerMock->expects($this->at(2))->method('get')->will($this->returnValue($listUtility));
		$emConfUtility->expects($this->at(0))->method('includeEmConf')->will($this->returnValue(array()));
		$emConfUtility->expects($this->at(1))->method('includeEmConf')->will($this->returnValue(array()));

		$extensions = $this->getExtensionArrayForCreateUploadFolders();
		$emConfUtility->expects($this->at(2))->method('includeEmConf')->will($this->returnValue($extensions['core']));
		$emConfUtility->expects($this->at(3))->method('includeEmConf')->will($this->returnValue($extensions['backend']));
		$this->objectManagerMock->expects($this->at(1))->method('get')->will($this->returnValue($emConfUtility));
		$this->objectManagerMock->expects($this->at(3))->method('get')->will($this->returnValue($emConfUtility));

		$this->subject->injectInstallUtility($installUtilityMock);
		$this->subject->injectObjectManager($this->objectManagerMock);

		$listSystem = $this->subject->listExtensions('System');
		$this->assertTrue(count($listSystem) === 0);

		$listSystem = $this->subject->listExtensions('System');
		$this->assertTrue(count($listSystem) === 2);
	}

	//
	// Tests for updateMirrors()
	//
	/**
	 * Creates and returns a repository object
	 *
	 * @return \TYPO3\CMS\ExtensionManager\Domain\Model\Repository
	 */
	public function getRepositoryData() {
		$repository = new \TYPO3\CMS\ExtensionManager\Domain\Model\Repository();
		$repository->setTitle('TYPO3.org Main Repository');
		$repository->setDescription('Main repository on typo3.org. This repository has some mirrors configured which are available with the mirror url.');
		$repository->setMirrorListUrl('http://repositories.typo3.org/mirrors.xml.gz');
		$repository->setWsdlUrl('http://typo3.org/wsdl/tx_ter_wsdl.php');
		$repository->setLastUpdate(new \DateTime('now'));
		$repository->setExtensionCount(42);
		$repository->setPid(0);

		return $repository;
	}

	/**
	 * @test
	 * @covers ::updateMirrors
	 */
	public function updateMirrorsReturnsFalse() {
		$repositoryRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array('findAll'), array(), '', FALSE);
		$repositoryRepositoryMock->expects($this->once())->method('findAll')->will($this->returnValue(array($this->getRepositoryData())));
		$this->repositoryHelperMock->expects($this->once())->method('updateExtList')->will($this->returnValue(FALSE));

		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectRepositoryRepository($repositoryRepositoryMock);

		$this->assertFalse($this->subject->updateMirrors());
	}

	/**
	 * @test
	 * @covers ::updateMirrors
	 */
	public function updateMirrorsReturnsTrue() {
		$repositoryRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array('findAll'), array(), '', FALSE);
		$repositoryRepositoryMock->expects($this->once())->method('findAll')->will($this->returnValue(array($this->getRepositoryData())));
		$this->repositoryHelperMock->expects($this->once())->method('updateExtList')->will($this->returnValue(TRUE));

		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectRepositoryRepository($repositoryRepositoryMock);

		$this->assertTrue($this->subject->updateMirrors());
	}

	//
	// Tests for createUpdateFolders()
	//

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

	//
	// Tests for installExtension()
	//

	/**
	 * @test
	 * @covers ::installExtension
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" does not exist!
	 */
	public function installExtensionNonExistentExtensionThrowsException() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(FALSE));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->installExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::installExtension
	 */
	public function installExtensionInstallsExtension() {
		$cacheManagerMock = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$installUtilityMock = $this->getAccessibleMock(
				'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
				array(
					'enrichExtensionWithDetails',
					'processDatabaseUpdates',
					'ensureConfiguredDirectoriesExist',
					'importInitialFiles',
					'isLoaded',
					'loadExtension',
					'reloadCaches',
					'processRuntimeDatabaseUpdates',
					'saveDefaultConfiguration',
					'isAvailable'
				));

		$extensions = $this->getExtensionArrayForCreateUploadFolders();
		$installUtilityMock
				->expects($this->once())
				->method('enrichExtensionWithDetails')
				->with('about')
				->will($this->returnValue($extensions['about'])
		);
		$installUtilityMock->expects($this->once())->method('processDatabaseUpdates')->with($extensions['about']);
		$installUtilityMock->expects($this->once())->method('ensureConfiguredDirectoriesExist');
		$installUtilityMock->expects($this->once())->method('importInitialFiles');
		$installUtilityMock->expects($this->once())->method('isLoaded');
		$installUtilityMock->expects($this->once())->method('loadExtension');
		$installUtilityMock->expects($this->once())->method('reloadCaches');
		$installUtilityMock->expects($this->once())->method('processRuntimeDatabaseUpdates');
		$installUtilityMock->expects($this->once())->method('saveDefaultConfiguration');
		$installUtilityMock->expects($this->once())->method('isAvailable')->with('about')->will($this->returnValue(TRUE));

		$installUtilityMock->_set('cacheManager', $cacheManagerMock);
		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->installExtension('about');
	}

	//
	// Tests for uninstallExtension()
	//
	/**
	 * @test
	 * @covers ::uninstallExtension
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Extension "coreapi" cannot be uninstalled!
	 */
	public function uninstallExtensionCoreApiThrowsException() {
		$this->subject->uninstallExtension('coreapi');
	}

	/**
	 * @test
	 * @covers ::uninstallExtension
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" does not exist!
	 */
	public function uninstallExtensionWhichNotExistsThrowsException() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(FALSE));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->uninstallExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::uninstallExtension
	 */
	public function uninstallExtensionUninstallExtension() {
		$installUtilityMock = $this->getAccessibleMock(
				'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
				array('unloadExtension', 'isAvailable', 'isLoaded')
		);
		$dependencyManagerMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DependencyUtility');

		$dependencyManagerMock->expects($this->once())->method('findInstalledExtensionsThatDependOnMe')->will($this->returnValue(array()));
		$installUtilityMock->expects($this->once())->method('unloadExtension');
		$installUtilityMock->expects($this->once())->method('isAvailable')->with('core')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('core')->will($this->returnValue(TRUE));

		$installUtilityMock->_set('dependencyUtility', $dependencyManagerMock);
		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->uninstallExtension('core');
	}

	//
	// Tests for configureExtension()
	//

	/**
	 * Creates the needed mocks for the test
	 */
	public function prepareConfigureExtensionTest(array $methodsToMock, $createConfigurationMock = TRUE) {
		if ($createConfigurationMock) {
			$this->configurationMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
			$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($this->configurationMock));
		}

		$this->subject = $this->getAccessibleMock('Etobi\\CoreApi\\Service\\ExtensionApiService', $methodsToMock);
		$this->subject->injectObjectManager($this->objectManagerMock);

		vfsStream::setup('root');
		vfsStream::create(array(
				'absolute' => array(
					'path' => array(
						'to' => array(
							'extension' => array('dummy' => array(
								'ext_conf_template.txt' => '
								# cat=basic; type=string; label=Excluded extensions: You can exclude extensions from being search for tests by writing their extension key here. Seperate the entries with comma.
								excludeextensions = lib, div
								# cat=basic; type=string; label=Path to Composer: Path to Composer installation which includes the vendor directory. Please remind to configure Composer to install the packages "phpunit/phpunit", "phpunit/phpunit-selenium" and "mikey179/vfsStream". Setting this will have preference over provided Composer packages.
								composerpath =
								# cat=selenium; type=small; label=Host of the Selenium RC server
								selenium_host = localhost
								# cat=selenium; type=int+; label=Port of the Selenium RC server
								selenium_port = 4444
								# cat=selenium; type=small; label=Browser that should be used to run Selenium tests: Allowed values are *firefox, *mock, *firefoxproxy, *pifirefox, *chrome, *iexploreproxy, *iexplore, *firefox3, *safariproxy, *googlechrome, *konqueror, *firefox2, *safari, *piiexplore, *firefoxchrome, *opera, *iehta, *custom
								selenium_browser = *firefox
								# cat=selenium; type=small; label=Default Selenium Browser URL: Leave empty to use domain of this TYPO3 installation (TYPO3_SITE_URL)
								selenium_browserurl =
								')
							)
						)
					)
				)
		));
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" does not exist!
	 */
	public function configureExtensionWhenExtensionNotExistsThrowsException() {
		$this->prepareConfigureExtensionTest(array('checkExtensionExists'), FALSE);
		$this->subject->expects($this->once())->method('checkExtensionExists')->will($this->throwException(new InvalidArgumentException(sprintf('Extension "dummy" does not exist!'))));

		$this->subject->configureExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" is not installed!
	 */
	public function configureExtensionWhenExtensionNotLoadedThrowsException() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(FALSE));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage No configuration provided for extension "dummy"!
	 */
	public function configureExtensionWhenNoExtensionConfigurationProvidedAsArgumentCommandLineThrowsException() {
		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" has no configuration options!
	 */
	public function configureExtensionNoDefaultExtensionsSettingsTemplateFileFoundThrowsException() {
		$this->prepareConfigureExtensionTest(array('getExtensionPath'), FALSE);

		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));
		$this->subject->expects($this->once())->method('getExtensionPath')->will($this->returnValue('/test/string'));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy', array('foo' => 'bar'));
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage No configuration setting with name "excludeextensions" for extension "dummy"!
	 */
	public function configureExtensionNewExtensionConfigurationSettingDefinesUnknownSettingsThrowsException() {
		$this->prepareConfigureExtensionTest(array('getExtensionPath'));

		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));

		$newExtensionConfiguration = array(
			'excludeextensions' => 'lib, div',
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => ''
		);

		$currentExtensionConfig = array(
			'composerpath' => array(
				'cat' => 'basic',
				'subcat' => 'x/z',
				'type' => 'string',
				'label' => 'Path to Composer: Path to Composer installation which includes the vendor directory. Please remind to configure Composer to install the packages "phpunit/phpunit", "phpunit/phpunit-selenium" and "mikey179/vfsStream". Setting this will have preference over provided Composer packages.',
				'name' => 'composerpath',
				'value' => '',
				'default_value' => ''
			),
			'selenium_host' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Host of the Selenium RC server',
				'name' => 'selenium_host',
				'value' => 'localhost',
				'default_value' => 'localhost'
			),
			'selenium_port' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'int+',
				'label' => 'Port of the Selenium RC server',
				'name' => 'selenium_port',
				'value' => '4444',
				'default_value' => '4444'
			),
			'selenium_browser' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Browser that should be used to run Selenium tests: Allowed values are *firefox, *mock, *firefoxproxy, *pifirefox, *chrome, *iexploreproxy, *iexplore, *firefox3, *safariproxy, *googlechrome, *konqueror, *firefox2, *safari, *piiexplore, *firefoxchrome, *opera, *iehta, *custom',
				'name' => 'selenium_browser',
				'value' => '*firefox',
				'default_value' => '*firefox'
			),
			'selenium_browserurl' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Default Selenium Browser URL: Leave empty to use domain of this TYPO3 installation (TYPO3_SITE_URL)',
				'name' => 'selenium_browserurl',
				'value' => '',
				'default_value' => ''
			)
		);

		$this->subject->expects($this->once())->method('getExtensionPath')->will($this->returnValue(vfsStream::url('root/absolute/path/to/extension/dummy/')));
		$this->configurationMock->expects($this->once())->method('getCurrentConfiguration')->will($this->returnValue($currentExtensionConfig));

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy', $newExtensionConfiguration);
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 */
	public function configureExtensionNewExtensionConfigurationMissesSomeKeyWritesConfiguration() {
		$this->prepareConfigureExtensionTest(array('getExtensionPath', 'writeConfiguration'));

		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));

		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));


		$newExtensionConfiguration = array(
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => ''
		);

		$this->subject->expects($this->once())->method('getExtensionPath')->will(
				$this->returnValue(vfsStream::url('root/absolute/path/to/extension/dummy/'))
		);

		$currentExtensionConfig = array(
						'excludeextensions' => array(
							'cat' => 'basic',
							'subcat' => 'x/z',
							'type' => 'string',
							'label' => 'Demo',
							'name' => 'excludeextensions',
							'value' => 'lib,div',
							'default_value' => 'lib, div'
						),
						'composerpath' => array(
							'cat' => 'basic',
							'subcat' => 'x/z',
							'type' => 'string',
							'label' => 'Path to Composer: Path to Composer installation which includes the vendor directory. Please remind to configure Composer to install the packages "phpunit/phpunit", "phpunit/phpunit-selenium" and "mikey179/vfsStream". Setting this will have preference over provided Composer packages.',
							'name' => 'composerpath',
							'value' => '',
							'default_value' => ''
						),
						'selenium_host' => array(
							'cat' => 'selenium',
							'subcat' => 'x/z',
							'type' => 'small',
							'label' => 'Host of the Selenium RC server',
							'name' => 'selenium_host',
							'value' => 'localhost',
							'default_value' => 'localhost'
						),
						'selenium_port' => array(
							'cat' => 'selenium',
							'subcat' => 'x/z',
							'type' => 'int+',
							'label' => 'Port of the Selenium RC server',
							'name' => 'selenium_port',
							'value' => '4444',
							'default_value' => '4444'
						),
						'selenium_browser' => array(
							'cat' => 'selenium',
							'subcat' => 'x/z',
							'type' => 'small',
							'label' => 'Browser that should be used to run Selenium tests: Allowed values are *firefox, *mock, *firefoxproxy, *pifirefox, *chrome, *iexploreproxy, *iexplore, *firefox3, *safariproxy, *googlechrome, *konqueror, *firefox2, *safari, *piiexplore, *firefoxchrome, *opera, *iehta, *custom',
							'name' => 'selenium_browser',
							'value' => '*firefox',
							'default_value' => '*firefox'
						),
						'selenium_browserurl' => array(
							'cat' => 'selenium',
							'subcat' => 'x/z',
							'type' => 'small',
							'label' => 'Default Selenium Browser URL: Leave empty to use domain of this TYPO3 installation (TYPO3_SITE_URL)',
							'name' => 'selenium_browserurl',
							'value' => '',
							'default_value' => ''
						)
					);
		$this->configurationMock
				->expects($this->once())
				->method('getCurrentConfiguration')
				->will($this->returnValue($currentExtensionConfig));

		$expectedExtensionConfiguration = array(
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => '',
			'excludeextensions' => 'lib,div'
		);

		$this->configurationMock
				->expects($this->once())
				->method('writeConfiguration')
				->with($expectedExtensionConfiguration, 'dummy');

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy', $newExtensionConfiguration);
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 */
	public function configureExtensionNewExtensionConfigurationMissesSomeKeyAndCurrentConfigurationValueIsEmptyWritesConfiguration() {
		$this->prepareConfigureExtensionTest(array('getExtensionPath', 'writeConfiguration'));

		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));
		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));
		$this->subject->expects($this->once())->method('getExtensionPath')->will(
				$this->returnValue(vfsStream::url('root/absolute/path/to/extension/dummy/'))
		);

		$currentExtensionConfig = array(
			'excludeextensions' => array(
				'cat' => 'basic',
				'subcat' => 'x/z',
				'type' => 'string',
				'label' => 'Demo',
				'name' => 'excludeextensions',
				'value' => '',
				'default_value' => 'lib,div'
			),
			'composerpath' => array(
				'cat' => 'basic',
				'subcat' => 'x/z',
				'type' => 'string',
				'label' => 'Path to Composer: Path to Composer installation which includes the vendor directory. Please remind to configure Composer to install the packages "phpunit/phpunit", "phpunit/phpunit-selenium" and "mikey179/vfsStream". Setting this will have preference over provided Composer packages.',
				'name' => 'composerpath',
				'value' => '',
				'default_value' => ''
			),
			'selenium_host' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Host of the Selenium RC server',
				'name' => 'selenium_host',
				'value' => 'localhost',
				'default_value' => 'localhost'
			),
			'selenium_port' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'int+',
				'label' => 'Port of the Selenium RC server',
				'name' => 'selenium_port',
				'value' => '4444',
				'default_value' => '4444'
			),
			'selenium_browser' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Browser that should be used to run Selenium tests: Allowed values are *firefox, *mock, *firefoxproxy, *pifirefox, *chrome, *iexploreproxy, *iexplore, *firefox3, *safariproxy, *googlechrome, *konqueror, *firefox2, *safari, *piiexplore, *firefoxchrome, *opera, *iehta, *custom',
				'name' => 'selenium_browser',
				'value' => '*firefox',
				'default_value' => '*firefox'
			),
			'selenium_browserurl' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Default Selenium Browser URL: Leave empty to use domain of this TYPO3 installation (TYPO3_SITE_URL)',
				'name' => 'selenium_browserurl',
				'value' => '',
				'default_value' => ''
			)
		);
		$this->configurationMock
				->expects($this->once())
				->method('getCurrentConfiguration')
				->will($this->returnValue($currentExtensionConfig));

		$expectedExtensionConfiguration = array(
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => '',
			'excludeextensions' => 'lib,div'
		);

		$this->configurationMock
				->expects($this->once())
				->method('writeConfiguration')
				->with($expectedExtensionConfiguration, 'dummy');

		$newExtensionConfiguration = array(
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => ''
		);

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy', $newExtensionConfiguration);
	}

	/**
	 * @test
	 * @covers ::configureExtension
	 */
	public function configureExtensionWhenExtensionWritesConfiguration() {
		$this->prepareConfigureExtensionTest(array('getExtensionPath', 'writeConfiguration'));

		$installUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility', array('isAvailable', 'isLoaded'));
		$installUtilityMock->expects($this->once())->method('isAvailable')->with('dummy')->will($this->returnValue(TRUE));
		$installUtilityMock->expects($this->once())->method('isLoaded')->with('dummy')->will($this->returnValue(TRUE));
		$this->subject->expects($this->once())->method('getExtensionPath')->will(
			$this->returnValue(vfsStream::url('root/absolute/path/to/extension/dummy/'))
		);

		$currentExtensionConfig = array(
			'excludeextensions' => array(
				'cat' => 'basic',
				'subcat' => 'x/z',
				'type' => 'string',
				'label' => 'Demo',
				'name' => 'excludeextensions',
				'value' => 'lib,div',
				'default_value' => 'lib, div'
			),
			'composerpath' => array(
				'cat' => 'basic',
				'subcat' => 'x/z',
				'type' => 'string',
				'label' => 'Path to Composer: Path to Composer installation which includes the vendor directory. Please remind to configure Composer to install the packages "phpunit/phpunit", "phpunit/phpunit-selenium" and "mikey179/vfsStream". Setting this will have preference over provided Composer packages.',
				'name' => 'composerpath',
				'value' => '',
				'default_value' => ''
			),
			'selenium_host' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Host of the Selenium RC server',
				'name' => 'selenium_host',
				'value' => 'localhost',
				'default_value' => 'localhost'
			),
			'selenium_port' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'int+',
				'label' => 'Port of the Selenium RC server',
				'name' => 'selenium_port',
				'value' => '4444',
				'default_value' => '4444'
			),
			'selenium_browser' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Browser that should be used to run Selenium tests: Allowed values are *firefox, *mock, *firefoxproxy, *pifirefox, *chrome, *iexploreproxy, *iexplore, *firefox3, *safariproxy, *googlechrome, *konqueror, *firefox2, *safari, *piiexplore, *firefoxchrome, *opera, *iehta, *custom',
				'name' => 'selenium_browser',
				'value' => '*firefox',
				'default_value' => '*firefox'
			),
			'selenium_browserurl' => array(
				'cat' => 'selenium',
				'subcat' => 'x/z',
				'type' => 'small',
				'label' => 'Default Selenium Browser URL: Leave empty to use domain of this TYPO3 installation (TYPO3_SITE_URL)',
				'name' => 'selenium_browserurl',
				'value' => '',
				'default_value' => ''
			)
		);

		$this->configurationMock
				->expects($this->once())
				->method('getCurrentConfiguration')
				->will($this->returnValue($currentExtensionConfig));

		$newExtensionConfiguration = array(
			'excludeextensions' => 'lib,div',
			'composerpath' => '',
			'selenium_host' => 'localhost',
			'selenium_port' => '4444',
			'selenium_browser' => '*firefox',
			'selenium_browserurl' => ''
		);

		$this->configurationMock
				->expects($this->once())
				->method('writeConfiguration')
				->with($newExtensionConfiguration, 'dummy');

		$this->subject->injectInstallUtility($installUtilityMock);

		$this->subject->configureExtension('dummy', $newExtensionConfiguration);
	}

	//
	// Tests for fetchExtension()
	//

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Extension "dummy" was not found on TER
	 */
	public function fetchExtensionNoVersionSetNoExtensionFound() {
		$this->extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findHighestAvailableVersion'), array(), '', FALSE);

		$this->extensionRepositoryMock->expects($this->once())->method('findHighestAvailableVersion')->with('dummy')->will($this->returnValue(NULL));

		$this->subject->injectExtensionRepository($this->extensionRepositoryMock);

		$this->subject->fetchExtension('dummy');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Version 1.0.0 of extension "dummy" does not exist
	 */
	public function fetchExtensionVersionSetNoExtensionFound() {
		$this->extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		$this->extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('dummy')->will($this->returnValue(NULL));

		$this->subject->injectExtensionRepository($this->extensionRepositoryMock);

		$this->subject->fetchExtension('dummy', '1.0.0');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 */
	public function fetchExtensionOptionVersionNotSetDownloadLatestVersion() {
		vfsStream::setup('root');
		vfsStream::create(array('typo3conf' => array('ext' => array('coreapi' => array()))));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('2.03');

		// Create the mock objects
		$extensionRepositoryMock = $this->getMock(
				'TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository',
				array('findHighestAvailableVersion'),
				array(),
				'',
				FALSE
		);

		$extensionManagementServiceMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService');
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$fileHandlingUtility = $this->getMock(
				'TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility',
				array('getExtensionDir')
		);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())
				->method('get')
				->will($this->returnValue($downloadUtilityMock)
		);

		$extensionRepositoryMock->expects($this->once())
				->method('findHighestAvailableVersion')
				->with('coreapi')
				->will($this->returnValue($this->extensionMock)
		);

		$extensionManagementServiceMock->expects($this->once())
				->method('downloadMainExtension')
				->with($this->extensionMock);

		$this->repositoryHelperMock->expects($this->once())
				->method('getMirrors')
				->will($this->returnValue($this->mirrorsMock)
		);

		$fileHandlingUtility->expects($this->at(1))
				->method('getExtensionDir')
				->with('coreapi', 'Local')
				->will($this->returnValue(vfsStream::url('root/typo3conf/ext/coreapi/'))
		);

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectExtensionManagementService($extensionManagementServiceMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 */
	public function fetchExtensionOptionVersionSetDownloadsDemandedVersion() {
		vfsStream::setup('root');
		vfsStream::create(array('typo3conf' => array('ext' => array('coreapi' => array()))));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$fileHandlingUtility = $this->getMock(
				'TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility',
				array('getExtensionDir')
		);
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);
		$extensionManagementServiceMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService');

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())
				->method('get')
				->will($this->returnValue($downloadUtilityMock)
		);

		$fileHandlingUtility->expects($this->at(1))
				->method('getExtensionDir')
				->with('coreapi', 'Local')
				->will($this->returnValue(vfsStream::url('root/typo3conf/ext/coreapi/'))
		);

		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('coreapi', '1.0')->will($this->returnValue($this->extensionMock));
		$extensionManagementServiceMock->expects($this->once())->method('downloadMainExtension')->with($this->extensionMock);
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectExtensionManagementService($extensionManagementServiceMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi', '1.0');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage System not in allowed download paths
	 */
	public function fetchExtensionOptionLocationSystemThrowsException() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['allowSystemInstall']);
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['allowGlobalInstall']);

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())
				->method('get')
				->will($this->returnValue($downloadUtilityMock)
		);
		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('coreapi', '1.0')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);

		$this->subject->fetchExtension('coreapi', '1.0', 'System');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage Global not in allowed download paths
	 */
	public function fetchExtensionOptionLocationGlobalThrowsException() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['allowSystemInstall']);
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['allowGlobalInstall']);

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($downloadUtilityMock));
		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('coreapi', '1.0')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);

		$this->subject->fetchExtension('coreapi', '1.0', 'Global');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage Local not in allowed download paths
	 */
	public function fetchExtensionOptionLocationLocalThrowsException() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['allowLocalInstall']);

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($downloadUtilityMock));
		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('coreapi', '1.0')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);

		$this->subject->fetchExtension('coreapi', '1.0', 'Local');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException
	 * @expectedExceptionMessage location not in allowed download paths
	 */
	public function fetchExtensionOptionLocationWithWrongDataThrowsException() {
		// Create the mock objects
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($downloadUtilityMock));
		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('coreapi', '1.0')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);

		$this->subject->fetchExtension('coreapi', '1.0', 'location');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Option --mirror must be a number. Run the command extensionapi:listmirrors to get the list of all available repositories
	 */
	public function fetchExtensionOptionMirrorIsNotANumber() {
		// Create the mock objects
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$extensionRepositoryMock->expects($this->never())->method('findOneByExtensionKeyAndVersion');

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);

		$this->subject->fetchExtension('coreapi', '', '', FALSE, 'test');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage No mirrors found!
	 */
	public function fetchExtensionNoMirrorsFoundThrowsException() {
		vfsStream::setup('root');
		vfsStream::create(array('dummy' => array()));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findHighestAvailableVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$fileHandlingUtility->expects($this->once())->method('getExtensionDir')->will($this->returnValue(vfsStream::url($this->installPath)));
		$extensionRepositoryMock->expects($this->once())->method('findHighestAvailableVersion')->with('coreapi')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue(NULL));

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi');
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 */
	public function fetchExtensionSetDemandedMirror() {
		vfsStream::setup('root');
		vfsStream::create(array('typo3conf' => array('ext' => array('coreapi' => array()))));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$extensionManagementServiceMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService');
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findHighestAvailableVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())
				->method('get')
				->will($this->returnValue($downloadUtilityMock)
		);
		$fileHandlingUtility->expects($this->at(1))->method('getExtensionDir')->will($this->returnValue(vfsStream::url('root/typo3conf/ext/coreapi/')));
		$extensionRepositoryMock->expects($this->once())->method('findHighestAvailableVersion')->with('coreapi')->will($this->returnValue($this->extensionMock));
		$repository = array(
			'title' => 'TYPO3.org Main Repository',
			'host' => 'typo3.org',
			'path' => '/fileadmin/ter/',
			'country' => 'DEU',
			'sponsorname' => 'punkt.de GmbH',
			'sponsorlink' => 'http://punkt.de/',
			'sponsorlogo' => 'http://repositories.typo3.org/sponsors/logo-punktde.gif'
		);
		$this->mirrorsMock->setMirrors($repository);
		$this->mirrorsMock->expects($this->once())->method('getMirrors')->will($this->returnValue($repository));
		$this->mirrorsMock->expects($this->once())->method('setSelect')->with(1);
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));
		$extensionManagementServiceMock->expects($this->once())->method('downloadMainExtension')->with($this->extensionMock);

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectExtensionManagementService($extensionManagementServiceMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi', '', 'Local', FALSE, 1);
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Extension "dummy" version 1.0 could not installed!
	 */
	public function fetchExtensionFoundDependencies() {
		$this->markTestSkipped();
		vfsStream::setup('root');
		vfsStream::create(array('dummy' => array()));

		$this->extensionMock->setExtensionKey('dummy');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$downloadUtilityMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility', array('dummy'));
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$this->extensionManagementServiceMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService');
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findOneByExtensionKeyAndVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$this->objectManagerMock->expects($this->once())
				->method('get')
				->will($this->returnValue($downloadUtilityMock)
		);
		$fileHandlingUtility->expects($this->once())->method('getExtensionDir')->will($this->returnValue(vfsStream::url($this->installPath)));
		$extensionRepositoryMock->expects($this->once())->method('findOneByExtensionKeyAndVersion')->with('dummy', '1.0')->will($this->returnValue($this->extensionMock));
		$repository = array(
			'title' => 'TYPO3.org Main Repository',
			'host' => 'typo3.org',
			'path' => '/fileadmin/ter/',
			'country' => 'DEU',
			'sponsorname' => 'punkt.de GmbH',
			'sponsorlink' => 'http://punkt.de/',
			'sponsorlogo' => 'http://repositories.typo3.org/sponsors/logo-punktde.gif'
		);

		$this->mirrorsMock->setMirrors($repository);
		$this->mirrorsMock->expects($this->once())->method('getMirrors')->will($this->returnValue($repository));
		$this->mirrorsMock->expects($this->once())->method('setSelect')->with(1);
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));
		$this->extensionManagementServiceMock->expects($this->once())->method('downloadMainExtension')->with($this->extensionMock);

		// Inject the mock objects
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectExtensionManagementService($this->extensionManagementServiceMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('dummy', '1.0', 'Local', FALSE, 1);
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Mirror "5" does not exist
	 */
	public function fetchExtensionDemandedMirrorOutOfRange() {
		vfsStream::setup('root');
		vfsStream::create(array('dummy' => array()));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findHighestAvailableVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$fileHandlingUtility->expects($this->once())->method('getExtensionDir')->will($this->returnValue(vfsStream::url($this->installPath)));
		$extensionRepositoryMock->expects($this->once())->method('findHighestAvailableVersion')->with('coreapi')->will($this->returnValue($this->extensionMock));
		$this->mirrorsMock->setMirrors(
				array(
					'title' => 'TYPO3.org Main Repository',
					'host' => 'typo3.org',
					'path' => '/fileadmin/ter/',
					'country' => 'DEU',
					'sponsorname' => 'punkt.de GmbH',
					'sponsorlink' => 'http://punkt.de/',
					'sponsorlogo' => 'http://repositories.typo3.org/sponsors/logo-punktde.gif'
				)
		);
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi', '', 'Local', FALSE, 5);
	}

	/**
	 * @test
	 * @covers ::fetchExtension
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Extension "coreapi" already exists at "vfs://root/coreapi/"!
	 */
	public function fetchExtensionOptionOverrideNotSetExtensionExistsAlready() {
		vfsStream::setup('root');
		vfsStream::create(array('coreapi' => array()));

		$this->extensionMock->setExtensionKey('coreapi');
		$this->extensionMock->setVersion('1.0');

		// Create the mock objects
		$fileHandlingUtility = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
		$extensionRepositoryMock = $this->getMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository', array('findHighestAvailableVersion'), array(), '', FALSE);

		// Add behavior to the mock objects
		$fileHandlingUtility->expects($this->once())->method('getExtensionDir')->will($this->returnValue(vfsStream::url($this->installPath)));
		$extensionRepositoryMock->expects($this->once())->method('findHighestAvailableVersion')->with('coreapi')->will($this->returnValue($this->extensionMock));
		$this->repositoryHelperMock->expects($this->never())->method('getMirrors');

		// Inject the mock objects
		$this->subject->injectExtensionRepository($extensionRepositoryMock);
		$this->subject->injectRepositoryHelper($this->repositoryHelperMock);
		$this->subject->injectFileHandlingUtility($fileHandlingUtility);

		$this->subject->fetchExtension('coreapi');
	}

	//
	// Tests for listMirrors()
	//

	/**
	 * @test
	 * @covers ::listMirrors
	 */
	public function listMirrorsListMirrors() {
		$repository = array(
			'title' => 'TYPO3.org Main Repository',
			'host' => 'typo3.org',
			'path' => '/fileadmin/ter/',
			'country' => 'DEU',
			'sponsorname' => 'punkt.de GmbH',
			'sponsorlink' => 'http://punkt.de/',
			'sponsorlogo' => 'http://repositories.typo3.org/sponsors/logo-punktde.gif'
		);

		$this->mirrorsMock->expects($this->once())->method('getMirrors')->will($this->returnValue($repository));
		$this->repositoryHelperMock->expects($this->once())->method('getMirrors')->will($this->returnValue($this->mirrorsMock));
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($this->repositoryHelperMock));
		$this->subject->injectObjectManager($this->objectManagerMock);

		$this->subject->listMirrors();
	}

	/**
	 * @param string $amount
	 *
	 * @return array
	 */
	protected function getFakeInstalledExtensionArray() {
		return array(
			'core' => array(
				'type' => 'System',
				'siteRelPath' => 'typo3/sysext/core/',
				'typo3RelPath' => 'sysext/core/',
				'ext_localconf.php' => 'path/to/core/ext_localconf.php',
				'ext_tables.php' => 'path/to/core/ext_tables.php',
				'ext_tables.sql' => 'path/to/core/ext_tables.sql',
				'ext_icon' => 'ext_icon.png'
			),
			'about' => array(),
			'backend' => array(
				'type' => 'System',
				'siteRelPath' => 'typo3/sysext/backend/',
				'typo3RelPath' => 'sysext/backend/',
				'ext_localconf.php' => 'path/to/backend/ext_localconf.php',
				'ext_tables.php' => 'path/to/backend/ext_tables.php',
				'ext_icon' => 'ext_icon.png'
			),
			'cms' => array(
				'type' => 'Global',
				'siteRelPath' => 'typo3/sysext/cms/',
				'typo3RelPath' => 'sysext/cms/',
				'ext_localconf.php' => 'path/to/cms/ext_localconf.php',
				'ext_tables.php' => 'path/to/cms/ext_tables.php',
				'ext_icon' => 'ext_icon.png'
			),
			'coreapi' => array(
				'type' => 'Local',
				'siteRelPath' => 'typo3conf/ext/coreapi/',
				'typo3RelPath' => '../typo3conf/ext/coreapi/',
				'ext_localconf.php' => 'path/to/coreapi/ext_localconf.php',
				'ext_icon' => 'ext_icon.png'
			),
			'dummy' => array(
				'type' => 'Local',
				'siteRelPath' => 'typo3conf/ext/dummy/',
				'typo3RelPath' => '../typo3conf/ext/dummy/',
				'ext_localconf.php' => 'path/to/dummy/ext_localconf.php',
				'ext_icon' => 'ext_icon.png'
			),
		);
	}
}

