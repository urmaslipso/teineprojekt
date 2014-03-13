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
 * Mageflow_Connect_Model_Api2_System_Rewrite_Rest_Admin_V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_Api2_System_Rewrite_Rest_Admin_V1
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
        $out = array();
        $this->log($this->getRequest()->getParams());
        $classInfoNodeList = Mage::getConfig()->getNode()->xpath(
            '//global//rewrite/..'
        );
        $outItems = array();

        foreach ($classInfoNodeList as $classInfoNode) {
//            $this->log($classInfoNode->rewrite);
            $rewrite = $classInfoNode->xpath('rewrite');
            $classSuffix = '';
            $outItem = array();
            if (is_array($rewrite) && sizeof($rewrite) > 0) {
                $keys = array_keys($rewrite[0]->asArray());
                $classSuffix = $keys[0];
                $rewriteClass = (string)$classInfoNode->rewrite->$classSuffix;
//                $this->log($rewriteClass);
                $className = $classInfoNode->class . '_' . uc_words(
                    $classSuffix,
                    '_'
                );
                $outItem = array(
                    'original' => $className,
                    'rewriter' => $rewriteClass
                );
                $outItems[] = $outItem;
            }
        }
        $this->log($outItems);
        return $outItems;
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