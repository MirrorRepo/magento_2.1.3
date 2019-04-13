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
namespace Ced\Jet\Block\Adminhtml\OrderReturn\Edit\Tab;

/**
 * jet refund edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $_wysiwygConfig;


    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $id=$this->getRequest()->getParam('id');
        $data = $this->_coreRegistry->registry('return_data');
        $helper=$this->_objectManager->get('Ced\Jet\Helper\Data');
        $feedback_arr=$helper->feedbackOptArray();
        $reason_arr=$helper->refundreasonOptionArr();
        $yesno=$this->_objectManager->get('Magento\Config\Model\Config\Source\Yesno');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        if (!empty($data)) {
            $skus_details=$data['sku_details'];
            $fieldset = $form->addFieldset('jet_return',['legend'=> __('Return Information')]);
            
            $fieldset->addField('id', 'hidden', ['label'     =>  __('id'),
                'readonly' => true,
                'required'  => true,
                'name'      => 'id',
                'value'=>$data['id']]);
            
            $fieldset->addField(
                'returnid', 'text', ['label'     =>  __('Return Id'),
                'readonly' => true,
                'required'  => true,
                'name'      => 'returnid',
                'value'=>$data['returnid'],
                'note'=>'This is a return id on Jet.com for current order.']);
            
            $fieldset->addField(
                'merchant_order_id', 'text', ['label'     =>  __('Merchant Order Id'),
                'readonly' => true,
                'required'  => true,
                'name'      => 'merchant_order_id',
                'value'=>$data['merchant_order_id'],
                'note'=>'This is Order Id for current Order at Jet.com.']);
            
            $fieldset->addField(
                'agreeto_return', 'select', ['label'     =>  __('Agree To Return'),
                'class'     => 'required-entry validate-select',
                'required'  => true,
                'name'=>"agreeto_return",
                'values'=>$yesno->toOptionArray(),
                'note'=>'If Yes that means the merchant agrees to wholly pay the return charge to Jet.com from the return notification.']);
            
            $fieldset->addField(
                'sendid', 'hidden', ['label'     =>  __('test'),
                'readonly' => true,
                'name'      => 'id',
                'value'=>$id]);

            if (!empty($skus_details)) {
                $i=0;
                foreach ($skus_details as $detail) {
                    if ($detail['available_to_refund_qty']>0)
                    {
                        $flag=false;
                        if(($detail["changes_made"] && $detail["changes_made"] =='1')){
                            $flag=true;
                        }

                        $html1="";
                        if ($flag) {
                            $html1='<script type="text/javascript">'.
                                'document.addEventListener("DOMContentLoaded",function() {'.
                                'var f="sku_return_'.$i.'";'.
                                'container=document.getElementById(f);
                                         var tagNames = ["INPUT", "SELECT", "TEXTAREA" ,"BUTTON"];
                                                for(var i = 0; i<tagNames.length; i++) {
                                                  var elems = container.getElementsByTagName(tagNames[i]);
                                                  for(var j = 0; j<elems.length; j++) {
                                                    elems[j].readOnly = true;
                                                  }
                                          }'

                                .'});'
                                .'</script>';
                        }

                        $fieldset1 = $fieldset->addFieldset(
                            "sku_return_".$i, ['legend'=> __('sku : '.$detail['merchant_sku'])]);
                        
                        if ($flag) {
                            $fieldset1->addField(
                                'want_to_return'.$i, 'hidden', ['label'     =>  __('Generate Return for the item'),
                                'name'      => "sku_details[sku$i][want_to_return]",
                                'value'=>$detail["want_to_return"]])->setAfterElementHtml("<h3>Submitted Item.</h3>");
                        } else {
                            $html4="";
                            $html4='<script type="text/javascript">'.
                                'function feedbackchange'.$i.'(ele) {'.
                                'var v=ele.options[ele.selectedIndex].value;'.
                                'if (v==1) {'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.add("required-entry");'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.add("validate-select");'.
                                'var parent_tr = document.getElementById("return_refundfeedback'.$i.'").parentNode.parentNode;'.
                                'var span=parent_tr.getElementsByTagName("span");'.
                                'span[0].style.display = "inline-block";'.
                                '}'.
                                'if (v==0) {'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.remove("validate-select");'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.remove("required-entry");'.
                                'var parent_tr = document.getElementById("return_refundfeedback'.$i.'").parentNode.parentNode;'.
                                'var span=parent_tr.getElementsByTagName("span");'.
                                'span[0].style.display = "block";'.
                                '}'.
                                '}'.
                                '</script>';

                            $html3="";
                            $html3='<script type="text/javascript">'.
                                'document.addEventListener("DOMContentLoaded",function() {'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.remove("required-entry");'.
                                'document.getElementById("return_refundfeedback'.$i.'").classList.remove("validate-select");'.
                                'var parent_tr = document.getElementById("return_refundfeedback'.$i.'").parentNode.parentNode;'.'});'.
                                '</script>';

                            $fieldset1->addField(
                                'want_to_return'.$i, 'select', ['label'     =>  __('Generate Return for the item'),
                                'class'     => 'required-entry validate-select',
                                'required'  => true,
                                'onchange'  => "feedbackchange$i(this)",
                                'name'      => "sku_details[sku$i][want_to_return]",
                                'values'    =>  $yesno->toOptionArray(),
                                'note'=>'If select Yes than data of current sku will beQty Available for Refund sent to Jet.com for return otherwise not.'])->setAfterElementHtml($html3.$html4);
                        }

                        $fieldset1->addField(
                            'order_item_id'.$i, 'text', ['label'     =>  __('Order Item Id'),
                            'readonly' => true,
                            'required'  => true,
                            'name'      => "sku_details[sku$i][order_item_id]",
                            'value'=>$detail["order_item_id"],
                            'note'=>'Jet\'s unique identifier for an item in a merchant order.']);
                        
                        $fieldset1->addField('available_to_refund_qty'.$i, 'text',
                            [   'label'     =>  __('Quantity Available for Refund'),
                                'class'     => ' validate-number',
                                'readonly' => true,
                                'name'      => "sku_details[sku$i][available_to_refund_qty]",
                                'value'=>$detail["available_to_refund_qty"],
                                'note'=>'Quantity available to refund for this item in current order.']);
                        
                        $fieldset1->addField('merchant_sku'.$i, 'hidden', ['readonly' => true,
                            'name'      => "sku_details[sku$i][merchant_sku]",
                            'value'=>$detail["merchant_sku"]]);
                        
                        $fieldset1->addField('changes_made'.$i, 'hidden', ['readonly' => true,
                            'name'      => "sku_details[sku$i][changes_made]",
                            'value'=>$detail["changes_made"]]);
                        
                        $fieldset1->addField('qty_returned'.$i, 'text', ['label'     =>  __('Quantity Returned by Customer'),
                            'class'     => 'required-entry  validate-number',
                            'readonly' => true,
                            'name'      => "sku_details[sku$i][return_quantity]",
                            'value'=>$detail["return_quantity"],
                            'note'=>'Quantity of the given item that was returned.']);
                        
                        $html="";
                        $html='<script type="text/javascript">'.
                            'function checkamount'.$i.'(ele) {
                                var qty_ret_cst = document.getElementById("qty_returned'.$i.'").value;
                                if (ele.value > qty_ret_cst) {
                                  alert("Refund Quantity can not be greator than return quantity");
                                  return;
                                }
            
                                var am='.$detail["return_principal"].';var amount=(am !="" ? am : 0);if (ele.value==0) {document.getElementById("amount'.$i.'").value=0;} else {document.getElementById("amount'.$i.'").value=(amount/qty_ret_cst)*ele.value;}}</script>';
                        
                        $fieldset1->addField(
                            'qty_refunded'.$i, 'text', ['label'     =>  __('Quantity Refund'),
                            'onchange' => "checkamount$i(this)",
                            'class'     => 'required-entry  validate-number',
                            'required'  => true,
                            'name'      => "sku_details[sku$i][refund_quantity]",
                            'value'=>(isset($detail["return_quantity"]) !="" ? $detail["return_quantity"] : $detail["available_to_refund_qty"]),
                            'note'=>'Qty you want to refund for this item in current order.'])->setAfterElementHtml($html);

                        $fieldset1->addField(
                            'return_refundfeedback'.$i, 'select', ['label'     =>  __('Return Feedback'),
                            'class'     => 'required-entry validate-select',
                            'required'  => true,
                            'name'=>"sku_details[sku$i][return_refundfeedback]",
                            'values'    =>  $feedback_arr,
                            'value'=>isset($detail["return_refundfeedback"])?$detail["return_refundfeedback"]:0,
                            'note'=>'The reason this refund is less than the full amount.']);

                        /*$htmlamount = "";
                        $htmlamount ='<script type="text/javascript">
                            document.getElementById("adminhtml-orderreturn-edit-tab-main-'.$i.'-fieldset-element-form-field-return-refundfeedback'.$i.'").hide();
                            function amountValidate'.$i.'(element) {
                                var attr = element.getAttribute("data-ui-id");
                            alert(attr);
                                if ('.$detail["return_principal"].' < element.value) {
                                  alert("Refund Amount can not be greator than principle amount");
                                  return;
                                }
                                if ('.$detail["return_principal"].' > element.value) {
                                    document.getElementById
                                    ("adminhtml-orderreturn-edit-tab-main-'.$i.'-fieldset-element-form-field-return-refundfeedback'.$i.'"
                            ).hide();
                                }
                            }</script>';*/
                        
                        $fieldset1->addField(
                            'amount'.$i, 'text', ['label'     =>  __('Amount'),
                            'class'     => 'required-entry  validate-number',
                            'onchange' => "amountValidate$i(this)",
                            'name'=>"sku_details[sku$i][return_principal]",
                            'value'=>($detail["return_principal"] !="" ? $detail["return_principal"] : 0),
                            'note'=>'Amount to be refund for the given item in USD associated with the item itself. This should be the total cost for this item not the unit cost.']);
                        
                        $fieldset1->addField(
                            'actual_amount'.$i, 'hidden', ['name'=>"sku_details[sku$i][return_actual_principal]",
                            'value'=>($detail["return_principal"] !="" ? $detail["return_principal"] : 0)
                            ]);
                        
                        $fieldset1->addField(
                            'shipping_cost'.$i, 'text', ['label'     =>  __('Shipping cost'),
                            'class'     => 'validate-number',
                            'name'=>"sku_details[sku$i][return_shipping_cost]",
                            'value'=>($detail["return_shipping_cost"] !="" ? $detail["return_shipping_cost"] : 0),
                            'note'=>'Amount to be refund for the given item in USD associated with the shipping cost that was allocated to this item.']);
                        
                        $fieldset1->addField(
                            'shipping_tax'.$i, 'text', ['label'     =>  __('Shipping tax'),
                            'value'=>($detail["return_shipping_tax"] !="" ? $detail["return_shipping_tax"] : 0),
                            'name'=>"sku_details[sku$i][return_shipping_tax]",
                            'class'     => 'validate-number',
                            'note'=>'Amount to be refund for the given item in USD associated with the tax that was charged on shipping.']);
                        
                        $fieldset1->addField(
                            'tax'.$i, 'text', ['label'     =>  __('Tax'),
                            'value'=>($detail["return_tax"] !="" ? $detail["return_tax"] : 0),
                            'name'=>"sku_details[sku$i][return_tax]",
                            'class'     => 'validate-number',
                            'note'=>'Amount to be refund for the given item in USD associated with tax that was charged for the item.']);
                        $i++;
                    }
                }

            }
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Basic Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Basic Settings');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
