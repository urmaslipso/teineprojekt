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
//namespace Application;

/**
 * Renderer
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/licenses/mageflow.txt
 *
 */
class Mageflow_Connect_Block_Adminhtml_Migrate_Grid_Column_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $output = 'Preview N/A';
        if ($row->getType()) {
            switch ($row->getType()) {
                case Mageflow_Connect_Model_Changeset_Item::TYPE_CMS_BLOCK:
//                    Mage::helper('mageflow_connect/log')->log($row);
                    $content = json_decode($row->getContent());
                    if ($content->title) {
                        $output = $content->title;
                    }
                    break;
                case Mageflow_Connect_Model_Changeset_Item::TYPE_CMS_PAGE:
                    $content = json_decode($row->getContent());
                    if ($content->title) {
                        $output = $content->title;
                    }
                    break;
                case
                Mageflow_Connect_Model_Changeset_Item::TYPE_SYSTEM_CONFIGURATION:
                    $content = json_decode($row->getContent());
                    $out = '';
                    if (isset($content->path)) {
                        $out = sprintf(
                            '%s=%s',
                            $content->path,
                            $content->value
                        );
                    }
                    $output = $out;
                    break;
                case
                Mageflow_Connect_Model_Changeset_Item::TYPE_CATALOG_CATEGORY:
                    $content = json_decode($row->getContent());
                    if ($content->name) {
                        $output = $content->name;
                    }
                    break;
                case
                Mageflow_Connect_Model_Changeset_Item::TYPE_CATALOG_ATTRIBUTE:
                    $content = json_decode($row->getContent());
                    if ($content->attribute_code) {
                        $output = $content->attribute_code;
                    }
                    break;
                case
                Mageflow_Connect_Model_Changeset_Item::TYPE_CATALOG_ATTRIBUTESET:
                    $content = json_decode($row->getContent());
                    if ($content->attribute_set_name) {
                        $output = $content->attribute_set_name;
                    }
                    break;
                case Mageflow_Connect_Model_Changeset_Item::TYPE_CORE_WEBSITE:
                    $content = json_decode($row->getContent());
                    if ($content->name) {
                        $output = $content->name;
                    }
                    break;
                case Mageflow_Connect_Model_Changeset_Item::TYPE_ADMIN_USER:
                    $content = json_decode($row->getContent());
                    if ($content->username) {
                        $output = $content->username;
                    }
                    break;
            }
        }
        if (strlen($output) > 100) {
            $output = substr($output, 0, 100) . '...';
        }
        return $output;
    }

}
