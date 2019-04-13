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

namespace Ced\Jet\Controller\Adminhtml\Jetproduct;

class Massimport extends \Magento\Backend\App\Action
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
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
    }

    /**
     * Mass Import Action
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $jetHelper = $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $dataHelper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $fullfillmentnodeid = $dataHelper->getFulfillmentNode();
        $jetHelper->createuploadDir();
        $resubmitedProduct = $this->getRequest()->getParam('resubmited_product');
        $productids = $this->getproductIds($resubmitedProduct);
        foreach ($productids as $val) {
            $commaseperatedids = implode(",", $val);
            $return = $this->resultData($val);
            if (empty($return['result']) || empty($return['price']) || empty($return['inventory'])) {
                continue;
            }

            $result = $return["result"];
            $price = $return["price"];
            $inventory = $return["inventory"];

            $upload_file = false;
            $t = time();
            if (!empty($result)) {
                $merchantSkuPath = $dataHelper->createJsonFile("MerchantSKUs", $result);
                $path_sku = explode('/', $merchantSkuPath);
                $sku_file_name = end($path_sku);
                $upload_file = true;
            }
            if (!empty($price)) {
                $pricePath = $dataHelper->createJsonFile("Price", $price);
                $path_price = explode('/', $pricePath);
                $price_file_name = end($path_price);
            }
            if (!empty($inventory)) {
                $inventoryPath = $dataHelper->createJsonFile("Inventory", $inventory);
                $path_invoice = explode('/', $inventoryPath);
                $inventory_file_name = end($path_invoice);
            }
            if ($upload_file == false) {
                $this->messageManager->addError(
                    'Some Product informtion was incomplete so they are not prepared for upload');
                continue;
            }

            $merchantSkuPath = $merchantSkuPath . ".gz";
            $pricePath = $pricePath . ".gz";
            $inventoryPath = $inventoryPath . ".gz";

            if (fopen($merchantSkuPath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
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
                $dataHelper->uploadFile($merchantSkuPath, $data->url);
                $postFields='{"url":"'.$data->url.'","file_type":"MerchantSKUs","file_name":"'.$sku_file_name.'"}';
                $response = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $skudata  = json_decode($response);
                $this->updateStatus($model, $skudata->status);
            }

            if (fopen($pricePath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $text = ['magento_batch_info'=>$commaseperatedids,
                        'jet_file_id'=>$fileid,'token_url'=>$tokenurl,
                        'file_name'=>$price_file_name,
                        'file_type'=>"Price",
                        'status'=>'unprocessed'
                    ];
                $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();
                $dataHelper->uploadFile($pricePath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"Price","file_name":"'.$price_file_name.'"}';
                $responseprice = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $pricedata  = json_decode($responseprice);
                $this->updateStatus($model, $pricedata->status);
            }

            if (fopen($inventoryPath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $text = [
                        'magento_batch_info'=>$commaseperatedids,
                        'jet_file_id'=>$fileid,
                        'token_url'=>$tokenurl,
                        'file_name'=>$inventory_file_name,
                        'file_type'=>"Inventory",
                        'status'=>'unprocessed'
                    ];
                $model = $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->addData($text)->save();
                $reponse = $dataHelper->uploadFile($inventoryPath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
                $responseinventry = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $invetrydata = json_decode($responseinventry);
                $this->updateStatus($model, $invetrydata->status);
            }
            $this->messageManager->addSuccess('Selected products Upload Request Send Successfully on Jet.com');
            if(!empty($return['error'])){
                $this->messageManager->addError($return['error']);
            }
            unset($result);
            unset($price);
            unset($inventory);
        }

        $this->_redirect('jet/jetrequest/uploadproduct');
    }

    /**
     * @param $resubmitedProduct
     * @return array|void
     */
    public function getproductIds($resubmitedProduct)
    {
        if (isset($resubmitedProduct)) {
            $data = $resubmitedProduct;
        } else {
            $collection = $this->filter->getCollection($this->_objectManager->create(
                'Magento\Catalog\Model\Product')->getCollection());
            $data = $collection->getAllIds();
        }

        if (is_string($data)) {
            $data = explode(",", $data);
        }
        $productids = array_chunk($data, 50);

        if (empty($productids)) {
            $this->messageManager->addError('No Product selected to upload.');
            $this->_redirect('jet/jetrequest/uploadproduct');
            return;
        }
        return $productids;
    }

    /**
     * @param $model
     * @param $status
     */
    public function updateStatus($model, $status)
    {
        if ($status == 'Acknowledged') {
            $update = ['status' => 'Acknowledged'];
            $model->addData($update)->save();
        }
        return;
    }

    /**
     * @param $val
     * @return array
     */
    public function resultData($val)
    {
        $result = [];
        $inventory = [];
        $price = [];
        $notice = '';
        foreach ($val as $pid) {
            $parentImage = '';
            $parentCategory = '';
            if ($resultData = $this->_objectManager->get('Ced\Jet\Helper\Data')->createProductOnJet($pid, $parentImage, $parentCategory)) {
                if (isset($resultData['msg']['type']) && ($resultData['msg']['type'] == 'error') && !isset($resultData['merchantsku'])) {
                    $this->messageManager->addError($resultData['msg']['data']);
                    continue;
                } elseif (!empty($resultData['msg']['sub-type'])) {
                    $this->messageManager->addError('Following product(s) are skipped from upload process ' . $resultData['msg']['data']);
                }
                /*echo"<pre>";print_r($resultData);die;*/
                $result = array_merge($result, $resultData['merchantsku']);
                $price = array_merge($price, $resultData['price']);
                $inventory = array_merge($inventory, $resultData['inventory']);
                if (isset($resultData['msg']['type']) && $resultData['msg']['type'] == 'error') {
                    if (!empty($resultData['msg']['sub-type'])) {
                        $resultData['msg']['cause'] = 'Error in product id : <b>' . $pid . '</b> Following child products are skipped from upload : ' . $resultData['msg']['cause'];
                        $notice = $notice . $resultData['msg']['cause'] . '<br/>';
                    } else {
                        $notice = $notice . $resultData['msg']['data'] . '<br/>';
                    }
                }
            }
        }
        $return = [];
        $return["result"] = $result;
        $return["price"] = $price;
        $return["inventory"] = $inventory;
        if (!empty($notice)) {
            $return["error"] = $notice;
        }
        $relationship = [];
        if (isset($resultData['relationship'])) {
            $relationship = array_merge($relationship, $resultData['relationship']);
        }
        return $return;
    }
}
