<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "move"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Move Task',
    'description' => 'This extension offers move tasks for the TYPO3 Scheduler which can be extended by other extensions. It is intended to modify the page ids of table records.',
    'category' => 'be',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'beta',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.2',
    'constraints' => array(
        'depends' => array(
            'php' => '5.6.0-7.99.99',
            'typo3' => '7.6.0-8.99.99',
            'div2007' => '1.6.12-0.0.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);

