<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_album3x_images=1
');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_album3x_pi1.php','_pi1','list_type',1);

// Page module hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['album3x_pi1'][] = 'EXT:album3x/class.tx_album3x_cms_layout.php:tx_album3x_cms_layout->getExtensionSummary';

// RealURL autoconfiguration
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['album3x'] = 'EXT:album3x/class.tx_album3x_realurl.php:tx_album3x_realurl->addAlbum3xConfig'
?>