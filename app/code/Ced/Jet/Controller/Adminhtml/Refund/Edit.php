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

namespace Ced\Jet\Controller\Adminhtml\Refund;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Jet::jet_refund');
    }

    /**
     * Init actions
     * @var \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function _initAction()
    {

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Jet::jet')->addBreadcrumb(__('Refund'), __('Refund'))->addBreadcrumb(__('Refund'), __('Refund'));

        return $resultPage;

    }

    /**
     * Edit grid record
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $refundModel = $this->_objectManager->get('Ced\Jet\Model\Refund')->load($id);
        if ($refundModel->getId() || $id == 0) {
            $this->_coreRegistry->register('ced_refund_data', $refundModel);
            $resultPage = $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend(__('Refunds'));
            if ($refundModel->getRefundOrderid()) {
                $helper=$this->_objectManager->create('Ced\Jet\Helper\Jet');
                $msg['success']="";
                $msg['error']="";
                $magento_order_id="";
                $order_data='';
                $shipment_data='';
                $merchant_order_id=$refundModel->getRefundOrderid();
                $merchant_order_id=trim($merchant_order_id);
                try{
                    $collection=$this->_objectManager->get(
                        'Ced\Jet\Model\JetOrders')->getCollection()->addFieldToFilter(
                        'merchant_order_id', $merchant_order_id);
                    if ($collection->getSize()>0) {
                        $orderData = $collection->getData();
                        $magento_order_id = $orderData[0]['magento_order_id'];
                        $order_data = $orderData[0]['order_data'];

                    } else {
                        $collection = $this->_objectManager->get(
                            'Ced\Jet\Model\JetOrders')->getCollection()->addFieldToFilter(
                            'reference_order_id', $merchant_order_id);
                            if ($collection->getSize()>0) {
                                $orderData = $collection->getData();
                                $magento_order_id = $orderData[0]['magento_order_id'];
                                $order_data = $orderData[0]['order_data'];
                            }
                    }
                    $updated_refundqty_data=$helper->getUpdatedRefundQty(
                        $merchant_order_id);

                    $refund_qty= [];
                    $refund_data = $this->RefundData($merchant_order_id);

                    if ($magento_order_id == "" || $order_data == '') {
                        $this->messageManager->addError(
                            "Order not found.Please enter correct
				 Order Id.");
                        $this->_redirect('*/*/index');
                        return;
                    }


                    $order_decoded_data="";
                    $items_data= [];
                    $order_decoded_data=unserialize($order_data);
                    $items_data = $this->ItemsData($order_decoded_data);
                    if (empty($items_data)) {
                        $this->messageManager->addError(
                            "Items Data not found for selected
				 Order.Please enter correct Order Id.");
                        $this->_redirect('*/*/index');
                        return;
                    }
                    $order ="";
                    $order = $this->_objectManager->create(
                        'Magento\Sales\Model\Order')->loadByIncrementId(
                        $magento_order_id);
                    if (!$order->getId()) {
                        $this->messageManager->addError(
                            "Order data not fou nd.Please enter
				 correct Order Id.");
                        $this->_redirect('*/*/index');
                        return;
                    }

                    if ($order->getStatus()!='complete') {
                        $this->messageManager->addError(
                            "Can't generate refunds for 
				incompleted orders.This order is incomplete.");
                        return;
                    }
                    $return_flag=false;
                    $helper=$this->_objectManager->create('Ced\Jet\Helper\Data');
                    $error_msg='';
                    $j=0;
                    foreach ($items_data as $item) {
                        $merchant_sku="";
                        $merchant_sku=$item->merchant_sku;

                        $check= [];
                        $check=$helper->getRefundedQtyInfo($order,$merchant_sku);



                        if ($check['error']=='1') {
                            $error_msg=$error_msg."Error for Order Item with sku : ".$merchant_sku."-> ";
                            $error_msg=$error_msg.$check['error_msg'];
                            continue;
                        }
                        $j++;
                    }
                    return $resultPage;
                } catch(\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/index');
                    return;
                }
            } else {
                return $resultPage;
            }
            return $resultPage;
        }
    }

    /**
     * get Refund Data
     * @param $merchant_order_id
     * @return array
     */

    public function RefundData($merchant_order_id = null)
    {
        $refund_data = [];
        $refundcollection=$this->_objectManager->create(
            'Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter(
            'refund_orderid', $merchant_order_id);
        if ($refundcollection->getSize()>0) {
            foreach ($refundcollection as $coll) {
                $refund_data = unserialize($coll->getData(
                    'saved_data'));
            }
        }
        return $refund_data;
    }

    /**
     * get Items Data
     * @param $order_decoded_data
     * @return array
     */

    public function ItemsData($order_decoded_data)
    {
        $items_data = [];
        if (is_object($order_decoded_data) && !empty($order_decoded_data->order_items)) {
            foreach ($order_decoded_data->order_items as $value) {
                $items_data[]=$value;
            }
        }
        return $items_data;
    }
}
