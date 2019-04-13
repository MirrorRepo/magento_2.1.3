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

class StartUpload extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHandler;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    public $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHandler,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->jsonHandler = $jsonHandler;
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
        $commaseperatedskus = '';
        $commaseperatedids = "";

        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $helper->initBatcherror();
        $key = $this->getRequest()->getParam('index');
        $api_dat = $this->_objectManager->create('Magento\Backend\Model\Session')->getProductChunks();
        $index = $key + 1;
        if (count($api_dat) <= $index) {
            $this->_objectManager->create('Magento\Backend\Model\Session')->unsProductChunks();
        }
        if (isset($api_dat[$key])) {
            $product_ids = [];
            $product_ids = $api_dat[$key];

            $fullfillmentnodeid = $helper->getFulfillmentNode();
            if (empty($fullfillmentnodeid)) {
                $this->getResponse()->setBody('Enter fullfillmentnode id in Jet Configuration.');
                return;
            }

            $ProductData = $this->ProductDetails($index, $product_ids);
            if (isset($ProductData)) {
                $result = $ProductData["result"];
                $price = $ProductData["price"];
                $inventory = $ProductData["inventory"];
                $commaseperatedskus = $ProductData["commaseperatedskus"];
                $commaseperatedids = $ProductData["commaseperatedids"];
            }


            $upload_file = false;
            $t = time();
            if (!empty($result)) {
                $merchantSkuPath = $helper->createJsonFile("MerchantSKUs", $result);
                $path_data = explode('/', $merchantSkuPath);
                $sku_file_name = end($path_data);
                $upload_file = true;
            }
            if (!empty($price)) {
                $pricePath = $helper->createJsonFile("Price", $price);
                $path_data = explode('/', $pricePath);
                $price_file_name = end($path_data);
            }
            if (!empty($inventory)) {
                $inventoryPath = $helper->createJsonFile("Inventory", $inventory);
                $path_data = explode('/', $inventoryPath);
                $inventory_file_name = end($path_data);        
            }
                    
            if ($upload_file == false) {
                $returnMsg = $this->errorMsg($index);
                if (is_string($returnMsg)) {
                    $message['error'] = $returnMsg;
                }
                $message['error'] = $message['error']."<br/>Some Product informtion was incomplete so they are not prepared for upload.";
                $message['error'] = $message['error']."<br/>Product sku(s) that are not uploaded :- $commaseperatedskus";

                try{
                    $helper->saveBatchData($index);
                } catch (Exception $e) {
                    return $e->getMessage();
                }
                print_r($this->jsonHandler->jsonEncode($message));
                return ;
                //return $this->jsonHandler->jsonEncode($message);
            }

            $merchantSkuPath = $merchantSkuPath.".gz";
            $pricePath = $pricePath.".gz";
            $inventoryPath = $inventoryPath.".gz";
                    
            if (fopen($merchantSkuPath, "r") != false) {
                $response = $helper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $text = ['magento_batch_info'=>$commaseperatedids,
                        'jet_file_id'=>$fileid,
                        'token_url'=>$tokenurl,
                        'file_name'=>$sku_file_name,
                        'file_type'=>"MerchantSKUs",
                        'status'=>'unprocessed'
                    ];
                $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();      
                $helper->uploadFile($merchantSkuPath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"MerchantSKUs","file_name":"'.$sku_file_name.'"}';
                $response = $helper->CPostRequest('/files/uploaded', $postFields);
                $skudata  = json_decode($response);
                $this->updateStatus($model, $skudata->status);
            } 
                    
            if (fopen($pricePath, "r") != false) {
                $response = $helper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $text = ['magento_batch_info'=>$commaseperatedids,
                        'jet_file_id'=>$fileid,
                        'token_url'=>$tokenurl,
                        'file_name'=>$price_file_name,
                        'file_type'=>"Price",'status'=>'unprocessed'
                    ];
                $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();
                $helper->uploadFile($pricePath, $data->url);
                $postFields ='{"url":"'.$data->url.'","file_type":"Price","file_name":"'.$price_file_name.'"}';
                $responseprice = $helper->CPostRequest('/files/uploaded',$postFields);
                $pricedata  = json_decode($responseprice);
                $this->updateStatus($model, $pricedata->status);
            }
                    
            if (fopen($inventoryPath, "r") != false) {
                $response = $helper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $text = ['magento_batch_info'=>$commaseperatedids,
                        'jet_file_id'=>$fileid,
                        'token_url'=>$tokenurl,
                        'file_name'=>$inventory_file_name,
                        'file_type'=>"Inventory",
                        'status'=>'unprocessed'
                    ];
                $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();
                $helper->uploadFile($inventoryPath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
                $responseinventry = $helper->CPostRequest('/files/uploaded', $postFields);
                $invetrydata = json_decode($responseinventry);
                $this->updateStatus($model, $invetrydata->status);
            }

            $returnMsg = $this->errorMsg($index);

            if (isset($returnMsg['msg'])) {
                $message['error'] = $returnMsg['msg'];
            }
            if (isset($returnMsg['success'])) {
                $imploded_str = "";
                $imploded_str = implode(' , ', $returnMsg['success']);
                $message['success'] = $message['success']."Batch $index products Upload Request Send Successfully on Jet.com. Successfully uploaded product skus are : $imploded_str .";
            }

            unset($result);
            unset($price);
            unset($inventory);
        } else {
            $message['error'] = $message['error']."Batch $index included Product(s) data not found.";
        }
        $helper->saveBatchData($index);

        return $resultJson->setData($message);
    }

    /**
     * Get Error message
     * @param $index
     * @return string | array
     */

    public function errorMsg($index)
    {
        $batcherror = $this->_objectManager->create(
            'Magento\Backend\Model\Session')->getBatcherror();
        $msg1 = "";
        $success_skus = [];

        foreach ($batcherror as $key=>$val) {
            if (($batcherror[$key]['error'] != "") && !isset($batcherror[$key]['sub-type'])) {
                $msg1 = $msg1."<br/>Error in Product Sku(".$batcherror[$key]['sku'].") :  ".$batcherror[$key]['error'];
            } else { 
                $success_skus[] = trim($batcherror[$key]['sku']);
                if(isset($batcherror[$key]['sub-type'])){
                    $msg1 = $msg1."<br/>Error in Parent Id(".$key.") :  ".$batcherror[$key]['error'];
                }
            }
        }

        $notice = [];
        /* push switch case in order to cover case where error sku and success sku can coexist */
        if ($msg1 != "") {
            $msg = "Error occured in Batch $index :";
            $msg = $msg.$msg1;
            $notice['msg'] = $msg;
            //return $msg;
        }
        if (isset($success_skus)) {
           $skus = $success_skus;
           $notice['success'] = $skus;
        }
        return $notice;
    }

    /**
     * Get Product Details
     * @param $index
     * @param $productIds
     * @return string | array
     */

    public function ProductDetails($index, $productIds)
    {

        $result = [];
        $inventory = [];
        $price = [];
        $commaseperatedskus = '';
        $commaseperatedids = "";
        $productdata = $this->_objectManager->create('Magento\Catalog\Model\Product');
        foreach ($productIds as $pid) {
            $model = $productdata->load($pid);
            if ($commaseperatedskus == "") {
                $commaseperatedids = $pid; 
                $commaseperatedskus = $model->getSku();
            } else {
                $commaseperatedids = $commaseperatedids.','.$pid; 
                $commaseperatedskus = $commaseperatedskus." , ".$model->getSku();
            }

            $this->_objectManager->get('Ced\Jet\Helper\Data')->initBatchErrorForProduct($model, $index);

            if ($resultData = $this->_objectManager->get('Ced\Jet\Helper\Data')->createProductOnJet($pid, $parentImage = '', $parentCategory = '')) {
                 if (isset($resultData['msg']['type']) && ($resultData['msg']['type'] == 'error') && !(isset($resultData['merchantsku'])) ) {
                    $batcherror = $this->_objectManager->create('Magento\Backend\Model\Session')->getBatcherror();
                    if (isset($batcherror[$pid]['sku'])) {
                        $batcherror[$pid]['error'] = 
                        $batcherror[$pid]['error'].' '.$resultData['msg']['data'];

                        $this->_objectManager->create('Magento\Backend\Model\Session')->setBatcherror($batcherror);
                    }
                } else {
                    $result = array_merge($result, $resultData['merchantsku']);
                    $price = array_merge($price, $resultData['price']);
                    $inventory = array_merge($inventory, 
                        $resultData['inventory']);

                     if(isset($resultData['msg']['sub-type'])){
                         $batcherror = $this->_objectManager->create('Magento\Backend\Model\Session')->getBatcherror();
                         if (isset($batcherror[$pid]['sku'])) {
                             $batcherror[$pid]['error'] =
                                 $batcherror[$pid]['error'].' '.$resultData['msg']['cause'];
                             $batcherror[$pid]['sub-type'] = 1;
                             $this->_objectManager->create(
                                 'Magento\Backend\Model\Session')->setBatcherror(
                                 $batcherror);
                         }
                     }
                }
            }
        }
        $ProductData = [
        "result"=>$result, 
        "price"=>$price,
        "inventory"=>$inventory, 
        "commaseperatedids"=> $commaseperatedids,
        "commaseperatedskus" => $commaseperatedskus];
        return $ProductData;
    }

    /**
     * @param $model
     * @param $status
     */
    public function updateStatus($model, $status)
    {
        if ($status == 'Acknowledged') {
            $update = ['status'=>'Acknowledged'];
            $model->addData($update)->save();
        }
        return;
    }
}
