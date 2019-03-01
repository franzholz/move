<?php

namespace JambageCom\Move\Task;

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
 * Move Task
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage move
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Task extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    /**
    * Array of tables to move
    *
    * @var array $tables
    */
    public $tables = array();

    /**
    * XML Configuration file
    *
    * @var $configurationFile
    */
    public $configurationFile = '';

    /**
    * Option checkbox
    * If set then the original record will be copied and not modified
    *
    * @var $options
    */
    public $options = array();


    public function __construct()
    {
        parent::__construct();
        // Your code here...
    }

    public function getComposition ()
    {
        $result = array(
            'tables' => $this->tables,
            'configurationFile' => $this->configurationFile,
            'options' => $this->options
        );

        return $result;
    }

    public function execute ()
    {

        $result = false;
        $hookDefinitionArray = \JambageCom\Move\Api\HookApi::getHookDefinitionArray();

        if (
            isset($this->tables) &&
            is_array($this->tables) &&
            !empty($this->tables) &&
            !empty($this->configurationFile)
        ) {
            $result = true;
            $localDefinitionArray = \JambageCom\Move\Api\MoveApi::getLocalDefinitionArray();
            $definitionArray = array_merge($localDefinitionArray, $hookDefinitionArray);

            foreach ($definitionArray as $definition) {
                if (
                    !isset($definition['tables']) ||
                    !is_array($definition['tables'])
                ) {
                    continue;
                }

                if (isset($definition['ext'])) {

                    $foreignExtension = $definition['ext'];
                    if (
                        $foreignExtension != MOVE_EXT &&
                        !\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded(
                            $foreignExtension
                        )
                    ) {
                        $result = false;
                        break;
                    }
                }

                $extensionTables = array();
                foreach ($definition['tables'] as $definition) {
                    $extensionTables[] = $definition['table'];
                }
                $theTableArray = array();

                foreach ($this->tables as $table) {
                    if (in_array($table, $extensionTables)) {
                        $theTableArray[] = $table;
                    }
                }

                if (!empty($theTableArray)) {
                    if (isset($definition['class'])) {

                        $foreignClass = $definition['class'];
                        if (
                            class_exists($foreignClass) &&
                            method_exists($foreignClass, 'execute')
                        ) {
                            $result = call_user_func(
                                $foreignClass . '::execute',
                                $theTableArray,
                                $this
                            );
                        } else {
                            $result = false;
                            break;
                        }
                    } else if (
                        isset($this->options['0']) &&
                        !empty($this->options['0'])
                    ) {
                        switch ($this->options['0']) {
                            case 'move':
                                \JambageCom\Move\Api\MoveApi::execute(
                                    $this->configurationFile,
                                    $theTableArray
                                );
                                break;
                            case 'copy':
                                $resultCopy = \JambageCom\Move\Api\CopyApi::execute(
                                    $this->configurationFile,
                                    $theTableArray
                                );
                                $message = '';
                                if (is_array($resultCopy)) {
                                    $message = sprintf(
                                    $this->getLanguageService()->sl('LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:info_copied'), $table, implode(',', $resultCopy[$table]));
                                    $result = true;
                                }

                                if (
                                    $message != '' &&
                                    version_compare(TYPO3_version, '8.7.0', '>=')
                                ) {
                                    $logger = $this->getLogger();
                                    $logger->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, $message);
                                } else {
                                    GeneralUtility::sysLog($message, MOVE_EXT, GeneralUtility::SYSLOG_SEVERITY_NOTICE);
                                }
                                break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * This method is designed to return some additional information about the task,
     * that may help to set it apart from other tasks from the same class
     * This additional information is used - for example - in the Scheduler's BE module
     * This method should be implemented in most task classes
     *
     * @return    string    Information to display
     */
    public function getAdditionalInformation()
    {
        $result = implode (',', $this->tables);
        $result .= ': ' . implode (',', $this->options);
        return $result;
    }

    /**
     * Returns the Language Service
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}

