<?php

/**
 * Migrate.php
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/licenses/mfx/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Mageflow_Connect_Block_Adminhtml_Migrate is used to display
 * migration grid
 *
 * PLEASE READ THIS SOFTWARE LICENSE AGREEMENT ("LICENSE") CAREFULLY
 * BEFORE USING THE SOFTWARE. BY USING THE SOFTWARE, YOU ARE AGREEING
 * TO BE BOUND BY THE TERMS OF THIS LICENSE.
 * IF YOU DO NOT AGREE TO THE TERMS OF THIS LICENSE, DO NOT USE THE SOFTWARE.
 *
 * Full text of this license is available @license
 *
 * @license    http://mageflow.com/licenses/mfx/eula.txt MageFlow EULA
 * @version    1.0
 * @author     MageFlow
 * @copyright  2014 MageFlow http://mageflow.com/
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 */
class Mageflow_Connect_Block_Adminhtml_Migrate
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{


    public function __construct()
    {
        $this->_controller = 'adminhtml_migrate';
        $this->_blockGroup = 'mageflow_connect';
        $this->_headerText = Mage::helper('mageflow_connect')->__(
            'Migrate changes'
        );
        //$this->_addButtonLabel = Mage::helper('mageflow_connect')
        //->__('Push ChangeSets to MageFlow');
        parent::__construct();
    }
}
