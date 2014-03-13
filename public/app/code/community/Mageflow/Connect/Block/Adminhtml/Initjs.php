<?php

/**
 * @package    Mageflow
 * @subpackage Connect
 */

/**
 * AdminHtml InitJS block for MageFlow backend that loads custom JS
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
class Mageflow_Connect_Block_Adminhtml_Initjs
    extends Mage_Adminhtml_Block_Template
{

    /**
     * Include JS in the head if section is Mageflow
     */
    protected function _prepareLayout()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);
        $module = $this->getAction()->getRequest()->getModuleName();
        if ($section == 'mageflow_connect' || $module == 'mageflow_connect') {
            $this->getLayout()
                ->getBlock('head')
                ->addCss('mageflow/styles.css');

            $this->getLayout()
                ->getBlock('mageflow_js_container')
                ->addJs('mageflow/jquery.js')
                ->addJs('mageflow/noconflict.js')
                ->addJs('mageflow/core.js');
        }
        parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $section = $this->getAction()->getRequest()->getParam('section', false);
        if ($section == 'mageflow_connect') {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function getModuleVersion()
    {
        return (string)Mage::getConfig()->getNode(
        )->modules->Mageflow_Connect->version;
    }

}
