<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Jet\Plugin;

use Magento\Framework\Exception\LocalizedException;

class SaveJetAttributeMapping
{
    /**
     * Request Variable
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Object Manager interface
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * SaveJetAttributeMapping constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\Entity\Attribute $dataObject
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\Entity\Attribute $dataObject,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->dataObj = $dataObject;
        $this->messageManager = $messageManager;
    }

    /**
     * Function call after attribute save action
     * @return void
     */
    public function afterafterSave()
    {
        $mediaDirectory =$this->objectManager->get(
            '\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $path = $mediaDirectory->getAbsolutePath('code/Ced/Jet/Setup/jetcsv/');
        
        $postData = $this->request->getParams();
        $jetAttrId = isset($postData['validate']) ? $postData['validate'] : "";
        $attrId = isset($postData['attribute_id']) ? $postData['attribute_id'] : "";
        $checkAttrId = $this->objectManager->create('Ced\Jet\Model\Jetattributes')->load($attrId, 'magento_attribute_id');
        $attrCode = $this->objectManager->get('Magento\Eav\Model\Entity\Attribute')->load($attrId)->getAttributeCode();
        $mapAttr = empty($jetAttrId) ? false : true;
        if ($mapAttr) {
            $unitsOrOptions = [];
            $csvhandler=$this->objectManager->create('Ced\Jet\Model\CsvHandler');
            $file = $path . "Jet_Taxonomy_attribute.csv";
            if (!file_exists($file)) {
                $this->messageManager->addError('Jet Extension Csv missing please check "Jet_Taxonomy_attribute.csv"');
                return;
            }
            $files['tmp_name'] = $file;
            $taxonomy = $csvhandler->readFromCsvFile($files);
            unset($taxonomy[0]);
            $jetAttributeId = false;
            $saveAttrUnitType = false;
            foreach ($taxonomy as $txt) {
                if (number_format($txt[0], 0, '', '') == $jetAttrId) {
                    $jetAttributeId = number_format($txt[0], 0, '', '');
                    $saveAttrUnitType = $txt[3];
                    break;
                }
            }
            if (!$jetAttributeId) {
                $this->messageManager->addError('Requested Jet Attribute id:  ' . $jetAttrId . ' does not exist in CSV.');
                return;
            }
            $file = $path."Jet_Taxonomy_attribute_value.csv";
            if (!file_exists($file)) {
                $this->messageManager->addError('Jet Extension Csv missing please check "Jet_Taxonomy_attribute_value.csv"');
                return;
            }
            $files['tmp_name'] = $file;
            $taxonomy = $csvhandler->readFromCsvFile($files);
            unset($taxonomy[0]);
            try {
                if ($saveAttrUnitType == 2) {
                    foreach ($taxonomy as $txt) {
                        $noFormatId = number_format($txt[0], 0, '', '');
                        if ($jetAttributeId == $noFormatId) {
                            $unitsOrOptions[] = $txt[2];
                        }
                    }
                    $unit = !empty($unitsOrOptions) ? $unitsOrOptions : "";
                    $freeText = 2;
                    $this->saveData($jetAttributeId, $attrId, $attrCode, $unit, $listOption = NUll, $freeText);
                } else if ($saveAttrUnitType == 0) {
                    foreach ($taxonomy as $txt) {
                        if ($jetAttributeId == number_format($txt[0], 0, '', '')) {
                            $unitsOrOptions[] = $txt[1];
                        }
                    }
                    $listOption = !empty($unitsOrOptions) ? $unitsOrOptions : "";
                    $freeText = 0;
                    $this->saveData($jetAttributeId, $attrId, $attrCode, $unit = NULL, $listOption, $freeText);
                } else {
                    $freeText = 1;
                    $this->saveData($jetAttributeId, $attrId, $attrCode, $unit = NULL, $listOption = NUll, $freeText);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else if ($checkAttrId->getId()) {
            $checkAttrId->delete();
        }
    }

    /**
     * Save Mapping Data
     * @param string $jetAttributeId
     * @param integer $attrId
     * @param string $attrCode
     * @param null $unit
     * @param null $listOption
     * @param integer $freeText
     * @return void
     */
    public function saveData($jetAttributeId, $attrId, $attrCode, $unit=NULL, $listOption=NUll, $freeText)
    {
        $jetAttrModel = $this->objectManager->create('Ced\Jet\Model\Jetattributes');
        $row = $jetAttrModel->load($attrCode, 'magento_attribute_code');
        if (!empty($unit) && empty($listOption)) {
            $allunits = addslashes(implode(',', $unit));
            if ($row) {
                $row->setJetAttributeId($jetAttributeId);
                $row->setMagentoAttributeId($attrId);
                $row->setMagentoAttributeCode($attrCode);
                $row->setUnit($allunits);
                $row->setListOption($listOption);
                $row->setFreeText($freeText);
                $row->save();
            } else {
                $jetAttrModel->setJetAttributeId($jetAttributeId);
                $jetAttrModel->setMagentoAttributeId($attrId);
                $jetAttrModel->setMagentoAttributeCode($attrCode);
                $jetAttrModel->setUnit($allunits);
                $jetAttrModel->setListOption($listOption);
                $jetAttrModel->setFreeText($freeText);
                $jetAttrModel->save();
            }
            $message = $jetAttributeId . ' is a "UNIT" type attribute in Jet.com so you need to add options values for these units: ' . $allunits . ' Please view details in "Jet Attribute" tab inside of "Attribute Information"';
            $this->messageManager->addNotice($message);
        } else if (!empty($listOption) && empty($unit)) {
            $alloptions = implode(',', $listOption);
            if ($row) {
                $row->setJetAttributeId($jetAttributeId);
                $row->setMagentoAttributeId($attrId);
                $row->setMagentoAttributeCode($attrCode);
                $row->setUnit($unit);
                $row->setListOption($alloptions);
                $row->setFreeText($freeText);
                $row->save();
            } else {
                $jetAttrModel->setJetAttributeId($jetAttributeId);
                $jetAttrModel->setMagentoAttributeId($attrId);
                $jetAttrModel->setMagentoAttributeCode($attrCode);
                $jetAttrModel->setUnit($unit);
                $jetAttrModel->setListOption($alloptions);
                $jetAttrModel->setFreeText($freeText);
                $jetAttrModel->save();
            }
            $message = $jetAttributeId . ' is a "List" type attribute in Jet.com so you need to add these options values : ' . $alloptions . ' Please view details in "Jet Attribute" tab inside of "Attribute Information"';
            $this->messageManager->addNotice($message);
        } else {
            if ($row) {
                $row->setJetAttributeId($jetAttributeId);
                $row->setMagentoAttributeId($attrId);
                $row->setMagentoAttributeCode($attrCode);
                $row->setUnit($unit);
                $row->setListOption($listOption);
                $row->setFreeText($freeText);
                $row->save();
            } else {
                $jetAttrModel->setJetAttributeId($jetAttributeId);
                $jetAttrModel->setMagentoAttributeId($attrId);
                $jetAttrModel->setMagentoAttributeCode($attrCode);
                $jetAttrModel->setUnit($unit);
                $jetAttrModel->setListOption($listOption);
                $jetAttrModel->setFreeText($freeText);
                $jetAttrModel->save();
            }
        }
    }
}
