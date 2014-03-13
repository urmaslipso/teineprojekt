<?php
/**
 * @package    Mageflow
 * @subpackage Connect
 */

/**
 * Mageflow_Connect_Block_Adminhtml_Page_Head is a wrapper between
 * normal Mage_Page_Block_Html_Head. It's main task is to avoid errors
 * when we have no scripts to load (elsewhere than under MageFlow pages)crea
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
 * @package    Mageflow
 * @subpackage Connect
 * @category   Mageflow
 */

class Mageflow_Connect_Block_Adminhtml_Page_Head
    extends Mage_Page_Block_Html_Head
{
    /**
     * Overload of mage class to avoid errors when we
     * don't need to load any MageFlow scripts
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);
        $module = $this->getAction()->getRequest()->getModuleName();
        if ($section == 'mageflow_connect' || $module == 'mageflow_connect') {
            return parent::getCssJsHtml();
        }
    }
}