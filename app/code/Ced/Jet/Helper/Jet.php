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
namespace Ced\Jet\Helper;

class Jet extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface|\Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var mixed
     */
    public $scopeConfigManager;
    /**
     * @var mixed
     */
    public $configValueManager;

    /**
     * Jet constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context);
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->objectManager->get('Magento\Framework\App\Config\ValueInterface');
    }


    /**
     * Create Upload Directory
     * @return void
     */
    public function createuploadDir()
    {
        $varDir = $this->objectManager->get('Magento\Framework\Filesystem\DirectoryList')->getPath('var');
        if (!file_exists($varDir . "/jetupload")) {
            $this->objectManager->get(
                '\Magento\Framework\Filesystem\Io\File')->mkdir(
                $varDir . "/jetupload", 0777, true);
        }
    }

    /**
     * @param $product
     * @return float|null
     */

    public function getJetPrice($product)
    {
        $price =(float)$product->getFinalPrice();
        $configPrice = trim($this->scopeConfigManager->getvalue(
            'jetconfiguration/productinfo_map/jet_product_price'));

        switch($configPrice) {
            case 'plus_fixed':
                $fixedPrice = trim($this->scopeConfigManager->getvalue(
                    'jetconfiguration/productinfo_map/jet_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                break;

            case 'plus_per':
                $percentPrice = trim($this->scopeConfigManager->getvalue(
                    'jetconfiguration/productinfo_map/jet_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                break;

            case 'min_fixed':
                $fixedPrice = trim($this->scopeConfigManager->getvalue(
                    'jetconfiguration/productinfo_map/jet_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                break;

            case 'min_per':
                $percentPrice = trim($this->scopeConfigManager->getvalue(
                    'jetconfiguration/productinfo_map/jet_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                break;

            case 'differ':
                $customPriceAttr = trim($this->scopeConfigManager->getvalue(
                    'jetconfiguration/productinfo_map/jet_different_price'));
                try {
                    $cprice =(float)$product -> getData($customPriceAttr);
                } catch(\Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
                }
                $price =($cprice != 0) ? $cprice : $price ;
                break;

            default:
                return(float)$price;

        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $fixedPrice
     * @param $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) &&($fixedPrice != ''))
        {
            $fixedPrice =(float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price= $configPrice == 'plus_fixed' ?(float)($price + $fixedPrice)
                    :(float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * @param null $price
     * @param null $percentPrice
     * @param $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice =(float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price/100)*$percentPrice))
                    :(float)($price - (($price/100)*$percentPrice));
            }
        }
        return $price;
    }

    /**
     * @param $pid
     * @return bool
     */
    public function getChildPrice($pid)
    {
        if (is_numeric($pid)) {
            $product = $this->objectManager->create(
                'Magento\Catalog\Model\Product')->load($pid);
        }
        if (isset($product) && $product->getId()) {
            if ($product->getTypeId() == "configurable") {
                $childPrices= [];
                $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                $basePrice = $product->getFinalPrice();

                foreach ($attributes as $attribute) {
                    $prices = $attribute->getPrices();
                    foreach ($prices as $price) {
                        if ($price['is_percent']) { //if the price is specified in percents
                            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                        } else { //if the price is absolute value
                            $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                        }
                    }
                }
                $simple = $product->getTypeInstance()->getUsedProducts();
                foreach ($simple as $sProduct) {
                    $totalPrice = $basePrice;

                    foreach ($attributes as $attribute) {
                        $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                        if (isset($pricesByAttributeValues[$value])) {
                            $totalPrice += $pricesByAttributeValues[$value];
                        }
                    }
                    $totalPrice = $this-> getJetPriceConfig($sProduct ,$totalPrice);
                    $childPrices[$sProduct->getSku()]= round($totalPrice, 2);
                }
                return $childPrices;
            } else {
                return false;
            }
        }
    }


    /**
     * @param null $only_id
     * @return mixed
     */
    public function getAllJetUploadableProduct($only_id = null)
    {
        $result = $this->objectManager->create('Ced\Jet\Model\Categories')->getCollection()->addFieldToSelect('magento_cat_id')->addFieldToFilter('magento_cat_id', ['neq' => [""]]);
        $jet = [];
        foreach ($result as $val) {
            $jet[] = $val['magento_cat_id'];
        }
        $resultdata = [];
        $resultdata = explode(",", implode("", $jet));
        unset($resultdata[count($resultdata)-1]);
        /*$resultdata = [];
        foreach ($result as $val) {
            $resultdata[] = $val['magento_cat_id'];
        }*/
        $tablename = $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getTableName('catalog_category_product');
        $collectionProd = $this->objectManager->create('Magento\Catalog\Model\Product')->getCollection();
        $collectionProd->joinField('category_id', $tablename, 'category_id', 'product_id =entity_id', null);
        $collectionProd->addAttributeToSelect('*')
            ->addAttributeToFilter('category_id', ['in' => $resultdata]);

        $ids = array_unique($collectionProd->getAllIds());
        
        $collection = $this->objectManager->create('Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect('entity_id');
        $collection->addFieldToFilter(
            'entity_id', ['in'=>$ids])->addAttributeToFilter(
            'type_id', ['in' => ['simple','configurable']])->addAttributeToFilter(
            'visibility', ['in' => ['1','2','3','4']]);
        if ($only_id != null) {
            return $collection->getAllIds();
        } else {
            return $collection->getData();
        }
    }

    /**
     * @param $status
     * @param $status_code
     * @return bool
     */

    public function getProductByStatus($status, $status_code)
    {
        $collection = $this->objectManager->create(
            'Magento\Catalog\Model\Product')->getCollection();
        $count = $collection->getSize() + 100;
        $data_helper = $this->objectManager->create('Ced\Jet\Helper\Data');

        $raw_encode = rawurlencode($status);
        $response = $data_helper->CGetRequest('/portal/merchantskus?from=0&size=5000&statuses='.$raw_encode);
        $result = json_decode($response, true);

        $SKU = [];

        if (isset($result) && is_array($result) && isset($result['merchant_skus'])
            && sizeof($result['merchant_skus']) > 0) {
            foreach ($result['merchant_skus'] as $sku) {
                $SKU[] = $sku['merchant_sku'];
            }
        }
        if (sizeof($SKU) == 0) {
            return false;
        }
        $collection->addAttributeToFilter('sku', ['in', $SKU]);
        $allIds = $collection->getAllIds();

        if (sizeof($allIds) > 0) {
            $chunk_data = array_chunk($allIds, 500);
            $productdata = $this->objectManager->create('Magento\Catalog\Model\Product');
            foreach ($chunk_data as $k => $chunk) {
                for($i=0;count($chunk)>$i;$i++) {
                    if (trim($chunk[$i]) != "") {
                        $model = $productdata->load(trim($chunk[$i]));
                        $model->setData('jet_product_status', $status_code);
                        $model->save();
                    }
                }
            }
        }
    }

    /**
     * update Product
     * @return void
     */

    public function updateProduct()
    {
        $success_count = 0;
        $error_count = 0;
        $collection = $this->objectManager->create(
            'Ced\Jet\Model\Fileinfo')->getCollection()->addFieldToFilter(
            'Status', 'Acknowledged')->addFieldToSelect(
            'jet_file_id')->addFieldToSelect('id')->getData();

        $error_file_count = 0;
        foreach ($collection as $jdata) {
            if (isset($jdata['jet_file_id']) && $jdata['jet_file_id'] != null) {
                $response = $this->objectManager->get('Ced\Jet\Helper\Data')->CGetRequest(
                    '/files/' . $jdata['jet_file_id']);
                $resvalue = json_decode($response);
                if (isset($resvalue->status) && trim($resvalue->status) =='Processed with errors') {
                    $error_count++;
                    $jetfileid = trim($resvalue->jet_file_id);
                    $filename = $resvalue->file_name;
                    $filetype = $resvalue->file_type;
                    $status = trim($resvalue->status);
                    $error = $resvalue->error_excerpt;
                    $comma_separatederrors = implode(",", $error);

                    $update = ['status' => trim($resvalue->status)];
                    $model = $this->objectManager->create('Ced\Jet\Model\Fileinfo')->load(
                        $jdata['id'])->addData($update)->save();

                    $collectiond = $this->objectManager->create('Ced\Jet\Model\Errorfile')->getCollection()->addFieldToFilter('jetinfofile_id', $jdata['id']);

                    if ($collectiond->getSize() ==(int)0) {
                        $data = [
                            'jet_file_id' => $jetfileid,
                            'file_name' => $filename,
                            'file_type' => $filetype,
                            'status' => $status,
                            'error' => $comma_separatederrors,
                            'date' => date('Y-m-d H:i:s'),
                            'jetinfofile_id' => $jdata['id'],
                        ];

                        $model1 = $this->objectManager->create('Ced\Jet\Model\Errorfile')->addData($data);
                        $model1->save();
                        $error_file_count++;
                    }
                } else {
                    $status = isset($resvalue->status) ? trim($resvalue->status) : '';
                    $update = ['status' => $status];
                    $model = $this->objectManager->create('Ced\Jet\Model\Fileinfo')->load($jdata['id'])->addData($update);
                    $model->save();
                    $success_count++;
                }
            }
        }
    }

    /**
     * Get Updated Refund Quantity
     * @param $merchant_order_id
     * @return array
     */

    public function getUpdatedRefundQty($merchant_order_id)
    {
        $refundcollection = $this->objectManager->create('Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter('refund_id', $merchant_order_id);
        $refund_qty=[];
        if ($refundcollection->getSize()>0) {

            foreach ($refundcollection as $coll) {
                $refund_data = unserialize($coll->getData('saved_data'));
                foreach ($refund_data['sku_details'] as $sku=>$data) {
                    $refund_qty[$data['merchant_sku']]+=$data['refund_quantity'];
                }
            }
        }
        return $refund_qty;
    }

    /**
     * @param $orderModelData
     * @return bool
     */

    public function getShipped_Cancelled_Qty($orderModelData)
    {
        $shipserializedata = 'check';
        foreach ($orderModelData as $order) {
            $shipserializedata = $order->getShipment_data();
        }
        $shipserializedata = $shipserializedata == 'check' ? $orderModelData->getShipmentData() : $shipserializedata;
        if (isset($shipserializedata)) {
            $shipData = unserialize($shipserializedata);
            if (isset($shipData) ? $shipData : false) {
                foreach ($shipData["shipments"] as $value) {
                    foreach ($value["shipment_items"] as $val) {
                        if (isset($shipdata[$val["merchant_sku"]])) {
                            $shipdata[$val["merchant_sku"]]['response_shipment_cancel_qty'] += $val["response_shipment_cancel_qty"];
                            $shipdata[$val["merchant_sku"]]['response_shipment_sku_quantity'] += $val["response_shipment_sku_quantity"];
                        } else {
                            $shipdata[$val["merchant_sku"]]['response_shipment_cancel_qty'] = $val["response_shipment_cancel_qty"];
                            $shipdata[$val["merchant_sku"]]['response_shipment_sku_quantity'] = $val["response_shipment_sku_quantity"];
                        }
                    }
                }
                return $shipdata;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $orderModelData
     * @return array|bool
     */

    public function getOrdered_Cancelled_Qty($orderModelData)
    {
        foreach ($orderModelData as $order) {
            $Orderserializedata=$order->getOrder_data();
        }
        if (isset($Orderserializedata)) {
            $orderData=unserialize($Orderserializedata);
            if (isset($orderData)) {
                $order_items_info = [];
                foreach ($orderData->order_items as $sdata) {
                    $order_items_info[$sdata->merchant_sku]['request_cancel_qty'] = $sdata->request_order_cancel_qty;
                    $order_items_info[$sdata->merchant_sku]['request_sku_quantity'] =
                        $sdata->request_order_quantity;
                }
                return $order_items_info;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $orderedQty
     * @param $prevShippedQty
     * @param $prevCancelledQty
     * @param $merchantShipQty
     * @param $merchantCancelQty
     * @param $sku
     * @return string
     */

    public function validateShipment($orderedQty , $prevShippedQty , $prevCancelledQty , $merchantShipQty ,
                                     $merchantCancelQty , $sku)
    {
        $totalCancelQty = $prevCancelledQty + $merchantCancelQty ;
        $totalShippedQty = $prevShippedQty + $merchantShipQty;
        $availableShipQty = $orderedQty -($totalCancelQty + $prevShippedQty);
        $msg = '';
        if ($availableShipQty >= 0) {
            $msg = "clear";
        } else {
            $msg = "Error for sku : ".$sku." .Total cancelled + shipped qty cannot be greater than ordered 
        quantity . Already Shipped qty : ".$prevShippedQty." . Already Cancelled qty : ".$prevCancelledQty;
        }
        return $msg;
    }

    /**
     * @param $product
     * @return string
     */

    public function updateonjet($product)
    {
        $response = '';
        $isUpdatePrice = $this->scopeConfig->getValue('jetconfiguration/product_edit/update_price');
        $isUpdateQty =  $this->scopeConfig->getValue('jetconfiguration/product_edit/update_inventory');
        $isUpdateImage = $this->scopeConfig->getValue('jetconfiguration/product_edit/update_image');
        $isUpdateAll = $this->scopeConfig->getValue('jetconfiguration/product_edit/other_detail');
        $fullfillmentnodeid = $this->scopeConfig->getValue('jetconfiguration/jetsetting/fulfillment_node_id');
        $id = $product->getId();
        $sku = trim($product->getSku());
        $helper = $this->objectManager->create('Ced\Jet\Helper\Data');
        $data = $helper->CGetRequest('/merchant-skus/' . $sku);
        $productAvailable = $this->checkProdAvailable($data);
        if ($productAvailable) {
            $stockRegistry = $this->objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
            /* Get stock item  */
            $stock = $stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $isInStock = $this->checkStock($stock);
            $productQty = $this->checkProductQty($stock);
            $status = "";
            $price = $this->getJetPrice($product);
            $status = $product->getStatus();
            if (!$status || !$isInStock) {
                $arr['is_archived'] = true;
                $helper->CPutRequest('/merchant-skus/' . $sku . '/status/archive', json_encode($arr));
            } else {
                $arr['is_archived'] = false;
                $helper->CPutRequest('/merchant-skus/' . $sku . '/status/archive', json_encode($arr));
            }
            if ($status) {
                if ($isUpdateAll) {
                    $alldataupdate =$helper->createProductOnJet($product->getId(),false,'');
                    if (isset($alldataupdate['merchantsku'][$product->getSku()])) {
                        $updatedata = $alldataupdate['merchantsku'][$product->getSku()];
                        $finalskujson = json_encode($updatedata);
                        $newJsondata = $helper->ConvertNodeInt($finalskujson);
                        $allData = $helper->CPutRequest('/merchant-skus/' . $sku, $newJsondata);
                        $response .= empty($allDate) ? "data" : "";
                        //$isUpdatePrice = $isUpdateQty = $isUpdateImage = 0;
                    }
                }
                if ($isUpdatePrice) {
                    $dataVar = [];
                    $fulfillmentArr = [];
                    $fulfillmentArr[0]['fulfillment_node_id'] = $fullfillmentnodeid;
                    $fulfillmentArr[0]['fulfillment_node_price'] = $price;
                    $dataVar['price'] =(float)$price;
                    $dataVar['fulfillment_nodes'] = $fulfillmentArr;
                    $priceData = $helper->CPutRequest('/merchant-skus/' . $sku . '/price', json_encode($dataVar));
                    $response .= empty($priceData) ? "price " : "";
                }
                if ($isUpdateQty) {
                    $dataVar = [];
                    $fulfillmentArr = [];
                    $fulfillmentArr[0]['fulfillment_node_id'] = $fullfillmentnodeid;
                    $fulfillmentArr[0]['quantity'] =(int)$productQty;
                    if (!$isInStock) {
                        $fulfillmentArr[0]['quantity'] = 0;
                    }
                    $dataVar['fulfillment_nodes'] = $fulfillmentArr;
                    
                    $qtyData = $helper->CPutRequest('/merchant-skus/' . $sku . '/inventory', json_encode($dataVar));
                    $response .= empty($qtyData) ? "inventory " : "";
                }
                if ($isUpdateImage) {
                    $imageData = $this->UpdateImage($product,$sku);
                    $response .= empty($imageData) ? "image" : "";
                }
            }
        }
        return $response;
    }

    /**
     * @param $product
     * @param $sku
     * @return string
     */

    public function UpdateImage($product, $sku)
    {
        $noImage = false;
        if ($product->getImage() == "no_selection") {
            $noImage = true;
        }
        $mainImageUrl = "";
        $altImages = [];
        if (!$noImage) {
            $mainImageUrl = $product->getImageUrl();
        }
        if ($mainImageUrl != "") {
            $altImages["main_image_url"] = $mainImageUrl;
        }
        $allImages = '';
        $allImages = $product->getMediaGalleryImages();
        $allImages = empty($allImages) ? [] : $allImages;
        $jetImageSlot = 1;
        $slot = 1;
        foreach ($allImages as $key => $alternateImage) {
            if ($alternateImage->getUrl() != '') {
                if (empty($altImages)) {
                    $altImages["main_image_url"] = $alternateImage->getUrl();
                }
                $altImages['alternate_images'][] = ['image_slot_id' => $slot,
                    'image_url' => $alternateImage->getUrl()];
                $slot++;
                if ($jetImageSlot > 7) {
                    break;
                }
                $jetImageSlot++;
            }
        }
        $data = $this->objectManager->create('Ced\Jet\Helper\Data')->CPutRequest(
            '/merchant-skus/' . $sku . '/image', json_encode($altImages));
        return $data;
    }

    /**
     * @param $stock
     * @return bool
     */
    public function checkStock($stock)
    {
        $isStock = true;
        if ($stock && $stock->getIsInStock() != '1') {
            $isStock = false;
        }
        return $isStock;
    }

    /**
     * @param $stock
     * @return int
     */
    public function checkProductQty($stock)
    {
        if ($stock && $stock->getQty() > 0) {
            $productQty = $stock->getQty();
        } else {
            $productQty = 0;
        }
        return $productQty;
    }

    /**
     * @param $data
     * @return bool
     */
    public function checkProdAvailable($data)
    {
        $productAvail = true;
        if (!$data) {
            $productAvail = false;
        }
        return $productAvail;
    }
}
