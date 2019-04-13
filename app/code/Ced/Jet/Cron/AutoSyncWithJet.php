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
namespace Ced\Jet\Cron;

class AutoSyncWithJet
{
    public $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $msg = '';
        $jetHelper = $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $dataHelper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $cronModel = $this->_objectManager->get('Ced\Jet\Model\CronScheduler')->getCollection()->addFieldToFilter('cron_status', 'scheduled')->getFirstItem();
        if (!empty($cronModel->getData())) {
            $startTime = date("Y-m-d H:i:s");
            $skuString = $cronModel->getProductSkus();
            $productskus = explode(',', $skuString);
            $fullfillmentnodeid = $dataHelper->getFulfillmentNode();
            if (empty($fullfillmentnodeid)) {
                $msg = 'fullfillment node id missing';
                $finishTime = date("Y-m-d H:i:s");
                $this->_objectManager->create('Ced\Jet\Model\CronScheduler')->load($cronModel->getId())->setStartTime($startTime)->setFinishTime($finishTime)->setCronStatus('error')->setError($msg)->save();
                $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                return true;
            }
            $jetHelper->createuploadDir();
            $return = $this->resultData($productskus);
            if (empty($return['result']) || empty($return['price']) || empty($return['inventory'])) {
                $msg = 'no data found for upload';
                $finishTime = date("Y-m-d H:i:s");
                $this->_objectManager->create('Ced\Jet\Model\CronScheduler')->load($cronModel->getId())->setStartTime($startTime)->setFinishTime($finishTime)->setCronStatus('error')->setError($msg)->save();
                $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                return true;
            }

            $result = $return["result"];
            $price = $return["price"];
            $inventory = $return["inventory"];
            $relationship = $return["relationship"];
            $upload_file = true;
            if (!empty($result)) {
                $merchantSkuPath = $dataHelper->createJsonFile("MerchantSKUs", $result);
                $path_sku = explode('/', $merchantSkuPath);
                $sku_file_name = end($path_sku);
                $upload_file = false;
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
            if ($upload_file) {
                $msg = 'error in preparing json file';
                $finishTime = date("Y-m-d H:i:s");
                $this->_objectManager->get('Ced\Jet\Model\CronScheduler')->load($cronModel->getId())->setStartTime($startTime)->setFinishTime($finishTime)->setCronStatus('error')->setError($msg)->save();
                $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                return true;
            }

            $merchantSkuPath = $merchantSkuPath . ".gz";
            $pricePath = $pricePath . ".gz";
            $inventoryPath = $inventoryPath . ".gz";

            if (fopen($merchantSkuPath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = isset($data->jet_file_id)? $data->jet_file_id : "";
                $tokenurl = isset($data->url)? $data->url : "";
                if (empty($fileid) || empty($tokenurl)) {
                    $msg = 'file id or token url missing';
                    $finishTime = date("Y-m-d H:i:s");
                    $this->_objectManager->get('Ced\Jet\Model\CronScheduler')->load($cronModel->getId())->setStartTime($startTime)->setFinishTime($finishTime)->setCronStatus('error')->setError($msg);
                    $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                    return true;
                }
                $dataHelper->uploadFile($merchantSkuPath, $data->url);
                $postFields='{"url":"'.$data->url.'","file_type":"MerchantSKUs","file_name":"'.$sku_file_name.'"}';
                $response = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $skudata  = json_decode($response);
                if($skudata->status=='Acknowledged'){
                    if (!empty($relationship) && count($relationship)>0) {
                        foreach($relationship as $key=>$relval){
                            $json_data = $dataHelper->Varitionfix(json_encode($relval), count($relval['variation_refinements']));
                            $dataHelper->CPutRequest('/merchant-skus/'.$key.'/variation',$json_data);
                                
                        }
                    }
                } else {
                    $msg .= 'MerchantSKUs file fail to upload';
                    
                }
            }

            if (fopen($pricePath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $dataHelper->uploadFile($pricePath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"Price","file_name":"'.$price_file_name.'"}';
                $responseprice = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $pricedata  = json_decode($responseprice);
                if($pricedata->status !='Acknowledged'){
                    $msg .= 'Price file fail to upload';
                }
            }

            if (fopen($inventoryPath, "r") != false) {
                $response = $dataHelper->CGetRequest('/files/uploadToken');
                $data = json_decode($response);
                $fileid = $data->jet_file_id;
                $tokenurl = $data->url;
                $reponse = $dataHelper->uploadFile($inventoryPath, $data->url);
                $postFields = '{"url":"'.$data->url.'","file_type":"Inventory","file_name":"'.$inventory_file_name.'"}';
                $responseinventry = $dataHelper->CPostRequest('/files/uploaded', $postFields);
                $invetrydata = json_decode($responseinventry);
                if($invetrydata->status !='Acknowledged'){
                    $msg .= 'Inventory file fail to upload';
                }
            }
            $finishTime = date("Y-m-d H:i:s");
            $updateScheduler = $this->_objectManager->get('Ced\Jet\Model\CronScheduler')->load($cronModel->getId());
            if ($msg == '') {
                $updateScheduler->setStartTime($startTime)->setFinishTime($finishTime)->setCronStatus('success')->save();
                $this->logger->info('AutoSyncWithJet: Success');
            } else {
                $updateScheduler->setStartTime($startTime)->setFinishTime($finishTime)->setError($msg)->setCronStatus('error')->save();
                $this->logger->info('AutoSyncWithJet: Error -'.$msg);
            }
        } else {
            $cronData = $this->_objectManager->get('Ced\Jet\Model\CronScheduler')->getCollection();
            if ($cronData->getSize() > 0) {
                $time = $cronData->getFirstItem()->getStartTime();
                $day = date("d", strtotime($time));
                $today = date("d");
                if ($today > $day) {
                    $model = $this->_objectManager->create('Ced\Jet\Model\CronScheduler');
                    $connection = $model->getResource()->getConnection()->truncateTable('jet_cron_scheduler');
                    $productskus = $jetHelper->getAllJetUploadableProduct(true);
                    $batchSize = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('jetconfiguration/jet_upload/cron_chunk_size');
                    $productids = array_chunk($productskus, $batchSize);
                    foreach ($productids as $ids) {
                        $idstring = implode(',', $ids);
                        $cronModel = $this->_objectManager->create('Ced\Jet\Model\CronScheduler');
                        $cronModel->setProductSkus($idstring);
                        $cronModel->setCronStatus('scheduled');
                        $cronModel->save();
                    }
                    $msg = 'Table truncate and batch scheduled successfully.';
                    $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                    return true;
                }
            } else {
                $productskus = $jetHelper->getAllJetUploadableProduct(true);
                $batchSize = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('jetconfiguration/jet_upload/cron_chunk_size');
                $productids = array_chunk($productskus, $batchSize);
                foreach ($productids as $ids) {
                    $idstring = implode(',', $ids);
                    $cronModel = $this->_objectManager->create('Ced\Jet\Model\CronScheduler');
                    $cronModel->setProductSkus($idstring);
                    $cronModel->setCronStatus('scheduled');
                    $cronModel->save();
                }
                $msg = "first time scheduler run";
                $this->logger->info('AutoSyncWithJet: Error -'.$msg);
                return true;
            }
        }
    }

    /**
     * @param $skus
     * @return array
     */
    public function resultData($skus)
    {
        $result = [];
        $inventory = [];
        $price = [];
        $relationship = [];
        foreach ($skus as $sku) {
            if ($resultData = $this->_objectManager->create('Ced\Jet\Helper\Data')->createProductOnJet($sku, "", "")) {
                if (isset($resultData['msg']['type']) && ($resultData['msg']['type'] == 'error') && !isset($resultData['merchantsku'])) {
                    continue;
                }
                $result = $result + $resultData['merchantsku'];
                $price = $price + $resultData['price'];
                $inventory = $inventory + $resultData['inventory'];
                if (isset($resultData['relationship'])) {
                    $relationship = $relationship + $resultData['relationship'];
                }
            }
        }
        $return = [];
        $return["result"] = $result;
        $return["price"] = $price;
        $return["inventory"] = $inventory;
        $return["relationship"] = $relationship;
        return $return;
    }
}