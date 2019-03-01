<?php
defined('TYPO3_MODE') || die('Access denied.');

if (TYPO3_MODE == 'BE') {

    // Add context sensitive help (csh) to the backend module
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        MOVE_CSHKEY,
        'EXT:' . MOVE_EXT . '/Resources/Private/Language/locallang_csh_move.xlf'
    );
}
