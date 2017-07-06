<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    // call very first TYPO3 hook for redirecting requests
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] = function($parameters, $parent) {
        $preProcess = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\StefanFroemken\UrlRedirect\Hooks\PreProcess::class);
        $preProcess->redirect($parameters, $parent);
    };
});
