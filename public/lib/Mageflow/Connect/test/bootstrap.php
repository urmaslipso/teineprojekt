<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Sven Varkel <sven@mageflow.com>
 */
// TODO: check include path
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'/opt/local/lib/php54/pear');

include_once __DIR__ .'/../Module.php';
$m = new \Connect\Module();
include_once '/opt/local/lib/php54/pear/PHPUnit/Autoload.php';
