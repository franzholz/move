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

class CopyItemsApi
{
    static public function execute
    (
        &$uidArray,
        $configurationFile,
        $from_table,
        $select_fields,
        $where_clause
    ) {
        $result = true;
        $infoArray = array();
        $uidArray = array();
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

                    foreach ($objConfs as $myConf) {
                        $tag = $myConf->nodeName;
                        if ($tag == 'Conf') {
                            $objConfDetails = $myConf->childNodes;
                            $xmlConf = array();

                            foreach ($objConfDetails as $rowDetail) {
                                if ($rowDetail->nodeType == XML_TEXT_NODE) {
                                    continue;
                                }
                                $detailValue = '';
                                $detailTag = $rowDetail->nodeName;

                                if ($rowDetail->hasChildNodes()) {
                                     foreach ( $rowDetail->childNodes as $child ) {
                                        if ($child->nodeType == XML_ELEMENT_NODE) {
                                            $detailName = trim($child->nodeName);
                                            $detailValue = trim($rowDetail->nodeValue);

                                            $xmlConf[$detailTag][$detailName] = $detailValue;
                                        }
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
                                $whereArray = array();
                                foreach ($xmlConf['Source'] as $field => $value) {
                                    $whereArray[] = $from_table . '.' . $field . '=' . intval($value);
                                }
                                $sourceWhere = $where_clause . implode(' AND ', $whereArray);
                                $sourceRow = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                                    '*',
                                    $from_table,
                                    $sourceWhere
                                );

                                if (
                                    $sourceRow !== false &&
                                    !empty($sourceRow)
                                ) {
                                    $fieldsArray = $sourceRow;
                                    unset($fieldsArray['uid']);
                                    $fieldVariantArray = array();

                                    foreach ($xmlConf['Destination'] as $field => $value) {
                                        $valueArray = GeneralUtility::trimExplode(',', $value);

                                        if (
                                            !empty($valueArray)
                                        ) {
                                            $index = 0;
                                            foreach ($valueArray as $internalValue) {

                                                if (strpos($internalValue, '-') > 0) {
                                                    $parts = GeneralUtility::trimExplode('-', $internalValue);
                                                    $fieldVariantArray[$field][$index]['start'] = $parts['0'];
                                                    $fieldVariantArray[$field][$index]['end'] = $parts['1'];
                                                } else {
                                                    $fieldVariantArray[$field][$index]['start'] = $internalValue;
                                                    $fieldVariantArray[$field][$index]['end'] = $internalValue;
                                                    $index++;
                                                }
                                            }
                                        }
                                    }
                                }

                                foreach ($fieldVariantArray as $variantField => $variantControlArray) {
                                    foreach ($variantControlArray as $variantControl) {
                                        if (
                                            isset($variantControl['start']) &&
                                            isset($variantControl['end'])
                                        ) {
                                            $start = intval($variantControl['start']);
                                            $end = intval($variantControl['end']);
                                            $insertFields = $fieldsArray;
                                            for ($i = $start; $i <= $end; ++$i) {
                                                $time = time();
                                                if (!empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'])) {
                                                    $time += ($GLOBALS['TYPO3_CONF_VARS']['SYS']['serverTimeZone'] * 3600);
                                                }

                                                $insertFields['crdate'] = $time;
                                                $insertFields['tstamp'] = $time;
                                                $insertFields[$variantField] = $i;

                                                $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                                                    $from_table,
                                                    $insertFields
                                                );
                                                $newId = $GLOBALS['TYPO3_DB']->sql_insert_id();

                                                $uidArray[] = $newId;
                                            }
                                        }
                                    }
                                }

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

