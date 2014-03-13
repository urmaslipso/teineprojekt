<?php

/**
 * IndexController
 *
 * PHP version 5
 *
 * @category Mageflow
 * @package  Mageflow_Connect
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/ MageFlow Commercial Software License
 * @link     http://mageflow.com/
 */

/**
 * IndexController
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Controller
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_SoftwareController
    extends Mageflow_Connect_Controller_AbstractController
{

    /**
     * construct
     */
    public function _construct()
    {
        ini_set(
            'include_path',
            get_include_path() . PATH_SEPARATOR . Mage::getBaseDir() . DS
            . 'downloader'
        );

        parent::_construct();
    }

    /**
     * index
     */
    public function indexAction()
    {

        $this->loadLayout();
        $this->_setActiveMenu('mageflow/connect');
        $this->_addContent(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_extensions_installed',
                'installed.extensions.grid'
            )
        );
        $this->renderLayout();
    }

    /**
     * grid action
     */
    public function gridAction()
    {
        return $this->availableAction();
    }

    /**
     * This action displays list of available software
     */
    public function availableAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('mageflow/connect');
        $this->_addContent(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_extensions_available',
                'available.extensions.grid'
            )
        );
        $this->renderLayout();
    }

}
