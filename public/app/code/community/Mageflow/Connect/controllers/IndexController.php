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
class Mageflow_Connect_IndexController extends Mage_Adminhtml_Controller_Action
{

    /**
     * construct
     *
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
     * display migration
     *
     */
    public function migrateAction()
    {

        $this->loadLayout();
        $this->_setActiveMenu('mageflow/connect');
        $this->_addContent(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_migrate',
                'connect.extensions'
            )
        );
        $this->renderLayout();
    }

    /**
     * index action
     */
    public function indexAction()
    {

    }

    /**
     * show action
     */
    public function showAction()
    {
        $this->loadLayout();
//        echo "Package details";
        $this->renderLayout();
    }

}
