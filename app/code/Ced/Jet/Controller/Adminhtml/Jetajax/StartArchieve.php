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

namespace Ced\Jet\Controller\Adminhtml\Jetajax;

class StartArchieve extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Product sync 
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $commaseperatedskus = '';
        $commp_ids = "";
        
        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $helper->initBatcherror();
        $key = $this->getRequest()->getParam('index');
        $api_dat = [];
        $api_dat = $this->_objectManager->create('Magento\Backend\Model\Session')->getProductArcChunks();
        $index = $key + 1;
        if (count($api_dat) <= $index) {
            $this->_objectManager->create('Magento\Backend\Model\Session')->unsProductArcChunks();
        }

        if (isset($api_dat[$key])) {
            $product_ids = $api_dat[$key];
            $fullfillmentnodeid = "";
            $fullfillmentnodeid = $helper->getFulfillmentNode();
            if (empty($fullfillmentnodeid) || $fullfillmentnodeid == '' || $fullfillmentnodeid == null) {
                $message['error'] = $message['error'] . 'Enter fullfillmentnode id in Jet Configuration.';
                $this->getResponse()->setBody(json_encode($message));
                return;
            }
        }

        $merchantNode = [];
        $productdata = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $commaseperatedids = implode(",", $product_ids);
        foreach ($product_ids as $pid) {
            $productLoad = $productdata->load($pid);
            
            if ($productLoad->getTypeId() == 'configurable') {
                // put here the code for configurable product
                $productTypeId = 'configurable';
            } else {
                $sku = $productLoad->getData('sku');
                $merchantNode[$sku] = ['is_archived'=>true];

                if ($commaseperatedskus == "") {
                    $commaseperatedskus = $sku;
                    $commp_ids = $pid; 
                } else {
                    $commp_ids = $commp_ids.",".$pid;
                    $commaseperatedskus = $commaseperatedskus.",".$sku;
                }
            }
        }

        $tokenresponse = $helper->CGetRequest('/files/uploadToken');
        $tokendata = json_decode($tokenresponse);
        $fileid = $tokendata->jet_file_id;
        $tokenurl = $tokendata->url;
        
         $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo');
        $data2 = json_decode($helper->archieveatJet($merchantNode,$commp_ids,$fileid,$tokenurl,$model,$helper, "ArchieveSKUs" ));

        if (isset($data2) && $data2->status == 'Acknowledged') {
            $update = ['status'=>'Acknowledged'];
            $model->addData($update)->save();
            $message['success'] = "Batch $index products Archieve Request Send Successfully on Jet.com.Contained product skus are : $commaseperatedskus.";
        } else {
            $message['error'] = "Batch $index products Archieve Request rejected on Jet.com";
        }
        return $this->getResponse()->setBody(json_encode($message));
    }
}
