<?php

namespace JambageCom\Move\Api;


/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger (franz@ttproducts.de)
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
 * Move Hook Api
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage move
 */



class MoveApi
{
    static public function getTableArray ()
    {
        $result = array();

        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]) &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.'])
        ) {
            $systemTableArray = array();
            $extensionTableArray = array();

            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['systemtables'])) {
                $systemTableArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['systemtables']);
            }

            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['extensiontables'])) {
                $extensionTableArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['extensiontables']);
            }

            $tableArray = array_merge($systemTableArray, $extensionTableArray);
            foreach ($tableArray as $table) {
                $result[] = array(
                    'table' => $table,
                    'title' => $table // TODO: title of the table
                );
            }
        }

        return $result;
    }

    static public function getLocalDefinitionArray ()
    {
        $tableArray = self::getTableArray();

        $result = array(
            array(
                'ext' => MOVE_EXT,
                'tables' => $tableArray
            )
        );
        return $result;
    }

    static public function getPathname ()
    {
        $result = false;

        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]) &&
            is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]) &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']) &&
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['path'])
        ) {
            $result = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]['move.']['path'];
        }

        return $result;
    }


    static public function execute (...$params)
    {
        $result = false;

        if (
            isset($params) &&
            is_array($params) &&
            !empty($params)
        ) {
            $result = true;
            $configurationFile = $params['0'];
            $tableArray = $params['1'];
            foreach ($tableArray as $table) {
                if (
                    isset($GLOBALS['TCA'][$table]) &&
                    isset($GLOBALS['TCA'][$table]['columns'])
                ) {
                    $select_fields = '*';
                    $where_clause =
                        $table . '.deleted=0';

                    $result = \JambageCom\Move\Api\MoveItemsApi::execute(
                        $configurationFile,
                        $table,
                        $select_fields,
                        $where_clause
                    );
                }
            }
        }

        return $result;
    }

}
