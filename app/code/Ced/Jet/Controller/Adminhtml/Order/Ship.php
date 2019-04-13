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
namespace Ced\Jet\Controller\Adminhtml\Order;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;


class Ship extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;   

    /**
     * Ship constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory     
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;        
        $this->scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @return string
     */

    public function execute()
    {
        $datahelper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $orderhelper = $this->_objectManager->get('Ced\Jet\Helper\Order');
        $jethelper = $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $offsetEnd = $this->_objectManager->get('Ced\Jet\Helper\Data')->getStandardOffsetUTC();

        if (empty($offsetEnd) || trim($offsetEnd) == '') {
            $offset = '.0000000-00:00';
        } else {
            $offset = '.0000000' . trim($offsetEnd);
        }
        // collect ship data

        $shipTodatetime = strtotime($this->getRequest()->getPost('ship_todate'));
        $exptime = strtotime($this->getRequest()->getPost('exp_deliver'));
        $carrtime = strtotime($this->getRequest()->getPost('carre_pickdate'));
        // get time values
        $shipToDate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime) . $offset;
        $expDelivery = date("Y-m-d", $exptime) . 'T' . date("H:i:s", $exptime) . $offset;
        $carrPickDate = date("Y-m-d", $carrtime) . 'T' . date("H:i:s", $carrtime) . $offset;
        // jet_order_detail table row id
        $jetOrderRow = $this->getRequest()->getPost('order_table_row');
        $postData = $this->getRequest()->getPost();
        $id = $postData['key1'];
        $orderid = $postData['order'];
        $carrier = $postData['carrier'];
        $method = $postData['method'];
        $service = $postData['request_service_level'];
        $orderId = $postData['orderid'];
        $tracking = $postData['tracking'];
        $itemsData = $postData['items'];
        $itemsData = json_decode($itemsData);
        if (empty($itemsData)) {
            $this->getResponse()->setBody("You have no item in your Order.");
            return;
        }
        $returnLocation = $datahelper->getReturnLocation();
        if (is_array($returnLocation)) {
            $returnLocation = $returnLocation;
        } else {
            $this->getResponse()->setBody($returnLocation);
            return;
        }
        $shippmentArray = [];
        $shipQtyForOrder = [];
        $cancelQtyForOrder = [];
        $jetmodel = $this->_objectManager->get('Ced\Jet\Model\JetOrders')->load($jetOrderRow);
        $merchantOrderId = trim($jetmodel->getMerchantOrderId());
        $prevShipItemsInfo = $jethelper->getShipped_Cancelled_Qty($jetmodel);
        $totalOrderQty = 0;
        $totalPrevShippedQty = 0;
        $prevCancelledQty = 0;
        $totalJetOrderQty = 0;
        $totalJetcancelQty = 0;
        $realcancelQty = 0;
        $totalAvailQty = 0;
        foreach ($itemsData as $k => $valdata) {
            // check available Quantity
            if ($valdata[7] <= 0) {
                continue;
            }
            $totalAvailQty += (int)$valdata[7];
            $totalOrderQty += (int)$valdata[1];
            $cancelQty = isset($valdata[2]) ? (int)$valdata[2]  : 0 ;
            $totalJetOrderQty += (int)$valdata[6];
            $totalJetcancelQty += (int)$valdata[2];
            if ($prevShipItemsInfo) {
                $prevShippedQty = $prevShipItemsInfo[$valdata[0]]['response_shipment_sku_quantity'];
                $prevCancelledQty = $prevShipItemsInfo[$valdata[0]]['response_shipment_cancel_qty'];
                $validate = $jethelper->validateShipment(
                    (int)$valdata[1],
                    (int)$prevShippedQty,
                    (int)$prevCancelledQty,
                    (int)$valdata[6],
                    (int)$valdata[2],
                    (int)$valdata[0]);
                $totalPrevShippedQty += $prevShippedQty;
                if ($validate != "clear") {
                    $this->messageManager->addError($validate);
                    break;
                }
            }
            $realcancelQty += $prevCancelledQty ;
            // Auto generate Shipment Id of every item
            $time = time() + ($k + 1);
            $shipId = implode("-", str_split($time, 3));
            $updatedQty = (int)$valdata[6];

            if ($valdata[3] == 1 && $updatedQty > 0) {
                $rma = isset($valdata[4]) ? $valdata[4] : "";
                $dayReturn = isset($valdata[5]) ? (int)$valdata[5] : 0;
                if (($cancelQty <= (int)$valdata[7]) && ($cancelQty != 0)) {
                        $shippmentArray[] = [
                        'merchant_sku' => $valdata[0],
                        'response_shipment_sku_quantity' => $updatedQty,
                        'response_shipment_cancel_qty' => $cancelQty,
                        'RMA_number' => "$rma",
                        'days_to_return' => $dayReturn,
                        'return_location' => $returnLocation
                        ];
                    if ($updatedQty != 0) {
                        $shipQtyForOrder[$valdata[0]] = $updatedQty;
                    }
                    if ($cancelQty != 0) {
                        $cancelQtyForOrder[$valdata[0]] = $cancelQty;
                    }
                } else {
                    $shippmentArray[] = [
                        'merchant_sku' => $valdata[0],
                        'response_shipment_sku_quantity' => $updatedQty,
                        'response_shipment_cancel_qty' => $cancelQty,
                        'RMA_number' => "$rma",
                        'days_to_return' => $dayReturn,
                        'return_location' => $returnLocation
                        ];
                    if ($updatedQty != 0) {
                        $shipQtyForOrder[$valdata[0]] = $updatedQty;
                    }
                    if ($cancelQty != 0) {
                        $cancelQtyForOrder[$valdata[0]] = $cancelQty;
                    }
                }
            } else {
                $shippmentArray[] = [
                    'merchant_sku' => $valdata[0],
                    'response_shipment_sku_quantity' => $updatedQty,
                    'response_shipment_cancel_qty' => (int)$cancelQty
                    ];
                if ($updatedQty != 0) {
                    $shipQtyForOrder[$valdata[0]] = $updatedQty;
                }
                if ($cancelQty != 0) {
                    $cancelQtyForOrder[$valdata[0]] = $cancelQty;
                }
            }
        }
        $uniqueRandomNo = $id.mt_rand(10, 10000);
        $dataShip = [];
        $zip = trim($this->scopeConfigManager->getValue('jetconfiguration/return_location/zip_code'));
        $checkShipdata = false;
        foreach ($shippmentArray as $value) {
            if (isset($value['response_shipment_sku_quantity'])) {
                if ($value['response_shipment_sku_quantity']) {
                    $checkShipdata = true;
                }
            }
        }
        if ($checkShipdata) {
            $dataShip['shipments'][] = [
                'alt_shipment_id' => $uniqueRandomNo,
                'shipment_tracking_number' => $tracking,
                'response_shipment_date' => $shipToDate,
                'response_shipment_method' => $method,
                'expected_delivery_date' => $expDelivery,
                'ship_from_zip_code' => $zip,
                'carrier_pick_up_date' => $carrPickDate,
                'carrier' => $carrier,
                'shipment_items' => $shippmentArray
            ];
        } else {
            $dataShip['shipments'][] = [
                'alt_shipment_id' => $uniqueRandomNo,
                'shipment_items' => $shippmentArray
            ];
        }
        $orderIsComplete = false;
        $cancelOrder = false;
        $cancelRestOrder = false;        

        if ($dataShip) {
            if (($totalJetOrderQty + $totalJetcancelQty + $realcancelQty + $totalPrevShippedQty) == $totalOrderQty) {
                $orderIsComplete = true;
            }
            if (($totalJetcancelQty == $totalOrderQty) && (!$prevShipItemsInfo)) {
                $cancelOrder = true;
            }
            if ($prevShipItemsInfo && ($totalJetcancelQty == $totalAvailQty)) { 
                $cancelRestOrder = true;
            }
            
            $data = $datahelper->CPutRequest('/orders/' . $orderId . '/shipped', json_encode($dataShip));           

            $responsedata = json_decode($data);
            if ((($responsedata == NULL) || ($responsedata == "")) && ($jetOrderRow)) {
                /*$check = $this->validateShipmentData($datahelper, $merchantOrderId, $shippmentArray, $uniqueRandomNo);
                if (!$check) {
                    $this->getResponse()->setBody("Something went wrong. Shippment Details not updated on Jet");
                    return;
                }*/
                $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($id);
                $itemQty = [];
                $itemQtytoCancel = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $shipSku = $item->getSku();
                    if (isset($shipQtyForOrder[$shipSku])) {
                        $itemQty[$item->getId()] = $shipQtyForOrder[$shipSku];
                    }
                    if (isset($cancelQtyForOrder[$shipSku])) {
                        $itemQtytoCancel[$item->getId()] = $cancelQtyForOrder[$shipSku];
                    }
                }
                try {
                    if (!empty($itemQty)) {
                            if ($order->canShip()) {
                                $orderhelper->generateShipment($order, $itemQty);
                            }
                        }
                        if (!empty($itemQtytoCancel)) {
                            $orderhelper->generateCreditMemo($order, $itemQtytoCancel);
                        }
                        $orderhelper->saveJetShipData($jetmodel, $dataShip, $cancelOrder, $orderIsComplete);

                    if ($cancelRestOrder && $orderIsComplete) {
                        $this->messageManager->addSuccess('Your Jet Order ' . $id . ' has been Completed');
                    } elseif ($cancelOrder && $orderIsComplete) {
                        $this->messageManager->addError('Your Jet Order ' . $id . ' has been Cancelled' );
                    } elseif ($orderIsComplete) {
                        $this->messageManager->addSuccess('Your Jet Order ' . $id . ' has been Completed');
                    } elseif ($cancelOrder) {
                        $this->messageManager->addError(
                            'Order Cancellation request for cancelled item(s) has been sent for Jet Order ' . $id );
                    } else {
                        $this->messageManager->addSuccess('Your Jet Order ' . $id . ' is under progress on Jet .');
                    }
                    $this->getResponse()->setBody("Success");
                    return;
                } catch (\Exception $e) {
                     $this->getResponse()->setBody($e->getMessage());
                    return;
                }
            } else {
                foreach ($responsedata as $key => $value) {
                    $this->getResponse()->setBody($value);
                }
                return;
            }
        } else {
            $msg = "You have no information to Ship on Jet.com";
            $this->getResponse()->setBody($msg);
            return;
        }
    }

    /**
     * Validate data on jet
     * @param $datahelper
     * @param $merchantOrderId
     * @param $shippmentArray
     * @param $uniqueRandomNo
     * @return bool
     */
    public function validateShipmentData($datahelper, $merchantOrderId, $shippmentArray, $uniqueRandomNo)
    {
        $getData = [];
        $getJetData = $datahelper->CGetRequest(
                '/portal/analytics/order/'.$merchantOrderId.'/history');
        $getData = json_decode($getJetData, true);
        if (isset($getData)) {
            foreach ($getData as $orderData) {
                if ($orderData['eventType'] == 'order_shipped') {
                    if (isset($orderData['payload']['shipments'][0]['alt_shipment_id']) && $orderData['payload']['shipments'][0]['alt_shipment_id'] == $uniqueRandomNo) {
                        $jetData = isset($orderData['payload']['shipments'][0]['shipment_items']) ? $orderData['payload']['shipments'][0]['shipment_items'] : [];
                        break;
                    }
                }
            }
        }
        $status = $jetData == $shippmentArray ? true : false;
        return $status;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Jet::jet_orders');
    }
}
