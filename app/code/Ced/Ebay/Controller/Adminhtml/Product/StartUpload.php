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
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Ebay\Controller\Adminhtml\Product;

class StartUpload extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    public $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";

        $ebayHelper = $this->_objectManager->get('Ced\Ebay\Helper\Ebay');
        $dataHelper = $this->_objectManager->get('Ced\Ebay\Helper\Data');
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_objectManager->create('Magento\Backend\Model\Session')->getProductChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_objectManager->create('Magento\Backend\Model\Session')->unsProductChunks();
        }
        if (isset($totalChunk[$key])) {
            $ids = [];
            $ids = $totalChunk[$key];

            foreach ($ids as $id) {
                $finaldata = $ebayHelper->prepareData($id);
                if ($finaldata['type'] == 'success') {
                    $checkError = true;
                    $finalXml .= str_replace('<?xml version="1.0"?>', '', $finaldata['data']);
                } else {
                    $error .= $finaldata['data']." for id: ".$id;
                    $this->messageManager->addError($error);
                }
            }
            if ($checkError) {
                $token = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/ebay_setting/token');
                $xmlHeader = '<?xml version="1.0" encoding="utf-8"?>
                            <AddItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                              <RequesterCredentials>
                                <eBayAuthToken>'.$token.'</eBayAuthToken>
                              </RequesterCredentials>
                              <Version>989</Version>
                              <ErrorLanguage>en_US</ErrorLanguage>
                              <WarningLevel>High</WarningLevel>';
                $xmlFooter = '</AddItemsRequest>';
                $return = $xmlHeader.$finalXml.$xmlFooter;
                $variable = "AddItems";
                $uploadOnEbay = $dataHelper->sendHttpRequest($return, $variable, 'server');
                if($uploadOnEbay->Ack == "Warning" || $uploadOnEbay->Ack == "Success") {
                    if(isset($uploadOnEbay->AddItemResponseContainer)){
                        if(count($uploadOnEbay->AddItemResponseContainer) > 1) {
                            foreach ($uploadOnEbay->AddItemResponseContainer as $key => $value){
                                $ebayID = $value->ItemID;
                                $corID = $value->CorrelationID;
                                $profileId = $this->_objectManager->create('Ced\Ebay\Model\Profileproducts')->loadByField('product_id', $corID)->setEbayItemId($ebayID)->save();
                                $successIds .= $corID.",";
                                if( isset( $value->Errors )) {
                                    foreach ($value->Errors as $val) {
                                        $msg .= $val->ShortMessage;
                                    }
                                    $this->messageManager->addError("Errors in Product Id (".$corID.'): '.$msg);
                                }
                            }
                        } else{
                            $ebayID = $uploadOnEbay->AddItemResponseContainer->ItemID;
                            $corID = $uploadOnEbay->AddItemResponseContainer->CorrelationID;
                            $profileId = $this->_objectManager->create('Ced\Ebay\Model\Profileproducts')->loadByField('product_id', $corID)->setEbayItemId($ebayID)->save();
                            $successIds .= $corID.",";
                            if( isset( $uploadOnEbay->AddItemResponseContainer->Errors )) {
                                foreach ($uploadOnEbay->AddItemResponseContainer->Errors as $value) {
                                    $msg .= $value->ShortMessage;
                                }
                                $this->messageManager->addError("Errors in Product Id (".$corID.'): '.$msg);
                            }
                        }
                    }
                } else if($uploadOnEbay->Ack == "PartialFailure") {
                    if(isset($uploadOnEbay->AddItemResponseContainer)){
                        if(count($uploadOnEbay->AddItemResponseContainer) > 1){
                            foreach ($uploadOnEbay->AddItemResponseContainer as $key=>$value) {
                                $corID = $value->CorrelationID;
                                if (isset( $value->ItemID)) {
                                    $ebayID = $value->ItemID;
                                    $profileId = $this->_objectManager->create('Ced\Ebay\Model\Profileproducts')->loadByField('product_id', $corID)->setEbayItemId($ebayID)->save();
                                    $successIds .= $corID.",";
                                }
                                if( isset( $value->Errors )) {
                                    $corID = $value->CorrelationID;
                                    foreach ($value->Errors as $val) {
                                        $msg .= $val->ShortMessage;
                                    }
                                    $this->messageManager->addError("Errors in Product Id (".$corID.'): '.$msg);
                                }
                            }
                        } else{
                            $corID = $uploadOnEbay->AddItemResponseContainer->CorrelationID;
                            if (isset( $uploadOnEbay->AddItemResponseContainer->ItemID)) {
                                $ebayID = $AddItemResponseContainer->ItemID;
                                $profileId = $this->_objectManager->create('Ced\Ebay\Model\Profileproducts')->loadByField('product_id', $corID)->setEbayItemId($ebayID)->save();
                                $successIds .= $corID.",";
                            }
                            if( isset( $uploadOnEbay->AddItemResponseContainer->Errors )) {
                                foreach ($uploadOnEbay->AddItemResponseContainer->Errors as $value) {
                                    $msg .= $value->ShortMessage;
                                }
                                $this->messageManager->addError("Errors in Product Id (".$corID.'): '.$msg);
                            }
                        }
                    }
                } else if($uploadOnEbay->Ack == "Failure") {
                    $this->messageManager->addError("Errors in Product Id (".$corID.'): '.$uploadOnEbay->Errors->ShortMessage);
                }
            }
            $message['success'] = $message['success']."Batch ". $index ." products Upload Request Send Successfully on eBay";
        } else {
            $message['error'] = $message['error']."Batch ".$index." included Product(s) data not found.";
        }

        return $resultJson->setData($message);
    }
}
