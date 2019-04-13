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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Controller\Adminhtml\Refund;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Getchildhtml extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Getchildhtml constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
    }

    /**
     * Index Action
     * @return
     */

    public function execute()
    {
        if ($this->getRequest()->getParam('mer_id')) {
            $helper = $this->_objectManager->create('Ced\Jet\Helper\Jet');
            $msg['success']="";
            $msg['error']="";
            $magento_order_id="";
            $order_data='';
            $shipment_data='';
            $merchant_order_id=$this->getRequest()->getParam('mer_id');
            $merchant_order_id=trim($merchant_order_id);
            if ($merchant_order_id) {
                $return = $this->CheckReturnAlreadyGenerated($merchant_order_id);
                if ($return === false) {
                    return $return;
                }
            }
            if (!isset($merchant_order_id)) {
                $msg['error']="Please enter merchant order id :".$merchant_order_id;
                return $this->getResponse()->setBody( json_encode($msg) );
            }
            $collection="";
            try{
                $collection=$this->_objectManager->get('Ced\Jet\Model\JetOrders')->getCollection();
                $collection->addFieldToFilter( 'merchant_order_id', $merchant_order_id );
                if ($collection->getSize()>0) {
                    foreach ($collection as $coll) {
                        $magento_order_id=$coll->getData('magento_order_id');
                        $order_data=$coll->getData('order_data');
                        $shipment_data=$coll->getData('shipment_data');
                        break;
                    }
                } else {
                    $collection=$this->_objectManager->create('Ced\Jet\Model\JetOrders')->getCollection();
                    $collection->addFieldToFilter( 'reference_order_id', $merchant_order_id );
                    if ($collection->getSize()>0) {
                        foreach ($collection as $coll) {
                            $magento_order_id=$coll->getData('magento_order_id');
                            $order_data=$coll->getData('order_data');
                            $shipment_data=$coll->getData('shipment_data');
                            break;
                        }
                    }
                }

                $updated_refundqty_data=$helper->getUpdatedRefundQty($merchant_order_id);

                $refundcollection=$this->_objectManager->create('Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter('refund_orderid', $merchant_order_id );
                $refund_qty= [];
                if ($refundcollection->getSize()>0) {
                    foreach ($refundcollection as $coll) {
                        $refund_data = unserialize($coll->getData('saved_data'));
                    }
                }

                if ($magento_order_id == "" || $order_data == '') {
                    $msg['error']="Order not found.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }

                $order_decoded_data="";
                $items_data= [] ;
                $order_decoded_data=unserialize($order_data);
                if (is_object($order_decoded_data) && !empty($order_decoded_data->order_items)) {
                    foreach ($order_decoded_data->order_items as $value) {
                        $items_data[]=$value;
                    }
                } else {

                    $msg['error']="Items Not found in Selected Order.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );

                }

                if (count($items_data)<=0) {
                    $msg['error']="Items Data not found for selected Order.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }
                $order ="";
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magento_order_id);
                if (!$order->getId()) {

                    $msg['error']="Order data not found.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }

                if ($order->getStatus()!='complete') {
                    $msg['error']="Can't generate refunds for incompleted orders.This order is incomplete.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }
                $return_flag=false;
                $helper=$this->_objectManager->create('Ced\Jet\Helper\Data');
                $error_msg='';
                $j=0;
                foreach ($items_data as $item) {
                    $merchant_sku="";
                    $merchant_sku=$item->merchant_sku;

                    $check = [];
                    $check=$helper->getRefundedQtyInfo($order,$merchant_sku);

                    if ($check['error']=='1') {
                        $error_msg=$error_msg."Error for Order Item with sku : ".$merchant_sku."-> ";
                        $error_msg=$error_msg.$check['error_msg'];
                        continue;
                    }
                    $j++;
                }
                if ($j==0) {
                    $msg['error']=$error_msg;
                    return $this->getResponse()->setBody( json_encode($msg) );
                }

                $resultPage = $this->resultPageFactory->create();
                $html=$resultPage->getLayout()
                    ->createBlock('Ced\Jet\Block\Adminhtml\Refund')->setTemplate("refund/refundhtml.phtml")
                    ->setData('items_data',$items_data)
                    ->setData('helper',$helper)
                    ->setData('order',$order)
                    ->setData('merchant_order_id',$merchant_order_id)
                    ->setData('refundtotalqty',$updated_refundqty_data)
                    ->setData('objectManager',$this->_objectManager)
                    ->toHtml();
                $msg['success']=$html;
                $this->getResponse()->setBody(
                    json_encode($msg)
                );
                return;
            }catch(\Exception $e) {
                $msg['error']=$e->getMessage();
                return $this->getResponse()->setBody( json_encode($msg) );

            }


        } else {
            $msg['error']="Merchant Order Id not found.Please enter again.";
            return $this->getResponse()->setBody( json_encode($msg) );

        }




    }

    public function CheckReturnAlreadyGenerated($merchant_order_id)
    {
        $collection = $this->_objectManager->create('Ced\Jet\Model\OrderReturn')->getCollection()->addFieldToSelect('merchant_order_id')
            ->addFieldToFilter('merchant_order_id',$merchant_order_id);

        if ($collection->getSize() > 0)
        {
            $msg['error']="Return already generated for merchant order id :".$merchant_order_id;
            return $this->getResponse()->setBody( json_encode($msg) );
        } else {
            return true;
        }
    }
    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Jet::jet_refund');
    }

}
