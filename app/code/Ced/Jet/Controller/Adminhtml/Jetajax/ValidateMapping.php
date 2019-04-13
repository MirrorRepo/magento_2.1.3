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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Jet\Controller\Adminhtml\Jetajax;

class ValidateMapping extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * CSV Processor
     * @var \Magento\Framework\File\Csv
     */
    public $csvProcessor;

    /**
     * CSV Processor
     * @var \Magento\Framework\File\Csv
     */
    public $resultJsonFactory;

    /**
     * Directory List
     * @var  \Magento\Framework\Filesystem\DirectoryList
     */
    public $_dir;

    /**
     * ValidateMapping constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->csvProcessor = $csvProcessor;
        $this->_dir = $dir;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Validate Mapping of attributes
     * @return $this|array
     */
    public function execute()
    {
        $jAttribute = $this->getRequest()->getPost('jet_id');
        $resultJson = $this->resultJsonFactory->create();
        $mediaDirectory =$this->_objectManager->get('\Magento\Framework\Filesystem')
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $path = $mediaDirectory->getAbsolutePath('code/Ced/Jet/Setup/jetcsv/');
        $file = $path. "Jet_Taxonomy_attribute.csv";

        if (!file_exists($file)) {
            $response = [
                'error' => 'true',
                'message' => '<div class="hor-scroll" id="map-message" style="color:red" >Jet Extension Csv missing please check "Jet_Taxonomy_attribute.csv" exist at "var/jetcsv" location.</div>'
            ];
            return $resultJson->setData($response);
        }

        $importAttrRawData = $this->csvProcessor->getData($file);
        unset($importAttrRawData[0]);
        $saveAttrId = false;
        $saveAttrUnitType = false;
        foreach ($importAttrRawData as $rowIndex ) {
            if (number_format((float)$rowIndex[0], 0, '', '') == $jAttribute) {
                $saveAttrId = number_format((float)$rowIndex[0], 0, '', '');
                $saveAttrUnitType = $rowIndex[6];
                break;
            }
        }

        if ($saveAttrId == false) {
            $response = [
                'error' => 'true',
                'message' => '<div class="hor-scroll" id="map-message" style="color:red" >Requested Jet Attribute id:  ' . $jAttribute . ' does not exist in Jet CSV.</div>'
            ];
            return $resultJson->setData($response);
        }

        $file = $path . "Jet_Taxonomy_attribute_value.csv";
        if (!file_exists($file)) {
            $response = [
                'error' => 'true',
                'message' => '<div class="hor-scroll" id="map-message" style="color:red" >Jet Extension Csv missing please check "Jet_Taxonomy_attribute_value.csv" exist at "var/jetcsv" location.</div>'
            ];
            return $resultJson->setData($response);
        }

        $unitsOrOptions = [];
        $taxonomy = $this->csvProcessor->getData($file);
        unset($taxonomy[0]);
        try {
            if ($saveAttrUnitType == 2) {
                foreach ($taxonomy as $txt) {
                    $numberfomatId = number_format($txt[0], 0, '', '');
                    if ($jAttribute == $numberfomatId) {
                        $unitsOrOptions[] = $txt[2];
                    }
                }
            } else if ($saveAttrUnitType == 0) {
                foreach ($taxonomy as $txt) {
                    if ($jAttribute == number_format($txt[0], 0, '', '')) {
                        $unitsOrOptions[] = $txt[1];
                    }
                }
            }
        } catch (\Exception $e) {
            $response = [
                'error' => 'true',
                'message' => '<div class="hor-scroll" id="map-message" >'.$e->getMessage().'<div>'
            ];
            return $resultJson->setData($response);
        }
        $response = $this->attributeInfo($unitsOrOptions, $jAttribute, $saveAttrUnitType);
        return $response;
    }

    /**
     * Attribute Infoformation
     * @param $unitsOrOptions
     * @param $jAttribute
     * @param $saveAttrUnitType
     * @return $this
     */

    public function attributeInfo($unitsOrOptions, $jAttribute, $saveAttrUnitType)
    {
        $resultJson = $this->resultJsonFactory->create();
        $optionsArray = $unitsOrOptions;
        $map = true ;
        $html = '<div class="hor-scroll" id="map-message" >';
        if ($saveAttrUnitType == 2) {
            $html = $html.'<label"><strong>Note:</strong></label>';
            $html = $html. '<p>Jet Atrribute id:'. $jAttribute .'which you trying to map is a <b>UNIT</b> type attribute in jet.com. You need to Add or Create new options based on these values in your Drop down options </p>';
            $html = $html. '<label>Example: <strong>"Your value"{space}"UNIT"</strong></label>';
            $html = $html. '<p>We have taken <b>10</b> as Value for example.</p>';
            $html = $html. '<select>';
            foreach ($optionsArray as $data) {
                $html = $html. '<option value="10 '.$data.'">10 '.$data.'</option>';
            }
            $html = $html. '</select></p>';
        } else if ($saveAttrUnitType == 0) {
            if (count($optionsArray)<0) {
                $map = false ;
                $html = $html. '<strong>Note:</strong></label>';
                $html = $html. '<p>Jet Atrribute id: '.$jAttribute .'which you trying to map is not available  in jet.com. </p>';
            } else {
                $html = $html.'<p> <b>The requested jet attribute is available for mapping <b /></p>';
            }
        } else {
            $html = $html.'<p><b>The requested jet attribute is available for mapping<b /> </p>';
        }
        $html = $html. '</div>';

        if ($map) {
            $response = ['success' => 'true', 'message' => $html];
            return $resultJson->setData($response);
        }
        $response = ['error' => 'true', 'message' => $html];
        return $resultJson->setData($response);
    }
}

