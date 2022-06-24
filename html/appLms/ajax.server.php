<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

const LMS = true;
const IN_FORMA = true;
const IS_AJAX = true;
const _deeppath_ = '../';
require __DIR__ . '/../base.php';

// start buffer
ob_start();

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_HOOKS);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if (!function_exists('aout')) {
    function aout($string)
    {
        $GLOBALS['operation_result'] .= $string;
    }
}
require_once _lms_ . '/lib/lib.permission.php';

$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

// load the correct module
$aj_file = '';
$mn = FormaLms\lib\Get::req('mn', DOTY_ALPHANUM, '');
$plf = FormaLms\lib\Get::req('plf', DOTY_ALPHANUM, ($session->has('current_action_platform') ? $session->get('current_action_platform') : FormaLms\lib\Get::cur_plat()));

$request = \FormaLms\lib\Get::req('r',DOTY_MIXED);
if (!empty($request)) {
    $GLOBALS['req'] = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $request);
}

if (!empty($GLOBALS['req'])) {
    $requesthandler = new RequestHandler($GLOBALS['req'], 'lms');
    $requesthandler->run(true);
} else {
    if ($mn == '') {
        $fl = FormaLms\lib\Get::req('file', DOTY_ALPHANUM, '');
        $sf = FormaLms\lib\Get::req('sf', DOTY_ALPHANUM, '');
        $aj_file = $GLOBALS['where_' . $plf] . '/lib/' . ($sf ? $sf . '/' : '') . 'ajax.' . $fl . '.php';
    } else {
        if ($plf == 'framework') {
            $aj_file = $GLOBALS['where_' . $plf] . '/modules/' . $mn . '/ajax.' . $mn . '.php';
        } else {
            $aj_file = $GLOBALS['where_' . $plf] . '/modules/' . $mn . '/ajax.' . $mn . '.php';
        }
    }
}
include $aj_file;

// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();
