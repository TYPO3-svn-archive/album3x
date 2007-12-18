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
 *   46: class tx_album3x_cms_layout
 *   55:     function getExtensionSummary($params, &$pObj)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Provides information about pi1 plugin for page module (TYPO3 4.2 feature).
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_album3x
 */
class tx_album3x_cms_layout {

	/**
	 * Callback for web module to return information about pi1 plugin.
	 *
	 * @param	array		$params	Parameters to hook
	 * @param	mixed		$pObj	Parent object (unused)
	 * @return	mixed		Description of the plugin or <code>false</code> if called for wrong type
	 */
	function getExtensionSummary($params, &$pObj) {
		if ($params['row']['list_type'] == 'album3x_pi1') {
			return $GLOBALS['LANG']->sL('EXT:album3x/locallang.xml:pi1_plus_wiz_description');
		}
		return false;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/class.tx_album3x_cms_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/album3x/class.tx_album3x_cms_layout.php']);
}

?>