<?php

/**
 * Renderer
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/licenses/mageflow.txt
 *
 */

/**
 * Renderer
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/licenses/mageflow.txt
 *
 */
class Mageflow_Connect_Block_Adminhtml_Pullgrid_Grid_Column_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        return $row->getContent();
    }

}
