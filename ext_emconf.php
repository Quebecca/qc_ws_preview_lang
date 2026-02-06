<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Qc Workspace Preview Language',
    'description' => 'This extension allow you to set the default key language for the workspace language',
    'category' => 'be',
    'author' => 'Quebec.ca',
    'author_company' => 'Québec',
    'state' => 'beta',
    'version' => '2.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.9.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Qc\\QcWsPreviewLang\\' => 'Classes/',
        ],
    ],
];
