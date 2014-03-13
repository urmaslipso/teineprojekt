<?php
/**
 *
 * Handler.php
 *
 * @author  sven
 * @created 02/26/2014 14:25
 */

class Mageflow_Connect_Helper_Handler_Cmspage
    extends Mageflow_Connect_Helper_Data
{
    /**
     * update or create cms/page from data array
     *
     * @param $filteredData
     *
     * @return array
     */
    public function handle($filteredData)
    {
        $itemFoundByIdentifier = false;
        $itemFoundByMfGuid = false;
        $foundItemsMatch = false;
        $itemModel = null;
        $originalData = array();

        $itemModelByIdentifier = Mage::getModel('cms/page')
            ->load($filteredData['identifier'], 'identifier');
        $itemModelByMfGuid = Mage::getModel('cms/page')
            ->load($filteredData['mf_guid'], 'mf_guid');

        if ($itemModelByIdentifier->getPageId()) {
            $itemFoundByIdentifier = true;
        }
        if ($itemModelByMfGuid->getPageId()) {
            $itemFoundByMfGuid = true;
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid) {
            $idByIdent = $itemModelByIdentifier->getPageId();
            $idByGuid = $itemModelByMfGuid->getPageId();

            Mage::helper('mageflow_connect/log')->log(
                'by mf_guid ' . $idByGuid
            );
            Mage::helper('mageflow_connect/log')->log('by ident ' . $idByIdent);

            if ($idByGuid == $idByIdent) {
                $foundItemsMatch = true;
            }
        }

        if ($itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 01');
            $itemModel = $itemModelByIdentifier;
            $filteredData['page_id'] = $itemModel->getPageId();
        }
        if (!$itemFoundByIdentifier && $itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 10');
            $itemModel = $itemModelByMfGuid;
            $filteredData['page_id'] = $itemModel->getPageId();
        }
        if (!$itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 00');
            $itemModel = Mage::getModel('cms/page');
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && $foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-1');
            $itemModel = $itemModelByMfGuid;
            $filteredData['page_id'] = $itemModel->getPageId();
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && !$foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-0');
            $itemModel = $itemModelByMfGuid;
            $filteredData['page_id'] = $itemModel->getPageId();
        }

        $originalData = null;
        if (!is_null($itemModel)) {
            $originalData = $itemModel->getData();
        }

        if (isset($filteredData['stores'])) {
            foreach ($filteredData['stores'] as $key => $storeCode) {
                if ($storeCode != "0") {
                    $storeEntity = Mage::getModel('core/store')
                        ->load($storeCode, 'code');
                    $filteredData['stores'][$key] = $storeEntity->getId();
                }
            }
        } else {
            $storeEntity = Mage::getModel('core/store')
                ->load('default', 'code');
            $filteredData['stores'][0] = $storeEntity->getId();
        }

        $savedEntity = $this->saveItem($itemModel, $filteredData);
        if ($savedEntity instanceof Mage_Cms_Model_Page) {
            return array(
                'entity'        => $savedEntity,
                'original_data' => $originalData
            );
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save CMS page. Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;
    }
}