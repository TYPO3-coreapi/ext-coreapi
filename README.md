[![Stories in Ready](https://badge.waffle.io/typo3-coreapi/ext-coreapi.png?label=ready&title=Ready)](https://waffle.io/typo3-coreapi/ext-coreapi) [![Build Status](https://travis-ci.org/TYPO3-coreapi/ext-coreapi.svg?branch=feature%2FMakeExtensionApiCompatibleTo62)](https://travis-ci.org/TYPO3-coreapi/ext-coreapi) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TYPO3-coreapi/ext-coreapi/badges/quality-score.png?b=feature%2FMakeExtensionApiCompatibleTo62)](https://scrutinizer-ci.com/g/TYPO3-coreapi/ext-coreapi/?branch=feature%2FMakeExtensionApiCompatibleTo62) [![Code Coverage](https://scrutinizer-ci.com/g/TYPO3-coreapi/ext-coreapi/badges/coverage.png?b=feature%2FMakeExtensionApiCompatibleTo62)](https://scrutinizer-ci.com/g/TYPO3-coreapi/ext-coreapi/?branch=feature%2FMakeExtensionApiCompatibleTo62)

## TYPO3 Extension 'coreapi'

The EXT:coreapi should provide a simple to use API for common core features. Goal is to be able to do the most common tasks by CLI instead of doing it in the backend/browser.

Beside of CLI commands, EXT:coreapi provides service classes, which can be used in your own implementation/extension.

Checkout the project website at forge.typo3.org:
	http://forge.typo3.org/projects/show/extension-coreapi

### Tasks
* DatabaseApi
	* databaseCompare
* CacheApi
	* clearAllCaches
	* clearPageCache
	* clearConfigurationCache
* ExtensionApi
	* info
	* listInstalled
	* updateList from TER
	* fetch an extension from TER
	* import an extension
	* install / uninstall extension
	* create upload folders
	* configure extension
* SiteApi
	* info
	* createSysNews

#### planned/comming soon

* Backend
	* manage users (list, create, update, delete)
	* lock/unlock the TYPO3 backend
* PageTree
	* print/get
* DataApi
 	* generic list/create/update/delete records (and not doing the plain SQL, but using the DataHandler (aka tcemain)!)
	* getRecordsByPid
	* create a database dump (exclude "temporary" tables like caches, sys_log, ...)
* ReportsApi
	* run/check the reports from the reports module
* ConfigurationApi
	* list, get and set TYPO3 configurations


### CLI call: ###

Make sure you have a backend user called `_cli_lowlevel`

If you want to use the cache clearing commands, you need to add the following snippet to the TSconfig field of this backend user:

	options.clearCache.all=1
	options.clearCache.pages=1

#### TYPO3 6.2 and below ####
Support for TYPO3 CMS below 6.2 was removed with version 0.2.0 of this extension. In case you need to use ext:coreapi in combination with lower version of TYPO3 CMS use version [0.1.0-beta](https://github.com/TYPO3-coreapi/ext-coreapi/releases/tag/0.1.0-beta "0.1.0-beta").

#### TYPO3 6.2+ ####
If you are using TYPO3 6.2+, you can use the awesome CommandController of Extbase.

This will show you all available calls
	./typo3/cli_dispatch.phpsh extbase help

### Usage in Composer ###

    {
        "name": "typo3cms/test-website",
        "description": "TYPO3 CMS: test.com",
        "keywords": ["typo3", "cms"],
        "require": {
            "php": ">=5.3.3",
            "typo3core/cms": "*",
            "etobi/coreapi": "dev-master",
        },
        "extra": {
            "installer-paths": {
                "typo3conf/ext/{$name}": [
                    "type:typo3-cms-extension"
                ]
            }
        },
        "minimum-stability": "dev",
        "require-dev": {},
        "scripts": {}
    }

### Running the unit tests

The Unit Tests rely on [vfsStream](https://github.com/mikey179/vfsStream "vfsStream"). For some reasons ext:coreapi don't add this dependencies by itself but uses the one which is alread defined for Core Unit Tests.
To install vfsStream copy the composer.json from the TYPO3 CMS package into you webroot folder and execute the command `composer install`. This will install all dependencies into Packages/Libraries/.

Then run the Unit Tests.

    cp typo3_src/composer.json .
    composer install
    ./bin/phpunit --colors -c typo3/sysext/core/Build/UnitTests.xml typo3conf/ext/coreapi/Tests/Unit/
