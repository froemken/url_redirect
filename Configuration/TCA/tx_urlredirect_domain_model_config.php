<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:url_redirect/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_urlredirect_config',
        'label' => 'request_uri',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'rootLevel' => 1,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,use_reg_exp,domain,complete_domain,request_uri,target_uri,http_status'
    ],
    'columns' => [
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'use_reg_exp' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.use_reg_exp',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'domain' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.domain',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'complete_domain' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.complete_domain',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'request_uri' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.request_uri',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'target_uri' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.target_uri',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'http_status' => [
            'exclude' => 1,
            'label' => $ll . 'tx_urlredirect_config.http_status',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'defaults' => 301,
                'eval' => 'int,required'
            ]
        ],
    ],
    'types' => [
        1 => [
            'showitem' => 'use_reg_exp, domain, complete_domain, request_uri, target_uri, http_status'
        ]
    ],
];
