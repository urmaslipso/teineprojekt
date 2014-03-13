<?php

/**
 * Logger
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect\Helper;

/**
 * Logger
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class Logger
{

    /**
     * Logs debug level messages
     *
     * @param type $message
     * @param type $method
     * @param type $line
     */
    public static function debug($message, $method = null, $line = null)
    {

        if ( function_exists('debug_backtrace') )
        {
            $backtrace = debug_backtrace();
            $method = $backtrace[1]['class'] . '::' . $backtrace[1]['function'];
            $line = $backtrace[0]['line'];
        }

        if ( is_null($method) ) $method = __METHOD__;
        if ( is_null($line) ) $line = __LINE__;

        self::writelog($message, $method, $line, \Zend_Log::DEBUG);
    }

    /**
     *
     * @param type $message
     * @param type $method
     * @param type $line
     * @param type $level
     */
    private static function writelog($message, $method, $line, $level)
    {
        $apiLog = \Mage::getBaseDir('var') . '/log/api.log';
        $systemLog = \Mage::getBaseDir('var') . '/log/system.log';

        if (@touch($apiLog)) {
            $moduleWriter = new \Zend_Log_Writer_Stream($apiLog);
            $logger1 = new \Zend_Log($moduleWriter);
            $logger1->log(sprintf('%s(%s): %s', $method, $line,
                    print_r($message, true)), $level);
        }

        if (@touch($systemLog)) {
            $globalWriter = new \Zend_Log_Writer_Stream($systemLog);
            $logger2 = new \Zend_Log($globalWriter);
            $logger2->log(sprintf('%s(%s): %s', $method, $line,
                    print_r($message, true)), $level);
        }
    }

    /**
     * Log error level messages
     *
     * @param mixed $message
     */
    public static function error($message)
    {
        if ( function_exists('debug_backtrace') )
        {
            $backtrace = debug_backtrace();
            $method = $backtrace[1]['class'] . '::' . $backtrace[1]['function'];
            $line = $backtrace[0]['line'];
        }

        if ( is_null($method) ) $method = __METHOD__;
        if ( is_null($line) ) $line = __LINE__;
        self::writelog($message, $method, $line, \Zend_Log::ERR);
    }

}