<?php

/**
 * Runner
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect;

use Mageflow\Connect\Model\Api\Esc\Client;

/**
 * Runner
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class Runner
{

    public function __construct()
    {
        /**
         * Init module
         */
        include_once __DIR__ . '/../Module.php';
        $m = new Module();
    }

    private $longoptions = array(
        'list-packages',
        'install',
        'uninstall',
        'upgrade',
        'search',
        'create-package'
    );

    public function run($args)
    {
        print_r($args);
        $client = new Client();
        return $client->listPackages();
//        echo "\n";
    }

}
