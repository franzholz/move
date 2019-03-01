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



class CopyApi
{
    static public function execute (...$params)
    {
        $result = false;

        if (
            isset($params) &&
            is_array($params) &&
            !empty($params)
        ) {
            $result = array();
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
                    $resultExecute = \JambageCom\Move\Api\CopyItemsApi::execute(
                        $uidArray,
                        $configurationFile,
                        $table,
                        $select_fields,
                        $where_clause
                    );

                    if ($resultExecute) {
                        $result[$table] = $uidArray;
                    }
                }
            }
        }

        return $result;
    }

}
