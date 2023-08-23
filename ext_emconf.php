<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Qc Workspace Preview Language',
    'description' => 'This extension allow you to set the default key language for the workspace language',
    'category' => 'be',
    'author' => 'Quebec.ca',
    'author_company' => 'Québec',
    'state' => 'beta',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.1.1',
    'constraints' => [
        'depends' => [
            'php' => '7.4-8.2',
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Qc\\QcWsPreviewLang\\' => 'Classes/',
        ],
    ],
];
