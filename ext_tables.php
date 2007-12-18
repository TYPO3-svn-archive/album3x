<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_album3x_images');

$TCA['tx_album3x_images'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images',
		'label'     => 'title',
		'label_alt' => 'image',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'mainpalette' => '1',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_album3x_images.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, title, description, image',
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:album3x/pi1/flexform_ds.xml');

t3lib_extMgm::addPlugin(array('LLL:EXT:album3x/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/3X_Album','3X Album');
t3lib_extMgm::addLLrefForTCAdescr('tx_album3x_images', 'EXT:album3x/locallang_csh.php');

if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_album3x_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_album3x_pi1_wizicon.php';
?>