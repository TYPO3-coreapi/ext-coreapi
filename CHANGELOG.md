# Change Log

## [1.2.1](https://github.com/TYPO3-coreapi/ext-coreapi/tree/1.2.1) (2015-11-15)

[Full Changelog](https://github.com/TYPO3-coreapi/ext-coreapi/compare/1.2.0...1.2.1)

**Closed issues:**

- extensionapi:info throws error [\#108](https://github.com/TYPO3-coreapi/ext-coreapi/issues/108)

- Add classloading information to composer.json [\#105](https://github.com/TYPO3-coreapi/ext-coreapi/issues/105)

**Merged pull requests:**

- Add autoloading [\#111](https://github.com/TYPO3-coreapi/ext-coreapi/pull/111) ([DanielSiepmann](https://github.com/DanielSiepmann))

- \[BUGFIX\] Changes self::MAXIMUM\_LINE\_LENGTH to -\>output-\>getMaximumLineLength\(\) [\#110](https://github.com/TYPO3-coreapi/ext-coreapi/pull/110) ([jmverges](https://github.com/jmverges))

- Fixed bug  [\#109](https://github.com/TYPO3-coreapi/ext-coreapi/pull/109) ([thomashohn](https://github.com/thomashohn))

- \[TASK\] Add autoload information to composer.json [\#107](https://github.com/TYPO3-coreapi/ext-coreapi/pull/107) ([smichaelsen](https://github.com/smichaelsen))

- \[BUGFIX\] missing constants MAXIMUM\_LINE\_LENGTH added [\#106](https://github.com/TYPO3-coreapi/ext-coreapi/pull/106) ([ddiebel](https://github.com/ddiebel))

## [1.2.0](https://github.com/TYPO3-coreapi/ext-coreapi/tree/1.2.0) (2015-05-05)

[Full Changelog](https://github.com/TYPO3-coreapi/ext-coreapi/compare/1.1.1...1.2.0)

**Implemented enhancements:**

- Add command which deletes really all caches [\#96](https://github.com/TYPO3-coreapi/ext-coreapi/issues/96)

**Closed issues:**

- Adding a Command to clear the cache like it is possible to do in the install tool [\#90](https://github.com/TYPO3-coreapi/ext-coreapi/issues/90)

- Add HHVM config [\#114](https://github.com/TYPO3-coreapi/ext-coreapi/issues/114)

- Add HHVM as build environment on Travis-CI and allow it to fail [\#113](https://github.com/TYPO3-coreapi/ext-coreapi/issues/113)

- Migrate to container-based infrastructure on Travis-CI [\#112](https://github.com/TYPO3-coreapi/ext-coreapi/issues/112)

**Merged pull requests:**

- \[TASK\] Remove leading Slash on class names [\#104](https://github.com/TYPO3-coreapi/ext-coreapi/pull/104) ([7elix](https://github.com/7elix))

- Exception with TYPO3 7 [\#103](https://github.com/TYPO3-coreapi/ext-coreapi/pull/103) ([smichaelsen](https://github.com/smichaelsen))

- \[TASK\] using sprintf for logger message too [\#102](https://github.com/TYPO3-coreapi/ext-coreapi/pull/102) ([greinerd](https://github.com/greinerd))

- \[FEATURE\] Make part of factory default [\#101](https://github.com/TYPO3-coreapi/ext-coreapi/pull/101) ([mbrodala](https://github.com/mbrodala))

- Add requirement for typo3/cms to composer.json [\#98](https://github.com/TYPO3-coreapi/ext-coreapi/pull/98) ([rabe69](https://github.com/rabe69))

- \[FEATURE\] Adds command to create uploads folders of all installed extensions [\#97](https://github.com/TYPO3-coreapi/ext-coreapi/pull/97) ([lars85](https://github.com/lars85))

- \[TASK\] ConfigurationApi [\#89](https://github.com/TYPO3-coreapi/ext-coreapi/pull/89) ([achimfritz](https://github.com/achimfritz))

## [1.1.1](https://github.com/TYPO3-coreapi/ext-coreapi/tree/1.1.1) (2014-10-28)

[Full Changelog](https://github.com/TYPO3-coreapi/ext-coreapi/compare/1.1.0...1.1.1)

## [1.1.0](https://github.com/TYPO3-coreapi/ext-coreapi/tree/1.1.0) (2014-10-28)

[Full Changelog](https://github.com/TYPO3-coreapi/ext-coreapi/compare/1.0.0-beta...1.1.0)

**Implemented enhancements:**

- Refactor calls to t3lib\_div::makeInstance [\#93](https://github.com/TYPO3-coreapi/ext-coreapi/issues/93)

- code cleanup [\#33](https://github.com/TYPO3-coreapi/ext-coreapi/issues/33)

- refactor database compare [\#31](https://github.com/TYPO3-coreapi/ext-coreapi/issues/31)

- Make extensionapi:fetch working with TYPO3 CMS \>= 6.2 [\#67](https://github.com/TYPO3-coreapi/ext-coreapi/issues/67)

**Fixed bugs:**

- databaseapi:databasecompare drops needed 'categories' fields [\#92](https://github.com/TYPO3-coreapi/ext-coreapi/issues/92)

**Closed issues:**

- Release to TER [\#58](https://github.com/TYPO3-coreapi/ext-coreapi/issues/58)

- logging with the new logging api [\#30](https://github.com/TYPO3-coreapi/ext-coreapi/issues/30)

- lock/unlock the TYPO3 backend [\#22](https://github.com/TYPO3-coreapi/ext-coreapi/issues/22)

- return valid exit codes [\#19](https://github.com/TYPO3-coreapi/ext-coreapi/issues/19)

**Merged pull requests:**

- adding option to clear all caches hard like in install tool [\#91](https://github.com/TYPO3-coreapi/ext-coreapi/pull/91) ([greinerd](https://github.com/greinerd))

- Clear all active opcode caches [\#88](https://github.com/TYPO3-coreapi/ext-coreapi/pull/88) ([bgmgmbh](https://github.com/bgmgmbh))

- Clear the new system cache [\#87](https://github.com/TYPO3-coreapi/ext-coreapi/pull/87) ([bgmgmbh](https://github.com/bgmgmbh))

- Clear system cache [\#71](https://github.com/TYPO3-coreapi/ext-coreapi/pull/71) ([bgmgmbh](https://github.com/bgmgmbh))

## [1.0.0-beta](https://github.com/TYPO3-coreapi/ext-coreapi/tree/1.0.0-beta) (2014-07-13)

[Full Changelog](https://github.com/TYPO3-coreapi/ext-coreapi/compare/0.1.0-beta...1.0.0-beta)

**Implemented enhancements:**

- Refactor CacheApiService and CacheApiCommandController [\#83](https://github.com/TYPO3-coreapi/ext-coreapi/issues/83)

- Create Unit Tests for CacheAPI [\#82](https://github.com/TYPO3-coreapi/ext-coreapi/issues/82)

- Make extensionapi:import compatible with TYPO3 CMS \>= 6.2 [\#80](https://github.com/TYPO3-coreapi/ext-coreapi/issues/80)

- Make extensionapi:createuploadfolders compatible with TYPO3 CMS \>= 6.2 [\#79](https://github.com/TYPO3-coreapi/ext-coreapi/issues/79)

- Make extensionapi:listmirrors compatible with TYPO3 CMS \>= 6.2 [\#78](https://github.com/TYPO3-coreapi/ext-coreapi/issues/78)

- Make extensionapi:configure compatible with TYPO3 CMS \>= 6.2 [\#77](https://github.com/TYPO3-coreapi/ext-coreapi/issues/77)

- Make extensionapi:uninstall compatible with TYPO3 CMS \>= 6.2 [\#76](https://github.com/TYPO3-coreapi/ext-coreapi/issues/76)

- Make extensionapi:install with TYPO3 CMS \>= 6.2 [\#75](https://github.com/TYPO3-coreapi/ext-coreapi/issues/75)

- Make extensionapi:updatelist with TYPO3 CMS \>= 6.2 [\#74](https://github.com/TYPO3-coreapi/ext-coreapi/issues/74)

- Make extensionapi:listinstalled with TYPO3 CMS \>= 6.2 [\#73](https://github.com/TYPO3-coreapi/ext-coreapi/issues/73)

- Make extensionapi:info with TYPO3 CMS \>= 6.2 [\#72](https://github.com/TYPO3-coreapi/ext-coreapi/issues/72)

- Make extensionapi:configure working with TYPO3 CMS \>= 6.2 [\#66](https://github.com/TYPO3-coreapi/ext-coreapi/issues/66)

- Make extensionapi:uninstall working with TYPO3 CMS \>= 6.2 [\#65](https://github.com/TYPO3-coreapi/ext-coreapi/issues/65)

- Make extensionapi:install working with TYPO3 CMS \>= 6.2 [\#64](https://github.com/TYPO3-coreapi/ext-coreapi/issues/64)

- Make extensionapi:updatelist working with TYPO3 CMS \>= 6.2 [\#63](https://github.com/TYPO3-coreapi/ext-coreapi/issues/63)

- Get Extension API working with TYPO3 CMS \>= 6.2 [\#46](https://github.com/TYPO3-coreapi/ext-coreapi/issues/46)

- deprecate Dispatcher.php in favour of CommandControllers [\#18](https://github.com/TYPO3-coreapi/ext-coreapi/issues/18)

**Fixed bugs:**

- extension:import doesn't work [\#51](https://github.com/TYPO3-coreapi/ext-coreapi/issues/51)

**Closed issues:**

- Add Unit Tests to SiteApiService [\#85](https://github.com/TYPO3-coreapi/ext-coreapi/issues/85)

- Refactor SiteApiService and SiteApiCommandController [\#84](https://github.com/TYPO3-coreapi/ext-coreapi/issues/84)

- vfsstream [\#69](https://github.com/TYPO3-coreapi/ext-coreapi/issues/69)

- Connect extension with Scrutinizer [\#68](https://github.com/TYPO3-coreapi/ext-coreapi/issues/68)

- Compatibility with 6.2.x [\#39](https://github.com/TYPO3-coreapi/ext-coreapi/issues/39)

**Merged pull requests:**

- waffle.io Badge [\#62](https://github.com/TYPO3-coreapi/ext-coreapi/pull/62) ([waffle-iron](https://github.com/waffle-iron))

## [0.1.0-beta](https://github.com/TYPO3-coreapi/ext-coreapi/tree/0.1.0-beta) (2014-05-30)

**Implemented enhancements:**

- refactor dispatcher [\#34](https://github.com/TYPO3-coreapi/ext-coreapi/issues/34)

**Fixed bugs:**

- Composer.json creates error [\#60](https://github.com/TYPO3-coreapi/ext-coreapi/issues/60)

- TYPO3 4.5 - Class 'Tx\_Coreapi\_Cli\_Dispatcher' not found [\#43](https://github.com/TYPO3-coreapi/ext-coreapi/issues/43)

**Closed issues:**

- un/install extensions [\#38](https://github.com/TYPO3-coreapi/ext-coreapi/issues/38)

- cache:clearconfigurationcache does not work with TYPO3 <= 4.7 [\#9](https://github.com/TYPO3-coreapi/ext-coreapi/issues/9)

- Missing classes in autoload file [\#8](https://github.com/TYPO3-coreapi/ext-coreapi/issues/8)

- Print available commands? [\#7](https://github.com/TYPO3-coreapi/ext-coreapi/issues/7)

**Merged pull requests:**

- \#60: Composer.json creates error  [\#61](https://github.com/TYPO3-coreapi/ext-coreapi/pull/61) ([bmoex](https://github.com/bmoex))

- \[BUGFIX\] Fix wrong method name [\#48](https://github.com/TYPO3-coreapi/ext-coreapi/pull/48) ([georgringer](https://github.com/georgringer))

- \[FEATURE\] Allow to clear all caches except page caches [\#45](https://github.com/TYPO3-coreapi/ext-coreapi/pull/45) ([georgringer](https://github.com/georgringer))

- \[BUGFIX\] Added class to ext\_autoload [\#44](https://github.com/TYPO3-coreapi/ext-coreapi/pull/44) ([christophlehmann](https://github.com/christophlehmann))

- CLI dispatch parameter help path "coreapi" [\#41](https://github.com/TYPO3-coreapi/ext-coreapi/pull/41) ([7elix](https://github.com/7elix))

- Refactor dispatcher [\#40](https://github.com/TYPO3-coreapi/ext-coreapi/pull/40) ([madsbrunn](https://github.com/madsbrunn))

- \[TASK\] Add a .gitignore file. [\#17](https://github.com/TYPO3-coreapi/ext-coreapi/pull/17) ([oliverklee](https://github.com/oliverklee))

- If a news is created, the field cruser\_id should be filled with the uid of the cli user. [\#16](https://github.com/TYPO3-coreapi/ext-coreapi/pull/16) ([georgringer](https://github.com/georgringer))

- \[BUGFIX\] Concatenate arrays instead of merging [\#15](https://github.com/TYPO3-coreapi/ext-coreapi/pull/15) ([lars85](https://github.com/lars85))

- Feature: create extension upload folder [\#14](https://github.com/TYPO3-coreapi/ext-coreapi/pull/14) ([jaguerra](https://github.com/jaguerra))

- Add hint to readme about TSconfig for clearing cache [\#12](https://github.com/TYPO3-coreapi/ext-coreapi/pull/12) ([stmllr](https://github.com/stmllr))

- cache:clearconfigurationcache fails in TYPO3 < 6.0 [\#10](https://github.com/TYPO3-coreapi/ext-coreapi/pull/10) ([stmllr](https://github.com/stmllr))

- Database api improvements [\#4](https://github.com/TYPO3-coreapi/ext-coreapi/pull/4) ([helhum](https://github.com/helhum))

- Handle allowed actions in database-compare properly [\#3](https://github.com/TYPO3-coreapi/ext-coreapi/pull/3) ([mficzel](https://github.com/mficzel))

- Documentation update [\#2](https://github.com/TYPO3-coreapi/ext-coreapi/pull/2) ([stmllr](https://github.com/stmllr))

- Improve the coreapi [\#1](https://github.com/TYPO3-coreapi/ext-coreapi/pull/1) ([georgringer](https://github.com/georgringer))

- New features, a little restructuring and better help function in 4.5 - 4.6 [\#13](https://github.com/TYPO3-coreapi/ext-coreapi/pull/13) ([madsbrunn](https://github.com/madsbrunn))

- Add hint to readme about TSconfig for clearing cache [\#11](https://github.com/TYPO3-coreapi/ext-coreapi/pull/11) ([stmllr](https://github.com/stmllr))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*