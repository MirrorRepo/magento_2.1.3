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
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Controller\Adminhtml\Order;

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
        $datahelper = $this->_objectManager->get('Ced\Ebay\Helper\Data');
        $orderhelper = $this->_objectManager->get('Ced\Ebay\Helper\Order');
        $ebayhelper = $this->_objectManager->get('Ced\Ebay\Helper\Ebay');
        
        // collect ship data
        $postData = $this->getRequest()->getPost();
        $shipTodatetime = strtotime($postData['ship_todate']);
        $deliverydate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime);
        $id = $postData['id'];
        $orderId = $postData['magento_orderid'];
        $incrementOrderId = $postData['incrementid'];
        $mageOrderId = $postData['magento_orderid'];
        $shippingCarrierUsed = $postData['carrier'];
        $ebayOrderId = $postData['ebayorderid'];
        $trackNumber = $postData['tracking'];
        $itemsData = json_decode($postData['items'],true);
        if (empty($itemsData)) {
            $this->getResponse()->setBody("You have no item in your Order.");
            return;
        }
        $shipData = [
            'ship_todate' => $shipTodatetime,
            'carrier' => $shippingCarrierUsed,
            'tracking' => $trackNumber,
            'items' => $itemsData
        ];
        $shipQtyForOrder = $cancelQtyForOrder = [];
        foreach ($itemsData as $value) {
            if ($value['ship_qty'] == $value['req_qty']) {
                $shipment = true;
            }
            if ($value['cancel_quantity'] == $value['req_qty']) {
                $shipment = false;
            }
            if ($value['ship_qty'] > 0) {
                $shipQtyForOrder[$value['sku']] = $value['ship_qty'];
            }
            if ($value['cancel_quantity'] > 0) {
                $cancelQtyForOrder[$value['sku']] = $value['cancel_quantity'];
            }
        }

        $ebayModel = $this->_objectManager->create('Ced\Ebay\Model\Orders')->load($id);
        $data = 'Success';
        $data = $datahelper->createShipmentOrderBody($ebayOrderId, $trackNumber, $shippingCarrierUsed, $deliverydate, $shipment);
        if ($data == 'Success') {
            $order = $this->_objectManager->get(
                    'Magento\Sales\Model\Order')->loadByIncrementId($incrementOrderId);
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
            if (!empty($itemQty)) {
                if ($order->canShip()) {
                    $orderhelper->generateShipment($order, $itemQty);
                }
            }
            if (!empty($itemQtytoCancel)) {
                $orderhelper->generateCreditMemo($order, $itemQtytoCancel);
            }
            $ebayModel->setStatus('shipped');
            $ebayModel->setShipmentData(serialize($shipData));
            $ebay->save();
            $this->messageManager->addSuccess('Your Jet Order ' . $incrementOrderId . ' has been Completed');
            $this->getResponse()->setBody("Success");
        } else {
            foreach ($responsedata as $key => $value) {
                $this->getResponse()->setBody($value);
            }
            return;
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Ebay::Ebay_orders');
    }
}
