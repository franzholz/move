<?php
defined('TYPO3_MODE') || die('Access denied.');

define('MOVE_EXT', 'move');
define('MOVE_CSHKEY', '_MOD_system_txschedulerM1_' . MOVE_EXT); // key for the Context Sensitive Help

// Add the move task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['JambageCom\\Move\\Task\\Task'] = array(
    'extension' => MOVE_EXT,
    'title' => 'LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:moveTask.name',
    'description' => 'LLL:EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang.xlf:moveTask.description',
    'additionalFields' => 'JambageCom\\Move\\Task\\TaskAdditionalFieldProvider'
);

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (
    isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT]) &&
    is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT])
) {
    $tmpArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT];
} else if (isset($tmpArray)) {
    unset($tmpArray);
}

if (isset($_EXTCONF) && is_array($_EXTCONF)) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT] = $_EXTCONF;
    if (isset($tmpArray) && is_array($tmpArray)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT] =
            array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT], $tmpArray);
    }
} else if (!isset($tmpArray)) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][MOVE_EXT] = array();
}

