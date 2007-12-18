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
 *   43: class tx_album3x_realurl
 *   55:     function main($params, $ref)
 *   65:     function id2alias($value)
 *   99:     function alias2id($value)
 *  117:     function addAlbum3xConfig($params, &$pObj)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('realurl', 'class.tx_realurl_advanced.php'));

class tx_album3x_realurl {

	private	$uidToAliasMap = array();
	private	$aliasToUidMap = array();

	/**
	 * Main etry point
	 *
	 * @param	array		$params	Paramters passed by realurl
	 * @param	tx_realurl		$pObj	Reference to tx_realurl intance
	 * @return	mixed		Conversion result
	 */
	function main($params, $ref)    {
		return ($params['decodeAlias'] ? $this->alias2id($params['value']) : $this->id2alias($params['value']));
	}

	/**
	 * COnverts uid of the record to readable alias
	 *
	 * @param	int		$value	uid of the record
	 * @return	string		Created alias
	 */
	function id2alias($value) {
		$value = intval($value);
		if (!($result = $this->uidToAliasMap[$value])) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title,image,realurl_alias', 'tx_album3x_images',
						'uid=' . $value . ' AND hidden=0 AND deleted=0 AND starttime<=UNIX_TIMESTAMP() AND (endtime>=UNIX_TIMESTAMP() OR endtime=0)');
			if (count($rows)) {
				if ($rows[0]['realurl_alias']) {
					$result = $rows[0]['realurl_alias'];
				}
				else {
					$realurl = t3lib_div::makeInstance('tx_realurl_advanced');
					/* @var $realurl tx_realurl_advanced */
					$result = $realurl->encodeTitle($rows[0]['title'] ? $rows[0]['title'] : $rows[0]['image']);
					// Check uniqueness
					list($info) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_album3x_images',
								'uid<>' . $value . ' AND realurl_alias=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($result, 'tx_album3x_images'));
					if ($info['t'] > 0) {
						$result .= '_' . $value;
					}
					// Update database
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_album3x_images', 'uid=' . $value, array('realurl_alias' => $result));
				}
				$this->uidToAliasMap[$value] = $result;
			}
		}
		return $result;
	}

	/**
	 * Converts alias of the record to its uid
	 *
	 * @param	string		$value	Alias of the record
	 * @return	mixed		uid of the record or <code>false</code> if could not be resolved
	 */
	function alias2id($value) {
		if (!($result = $this->aliasToUidMap[$value])) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'tx_album3x_images',
						'realurl_alias=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, 'tx_album3x_images') . ' AND hidden=0 AND deleted=0 AND starttime<=UNIX_TIMESTAMP() AND (endtime>=UNIX_TIMESTAMP() OR endtime=0)');
			if (count($rows)) {
				$this->aliasToUidMap[$value] = $result = $rows[0]['uid'];
			}
		}
		return $result;
	}

	/**
	 * Generates additional RealURL configuration and merges it with provided configuration
	 *
	 * @param	array		$params	Default configuration
	 * @param	tx_realurl_autoconfgen		$pObj	Parent object
	 * @return	array		Updated configuration
	 */
	function addAlbum3xConfig($params, &$pObj) {
		return array_merge_recursive($params['config'], array(
				    'postVarSets' => array(
						'_DEFAULT' => array(
							'page3x' => array(
								array(
									'GETvar' => 'tx_album3x_pi1[page]',
								),
							),
							'image3x' => array(
								array(
									'GETvar' => 'tx_album3x_pi1[showUid]',
									'userFunc' => 'EXT:album3x/class.tx_album3x_realurl.php:&tx_album3x_realurl->main',
								),
							),
					))));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/class.tx_album3x_realurl.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/class.tx_album3x_realurl.php']);
}

?>