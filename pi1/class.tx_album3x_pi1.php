<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov <dmitry@typo3.org>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_album3x_pi1 extends tslib_pibase
 *   72:     function main($content, $conf)
 *   84:     function init($conf)
 *  120:     function fetchConfigValue($param)
 *  140:     function getList()
 *  235:     function getSingle()
 *  322:     function getThumbnail($imgFileName, &$width, &$height)
 *  343:     function getMidImage($imgFileName, &$width, &$height)
 *  362:     function generateImage($imagePath, $cObjName, $subst)
 *  380:     function preProcessImageObjConf(&$item, $key, $subst)
 *  397:     function getPageBrowser($page, $rpp, $rowCount)
 *  457:     function getPageBrowser_getPageLink($page)
 *
 * TOTAL FUNCTIONS: 11
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'class.t3lib_refindex.php');


/**
 * Plugin '3X Album' for the 'album3x' extension.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_album3x
 */
class tx_album3x_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_album3x_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_album3x_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'album3x';	// The extension key.
	var $pi_checkCHash = true;	// Required for proper caching when piVars are used
	var $templateCode;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content, $conf)	{
		$this->init($conf);
		$content = ($this->piVars['showUid'] ? $this->getSingle() : $this->getList());
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Initializes plugin configuration.
	 *
	 * @param	array		$conf	Configuration from TS
	 * @return	[type]		...
	 */
	function init($conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();

		t3lib_div::loadTCA('tx_album3x_images');

		foreach (explode(',', 'thumbnailWidth,thumbnailHeight,midImageWidth,midImageHeight,columns,rows,storagePid,templateFile') as $key) {
			$this->fetchConfigValue($key);
		}

		if (0 == intval($this->conf['storagePid'])) {
			$this->conf['storagePid'] = $GLOBALS['TSFE']->id;
		}

		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);

		// Add tags to page header (if any)
		$key = 'EXT:album3x' . md5($this->templateCode);
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {	// Addo only once!
			$headerParts = $this->cObj->getSubpart($this->templateCode, '###HEADER_ADDITIONS###');
			if ($headerParts) {
				$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath('album3x'));
				$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
			}
		}
	}

	/**
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 *
	 * @param	string		$param	Parameter name. If <code>.</code> is found, the first part is section name, second is key (applies only to $this->conf)
	 * @return	void
	 */
	function fetchConfigValue($param) {
		if (strchr($param, '.')) {
			list($section, $param) = explode('.', $param, 2);
		}
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, ($section ? 's' . ucfirst($section) : 'sDEF')));
		if (!is_null($value) && $value != '') {
			if ($section) {
				$this->conf[$section . '.'][$param] = $value;
			}
			else {
				$this->conf[$param] = $value;
			}
		}
	}

	/**
	 * Generates paged image list.
	 *
	 * @return	string		Generated content
	 */
	function getList() {
		// Get layout information
		$page = max(1, intval($this->piVars['page']));
		$cols = intval($this->conf['columns']); $cols = ($cols ? $cols : 4);
		$rows = intval($this->conf['rows']); $rows = ($rows ? $rows : 3);

		// Get rows
		$imgRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title,image,image_thumb', 'tx_album3x_images',
					'pid=' . intval($this->conf['storagePid']) . $this->cObj->enableFields('tx_album3x_images'),
					'sorting', '', (($page - 1)*$rows*$cols) . ',' . ($rows*$cols));
		if (count($imgRows) == 0) {
			// No images
			return $this->cObj->substituteMarker(
						$this->cObj->getSubpart($this->templateCode, '###IMAGELIST_EMPTY###'),
						'###EMPTY_ALBUM###', $this->pi_getLL('empty.album')
						);
		}

		$formattedRows = array();
		$template = $this->cObj->getSubpart($this->templateCode, '###IMAGELIST_COLUMN###');
		foreach ($imgRows as $key => $row) {
			// Check if thumbnail is up to date
			$imagePath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image']['config']['uploadfolder'] . '/' . $row['image'];
			$absImagePath = PATH_site . $imagePath;
			$imageThumbPath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image_thumb']['config']['uploadfolder'] . '/' . $row['image_thumb'];
			$absImageThumbPath = PATH_site . $imageThumbPath;
			if (!$row['image_thumb'] || !@file_exists($absImageThumbPath) || @filemtime($absImagePath) > @filemtime($absImageThumbPath)) {
				// Need to generate image
				list($width, $height) = getimagesize($absImagePath);
				$imageThumbPath = $this->getThumbnail($imagePath, $width, $height);
				// Update database
				$newPath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image_thumb']['config']['uploadfolder'] . preg_replace('/^.*(\/[^\/]*)(\.[^\.]*)$/', '\1_thumb\2', $imagePath);
				@unlink(PATH_site . $newPath);
				@copy(PATH_site . $imageThumbPath, PATH_site . $newPath);
				unlink(PATH_site . $imageThumbPath);
				$imageThumbPath = $newPath;
				$absImageThumbPath = PATH_site . $imageThumbPath;
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_album3x_images', 'uid=' . $row['uid'], array('image_thumb' => substr($imageThumbPath, strlen($GLOBALS['TCA']['tx_album3x_images']['columns']['image_thumb']['config']['uploadfolder']) + 1)));
				$refindex = t3lib_div::makeInstance('t3lib_refindex');
				/* @var $refindex t3lib_refindex */
				$refindex->updateRefIndexTable('tx_album3x_images', $row['uid']);
			}
			else {
				// Get image size information
				if (false === ($result = getimagesize($absImageThumbPath))) {
					// Ignore image
					unset($imgRows[$key]);
					continue;
				}
				$width = $result[0]; $height = $result[1];
			}
			// Format image
			$formattedRows[] = $this->cObj->substituteMarkerArray($template, array(
					'###SINGLEVIEW_URL###' => $this->pi_list_linkSingle('', $row['uid'], true, array(), true),
					'###IMAGE_TITLE###' => htmlspecialchars($row['title']),
					'###IMAGE_URL###' => $imageThumbPath,
					'###IMAGE_WIDTH###' => $width,
					'###IMAGE_HEIGHT###' => $height,
				));
		}

		// Put it all together
		$imgRowCount = count($formattedRows);
		if ($imgRowCount == 0) {
			// No rows
			return $this->cObj->substituteMarker(
						$this->cObj->getSubpart($this->templateCode, '###IMAGELIST_EMPTY###'),
						'###EMPTY_ALBUM###', $this->pi_getLL('empty.album')
						);
		}
		$allRows = array();
		$rowTemplate = $this->cObj->getSubpart($this->templateCode, '###IMAGE_ROW###');
		for ($row = 0, $i = 0; $row < $rows && $i < $imgRowCount; $row++) {
			$rowContent = array();
			for ($col = 0; $col < $cols && $i < $imgRowCount; $col++, $i++) {
				$rowContent[] = $formattedRows[$i];
			}
			if ($col < $cols) {
				$emptyColTemplate = $this->cObj->getSubpart($this->templateCode, '###IMAGELIST_EMPTY_COLUMN###');
				for ( ; $col < $cols; $col++) {
					$rowContent[] = $emptyColTemplate;
				}
			}
			$allRows[] = $this->cObj->substituteMarker($rowTemplate, '###IMAGE_COLUMN###', implode('', $rowContent));
		}

		$content = $this->cObj->substituteSubpart($this->cObj->getSubpart($this->templateCode, '###IMAGE_LIST###'), '###IMAGE_ROW###', implode('', $allRows));
		return $this->cObj->substituteSubpart($content, '###PAGE_BROWSER###', $this->getPageBrowser($page, $cols*$rows, count($imgRows)));
	}

	/**
	 * Shows single image
	 *
	 * @return	string		Generated HTML
	 */
	function getSingle() {
		$imgRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title,description,image,image_mid', 'tx_album3x_images',
					'uid=' . intval($this->piVars['showUid']) . $this->cObj->enableFields('tx_album3x_images'));
		if (count($imgRows) == 0) {
			// No images
			return $this->cObj->substituteMarker(
						$this->cObj->getSubpart($this->templateCode, '###IMAGE_EMPTY###'),
						'###NO_IMAGE###', $this->pi_getLL('no.image')
						);
		}

		$imagePath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image']['config']['uploadfolder'] . '/' . $imgRows[0]['image'];
		$absImagePath = PATH_site . $imagePath;
		$imageMidPath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image_mid']['config']['uploadfolder'] . '/' . $imgRows[0]['image_mid'];
		$absImageMidPath = PATH_site . $imageMidPath;
		if (!$imgRows[0]['image_mid'] || !@file_exists($absImageMidPath) || @filemtime($absImagePath) > @filemtime($absImageMidPath)) {
			// Need to generate image
			list($width, $height) = getimagesize($absImagePath);
			$imageMidPath = $this->getMidImage($imagePath, $width, $height);
			// Update database
			$newPath = $GLOBALS['TCA']['tx_album3x_images']['columns']['image_mid']['config']['uploadfolder'] . preg_replace('/^.*(\/[^\/]*)(\.[^\.]*)$/', '\1_mid\2', $imagePath);
			@unlink(PATH_site . $newPath);
			@copy(PATH_site . $imageMidPath, PATH_site . $newPath);
			unlink(PATH_site . $imageMidPath);
			$imageMidPath = $newPath;
			$absImageMidPath = PATH_site . $imageMidPath;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_album3x_images', 'uid=' . $imgRows[0]['uid'], array('image_mid' => substr($imageMidPath, strlen($GLOBALS['TCA']['tx_album3x_images']['columns']['image_mid']['config']['uploadfolder']) + 1)));
			// Update reference index -- important for 4.2 clean up tool!
			$refindex = t3lib_div::makeInstance('t3lib_refindex');
			/* @var $refindex t3lib_refindex */
			$refindex->updateRefIndexTable('tx_album3x_images', $imgRows[0]['uid']);
		}
		else {
			// Get image size information
			if (false === ($result = getimagesize($absImageMidPath))) {
				// Ignore image
				return $this->cObj->substituteMarker(
							$this->cObj->getSubpart($this->templateCode, '###IMAGE_EMPTY###'),
							'###NO_IMAGE###', $this->pi_getLL('no.image')
							);
			}
			$width = $result[0]; $height = $result[1];
		}
		// Format image
		$template = $this->cObj->getSubpart($this->templateCode, '###SINGLE_IMAGE###');
		$image = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($template, '###SINGLE_IMAGE_IMG###'),
				 array(
					'###IMAGE_TITLE###' => htmlspecialchars($imgRows[0]['title']),
					'###IMAGE_URL###' => $imageMidPath,
					'###IMAGE_WIDTH###' => $width,
					'###IMAGE_HEIGHT###' => $height,
				 ));
		$imgInfo = @getimagesize($absImagePath);
		$save = $GLOBALS['TSFE']->ATagParams;
		$GLOBALS['TSFE']->ATagParams .= ' ' . $imgRows[0]['title'] . ' (' . $imgInfo[0] . 'x' . $imgInfo[1] . ')';
		$image = $this->cObj->imageLinkWrap($image, $imagePath, array(
						'enable' => true,
						'width' => $imgInfo[0],
						'height' => $imgInfo[1],
						'title' => $imgRows[0]['title'] . ' (' . $imgInfo[0] . 'x' . $imgInfo[1] . ')',
						'JSwindow' => true,
						'bodyTag' => '<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0">',
						'wrap' => '<a href="#" onclick="window.close()">|</a>'
					));
		$GLOBALS['TSFE']->ATagParams = $save;
		$content = $this->cObj->substituteMarkerArrayCached($template,
					array(
						'###IMAGE_TITLE###' => htmlspecialchars($imgRows[0]['title']),
						'###IMAGE_DESC###' => nl2br(htmlspecialchars($imgRows[0]['description'])),
					),
					array(
						'###SINGLE_IMAGE_IMG###' => $image
					)
			);


		return $content;
	}

	/**
	 * Generates thumbnail
	 *
	 * @param	string		$absImgFileName	Absolute file name of the image
	 * @param	int		$width	Width
	 * @param	int		$height	Height
	 * @return	string		File name of the generated image
	 */
	function getThumbnail($imgFileName, &$width, &$height) {
		$subst = array(
					'width' => min($width, $this->conf['thumbnailWidth']) . 'm',
					'height' => min($height, $this->conf['thumbnailHeight']) . 'm',
					'image' => $imgFileName
				);
		$this->generateImage($imgFileName, 'thumbnailCObj', $subst);
		$width = $GLOBALS["TSFE"]->lastImgResourceInfo[0];
		$height = $GLOBALS["TSFE"]->lastImgResourceInfo[1];
		return $GLOBALS["TSFE"]->lastImgResourceInfo[3];
	}


	/**
	 * Generates mid-size image
	 *
	 * @param	string		$absImgFileName	Absolute file name of the image
	 * @param	int		$width	Width
	 * @param	int		$height	Height
	 * @return	string		File name of the generated image
	 */
	function getMidImage($imgFileName, &$width, &$height) {
		$subst = array(
					'width' => min($width, $this->conf['midImageWidth']) . 'm',
					'height' => min($height, $this->conf['midImageHeight']) . 'm',
					'image' => $absImgFileName
				);
		$this->generateImage($imgFileName, 'midImageCObj', $subst);
		$width = $GLOBALS["TSFE"]->lastImgResourceInfo[0];
		$height = $GLOBALS["TSFE"]->lastImgResourceInfo[1];
		return $GLOBALS["TSFE"]->lastImgResourceInfo[3];
	}
	/**
	 * Generates image if necessary
	 *
	 * @param	string		$absImagePath	Absolute path to full image path
	 * @param	string		$absNewImagePath	Absolute path to full image path
	 * @param	[type]		$subst: ...
	 * @return	void
	 */
	function generateImage($imagePath, $cObjName, $subst) {
		$imgObjType = $this->conf[$cObjName];
		$imgObjConf = $this->conf[$cObjName . '.'];
		array_walk_recursive($imgObjConf, array($this, 'preProcessImageObjConf'), $subst);
		$newCObj = t3lib_div::makeInstance('tslib_cObj');
		/* @var $newCObj tslib_cObj */
		$newCObj->start(array($newCObj->currentValKey => $imagePath));
		$newCObj->cObjGetSingle($imgObjType, $imgObjConf);
	}

	/**
	 * Callback for setting values in array
	 *
	 * @param	mixed		$item	Array value
	 * @param	mixed		$key	Array key
	 * @param	mixed		$subst	Substitution values
	 * @return	void
	 */
	function preProcessImageObjConf(&$item, $key, $subst) {
		if ($item{0} == '$') {
			$param = substr($item, 1);
			if (isset($subst[$param])) {
				$item = $subst[$param];
			}
		}
	}

	/**
	 * Creates a page browser
	 *
	 * @param	int		$page	Page numer
	 * @param	int		$rpp	Record per page
	 * @param	int		$rowCount	Numer of rown on the current page
	 * @return	string		Generated HTML
	 */
	function getPageBrowser($page, $rpp, $rowCount) {
		$haveNext = $haveLast = false;
		if ($rowCount == $rpp) {
			// Possibly next page
			list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('(COUNT(*)-' . ($page*$rpp) . ') AS t',
					'tx_album3x_images', 'pid=' . intval($this->conf['storagePid']) . $this->cObj->enableFields('tx_album3x_images'));
			$haveLast = ($info['t'] > $rpp);
			$haveNext = ($info['t'] > 0);
			$lastPage = $page + intval($info['t']/$rpp) + (($info['t'] % $rpp) ? 1 : 0);
		}
		$haveFirst = ($page > 2);
		$havePrev = ($page > 1);

		$markerArray = array();
		$sectionArray = array();

		$template = $this->cObj->getSubpart($this->templateCode, '###PAGE_BROWSER###');

		if (!$havePrev && !$haveNext) {
			return '';
		}

		if ($haveFirst) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_FIRST_WRAP###');
			$sectionArray['###LINK_FIRST_WRAP###'] = $this->cObj->substituteMarker($subTemplate, '###LINK_FIRST###', $this->getPageBrowser_getPageLink(1));
		}
		else {
			$sectionArray['###LINK_FIRST_WRAP###'] = '';
		}
		if ($havePrev) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_PREV_WRAP###');
			$sectionArray['###LINK_PREV_WRAP###'] = $this->cObj->substituteMarker($subTemplate, '###LINK_PREV###', $this->getPageBrowser_getPageLink($page - 1));
		}
		else {
			$sectionArray['###LINK_PREV_WRAP###'] = '';
		}
		if ($haveNext) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_NEXT_WRAP###');
			$sectionArray['###LINK_NEXT_WRAP###'] = $this->cObj->substituteMarker($subTemplate, '###LINK_NEXT###', $this->getPageBrowser_getPageLink($page + 1));
		}
		else {
			$sectionArray['###LINK_NEXT_WRAP###'] = '';
		}
		if ($haveLast) {
			$subTemplate = $this->cObj->getSubpart($template, '###LINK_LAST_WRAP###');
			$sectionArray['###LINK_LAST_WRAP###'] = $this->cObj->substituteMarker($subTemplate, '###LINK_LAST###', $this->getPageBrowser_getPageLink($lastPage));
		}
		else {
			$sectionArray['###LINK_LAST_WRAP###'] = '';
		}

		return $this->cObj->substituteMarkerArrayCached($template, array('###CUR_PAGE###' => $page), $sectionArray);
	}

	/**
	 * Generates page link. Keeps all current URL parameters except for cHash and tx_nccommerce_pi1[page].
	 *
	 * @param	int		$page	Page number starting from 1
	 * @return	string		Generated link
	 */
	function getPageBrowser_getPageLink($page) {
		return $this->cObj->typoLink('', array(
			'parameter' => $GLOBALS['TSFE']->id,
			'addQueryString' => 1,
			'addQueryString.' => array(
				'exclude' => $this->prefixId . '[page],cHash,no_cache',
			),
			'additionalParams' => '&' . $this->prefixId . '[page]=' . $page,
			'returnLast' => 'url',
			'useCacheHash' => true,
		));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/pi1/class.tx_album3x_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/pi1/class.tx_album3x_pi1.php']);
}
?>