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

class BeginInvPriceSyn extends \Magento\Backend\App\Action
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
        $commaseperatedids  = '';
        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');

        $fullfillmentnodeid = $helper->getFulfillmentNode();
        if (empty($fullfillmentnodeid) || $fullfillmentnodeid == '' || $fullfillmentnodeid == null) {
            $message['error'] = $message['error'] . 'Enter fullfillmentnode id in Jet Configuration.';
           $this->getResponse()->setBody(json_encode($message));
            return;
        }

        $helper->initBatcherror();
        $key = $this->getRequest()->getParam('index');
        
        $api_dat = [];
        $api_dat = $this->_objectManager->create('Magento\Backend\Model\Session')->getSyncChunks();
        $index = $key + 1;

        if (count($api_dat) <= $index) {
            $this->_objectManager->create('Magento\Backend\Model\Session')->unsSyncChunks();
        }
    
        if (isset($api_dat[$key])) {
            $product_ids = [];
            $product_ids = $api_dat[$key];
        }
        $Inventory = [];
        $Price = [];
        foreach ($product_ids as $prow) {
            $productdata = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $commaseperatedids = $commaseperatedids.$prow['entity_id'].',';
            $productLoad = $productdata->load($prow['entity_id']);
            
            if (!$productLoad) {
                return;
            }
            $node = [];
            $node1 = [];
            $qty = $productLoad->getQuantityAndStockStatus()['qty'];
            $qty =($qty < 0) ? 0 : $qty;

            $node1['fulfillment_node_id'] = "$fullfillmentnodeid";
            $node1['quantity'] = $qty;
            $Inventory[$productLoad->getSku()]['fulfillment_nodes'][] = $node1;
            
            $product_price = 0;
            $product_price = $this->_objectManager->get('Ced\Jet\Helper\Jet')->getJetPrice($productLoad);
              
            $node['fulfillment_node_id'] = "$fullfillmentnodeid";
            $node['fulfillment_node_price'] = $product_price;
            $Price[$productLoad->getSku()]['price'] = $product_price;
            $Price[$productLoad->getSku()]['fulfillment_nodes'][] = $node;
        }
         
        if (!empty($Inventory)) {
            $inventoryPath = "";
            $inventoryPath = $helper->createJsonFile("SyncInventory", $Inventory);
            $path_data = explode('/', $inventoryPath);
            $inventory_file_name =  end($path_data);
            $inventoryPath = $inventoryPath.'.gz';
            
            $tokenresponse = $helper->CGetRequest('/files/uploadToken');
            $tokendata = json_decode($tokenresponse);
              
            $helper->uploadFile($inventoryPath,$tokendata->url);
              
            $postFields ='{"url":"'.$tokendata->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
            $responseInv = $helper->CPostRequest('/files/uploaded',$postFields);
              
            $data2 = json_decode($responseInv);
            if (isset($data2) && $data2->status == 'Acknowledged') {
                $message['success'] = "Batch $index products Inventory Update Request Sent Successfully on Jet.com. Contained Product Ids are : $commaseperatedids.";
            } else {
                $message['error'] = "Batch $index products Inventory Update Request Rejected on Jet.com";
            }  
        }
    
        if (!empty($Price)) {

            $price_Path = "";
            $price_file_name = "";
          
            $price_Path = $helper->createJsonFile("SyncPrice", $Price);
            $path_data = explode('/', $price_Path);
            $price_file_name =  end($path_data);
            $price_Path = $price_Path.'.gz';
          
            $tokenresponse2 = $helper->CGetRequest('/files/uploadToken');
            $tokendata2 = json_decode($tokenresponse2);
          
            $helper->uploadFile($price_Path, $tokendata2->url);
            $postFields = '{"url":"'.$tokendata2->url.'","file_type":"Price","file_name":"'.$price_file_name.'"}';
            $responsePrice = $helper->CPostRequest('/files/uploaded', $postFields);
          
            $pricedata = json_decode($responsePrice);
      
            if (isset($pricedata) && $pricedata->status == 'Acknowledged') {
                $message['success1'] = "Batch $index products Price Update Request Sent Successfully on Jet.com. Contained Product Ids are : $commaseperatedids.";
            } else {
                $message['error1'] = "Batch $index products Price Update Request Rejected on Jet.com";
            }
        }

        $this->getResponse()->setBody(json_encode($message));
        return;
    }
}
