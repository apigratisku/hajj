<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

// Set timezone to GMT+8 (Singapore/Malaysia/Philippines/Hong Kong/Western Australia)
$hook['pre_system'] = array(
    'class'    => '',
    'function' => 'set_timezone',
    'filename' => 'timezone_hook.php',
    'filepath' => 'hooks',
    'params'   => array()
);
