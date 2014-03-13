<?php

/**
 * Module
 *
 * PHP version 5.3
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect;

define('MODULEROOT', __DIR__);

/**
 * Module
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
final class Module
{

    /**
     * Class constructor
     *
     * @return Module
     */
    public function __construct()
    {
        $this->registerAutoloader();
        $this->registerModule();
        return $this;
    }

    private function registerModule()
    {
        global $moduleRegistry;
        if ( !isset($moduleRegistry) )
        {
            $moduleRegistry = array();
            $moduleRegistry[__CLASS__] = __DIR__;
        }
    }

    private function registerAutoloader()
    {
        spl_autoload_register(array($this, 'autoload'), true, true);
    }

    /**
     * Simple autoloader for Zend2-like module
     *
     * @param string $className
     */
    private function autoload($className)
    {
//        $dir = __DIR__;
        if ( stristr($className, 'Mageflow\\') )
        {

            $classPath = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace('\\',
                    DIRECTORY_SEPARATOR, $className) . '.php';
            $classPath = str_replace('src/Mageflow/Connect', 'src',  $classPath);
            include_once $classPath;
        }
    }

    /**
     * Return module's config as array
     *
     * @return array
     */
    public function getConfig()
    {
        return include dirname(__FILE__) . '/config/module.config.php';
    }

}
