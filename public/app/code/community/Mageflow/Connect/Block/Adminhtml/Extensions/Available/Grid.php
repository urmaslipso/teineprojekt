<?php

/**
 * Grid
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Grid
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Block_Adminhtml_Extensions_Available_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $limit = (int)$this->getParam(
            $this->getVarNameLimit(),
            $this->_defaultLimit
        );
        $currentPage = (int)$this->getParam(
            $this->getVarNamePage(),
            $this->_defaultPage
        );
        $collection = Mage::getModel('mageflow_connect/client_magepit_api')
            ->getAvailablePackages();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    public function _afterLoadCollection()
    {
        parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('ID'),
                 'width'  => '50px',
                 'index'  => 'id',
                 'type'   => 'text',
            )
        );
        /* $this->addColumn('firstname', array(
          'header'    => Mage::helper('customer')->__('First Name'),
          'index'     => 'firstname'
          ));
          $this->addColumn('lastname', array(
          'header'    => Mage::helper('customer')->__('Last Name'),
          'index'     => 'lastname'
          )); */
        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Name'),
                 'index'  => 'name'
            )
        );
        $this->addColumn(
            'version',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Version'),
                 'width'  => '150',
                 'index'  => 'version'
            )
        );
        $this->addColumn(
            'channel',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Channel'),
                 'width'  => '150',
                 'index'  => 'channel'
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('mageflow_connect')->__('Action'),
                 'width'     => '150',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Install'
                         ),
                         'url'     => array('base' => '*/*/upgrade'),
                         'field'   => 'id'
                     ),
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Show details'
                         ),
                         'url'     => array('base' => '*/*/show'),
                         'field'   => 'id'
                     ),
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        $this->addExportType(
            '*/*/exportCsv',
            Mage::helper('customer')->__('CSV')
        );
        $this->addExportType(
            '*/*/exportXml',
            Mage::helper('customer')->__('Excel XML')
        );
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            array(
                 'id' => $row->getId()
            )
        );
    }

}
