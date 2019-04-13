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
namespace Ced\Jet\Controller\Adminhtml\Jetproduct;

class Clearall extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::rejected_files';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Product sync 
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $status = ['Processed with errors', 'Processed successfully'];
        $this->_objectManager->create('Ced\Jet\Model\Fileinfo')->getCollection()
              ->addFieldToFilter('status',['in' => $status])
              ->walk('delete');

        $this->_objectManager->create('Ced\Jet\Model\Errorfile')->getCollection()->walk('delete');
        $this->messageManager->addSuccess('Rejected batch File Log cleared.');
        $this->_redirect('jet/jetproduct/rejected');
    }
}
