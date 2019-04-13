<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Controller\Adminhtml\OrderReturn;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *@var \Magento\Framework\Registry
     *
     */
    protected $_coreRegistry = null;

    /**
     *@var \Magento\Framework\View\Result\PageFactory
     *
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }
    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     * @return \Magento\Backend\Model\View\Result\Page
     */

    protected function _initAction()
    {	 /** @var \Magento\Backend\Model\View\Result\Page $resultPage */


        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Jet::jet')
            ->addBreadcrumb(__('Return'), __('Return'))
            ->addBreadcrumb(__('Manage Return'), __('Manage Return'));
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {
        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $id = $this->getRequest()->getParam('id');
        $returnModel = $this->_objectManager->get( 'Ced\Jet\Model\OrderReturn')->load($id);
        $refund_flag = false;
        if ($returnModel->getId() || $id == 0) {
            $return_data = [];
            $resulting_data = [];
            if ($returnModel->getData('details_saved_after') != "") {
                $details_saved_after = "";
                $details_saved_after = $returnModel->getData('details_saved_after');
                $return_data = "";
                $return_data = unserialize($details_saved_after);
                $return_data['merchant_order_id'] = $return_data['merchant_order_id'];
                $magento_order_id = $helper->getMagentoIncrementOrderId($return_data['merchant_order_id']);
                if ($magento_order_id == 0)  {
                    $this->messageManager->addError('Incomplete information of Order in Return');
                    $this->_redirect('*/*/index');
                    return;
                }
                $order = "";
                $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($magento_order_id);
                if (!$order->getId()) {
                    $this->messageManager->addError("Order not Exists for this return.");
                    $this->_redirect('*/*/index');
                    return;
                }
                $resulting_data = $return_data;
                $resulting_data['status'] = $returnModel->getData('status');
                $view_case_for_return = false;
                $view_case_for_return = $helper->checkViewCaseForReturn($return_data);
                if (!$view_case_for_return) {
                    $skus = "";
                    $skus = $return_data['sku_details'];
                    foreach ($skus as $key => $detail) {
                        $orderItem = "";
                        $qty_refunded = 0;
                        $orderItem = $order->getItemsCollection()->addFieldToFilter('sku', $detail['merchant_sku']);
                        foreach ($orderItem as $data) {
                            $qty_refunded = intval($data->getQtyRefunded());
                        }
                        if ($qty_refunded > 0) {
                            $this->messageManager->addError("Order Item with sku : " . $detail['merchant_sku'] . " is refunded without using Return Functionality.");
                            $this->_redirect('*/*/index');
                            return;
                        }
                    }
                }
            } elseif ($returnModel->getData('return_details') != "") {
                $return_ser_data = "";
                $return_ser_data = $returnModel->getData('return_details');
                $return_data = "";
                $return_data = unserialize($return_ser_data);
                $resulting_data['status'] = $returnModel->getData('status');
                $resulting_data['id'] = $returnModel->getData('id');
                $resulting_data['returnid'] = $returnModel->getData('returnid');
                $resulting_data['merchant_order_id'] = $return_data->merchant_order_id;
                $magento_order_id = $helper->getMagentoIncrementOrderId($return_data->merchant_order_id);
                if ($magento_order_id == 0)  {
                    $this->messageManager->addError('Incomplete information of Order in Return.');
                    $this->_redirect('*/*/index');
                    return;

                }
                $order = "";
                $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($magento_order_id);
                if (!$order->getId()) {
                    $this->messageManager->addError("Order not Exists for this return.");
                    $this->_redirect('*/*/index');
                    return;
                }
                $refund_flag = $helper->checkOrderInRefund($return_data->merchant_order_id);
                if ($refund_flag) {
                    $this->messageManager->addError("Refund of this order already exists.Return can't be generated.");
                    $this->_redirect('*/*/index');
                    return;
                }

                $resulting_data['merchant_return_authorization_id'] = $return_data->merchant_return_authorization_id;
                $resulting_data['merchant_return_charge'] = $return_data->merchant_return_charge;
                $resulting_data['reference_order_id'] = $return_data->reference_order_id;
                $resulting_data['reference_return_authorization_id'] = $return_data->reference_return_authorization_id;
                $resulting_data['refund_without_return'] = $return_data->refund_without_return;
                $resulting_data['return_date'] = $return_data->return_date;
                $resulting_data['return_status'] = $return_data->return_status;
                $resulting_data['shipping_carrier'] = $return_data->shipping_carrier;
                $resulting_data['tracking_number'] = $return_data->tracking_number;
                $i = 0;
                $error_msg = "";
                foreach ($return_data->return_merchant_SKUs as $sku_detail) {
                    $check = $helper->getRefundedQtyInfo($order, $sku_detail->merchant_sku);
                    $resulting_data['sku_details']["sku$i"]['changes_made'] = 0;
                    $resulting_data['sku_details']["sku$i"]['qty_already_refunded'] = isset($check['qty_already_refunded']) ? $check['qty_already_refunded'] : 0;
                    $resulting_data['sku_details']["sku$i"]['available_to_refund_qty'] = isset($check['available_to_refund_qty']) ? $check['available_to_refund_qty'] : 0;
                    $resulting_data['sku_details']["sku$i"]['qty_ordered'] = isset($check['qty_ordered']) ? $check['qty_ordered'] : 0;
                    $resulting_data['sku_details']["sku$i"]['order_item_id'] = $sku_detail->order_item_id;
                    $resulting_data['sku_details']["sku$i"]['return_quantity'] = $sku_detail->return_quantity;
                    $resulting_data['sku_details']["sku$i"]['merchant_sku'] = $sku_detail->merchant_sku;
                    $resulting_data['sku_details']["sku$i"]['reason'] = $sku_detail->reason;
                    $resulting_data['sku_details']["sku$i"]['return_principal'] = $sku_detail->requested_refund_amount->principal;
                    $resulting_data['sku_details']["sku$i"]['return_tax'] = $sku_detail->requested_refund_amount->tax;
                    $resulting_data['sku_details']["sku$i"]['return_shipping_cost'] = $sku_detail->requested_refund_amount->shipping_cost;
                    $resulting_data['sku_details']["sku$i"]['return_shipping_tax'] = $sku_detail->requested_refund_amount->shipping_tax;
                    $i++;

                }

                if ($i == 0) {
                    $this->messageManager->addError("No items found in return order.");
                }
            }
            $this->_coreRegistry->register('return_data', $resulting_data);

            $resultPage = $this->_initAction();


            $resultPage->addBreadcrumb('Return Manager', 'Return Manager');
            $resultPage->addBreadcrumb('Return Description', 'Return Description');
            $resultPage->getConfig()->getTitle()->prepend(__('Jet Returns'));
            return $resultPage;

        } else {
            $this->_coreRegistry->register('return_data', '');

            $this->messageManager->addError('Order not found');

            $this->_redirect('*/*/index');
        }
    }
}
