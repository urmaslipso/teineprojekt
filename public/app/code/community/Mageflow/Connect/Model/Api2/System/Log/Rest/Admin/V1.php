<?php

/**
 * V1
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */


/**
 * Mageflow_Connect_Model_Api2_System_Log_Rest_Admin_V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_Api2_System_Log_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'system_configuration';

    /**
     * Class constructor
     *
     * @return V1
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns array with system info
     *
     * @return array
     */
    public function _retrieve()
    {
        $out = array(
            'log'       => array(),
            'exception' => array()
        );
        $this->log($this->getRequest()->getParams());

        try {
            $maxLines = Mage::getStoreConfig(
                'mageflow_connect/advanced/log_lines'
            );
            //failsafe is 100 lines
            if (!$maxLines) {
                $maxLines = 100;
            }

            $file = Mage::getStoreConfig('dev/log/file');
            $exceptionFile = Mage::getStoreConfig('dev/log/exception_file');
            $logDir = Mage::getBaseDir('var') . DS . 'log';

            $logFilePath = $logDir . DS . $file;
            $exceptionFilePath = $logDir . DS . $exceptionFile;
            $logTypes = array(
                'log'       => $logFilePath,
                'exception' => $exceptionFilePath
            );
            //safety output
            $out['log'] = $logFilePath;
            $out['exception'] = $exceptionFilePath;

            foreach ($logTypes as $logType => $path) {
                $cmd = sprintf('wc -l %s', $path);
                $numLines = shell_exec($cmd);
                $cmd = sprintf('tail -%s %s', $maxLines, $path);
                $logStr = shell_exec($cmd);
                $lastLines = explode(PHP_EOL, $logStr);
                $logLines = array_combine(
                    range($numLines - $maxLines, $numLines),
                    $lastLines
                );
                $out[$logType] = $logLines;
            }

        } catch (Exception $e) {
            $this->log($e->getMessage());
        }

        return $out;
    }

    public function _retrieveCollection()
    {
        return $this->_retrieve();
    }

    public function _update(array $filteredData)
    {
        $this->log(__METHOD__);
        $this->log($filteredData);
        $this->getResponse()->addMessage(
            'status',
            self::STATUS_SUCCESS,
            array(),
            Mage_Api2_Model_Response::MESSAGE_TYPE_SUCCESS
        );
    }

    public function _multiUpdate(array $filteredData)
    {
        $this->log(__METHOD__);
        foreach ($filteredData as $data) {
            $this->_update($data);
        }
    }

    public function _create(array $filteredData)
    {
        $this->log(__METHOD__);
        $this->log($filteredData);
        $this->getResponse()->addMessage(
            'status',
            self::STATUS_SUCCESS,
            array(),
            Mage_Api2_Model_Response::MESSAGE_TYPE_SUCCESS
        );
    }

    public function _multiCreate(array $filteredData)
    {
        $this->log(__METHOD__);
        foreach ($filteredData as $data) {
            $this->_create($data);
        }
    }

}