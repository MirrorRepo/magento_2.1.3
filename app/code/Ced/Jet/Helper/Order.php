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
namespace Ced\Jet\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;
    /**
     * @var
     */
    public $resultJsonFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $_jdecode;
    /**
     * @var \Ced\Jet\Model\ResourceModel\JetOrders\CollectionFactory
     */
    public $_jetOrder;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;
    /**
     * @var mixed
     */
    public $messageManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_product;


    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $_objectManager
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Json\Helper\Data $_jdecode
     * @param \Ced\Jet\Model\ResourceModel\JetOrders\CollectionFactory $_jetOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Catalog\Model\ProductFactory $_product
     * @param \Magento\Framework\App\Cache\TypeListInterface $cache
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $_objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Json\Helper\Data $_jdecode,
        \Ced\Jet\Model\ResourceModel\JetOrders\CollectionFactory $_jetOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $_product,
        \Magento\Framework\App\Cache\TypeListInterface $cache
    )
    {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->_objectManager = $_objectManager;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->_product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->_jdecode = $_jdecode;
        $this->customerFactory = $customerFactory;
        $this->_jetOrder = $_jetOrder;
        $this->_product = $_product;
        $this->cache = $cache;
        parent::__construct($context);
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->_objectManager->get('Magento\Framework\App\Config\ValueInterface');
        $this->messageManager = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        $this->datahelper = $this->_objectManager->get('Ced\Jet\Helper\Data');
    }

    /**
     * @return bool
     */
    public function fetchLatestJetOrders()
    {
        if (!$this->scopeConfigManager->getValue('jetconfiguration/jetsetting/enable')) {
            return false;
        }
        $cacheType = ['translate','config','block_html','config_integration','reflection','db_ddl','layout','eav','config_integration_api','full_page','collections','config_webservice'];
        foreach ($cacheType as $cache) {
            $this->cache->cleanType($cache);
        }
        $store_id = $this->scopeConfigManager->getValue('jetconfiguration/jetsetting/jet_storeid');
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $store = $this->_storeManager->getStore($store_id);
        $orderdata = $this->datahelper->CGetRequest('/orders/ready');
        $response = $this->_jdecode->jsonDecode($orderdata);
        if (isset($response ['order_urls']))
            if ($this->validateString($response ['order_urls']) && !empty($response['order_urls'])) {
                $count = 0;
                foreach ($response ['order_urls'] as $jetorderurl) {
                    $res = $this->datahelper->CGetRequest($jetorderurl);
                    $result = $this->_jdecode->jsonDecode($res);
                    $resultObject = json_decode($res);
                    $email = $this->validateString(isset($result ['hash_email'])) ? $result ['hash_email'] : 'customer@jet.com';
                    $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                    if (!empty($result) && $this->validateString($result['merchant_order_id'])) {
                        $merchantOrderid = $result ['merchant_order_id'];
                        $resultdata = $this->_jetOrder->create()->addFieldToFilter('merchant_order_id', $merchantOrderid);
                        if (!$this->validateString($resultdata->getData())) {
                            $ncustomer = $this->_assignCustomer($result, $customer, $store, $email);
                            if (!$ncustomer) {
                                return false;
                            } else {
                                $count = $this->generateQuote($store, $ncustomer, $result, $resultObject, $count);
                            }
                        }
                    }
                }
                if ($count > 0) {
                    $this->notificationSuccess($count);
                }
            }
            return true;
    }

    /**
     * @param $count
     * @return void
     */
    public function notificationSuccess($count)
    {
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "Incoming Jet Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for jet");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }

    /**
     * @param $string
     * @return bool
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && !empty($string)) ? true : false;
        return $stringValidation;
    }

    /**
     * @param $result
     * @param $customer
     * @param null $store
     * @param $email
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function _assignCustomer($result, $customer, $store = null, $email)
    {
        if (!$this->validateString($customer->getId())) {
            try {
                $Cname = $result ['buyer'] ['name'];

                if (trim($Cname) == '' || $Cname == null) {
                    $Cname = $result ['shipping_to'] ['recipient'] ['name'];
                }
                $Cname = preg_replace('/\s+/', ' ', $Cname);
                $customer_name = explode(' ', $Cname);

                if (!isset($customer_name [1]) || $customer_name [1] == '') {
                    $customer_name [1] = $customer_name [0];
                }
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($customer_name [0]);
                $customer->setLastname($customer_name [1]);
                $customer->setPassword("password");
                $customer->save();
                return $customer;
            } catch (\Exception $e) {

                $message = $e->getMessage();
                $jetOrderError = $this->_objectManager->create('Ced\Jet\Model\Failedorders');
                $jetOrderError->setMerchantOrderId($result ['merchant_order_id']);
                $jetOrderError->setReferenceNumber($result ['reference_order_id']);
                $jetOrderError->setReason($message);
                $jetOrderError->save();
                return false;
            }
        } else {
            $nCustomer = $this->customerRepository->getById($customer->getId());
            return $nCustomer;
        }
    }

    /**
     * @param $store
     * @param $ncustomer
     * @param $result
     * @param $resultObject
     */
    public function generateQuote($store, $ncustomer, $result, $resultObject, $count)
    {
        $autoReject = false;
        $items_array = $result ['order_items'];
        $baseprice = '';
        $itemsArr = [];
        $shippingcost = '';
        $tax = '';
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        $quote = $this->cartRepositoryInterface->get($cart_id);
        //$quote = $this->quote->create();
        $quote->setStore($store);
        $quote->setCurrency();
        $customer = $this->customerRepository->getById($ncustomer->getId());
        $quote->assignCustomer($customer);
        $rejectOrder = false;
        $outOfStockSkus = '';
        $disabledSkus = '';
        foreach ($items_array as $item) {
            $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
            $product = $product_obj->loadByAttribute('sku', $item ['merchant_sku']);
            if ($product) {
                $product = $this->_product->create()->load($product->getEntityId());
                if ($product->getStatus() == '1') {
                    $stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                    /* Get stock item */
                    $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                    $stockstatus = ($stock->getQty() > 1) ? ($stock->getIsInStock() == '1' ? ($stock->getQty() >= $item ['request_order_quantity'] ? ($item ['request_order_quantity'] != $item ['request_order_cancel_qty'] ? true : false) : false) : false) : false;
                    if (!$stockstatus) {
                        $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                        $product_obj = $this->_objectManager->create('Magento\Catalog\Model\Product');
                        $product = $product_obj->loadByAttribute('sku', $item ['merchant_sku']);
                        $updateQty = $item ['request_order_quantity'] +1 ;
                        $stock= $stockRegistry->getStockItem($product->getId());
                        $stock->setIsInStock(1);
                        $stock->setQty(intval($updateQty));
                        $stock->save();
                        $product->save();
                        $stockstatus = true;
                    }
                    if ($stockstatus) {
                        $productArray [] = [
                            'id' => $product->getEntityId(),
                            'qty' => $item ['request_order_quantity']];
                        $price = $item ['item_price'] ['base_price'];
                        $qty = $item ['request_order_quantity'];
                        $cancelqty = $item ['request_order_cancel_qty'];
                        $baseprice += $qty * $price;
                        $shippingcost += ($item ['item_price'] ['item_shipping_cost'] * $qty) + ($item ['item_price'] ['item_shipping_tax'] * $qty);
                        $tax += $item ['item_price'] ['item_tax'];
                        $rowTotal = $price * $qty;
                        $product->setPrice($price)
                            ->setBasePrice($price)
                            ->setOriginalCustomPrice($price)
                            ->setRowTotal($rowTotal)
                            ->setBaseRowTotal($rowTotal);
                        $quote->addProduct($product, intval($qty));
                        $itemsArr [] = ['order_item_acknowledgement_status' => 'fulfillable',
                            'order_item_id' => $item ['order_item_id']];
                    } else {
                        $rejectOrder = true;
                        $outOfStockSkus .= $item['merchant_sku'];
                        $outOfStockSkus .= ",";
                        $orderItemId = $item['order_item_id'];
                    }
                } else {
                    $rejectOrder = true;
                    $disabledSkus .= $item['merchant_sku'];
                    $disabledSkus .= '';
                    $orderItemId = $item['order_item_id'];
                }
            }
        }
        if ($rejectOrder) {
            $message = '';
            if (!empty($outOfStockSkus)) {
                $message .= $outOfStockSkus . " is/are Out of Stock SKUs.";
            }
            if (!empty($disabledSkus)) {
                $message .= $disabledSkus . " is/are Not Enabled.";
            }
            $this->rejectOrder($result, $message, $orderItemId, $itemsArr);
        }
        if (isset($productArray))
            if (!empty($productArray) && count($items_array) == count($productArray) && !$autoReject) {
                $shipToName = $result ['shipping_to'] ['recipient'] ['name'];
                $buyerName = $result ['buyer'] ['name'];
                if ($shipToName != '' && $buyerName != '' && $shipToName != $buyerName) {
                    $shipToName = preg_replace('/\s+/', ' ', $shipToName);
                    $sName = explode(' ', $shipToName);
                    if (!isset($sName [1]) || $sName [1] == '') {
                        $sName [1] = $sName [0];
                    }

                    $buyerName = preg_replace('/\s+/', ' ', $buyerName);
                    $bName = explode(' ', $buyerName);

                    if (!isset($bName [1]) || $bName [1] == '') {
                        $bName [1] = $bName [0];
                    }
                }
                $shipAdd = [
                        'firstname' => isset($sName[0]) ? $sName[0] : $customer->getFirstName(),
                        'lastname' => isset($sName[1]) ? $sName[1] : $customer->getLastName(),
                        'street' => $result ['shipping_to']['address']['address1'],
                        'city' => $result ['shipping_to'] ['address'] ['city'],
                        'country_id' => 'US',
                        'region' => $result ['shipping_to'] ['address'] ['state'],
                        'postcode' => $result ['shipping_to'] ['address'] ['zip_code'],
                        'telephone' => $result ['shipping_to'] ['recipient'] ['phone_number'],
                        'fax' => '',
                        'save_in_address_book' => 1
                    ];
                $billAdd = [
                        'firstname' => isset($bName[0]) ? $bName[0] : $customer->getFirstName(),
                        'lastname' => isset($bName[1]) ? $bName[1] : $customer->getLastName(),
                        'street' => $result ['shipping_to']['address']['address1'],
                        'city' => $result ['shipping_to'] ['address'] ['city'],
                        'country_id' => 'US',
                        'region' => $result ['shipping_to'] ['address'] ['state'],
                        'postcode' => $result ['shipping_to'] ['address'] ['zip_code'],
                        'telephone' => $result ['shipping_to'] ['recipient'] ['phone_number'],
                        'fax' => '',
                        'save_in_address_book' => 1
                    ];
                $orderData = [
                    'currency_id' => 'USD',
                    'email' => 'test@cedcommerce.com',
                    'shipping_address' => $shipAdd
                ];
                $quote->getBillingAddress()->addData($billAdd);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAdd);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('flatrate_flatrate');
                $quote->setPaymentMethod('payjetcom'); 
                $quote->setInventoryProcessed(false); 
                $quote->save();
                $quote->getPayment()->importData([
                    'method' => 'payjetcom'
                ]);
                $quote->collectTotals()->save();
                foreach ($quote->getAllItems() as $item) {
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setOriginalCustomPrice($item->getPrice())
                        ->setOriginalPrice($item->getPrice())
                        ->save();
                }

                //$quote = $this->cartRepositoryInterface->get($quote->getId());
                $order = $this->cartManagementInterface->submit($quote);
                $order->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)->save();
                $count = isset($order) ? $count + 1 : $count;
                foreach ($order->getAllItems() as $item) {
                    $item->setOriginalPrice($item->getPrice())
                        ->setBaseOriginalPrice($item->getPrice())
                        ->save();
                }
                // after save order

                $deliver_by = date('Y-m-d H:i:s', strtotime($result ['order_detail'] ['request_delivery_by']));
                $order_place = date('Y-m-d H:i:s', strtotime($result ['order_placed_date']));
                $OrderData = [
                    'order_item_id' => $result ['order_items'] [0] ['order_item_id'],
                    'merchant_order_id' => $result ['merchant_order_id'],
                    'merchant_sku' => $result ['order_items'] [0] ['merchant_sku'],
                    'deliver_by' => $deliver_by,
                    'order_place_date' => $order_place,
                    'magento_order_id' => $order->getIncrementId(),
                    'status' => $result ['status'],
                    'order_data' => serialize($resultObject),
                    'reference_order_id' => $result ['reference_order_id']];
                $model = $this->_objectManager->create('Ced\Jet\Model\JetOrders')->addData($OrderData);
                $model->save();
                $this->sendMail($result ['merchant_order_id'], $order->getIncrementId(), $order_place, $deliver_by);
                $this->generateInvoice($order);
                $this->autoOrderacknowledge($order->getIncrementId(), $model);
                foreach ($result ['order_items'] as $item) {
                    $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku', $item ['merchant_sku']);
                    $this->_objectManager->get('Ced\Jet\Helper\Jet')->updateonjet($product);
                }
            }
        return $count;
    }

    /**
     * @param $jetOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @param $reqDeliveryDate
     * @return void
     */
    public function sendMail($jetOrderId, $mageOrderId, $placeDate, $reqDeliveryDate)
    {
        $body ='<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new order from jet.</h1>
                    <p> Please review your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Merchant Order Id'.$jetOrderId.'</h4>
                </td>
                <td>
                    <h4>Magneto Order Id'.$mageOrderId.'</h4>
                </td>
                <td>
                    <h4>Order Place Date'.$placeDate.'</h4>
                </td>
                <td>
                    <h4>Requested Delivery Date'.$reqDeliveryDate.'</h4>
                </td> 
            </tr>  
        </table>';
        $to_email = $this->scopeConfigManager->getValue('jetconfiguration/jet_order/order_notify_email');
        $to_email = isset($to_email) ? $to_email : "nir@dulcefina.com";
        $to_name =  'Jet Seller';
        $subject = 'Imp: New Jet Order Imported';
        $senderEmail ='jetadmin@cedcommerce.com';
        $senderName ='Jet';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: '.$senderEmail.'' . "\r\n";
        mail($to_email,$subject,$body,$headers);
        return true;
    }

    /**
     * @param $Incrementid
     * @param $Ordermodel
     * @return int
     */
    public function autoOrderacknowledge($Incrementid, $Ordermodel)
    {
        $serialize_data = unserialize($Ordermodel->getOrderData());
        if (empty($serialize_data)) {
            $result = $this->datahelper->CGetRequest('/orders/withoutShipmentDetail/' . $Ordermodel->getMerchantOrderId());
            $Ord_result = json_decode($result);
            if (empty($result)) {
                return 0;
            } else {
                $jobj = $this->_objectManager->create('Ced\Jet\Model\JetOrders')->load($Ordermodel->getId());
                $jobj->setOrderData(serialize($Ord_result));
                $jobj->save();
                $serialize_data = $Ord_result;
            }
        }
        if (empty($serialize_data)) {
            return 0;
        }
        $fullfill_array = [];
        foreach ($serialize_data->order_items as $k => $valdata) {
            $fullfill_array [] = ['order_item_acknowledgement_status' => 'fulfillable',
                'order_item_id' => $valdata->order_item_id];
        }
        $order_id = $Ordermodel->getMerchantOrderId();
        $data_var = [];
        $data_var ['acknowledgement_status'] = "accepted";
        $data_var ['order_items'] = $fullfill_array;

        // Api call to Acknowledge Order
        $data = $this->datahelper->CPutRequest(
            '/orders/' . $order_id . '/acknowledge', json_encode($data_var));
        $response = json_decode($data);
        if (isset($response->errors [0]) && !empty($response->errors)) {
            return 0;
        } else {
            // Setting acknowleged status here
            $modeldata = $this->_objectManager->get(
                'Ced\Jet\Model\JetOrders')->getCollection()->addFieldToFilter('magento_order_id', $Incrementid)->getData();
            if (!empty($modeldata)) {
                $id = $modeldata [0] ['id'];
                $model = $this->_objectManager->get(
                    'Ced\Jet\Model\JetOrders')->load($id);
                $model->setStatus('acknowledged');
                $model->save();
            }
        }
        return 0;
    }

    /**
     * @param $result
     * @param $message
     * @param $orderItemId
     * @param $itemsArr
     * @return void
     */
    public function rejectOrder($result, $message, $orderItemId, $itemsArr)
    {
        $date = date("Y-m-d H:i:s");
        if ($this->scopeConfigManager->getValue('jetconfiguration/jet_order/order_autocancel')) {
            $status = 'cancelled';
            $response = $this->orderCancelRequest($result['merchant_order_id'], $orderItemId, $itemsArr);
            $this->messageManager->addError('your order with reference id ' . $result ['reference_order_id'] . 'has been cancelled on Jet');
        } else {
            $status = 'pending';
        }
        $jetOrderError = $this->_objectManager->create('Ced\Jet\Model\OrderImportError');
        $jetError = $jetOrderError->getCollection()->addFieldToFilter('merchant_order_id', $result['merchant_order_id'])->getData();
        if (empty($jetError)) {
            $jetOrderError->setMerchantOrderId($result ['merchant_order_id']);
            $jetOrderError->setReferenceNumber($result ['reference_order_id']);
            $jetOrderError->setOrderItemId($orderItemId);
            $jetOrderError->setReason($message);
            $jetOrderError->setStatus($status);
            $jetOrderError->setOrderTime($date);
            $jetOrderError->save();
            $this->notificationFailed($message);
        }
        return true;
    }

    /**
     * @param $message
     * @return void
     */
    public function notificationFailed($message)
    {
        $date = date("Y-m-d H:i:s");
        $model = $this->_objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $model->setData('severity', 1);
        $model->setData('date_added', $date);
        $model->setData('title', "Failed Jet Order");
        $model->setData('description', "You have one pending order." . $message . ". Please update your inventory immediately");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
    }

    /**
     * Request On Jet For Cancel Ready-Order
     * @param $merchantOrderid
     * @param $orderItemId
     * @param $itemsArr
     * @return void
     */
    public function orderCancelRequest($merchantOrderid, $orderItemId, $itemsArr=[])
    {
        $itemsArr [] = [
            'order_item_acknowledgement_status' => "nonfulfillable - no inventory",
            'order_item_id' => $orderItemId
        ];
        $dataVar = [];
        $dataVar ['acknowledgement_status'] = "rejected - item level error";
        $dataVar ['order_items'] = $itemsArr;
        $data = $this->datahelper->CPutRequest(
            '/orders/' . $merchantOrderid . '/acknowledge', json_encode($dataVar));
        $this->messageManager->addSuccess('Order with ' . $merchantOrderid . ' Merchant Order Id has been cancelled on Jet');
    }

    /**
     * Update Status of Failed Orders
     * @return bool
     */
    public function updateFailedOrderStatus()
    {
        $failedOrder = $this->_objectManager->get('Ced\Jet\Model\OrderImportError')->getCollection()->addFieldToFilter('status', 'pending');
        $orderAck = 0;
        $orderCancel = 0;
        $toDate = date("Y-m-d H:i:s");
        foreach ($failedOrder as $value) {
            $merchantOrderId = $value->getMerchantOrderId();
            $id = $value->getId();
            $loadModel = $this->_objectManager->get('Ced\Jet\Model\OrderImportError')->load($id);
            $orderData = $this->_objectManager->get('Ced\Jet\Model\JetOrders')->getCollection()->addFieldToFilter('merchant_order_id', $merchantOrderId)->getData();
            if (!empty($orderData)) {
                $loadModel->delete();
                $orderAck++;
                continue;
            }
            $fromDate = $value->getOrderTime();
            $orderItemId = $value->getOrderItemId();
            $fromDate = strtotime($fromDate);
            $toDate = strtotime($toDate);
            $minute = round(abs($toDate - $fromDate) / 60, 2);
            if (intval($minute) > 1400) {
                $this->orderCancelRequest($merchantOrderId, $orderItemId);
                $loadModel->setStatus('cancelled');
                $loadModel->save();
                $orderCancel++;
            }
        }
        if ($orderAck) {
            $this->messageManager->addSuccess(__('%1 record(s) were acknowledged now.', $orderAck));
        }
        if ($orderCancel) {
            $this->messageManager->addSuccess(__('%1 record(s) were cancelled now.', $orderCancel));
        }
        return true;
    }


    /**
     * @param $order
     */
    public function generateInvoice($order)
    {
        $invoice = $this->_objectManager->create(
            'Magento\Sales\Model\Service\InvoiceService')->prepareInvoice(
            $order);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction')->addObject(
            $invoice)->addObject($invoice->getOrder());
        $transactionSave->save();
        $order->addStatusHistoryComment(__(
            'Notified customer about invoice #%1.'
            , $invoice->getId()))->setIsCustomerNotified(true)->save();
        $order->setStatus('processing')->save();
    }

    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateShipment($order, $cancelleditems)
    {
        $shipment = $this->_prepareShipment($order, $cancelleditems);
        if ($shipment) {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = $this->_objectManager->create(
                    'Magento\Framework\DB\Transaction')->addObject(
                    $shipment)->addObject($shipment->getOrder());
                $transactionSave->save();
                $order->setStatus('complete')->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    'Error in saving shipping:'
                    . $e);
            }
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     * @return bool
     */
    public function _prepareShipment($order, $cancelleditems)
    {
        $shipment = $this->_objectManager->get(
            'Magento\Sales\Model\Order\ShipmentFactory')->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
        if (!$shipment->getTotalQty()) {
            return false;
        }
        return $shipment;
    }

    /**
     * @param $order
     * @param $cancelleditems
     */

    public function generateCreditMemo($order, $cancelleditems)
    {
        foreach ($order->getAllItems() as $orderItems) {
            $items_id = $orderItems->getId();
            $order_id = $orderItems->getOrderId();
        }
        $creditmemoLoader = $this->creditmemoLoaderFactory->create();
        $creditmemoLoader->setOrderId($order_id);
        foreach ($cancelleditems as $item_id => $cancelQty) {
            $creditmemo[$item_id] = ['qty' => $cancelQty];
        }
        $items = ['items' => $creditmemo,
            'do_offline' => '1',
            'comment_text' => 'Jet Cancelled Orders',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'];
        $creditmemoLoader->setCreditmemo($items);
        $creditmemo = $creditmemoLoader->load();
        $creditmemoManagement = $this->_objectManager->create(
            'Magento\Sales\Api\CreditmemoManagementInterface'
        );
        if ($creditmemo) {
            $creditmemo->setOfflineRequested(true);
            $creditmemoManagement->refund($creditmemo, true);
        }
    }

    /**
     * @param $jetmodel
     * @param $dataShip
     * @param $cancelJetOrder
     * @param $orderIsComplete
     */

    public function saveJetShipData($jetmodel, $dataShip, $cancelJetOrder, $orderIsComplete)
    {
        $shipDbdata = $jetmodel->getShipmentData();
        if (isset($shipDbdata)) {
            $tempArr = unserialize($shipDbdata);
            $tempArr["shipments"][] = $dataShip["shipments"][0];
        } else {
            $tempArr = $dataShip;
        }
        if ($cancelJetOrder) {
            $jetmodel->setStatus('cancelled');
            $jetmodel->setShipmentData(serialize($tempArr));
            $jetmodel->save();
        } elseif ($orderIsComplete) {
            $jetmodel->setStatus('complete');
            $jetmodel->setShipmentData(serialize($tempArr));
            $jetmodel->save();
        } else {
            $jetmodel->setStatus('inprogress');
            $jetmodel->setShipmentData(serialize($tempArr));
            $jetmodel->save();
        }
    }

    /**
     * @return bool
     */
    public function jetreturn()
    {
        $false_return = "";
        $success_return = "";
        $success_count = 0;
        $false_count = 0;
        $helper = $this->_objectManager->create('Ced\Jet\Helper\Data');
        $data = $helper->CGetRequest('/returns/created');
        $returnObj = $this->_objectManager->create('Ced\Jet\Model\OrderReturn');
        $orderObj = $this->_objectManager->create('Ced\Jet\Model\JetOrders');
        $response = json_decode($data);
        $response = isset($response->return_urls) ? $response->return_urls : null;
        if (!empty($response)) {
            foreach ($response as $res) {
                $arr = explode("/", $res);
                $returnid = $arr[3];

                $resultdata = $returnObj->getCollection()->addFieldToFilter('returnid', $returnid)->getData();
                if (empty($resultdata)) {
                    $returndetails = $helper->CGetRequest($res);
                    if ($returndetails) {
                        $return = json_decode($returndetails);
                        $orderData = $orderObj->getCollection()->addFieldToFilter('reference_order_id', $return->reference_order_id)->getData();
                        $serialized_details = serialize($return);
                        try {
                            if (!empty($orderData)) {
                                $text = [
                                    'reference_order_id' => $return->reference_order_id,
                                    'merchant_order_id' => $return->merchant_order_id,
                                    'status' => $return->return_status,
                                    'returnid' => "$returnid",
                                    'return_details' => $serialized_details
                                ];
                                $model = $returnObj->addData($text);
                                $model->save();
                                $this->sendReturnMail($return->reference_order_id, $return->merchant_order_id, $returnid);
                            }
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }

                        if ($success_return == "") {
                            $success_return = $returnid;
                            $success_count++;
                        } else {
                            $success_return = $success_return . " , " . $returnid;
                            $success_count++;
                        }
                    } else {
                        if ($false_return == "") {
                            $false_return = $returnid;
                            $false_count++;
                        } else {
                            $false_return = $false_return . " , " . $returnid;
                            $false_count++;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $referenceOrderId
     * @param $merchantOrderId
     * @param $returnid
     * @return void
     */
    public function sendReturnMail($referenceOrderId, $merchantOrderId, $returnid)
    {
        $body ='<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new return from jet.</h1>
                    <p> Please view your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Referance Order Id'.$referenceOrderId.'</h4>
                </td>
                <td>
                    <h4>Merchant Order Id'.$merchantOrderid.'</h4>
                </td>
                <td>
                    <h4>Return ID'.$returnid.'</h4>
                </td>
            </tr>  
        </table>';
        $to_email = $this->scopeConfigManager->getValue('jetconfiguration/jet_order/order_notify_email');
        $to_email = isset($to_email) ? $to_email : "nir@dulcefina.com";
        $to_name =  'Jet Seller';
        $subject = 'Imp: New Jet Return';
        $senderEmail ='jetadmin@cedcommerce.com';
        $senderName ='Jet';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: '.$senderEmail.'' . "\r\n";
        mail($to_email,$subject,$body,$headers);
        return true;
    }
}
