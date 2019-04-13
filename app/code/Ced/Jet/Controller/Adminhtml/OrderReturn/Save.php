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

namespace Ced\Jet\Controller\Adminhtml\OrderReturn;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    public $_objectManager;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    public $resultRedirectFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */
    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect | void
     */

    public function execute()
    {
        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        if ($this->getRequest()->getParam('id') && $this->getRequest()->getParam('returnid')) {
            $returnId = $this->getRequest()->getParam('returnid');
            $id = trim($this->getRequest()->getParam('id'));
            $dataShip = [];
            $detailSavedAfter = $this->getRequest()->getParams();
            $detailSavedAfter = $helper->prepareDataAfterSubmitReturn($detailSavedAfter, $id);
            $orderId = $this->getRequest()->getParam('merchant_order_id');
            $orderId = trim($orderId);
            $mageOrderId = $helper->getMagentoIncrementOrderId($orderId);
            $this->checkMagentoOrderId($id, $mageOrderId);
            $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($mageOrderId);
            if (!$order->getId()) {
                $this->messageManager->addError("Order not Exists for this return.");
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;

            }
            $refundFlag = $helper->checkOrderInRefund($orderId);
            if ($refundFlag) {
                $this->messageManager->addError(
                    "Order Refund is already exists.Can't generate return");
                $this->_redirect('adminhtml/adminhtml_jetorder/return');
                return;
            }
            $dataShip['merchant_order_id'] = $orderId;
            $status = $this->getRequest()->getParam('agreeto_return');
            $agreeToReturn = $status == 0 ? false : true;
            $dataShip['agree_to_return_charge'] = $agreeToReturn;
            $skuDetail = $this->getRequest()->getParam('sku_details');
            if (!empty($skuDetail)) {
                $shipItems = $this->getShipItems($skuDetail, $id, $order);
                if (empty($shipItems)) {
                    $this->messageManager->addError("No item's 'Want to Send' is selected Yes to send its data to Jet.com");
                    $this->_redirect('*/*/edit', ['id' => $id]);
                    return;
                }
                if (is_array($shipItems)) {
                    $dataShip['items'] = $shipItems;
                }

                $dataShip['agree_to_return_charge'] = true;
                $returndetails = $helper->CGetRequest("/returns/state/" . rawurlencode($returnId));
                if ($returndetails) {
                    $this->SaveData($returndetails, $id, $returnId, $dataShip, $detailSavedAfter);

                }
            } else {
                $this->messageManager->addError("Return Data Missing.Please try again.");
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            }
        } else {
            $this->messageManager->addError("Return Id not found.");
            $this->_redirect('adminhtml/adminhtml_jetorder/return');
            return;
        }
    }

    /**
     * Check magento order id
     * @param $id
     * @param $mageOrderId
     * @return void
     */

    public function checkMagentoOrderId($id, $mageOrderId)
    {
        if ($mageOrderId != null) {

            if (is_numeric($mageOrderId) && $mageOrderId == 0) {
                $this->messageManager->addError(
                    'Incomplete information of Order in Return.');
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            }

            if (is_numeric($mageOrderId) == false && $mageOrderId == '') {
                $this->messageManager->addError(
                    'Incomplete information of Order in Return.');
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            }
        } else {
            $this->messageManager->addError(
                'Incomplete information of Order in Return.');
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;

        }
    }

    /**
     * @param $skuDetails
     * @param $id
     * @param $order
     * @return array|void
     */

    public function getShipItems($skuDetails, $id, $order)
    {
        $count = 0;
        foreach ($skuDetails as $detail) {
            if ($detail['changes_made'] == '1') {
                continue;
            }
            $this->compRefundReturnQty($id, $detail);
            $check = [];
            $check = $this->_objectManager->get(
                'Ced\Jet\Helper\Data')->getRefundedQtyInfo($order, $detail['merchant_sku']);
            $this->checkRefundQty($check, $id, $detail);
            if ($detail['refund_quantity'] < 0) {
                continue;
            }
            $arr = [];
            $arr['order_item_id'] = $detail['order_item_id'];
            $arr['total_quantity_returned'] = (int)trim($detail['return_quantity']);
            $arr['order_return_refund_qty'] = (int)trim($detail['refund_quantity']);
            $arr['return_refund_feedback'] = "";
            if ($detail['return_refundfeedback'] != "") {
                $arr['return_refund_feedback'] = $detail['return_refundfeedback'];

            }
            $returnPrincipal = (float)trim($detail['return_principal']);
            $returnShippingTax = (float)trim($detail['return_shipping_tax']);
            $returnShippingCost = (float)trim($detail['return_shipping_cost']);
            $returnTax = (float)trim($detail['return_tax']);
            $this->checkAmountCostTax($returnPrincipal, $returnShippingTax, $returnTax, $returnShippingCost, $id);
            $arr['refund_amount'] = [
                'principal' => $returnPrincipal,
                'tax' => $returnTax,
                'shipping_tax' => $returnShippingTax,
                'shipping_cost' => $returnShippingTax
            ];
            $shipItems[] = $arr;
            $count++;
        }
        if ($count == 0) {
            return;
        } else {
            return $shipItems;
        }
    }

    /**
     * @param $check
     * @param $id
     * @param $detail
     * @return void
     */
    public function checkRefundQty($check, $id, $detail)
    {
        if ($check['error'] == '1') {
            $error = "Error for Order Item with sku : " . $detail['merchant_sku'] . " ";
            $error = $error . $check['error_msg'];
            $this->messageManager->addError($error);
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }
        $availRefundQty = 0;
        if (isset($check['qty_already_refunded'])) {
            $availRefundQty = $check['available_to_refund_qty'];
        }
        if ($detail['refund_quantity'] > $availRefundQty) {
            $this->messageManager->addError("Error to generate return for sku : " . $detail['merchant_sku'] . " -> Qty Refunded is greater than Qty Available for Refund.");
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }
        return;
    }

    /**
     * @param $id
     * @param $detail
     * @return void
     */
    public function compRefundReturnQty($id, $detail)
    {
        if ($detail['return_quantity'] == "") {
            $this->messageManager->addError("Please enter Qty Returned.");
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }
        if ($detail['refund_quantity'] == "") {
            $this->messageManager->addError("Please enter Qty Refunded.");
            $this->_redirect('*/*/edit', []);
            return;
        }
        $detail['return_quantity'] = (int)trim($detail['return_quantity']);
        $detail['refund_quantity'] = (int)trim($detail['refund_quantity']);
        if (is_numeric($detail['refund_quantity']) && $detail['refund_quantity'] >= 0 && $detail['refund_quantity'] <= $detail['return_quantity']) {

        } else {
            $this->messageManager->addError("Please enter correct value to Qty Refunded.");
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }
        return;
    }

    /**
     * @param $returnprincipal
     * @param $returnshippingtax
     * @param $returntax
     * @param $returnshippingcost
     * @param $id
     */

    public function checkAmountCostTax($returnprincipal, $returnshippingtax, $returntax, $returnshippingcost, $id)
    {
        if ($returnprincipal === "" || $returnprincipal < 0 || $returnshippingtax === "" || $returnshippingtax < 0 || $returntax === "" || $returntax < 0 || $returnshippingcost === "" || $returnshippingcost < 0) {
            $this->messageManager->addError("Please enter correct values in Amount,Shipping cost,Shipping tax or Tax.");
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }
        return;
    }

    /**
     * @param $returndetails
     * @param $id
     * @param $returnId
     * @param $dataShip
     * @param $detailSavedAfter
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function SaveData($returndetails, $id, $returnId, $dataShip, $detailSavedAfter)
    {
        $return = json_decode($returndetails);
        if (isset($return->return_status)) {
            if ($return->return_status == "completed by merchant") {
                $model = $this->_objectManager->create('Ced\Jet\Model\OrderReturn')->load($id);
                $model->setData('status', 'completed')->save();
                $this->messageManager->addSuccess('Your return has been posted to Jet successfully.');
                return $this->_redirect('*/*/index');
            } else {
                $data = $this->_objectManager->get('Ced\Jet\Helper\Data')->CPutRequest('/returns/' . $returnId . '/complete', json_encode($dataShip));
                $responsedata = json_decode($data);
                if (empty($responsedata) || $responsedata == "") {
                    $model = "";
                    $detailSavedAfter = $this->_objectManager->get('Ced\Jet\Helper\Data')->saveChangesMadeValue($detailSavedAfter);
                    $detailSavedAfter = serialize($detailSavedAfter);
                    try {
                        $model = $this->_objectManager->create('Ced\Jet\Model\OrderReturn')->load($id);
                        $model->setData('status', 'inprogress');
                        $model->setData('details_saved_after', $detailSavedAfter);
                        $model->save();
                        $this->messageManager->addSuccess('Return submitted successfully to Jet');
                        $this->_redirect('*/*/index');
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                    return;
                }
                if (!empty($responsedata->errors)) {
                    $error_data = $responsedata->errors;
                    $str = "";
                    foreach ($error_data as $val) {
                        if ($str == "") {
                            $str = $val;
                        } else {
                            $str = $str . "<br/>" . $val;
                        }
                    }
                    $this->messageManager->addError($str);
                    $this->_redirect('*/*/edit', ['id' => $id]);
                    return;
                }
            }
        }
        $this->messageManager->addError("Return Data Missing.Please try again.");
        $this->_redirect('*/*/edit', ['id' => $id]);
        return;
    }
}
