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
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory ,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultForwardFactory= $resultForwardFactory;
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
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function execute()
    {
        $refund_mer_orderid=$this->getRequest()->getPost('refund_orderid');
        $skudetails=$this->getRequest()->getParam('sku_details');

        $helper=$this->_objectManager->create('Ced\Jet\Helper\Data');
        if ($this->getRequest()->getParam('refund_orderid')=="") {
            $this->messageManager->addError(__('Please enter Refund Order Id.'));
            $this->_redirect('*/*/new');
            return;
        }
        if (!$this->getRequest()->getParam('sku_details')) {
            $this->messageManager->addError(__(
                'Please select any Item of Order to refund.'));
            $this->_redirect('*/*/new');
            return;
        }
        $orderid="";
        $merchantid="";
        $sku_details= [];
        $items= [];
        $orderid=$this->getRequest()->getPost('refund_orderid');
        $orderid=trim($orderid);
        $magento_order_id=0;

        $magento_order_id=$helper->getMagentoIncrementOrderId($refund_mer_orderid,$refund_mer_orderid);
        if ($magento_order_id!=null) {
            if (is_numeric($magento_order_id) &&  $magento_order_id == 0) {
                $this->messageManager->addError(__('Incomplete information of
		    	 Order in Refund.'));
                $this->_redirect('*/*/new');
                return;
            }
            if (is_numeric($magento_order_id)==false  &&  $magento_order_id == '') {
                $this->messageManager->addError(__('Incomplete information of Order in
		    	 Refund.'));
                $this->_redirect('*/*/new');
                return;
            }
        } else {
            $this->messageManager->addError(__('Incomplete information of Order in 
		   		Refund.'));
            $this->_redirect('*/*/new');
            return;
        }
        $order ="";
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magento_order_id);
        if (!$order->getId()) {
            $this->messageManager->addError(__('Order not Exists for this refund.'));
            $this->_redirect('*/*/new');
            return;
        }
        if (empty($skudetails)) {
            $this->messageManager->addError(__('Please select any Item of Order to 
				refund.'));
            $this->_redirect('*/*/new');
            return;
        }
        $i=0;
        $jetHelper=$this->_objectManager->create('Ced\Jet\Helper\Jet');

        foreach ($skudetails as $detail) {
            $refundQtyData=$jetHelper->getUpdatedRefundQty($refund_mer_orderid);

            if (isset($refundQtyData[$detail['merchant_sku']])){

                if(($refundQtyData[$detail['merchant_sku']] + $detail['refund_quantity'])
                    >	$detail['qty_requested']) {
                    if ($refundQtyData[$detail['merchant_sku']] == '') {
                        $this->messageManager->addError(
                            "Refund quantity can't be greater than requested quantity for sku : "
                            .$detail['merchant_sku']);
                    } else {
                        $this->messageManager->addError("Refund quantity can't be greater 
		            	than requested quantity for sku : ".$detail['merchant_sku']." .
		            	Quantity already processed for refund : ".$refundQtyData[$detail['merchant_sku']]);
                    }
                    $this->_redirect('*/*/new');
                    return;
                }
            }
            $this->ValidateDetails($detail);
            if ($detail['refund_quantity']<=0) {
                continue;
            }

            $check=[];
            $check=$helper->getRefundedQtyInfo($order,$detail['merchant_sku']);
            if ($check['error']=='1') {
                $error_msg="";
                $error_msg="Error for Order Item with sku : ".$detail['merchant_sku']." ";
                $error_msg=$error_msg.$check['error_msg'];
                $this->messageManager->addError($error_msg);
                $this->_redirect('*/*/new');
                return;
            }

            $qty_already_refunded=0;
            $available_to_refund_qty=0;
            $qty_ordered=0;

            $qty_already_refunded=$check['qty_already_refunded'];
            $available_to_refund_qty=$check['available_to_refund_qty'];
            $qty_ordered=$check['qty_ordered'];

            $items['items'][$i]['order_item_id']=trim($detail['order_item_id']);
            $items['items'][$i]['total_quantity_returned']=(int)trim(
                $detail['return_quantity']);
            $items['items'][$i]['order_return_refund_qty']=(int)trim(
                $detail['refund_quantity']);
            $items['items'][$i]['refund_reason']=trim(
                $detail['return_refundreason']);
            $items['items'][$i]['refund_feedback']=trim(
                $detail['return_refundfeedback']);
            $items['items'][$i]['refund_amount']['principal']=(float)trim($detail['return_principal']);
            $items['items'][$i]['refund_amount']['shipping_tax']=(float)trim($detail['return_shipping_tax']);
            $items['items'][$i]['refund_amount']['shipping_cost']=(float)trim($detail['return_shipping_cost']);
            $items['items'][$i]['refund_amount']['tax']=(float)trim($detail['return_tax']);
            $i++;
        }

        if ($i==0) {
            $this->messageManager->addError("Some information missing.Refund not generated");
            $this->_redirect('*/*/new');
            return;
        }
        if (empty($items)) {
            $this->messageManager->addError("Items information missing.Refund not generated.");
            $this->_redirect('*/*/new');
            return;
        }
        $saved_data= [];
        $saved_data=$this->getRequest()->getParams();
        $saved_data=serialize($saved_data);
        $time=time();

        $response = $helper->CPostRequest('/refunds/'.$orderid.'/'.$time.'',
            json_encode($items));

        $response=json_decode($response);
        $error_array=[];
        if (isset($response->errors))
            $error_array = $response->errors;
        if (!empty($error_array)) {
            // Now Show error to same panel
            $err_msg = "";
            foreach ($error_array as $valerr) {
                $err_msg = $valerr.' | ';
            }
            $this->messageManager->addError('Invalid Values: '.$err_msg);
            // set form values
            $this->_redirect('*/*/refund');

        } else {
            $refund_authorisation_id="";
            $refund_authorisation_id=$response->refund_authorization_id;
            if (!empty($refund_authorisation_id)){
                $i=0;
                $status="";
                $status=$response->refund_status;
                $status=trim($status);

                $text = ['refund_orderid'=>$orderid,
                    'refund_merchantid'=>$merchantid,
                    'refund_merchantid'=>$refund_authorisation_id,
                    'refund_status'=>'created'];

                foreach ($skudetails as $detail) {
                    if (isset($detail['merchant_sku']))
                        $sku = $detail['merchant_sku'];

                }

                $model = $this->_objectManager->create('Ced\Jet\Model\Refund');
                //added
                $model->setData(
                    'quantity_returned', $detail['return_quantity']);
                $model->setData(
                    'refund_quantity',$detail['refund_quantity']);
                $model->setData('order_item_id',$magento_order_id);

                $model->setData('refund_orderid',$orderid);
                $model->setData('refund_merchantid',$merchantid);
                $model->setData('refund_status',$status);
                $model->setData('saved_data',$saved_data);
                $model->setData('refund_id',$refund_authorisation_id);
                $model->save();

                $collection = $this->_objectManager->create(
                    'Magento\Sales\Model\Order\Item')->getCollection()->addFieldToFilter(
                    'sku',$sku);
                foreach ($collection as $data)
                {
                    $id=$data->getItemId();
                }
                $model=$this->_objectManager->create(
                    'Magento\Sales\Model\Order\Item')->load($id);
                $model->setData(
                    'qty_refunded',$items['items'][$i]['order_return_refund_qty']);
                $model->save();
                $this->messageManager->addSuccess(
                    'Your Refund has been created successfully');
                $this->_redirect('*/refund/index');
                return;

            } else {
                $this->messageManager->addError('Value inserted by you is not correct');
                $this->_redirect('*/refund/index');
                return;
            }
        }

        $this->_redirect('*/*/new');
    }

    /**
     * Validate details
     * @param $detail
     * @return void
     */

    public function ValidateDetails($detail)
    {
        if ($detail['return_quantity']=="" || $detail['return_quantity']<0) {
            $this->messageManager->addError("Please enter Qty Returned for sku : 
				".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['refund_quantity']=="") {
            $this->messageManager->addError("Please enter Qty Refunded for sku : 
				".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }

        if ($detail['return_principal']<0) {
            $this->messageManager->addError("Please enter  correct Refund Amount 
				for sku : ".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['return_shipping_cost']<0) {
            $this->messageManager->addError(
                "Please enter  correct Refund Shipping Cost for sku : "
                .$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['return_shipping_tax']<0) {
            $this->messageManager->addError(
                "Please enter  correct Refund Shipping Tax for sku : "
                .$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['return_tax']<0) {
            $this->messageManager->addError("Please enter  correct Refund Tax for 
				sku : ".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['return_refundreason']=="") {
            $this->messageManager->addError("Please enter Refund reason for sku :
				".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
        if ($detail['return_refundfeedback']=="") {
            $this->messageManager->addError("Please enter Refund reason for sku :
			 ".$detail['merchant_sku']);
            $this->_redirect('*/*/new');
            return;
        }
    }
}
