<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// call very first TYPO3 hook for redirecting requests
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] = function($parameters, $parent) {
    $preProcess = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\StefanFroemken\UrlRedirect\Hooks\PreProcess::class);
    $preProcess->redirect($parameters, $parent);
};

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'StefanFroemken.url_redirect',
        'web',
        'tx_urirequest_mod1',
        '',
        ['Redirect' => 'list, new, create, edit, update, delete, doDelete, importForm, import'],
        [
            'access' => 'user,group',
            'icon' => 'EXT:url_redirect/Resources/Public/Icons/module_redirect.svg',
            'labels' => 'LLL:EXT:url_redirect/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}
