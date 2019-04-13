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
namespace Ced\Ebay\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\JsonFactory;

class OtherDetails extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether vat is valid
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $msg = false;
        $helper = $this->_objectManager->get('Ced\Ebay\Helper\Data');
        $location= $this->getRequest()->getParam('location');
        $locationList = $this->_objectManager->get('Ced\Ebay\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        
        $mediaDirectory = $this->_objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Ebay/Setup/json/');
        if (!file_exists($folderPath .$locationName)) {
            $this->_objectManager->get('\Magento\Framework\Filesystem\Io\File')->mkdir($folderPath .$locationName, 0777, true);
        }

        // fetch category
        $levels = [1,2,3,4];
        foreach ($levels as $level) {
            $getCat = $helper->getCategories($level);
            if($getCat != "error"){
                $path = $folderPath .$locationName. '/categoryLevel-'.$level.'.json';
                $file   = fopen($path, "w");
                $pieces = str_split(json_encode($getCat), 1024);
                foreach ($pieces as $piece) {
                    if(!fwrite($file, $piece, strlen($piece))){
                        $msg = false;
                    } else {
                        $msg = true;
                    }
                }
                fclose($file);
            }
        }

        // fetch payment methods
        $getPayMethods = $helper->getSiteSpecificPaymentMethods();
        if ($getPayMethods != 'error') {
            $path = $folderPath .$locationName. '/payment-methods.json';
            $file   = fopen($path, "w");
            if (!fwrite($file, $getPayMethods)) {
                $msg = false;
            } else {
                $msg = true;
            }
            fclose($file);
        }

        // fetch return policy
        $getReturnPolicy = $helper->getSiteSpecificReturnPolicy();
        if ($getReturnPolicy != 'error') {
            $path = $folderPath .$locationName. '/returnPolicy.json';
            $file   = fopen($path, "w");
            if (!fwrite($file, $getReturnPolicy)) {
                $msg = false;
            } else {
                $msg = true;
            }
            fclose($file);
        }

        // fetch shipping details
        $getShippingDetails = $helper->getSiteSpecificShippingDetails();
        if ($getShippingDetails != 'error') {
            $path = $folderPath .$locationName. '/shippingDetails.json';
            $file   = fopen($path, "w");
            if (!fwrite($file, $getShippingDetails)) {
                $msg = false;
            } else {
                $msg = true;
            }
            fclose($file);
        }
        if ($msg) {
            $currentUrl = $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl();
            $url = $currentUrl.'system_config/edit/section/ebay_config';
            $response['data'] = $url;
            $response['msg'] = "success";
        } else {
            $result['data'] = "Something went wrong";
            $result['msg'] = "error";
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
