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
namespace Ced\Jet\Controller\Adminhtml\Jetattribute;

class Mapsubmit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';
    
    public $registry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Jet Mapp Submit
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('jet_code');
        $mapp_data = $this->getRequest()->getParam('jet_code_mapp');
        if (isset($data)) {
            $jet_code_coll = $this->_objectManager->create('Ced\Jet\Model\OptionalAttribute')->getCollection();
            $model = $this->_objectManager->create('Ced\Jet\Model\OptionalAttribute');
            foreach ($jet_code_coll as $key => $jet_model) {
                $model->load($jet_model->getId());
                $real_key = array_search($model->getJetCode() , $data);
                if ($real_key !== false) {
                    $model->setData('used', 1);
                    $model->setData('map_attribute_code', $mapp_data[$real_key]);
                    $model->save();
                } else {
                    $model->setData('used', 0);
                    $model->setData('map_attribute_code', '');
                    $model->save();
                }
            }
            $this->messageManager->addSuccess(__('Attributes Mapped Successfully.'));
        } else {
            $this->messageManager->addError(__('No data Posted.'));
        }
        $this->_redirect('jet/*/index');
    }
}
