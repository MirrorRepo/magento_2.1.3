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

class StartUnarchieve extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

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
        $api_dat = $this->_objectManager->create('Magento\Backend\Model\Session')->getProductUndoArcChunks();
        $index = $key + 1;
        if (count($api_dat) <= $index) {
            $this->_objectManager->create('Magento\Backend\Model\Session')->unsProductUndoArcChunks();
        }

        if (isset($api_dat[$key])) {
            $product_ids = [];
            $product_ids = $api_dat[$key];
            $fullfillmentnodeid = "";
            $fullfillmentnodeid = $helper->getFulfillmentNode();
            if (empty($fullfillmentnodeid) || $fullfillmentnodeid == '' || $fullfillmentnodeid == null) {
                $message['error'] = $message['error'] . 'Enter fullfillmentnode id in Jet Configuration.';
                return $this->getResponse()->setBody(json_encode($message));
            }
        }

        $merchantNode = [];
        $Inventory = [];
        
        $productdata = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $commaseperatedids = implode(",", $product_ids);
        foreach ($product_ids as $pid) {
            $productLoad = $productdata->load($pid);
            
            if ($productLoad->getTypeId() == 'configurable') {
                $type = 'configurable';
            } else {
                $sku = $productLoad->getData('sku');
                $merchantNode[$sku] = ['is_archived'=>false];

                if ($commaseperatedskus == "") {
                    $commaseperatedskus = $sku;
                    $commp_ids = $productLoad->getId(); 
                } else {
                    $commp_ids = $commp_ids.",".$productLoad->getId(); 
                    $commaseperatedskus = $commaseperatedskus.",".$sku;
                }
                $temp = [];
                $qty = $productLoad->getQuantityAndStockStatus()['qty'];
                $qty =($qty < 0) ? 0 : $qty;
                
                $temp = [$sku => ['fulfillment_nodes' =>[ ['fulfillment_node_id' => $fullfillmentnodeid, 'quantity' => $qty] ] ] ];
                $Inventory = array_merge($temp, $Inventory);
            }

        }

        $tokenresponse = $helper->CGetRequest('/files/uploadToken');
        $tokendata = json_decode($tokenresponse);
        $fileid = $tokendata->jet_file_id;
        $tokenurl = $tokendata->url; 

        /*$model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo');
        $data2 = json_decode($helper->archieveatJet($merchantNode,$commp_ids,$fileid,$tokenurl,$model,$helper, "unArchieveSKUs" ));
        
        if (isset($data2) && $data2->status == 'Acknowledged') {
            $update = ['status'=>'Acknowledged'];
            $model->addData($update)->save();*/
            
            if (!empty($Inventory)) {
                $tokenresponse2 = $helper->CGetRequest('/files/uploadToken');
                $tokendata2 = json_decode($tokenresponse2);
                    
                $inventorypath = $helper->createJsonFile("UncinventorySKUs", $Inventory);
                $path_data = explode('/', $inventorypath);
                $inventory_file_name = end($path_data);
                $inventorypath = $inventorypath.'.gz';
    
                $text = ['magento_batch_info'=>$commp_ids,
                        'jet_file_id'=>$tokendata2->jet_file_id,
                        'token_url'=>$tokendata2->url,
                        'file_name'=>$inventory_file_name,
                        'file_type'=>"Inventory",
                        'status'=>'unprocessed'
                    ];
                $model2 = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();
                
                $helper->uploadFile($inventorypath, $tokendata2->url);
                $postFields = '{"url":"'.$tokendata2->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
                $responseinventry = $helper->CPostRequest('/files/uploaded', $postFields);
                $invetrydata = json_decode($responseinventry);
    
                if (isset($invetrydata) && $invetrydata->status == 'Acknowledged') {
                    $update = ['status'=>'Acknowledged'];
                    $model2->addData($update)->save();
                    $message['success'] = "Batch $index products Unarchieve Request Sent Successfully on Jet.com. Contained product skus are : $commaseperatedskus.";
                } else {
                    $message['error'] = "Batch $index products Uarchieve Request rejected on Jet.com";
                }
            }       
      /*  }*/
        
        return $this->getResponse()->setBody(json_encode($message));
    }
}
