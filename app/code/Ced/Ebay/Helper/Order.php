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
 * @package     Ced_Ebay
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Helper;

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
     * @var \Ced\Ebay\Model\ResourceModel\Orders\CollectionFactory
     */
    public $_ebayOrder;
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
     * @param \Ced\Ebay\Model\ResourceModel\Orders\CollectionFactory $_ebayOrder
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
        \Ced\Ebay\Model\ResourceModel\Orders\CollectionFactory $_ebayOrder,
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
        $this->_ebayOrder = $_ebayOrder;
        $this->_product = $_product;
        $this->cache = $cache;
        parent::__construct($context);
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->_objectManager->get('Magento\Framework\App\Config\ValueInterface');
        $this->messageManager = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
        $this->datahelper = $this->_objectManager->get('Ced\Ebay\Helper\Data');
    }

    /**
     * @return bool
     */
    public function getNewOrders()
    {
        $cacheType = ['translate','config','block_html','config_integration','reflection','db_ddl','layout','eav','config_integration_api','full_page','collections','config_webservice'];
        foreach ($cacheType as $cache) {
            $this->cache->cleanType($cache);
        }
        $store_id = $this->scopeConfigManager->getValue('ebay_config/ebay_setting/mage_storeid');
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $store = $this->_storeManager->getStore($store_id);
        $orderdata = $this->datahelper->getOrderRequestBody();
        $response = $this->_jdecode->jsonDecode($orderdata);
        //echo "<pre>"; print_r($response); die;
        $count = 0;
        foreach ($response as $result) {
            $transactions = $result['TransactionArray']['Transaction'];
    
            $email = $transactions['Buyer']['Email'];
            $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
            $ebayOrderid = $result ['OrderID'];
            $resultdata = $this->_ebayOrder->create()->addFieldToFilter('ebay_order_id', $ebayOrderid);
            if (!$this->validateString($resultdata->getData())) {
                $ncustomer = $this->_assignCustomer($result, $customer, $store, $email, $websiteId);
                if (!$ncustomer) {
                    return false;
                } else {
                    $count = $this->generateQuote($store, $ncustomer, $result, $count);
                }
            }
        }
        if ($count > 0) {
            $this->notificationSuccess($count);
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
        $model->setData('title', "New eBay Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders for eBay");
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
    public function _assignCustomer($result, $customer, $store = null, $email, $websiteId)
    {
        if (!$this->validateString($customer->getId())) {
            try {
                $transactions = $result['TransactionArray']['Transaction'];
                $firstname = $transactions['Buyer']['UserFirstName'];
                $lastname = $transactions['Buyer']['UserLastName'];

                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstname);
                $customer->setLastname($lastname);
                $customer->setPassword("password");
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $message = $e->getMessage();
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
    public function generateQuote($store, $ncustomer, $result, $count)
    {
        $autoReject = false;
        $baseprice = '';
        $itemsArr = [];
        $shippingcost = '';
        $tax = '';
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        $quote = $this->cartRepositoryInterface->get($cart_id);
        $quote->setStore($store);
        $quote->setCurrency();
        $customer = $this->customerRepository->getById($ncustomer->getId());
        $quote->assignCustomer($customer);
        $rejectOrder = false;
        $outOfStockSkus = '';
        $disabledSkus = '';
        $transactions = $result['TransactionArray']['Transaction'];
        $sku = $transactions['Item']['SKU'];
        $product_obj = $this->_objectManager->get('Magento\Catalog\Model\Product');
        $product = $product_obj->loadByAttribute('sku', $sku);
        if ($product) {
            $product = $this->_product->create()->load($product->getEntityId());
            if ($product->getStatus() == '1') {
                $stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                /* Get stock item */
                $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ? ($stock->getQty() >= $transactions ['QuantityPurchased'] ? true : false) : false) : false;
                if ($stockstatus) {
                    $productArray [] = [
                        'id' => $product->getEntityId(),
                        'qty' => $transactions ['QuantityPurchased']];
                    $price = $transactions['TransactionPrice'];
                    $qty = $transactions ['QuantityPurchased'];
                    $baseprice = $qty * $price;
                    $shippingcost = $result ['ShippingServiceSelected']['ShippingServiceCost'];
                    $tax = $transactions['Taxes']['TotalTaxAmount'];
                    $rowTotal = $price * $qty;
                    $product->setPrice($price)
                        ->setBasePrice($price)
                        ->setOriginalCustomPrice($price)
                        ->setRowTotal($rowTotal)
                        ->setBaseRowTotal($rowTotal);
                    $quote->addProduct($product, intval($qty));
                    /*$itemsArr [] = ['order_item_acknowledgement_status' => 'fulfillable',
                        'order_item_id' => $item ['order_item_id']];*/
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
        if ($rejectOrder) {
            $message = '';
            if (!empty($outOfStockSkus)) {
                $message .= $outOfStockSkus . " is/are Out of Stock SKUs.";
            }
            if (!empty($disabledSkus)) {
                $message .= $disabledSkus . " is/are Not Enabled.";
            }
            //$this->rejectOrder($result, $message, $orderItemId, $itemsArr);
        }
        if (isset($productArray)) {

                $firstName = $transactions['Buyer']['UserFirstName'];
                $lastName = $transactions['Buyer']['UserLastName'];
                $phone = 0;
                foreach ($result['ShippingAddress']['Phone'] as $value) {
                    $phone = $value;
                    break;
                }
                $shipAdd = [
                        'firstname' => $result['ShippingAddress']['Name'],
                        'lastname' => $result['ShippingAddress']['Name'],
                        'street' => $result['ShippingAddress']['Street1'],
                        'city' => $result['ShippingAddress']['CityName'],
                        'country_id' => $result['ShippingAddress']['Country'],
                        'region' => $result['ShippingAddress']['StateOrProvince'],
                        'postcode' => $result ['ShippingAddress']['PostalCode'],
                        'telephone' => $phone,
                        'fax' => '',
                        'save_in_address_book' => 1
                    ];
                $billAdd = [
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'street' => $result['ShippingAddress']['Street1'],
                        'city' => $result['ShippingAddress']['CityName'],
                        'country_id' => $result['ShippingAddress']['Country'],
                        'region' => $result['ShippingAddress']['StateOrProvince'],
                        'postcode' => $result ['ShippingAddress']['PostalCode'],
                        'telephone' => $phone,
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

                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('shipbyebay_shipbyebay');
                $quote->setPaymentMethod('paybyebay'); 
                $quote->setInventoryProcessed(false); 
                $quote->save();
                $quote->getPayment()->importData([
                    'method' => 'paybyebay'
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

                $order_place = date('Y-m-d', strtotime($transactions['CreatedDate']));
                $orderData = [
                    'ebay_order_id' => $result['OrderID'],
                    'order_place_date' => $order_place,
                    'magento_order_id' => $order->getIncrementId(),
                    'status' => $result['OrderStatus'] == 'Completed' ? 'acknowledge' : $result['OrderStatus'],
                    'order_data' => serialize($result)
                ];
                $model = $this->_objectManager->create('Ced\Ebay\Model\Orders')->addData($orderData);
                $model->save();
                $this->sendMail($result['OrderID'], $order->getIncrementId(), $order_place);
                $this->generateInvoice($order);
            }
        return $count;
    }

    /**
     * @param $ebayOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return void
     */
    public function sendMail($ebayOrderId, $mageOrderId, $placeDate)
    {
        $body ='<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new order from Ebay.</h1>
                    <p> Please review your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Merchant Order Id'.$EeayOrderId.'</h4>
                </td>
                <td>
                    <h4>Magneto Order Id'.$mageOrderId.'</h4>
                </td>
                <td>
                    <h4>Order Place Date'.$placeDate.'</h4>
                </td>
            </tr>  
        </table>';
        $to_email = $this->scopeConfigManager->getValue('ebay_config/ebay_order/order_notify_email');
        $to_name =  'Ebay Seller';
        $subject = 'Imp: New Ebay Order Imported';
        $senderEmail ='ebayadmin@cedcommerce.com';
        $senderName ='Ebay';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: '.$senderEmail.'' . "\r\n";
        mail($to_email,$subject,$body,$headers);
        return true;
    }

    /**
     * @param $result
     * @param $message
     * @param $orderItemId
     * @param $itemsArr
     * @return bool
     */
    public function rejectOrder($result, $message, $orderItemId, $itemsArr)
    {
        $date = date("Y-m-d H:i:s");
        if ($this->scopeConfigManager->getValue('Ebayconfiguration/Ebay_order/order_autocancel')) {
            $status = 'cancelled';
            $response = $this->orderCancelRequest($result['merchant_order_id'], $orderItemId, $itemsArr);
            $this->messageManager->addError('your order with reference id ' . $result ['reference_order_id'] . 'has been cancelled on Ebay');
        } else {
            $status = 'pending';
        }
        $EbayOrderError = $this->_objectManager->create('Ced\Ebay\Model\OrderImportError');
        $EbayError = $EbayOrderError->getCollection()->addFieldToFilter('merchant_order_id', $result['merchant_order_id'])->getData();
        if (empty($EbayError)) {
            $EbayOrderError->setMerchantOrderId($result ['merchant_order_id']);
            $EbayOrderError->setReferenceNumber($result ['reference_order_id']);
            $EbayOrderError->setOrderItemId($orderItemId);
            $EbayOrderError->setReason($message);
            $EbayOrderError->setStatus($status);
            $EbayOrderError->setOrderTime($date);
            $EbayOrderError->save();
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
        $model->setData('title', "Failed Ebay Order");
        $model->setData('description', "You have one pending order." . $message . ". Please update your inventory immediately");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
    }

    /**
     * Request On Ebay For Cancel Ready-Order
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
        $this->messageManager->addSuccess('Order with ' . $merchantOrderid . ' Merchant Order Id has been cancelled on Ebay');
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
            'comment_text' => 'Ebay Cancelled Orders',
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
                    <h1>You have a new return from Ebay.</h1>
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
        $to_email = $this->scopeConfigManager->getValue('Ebayconfiguration/Ebay_order/order_notify_email');
        $to_email = isset($to_email) ? $to_email : "nir@dulcefina.com";
        $to_name =  'Ebay Seller';
        $subject = 'Imp: New Ebay Return';
        $senderEmail ='Ebayadmin@cedcommerce.com';
        $senderName ='Ebay';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: '.$senderEmail.'' . "\r\n";
        mail($to_email,$subject,$body,$headers);
        return true;
    }
}
