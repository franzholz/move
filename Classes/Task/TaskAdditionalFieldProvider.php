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
 * Additional Field Provider for the Move Task
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage move
 */


class TaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{
    public function getAdditionalFields (
        array &$taskInfo,
        $task,
        \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject
    )
    {
        $fieldCode = '';
        $checkbox = array();
        $selectedTables = array();
        $result = array();
        $cmd = $parentObject->CMD;

        if (
            !isset($taskInfo) ||
            !isset($taskInfo[MOVE_EXT])
        ) {
            $taskInfo[MOVE_EXT] = array();
        }

        if (
            isset($task) &&
            empty($taskInfo[MOVE_EXT]) &&
            ($cmd == 'edit')
        ) {
            $composition = $task->getComposition();
            foreach ($composition as $field => $value) {
                $taskInfo[MOVE_EXT][$field] = $value;
            }
        }

        $field = 'configurationFile';
        $fieldValue = '';
        if (
            isset($taskInfo[MOVE_EXT][$field])
        ) {
            $fieldValue = $taskInfo[MOVE_EXT][$field];
        }

        $fieldID = MOVE_EXT . '_' . $field;
        $fieldCode = '<input type="text" name="tx_scheduler[' . MOVE_EXT . '][' . $field . ']" id="' . $fieldID . '" value="' . $fieldValue . '" size="80" />';

        $result[$fieldID] = array(
            'code'     => $fieldCode,
            'label'    => 'LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:' . $field,
            'cshKey'   => MOVE_CSHKEY,
            'cshLabel' => $field
        );

    // tables selector checkboxes
        $fieldCode = '';

        $hookArray = \JambageCom\Move\Api\HookApi::getHookArray();
        $field = 'tables';
        $fieldID = MOVE_EXT . '_' . $field;
        $mainFieldDefaultName = 'tx_scheduler[' . MOVE_EXT . ']';
        $tableFieldName = 'tx_scheduler[' . MOVE_EXT . '][' . $field . ']';

        $hookDefinitionArray = \JambageCom\Move\Api\HookApi::getHookDefinitionArray();

        $localDefinitionArray = \JambageCom\Move\Api\MoveApi::getLocalDefinitionArray();
        $definitionArray = array_merge($localDefinitionArray, $hookDefinitionArray);

        if (
            isset($taskInfo[MOVE_EXT][$field])
        ) {
            $selectedTables = $taskInfo[MOVE_EXT][$field];
        }

        foreach ($definitionArray as $definition) {
            if (
                !isset($definition['ext']) ||
                !isset($definition['tables']) ||
                !is_array($definition['tables'])
            ) {
                continue;
            }
            $foreignExtension = $definition['ext'];
            if (
                $foreignExtension != MOVE_EXT &&
                !\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($foreignExtension)
            ) {
                continue;
            }

            foreach ($definition['tables'] as $definitionArray) {
                if (
                    !isset($definitionArray['table']) ||
                    !isset($definitionArray['title'])
                ) {
                    continue;
                }
                $theTable = $definitionArray['table'];
                $checked = '';
                if (in_array($theTable, $selectedTables)) {
                    $checked = ' checked ';
                }

                $checkbox[] = '<input type="checkbox" name="' . $tableFieldName . '[]" value="' . htmlspecialchars($theTable) . '"' . $checked . '> ' . htmlspecialchars($definitionArray['title']) . ' </label>';
            }
        }

        $fieldCode .= $label;
        $innerHtml = '';

        if (!empty($checkbox)) {
            foreach ($checkbox as $line) {
                $innerHtml .= '<li>' . $line . '</li>';
            }
        }

        $fieldCode .= '<fieldset><ul>' . $innerHtml . '</ul></fieldset>';

        $result[$fieldID] = array(
            'code'     => $fieldCode,
            'label'    => 'LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:moveTables', // Todo: use the $foreignExtension label here
            'cshKey'   => MOVE_CSHKEY,
            'cshLabel' => $field
        );

        $fieldCode = '';
        $field = 'options';
        $fieldID = MOVE_EXT . '_' . $field;
        $radioFieldName = 'tx_scheduler[' . MOVE_EXT . '][' . $field . ']';
        $radioBox = array();
        if (
            isset($taskInfo[MOVE_EXT][$field])
        ) {
            $selectedOptions = $taskInfo[MOVE_EXT][$field];
        }
        if (empty($selectedOptions)) {
            $selectedOptions = array('move');
        }

        $optionTypes = array(
            'move' => $this->getLanguageService()->sL(
                    'LLL:EXT:' . MOVE_EXT .
                    '/Resources/Private/Language/locallang.xlf:options_move'
                ),
            'copy' => $this->getLanguageService()->sL(
                    'LLL:EXT:' . MOVE_EXT .
                    '/Resources/Private/Language/locallang.xlf:options_copy'
                )
        );

        foreach ($optionTypes as $optionType => $optionText) {

            $checked = '';

            if (in_array($optionType, $selectedOptions)) {
                $checked = ' checked ';
            }
            $radioBox[] = '<input type="radio" name="' . $radioFieldName . '[]" value="' . htmlspecialchars($optionType) . '"' . $checked . '> ' . htmlspecialchars($optionText) . ' </label>';
        }

        $innerHtml = '';
        if (!empty($radioBox)) {
            foreach ($radioBox as $line) {
                $innerHtml .= '<li>' . $line . '</li>';
            }
        }

        $fieldCode .= '<fieldset><ul>' . $innerHtml . '</ul></fieldset>';

        $result[$fieldID] = array(
            'code'     => $fieldCode,
            'label'    => 'LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:options',
            'cshKey'   => MOVE_CSHKEY,
            'cshLabel' => $field
        );
        return $result;
    }

    public function validateAdditionalFields (
        array &$submittedData,
        \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject
    )
    {
        $result = true;
        if (
            !isset($submittedData[MOVE_EXT]) &&
            !is_array($submittedData[MOVE_EXT])
        ) {
            $parentObject->addMessage(
                $this->getLanguageService()->sL(
                    'LLL:EXT:' . MOVE_EXT .
                    '/Resources/Private/Language/locallang.xlf:error_internal'
                ),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $result = false;
        } else {
            if (empty($submittedData[MOVE_EXT]['configurationFile'])) {
                $parentObject->addMessage(
                    $this->getLanguageService()->sL(
                        'LLL:EXT:' . MOVE_EXT .
                        '/Resources/Private/Language/locallang.xlf:error_empty_configurationFile'
                    ),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                );
                $result = false;
            } else if (empty($submittedData[MOVE_EXT]['tables'])) {
                $parentObject->addMessage(
                    $this->getLanguageService()->sL(
                        'LLL:EXT:' . MOVE_EXT .
                        '/Resources/Private/Language/locallang.xlf:error_empty_tables'
                    ),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                );
                $result = false;
            }
        }

        return $result;

    }

    public function saveAdditionalFields (
        array $submittedData,
        \TYPO3\CMS\Scheduler\Task\AbstractTask $task
    )
    {
        if (
            isset($submittedData[MOVE_EXT]) &&
            is_array($submittedData[MOVE_EXT])
        ) {
            if (!empty($submittedData[MOVE_EXT]['tables'])) {
                $task->tables = $submittedData[MOVE_EXT]['tables'];
            }
            if (!empty($submittedData[MOVE_EXT]['configurationFile'])) {
                $task->configurationFile = $submittedData[MOVE_EXT]['configurationFile'];
            }

            if (!empty($submittedData[MOVE_EXT]['options'])) {
                $task->options = $submittedData[MOVE_EXT]['options'];
            }

        }
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
