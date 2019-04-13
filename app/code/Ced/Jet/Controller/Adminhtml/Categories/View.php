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
namespace Ced\Jet\Controller\Adminhtml\Categories;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;        
    }

    /**
     * Category View page
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Jet::jet_categories');
        $text = "";
        if($this->getRequest()->getParam('id')){
            $id=$this->getRequest()->getParam('id');
            $model=$this->_objectManager->get('Ced\Jet\Model\Categories')->load($id);
            $name=$model->getData('name');
            $jet_id=$model->getData('cat_id');
            $text=" - ".$name." ( jet category id : ".$jet_id." )" ;
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Jet Attribute Listing'.$text));
        return $resultPage;
    }

    /**
     * Check admin permissions for this controller
     * @return boolean
     */

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Jet::jet_categories');
    }
}
