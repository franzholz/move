<?php

namespace JambageCom\Move\Api;


/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger <franz@ttproducts.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Part of the tt_products (Shop System) extension.
 *
 * functions for the import of images into FAL
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_products
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class MoveItemsApi
{
    static public function execute (
        $configurationFile,
        $from_table,
        $select_fields,
        $where_clause
    )
    {
        $result = true;
        $infoArray = array();
        if ($where_clause != '') {
            $where_clause = $where_clause . ' AND ';
        }

        $absoluteFilename = GeneralUtility::getFileAbsFileName($configurationFile);
        $handle = fopen($absoluteFilename, 'rt');
        if ($handle === FALSE) {
            throw new \TYPO3\CMS\Core\Exception(MOVE_EXT . ': File not found ("' . $absoluteFilename . '")');
        } else {
            // Dateityp bestimmen
            $basename = basename($absoluteFilename);
            $posFileExtension = strrpos($basename, '.');
            $fileExtension = substr($basename, $posFileExtension + 1);

            if ($fileExtension == 'xml') {
                $objDom = new \DOMDocument;
                $resultLoad = $objDom->load($absoluteFilename, LIBXML_COMPACT);

                if ($resultLoad) {

                    $error = true;
                    $objConfs = $objDom->getElementsByTagName('Conf');
                    $count = 0;

                    foreach ($objConfs as $myConf) {
                        $count++;
                        $tag = $myConf->nodeName;
                        if ($tag == 'Conf') {
                            $objConfDetails = $myConf->childNodes;
                            $xmlConf = array();
                            $count = 0;

                            foreach ($objConfDetails as $rowDetail) {
                                $count++;
                                $detailValue = '';
                                $detailTag = $rowDetail->nodeName;

                                if ($detailTag != '#text') {
                                    $detailValue = trim($rowDetail->nodeValue);
                                    $xmlConf[$detailTag] = $detailValue;
                                }
                            }
                            // strip off leading zeros
                            // preg_replace('@^(0*)@', '', $variable);

                            if (
                                isset($xmlConf['Source']) &&
                                !empty($xmlConf['Source']) &&
                                isset($xmlConf['Destination']) &&
                                !empty($xmlConf['Destination'])
                            ) {
                                $time = time();
                                if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
                                    $time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
                                }

                                $fieldsArray = array();
                                $fieldsArray['tstamp'] = $time;
                                $fieldsArray['pid'] = intval($xmlConf['Destination']);
                                $updateWhere = $where_clause . $from_table . '.pid=' . intval($xmlConf['Source']);

                                $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
                                    $from_table,
                                    $updateWhere,
                                    $fieldsArray
                                );

                                $error = false;
                            } else {
                                break;
                            }
                        }
                    } // end of foreach
                } else {
                    throw new \TYPO3\CMS\Core\Exception($extKey . ': The file "' . $absoluteFilename . '" is not XML valid.');
                }
            } else {
                throw new \TYPO3\CMS\Core\Exception($extKey . ': The file "' . $absoluteFilename . '" has an invalid extension.');
            }
        }

        return $result;
    }
}

