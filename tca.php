<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_album3x_images'] = array (
	'ctrl' => $TCA['tx_album3x_images']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,title,description,image'
	),
	'feInterface' => $TCA['tx_album3x_images']['feInterface'],
	'columns' => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'title' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),
		'description' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_album3x',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'image_thumb' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.image_thumb',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_album3x',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'image_mid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.image_mid',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_album3x',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'realurl_alias' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:album3x/locallang_db.xml:tx_album3x_images.realurl_alias',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim,alphanum_x',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'title;;2;;1-1-1, description;;;;2-2-2, image')
	),
	'palettes' => array (
		'1' => array('showitem' => 'hidden, starttime, endtime'),
		'2' => array('showitem' => 'realurl_alias'),
	)
);
?>