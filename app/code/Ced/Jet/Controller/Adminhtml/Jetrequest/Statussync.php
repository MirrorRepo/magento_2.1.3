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
namespace Ced\Jet\Controller\Adminhtml\Jetrequest;

class Statussync extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Sync Product Status
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $jetHelper = $this->_objectManager->get('Ced\Jet\Helper\Jet');
        $jetHelper->getProductByStatus('Under Jet Review', 'under_jet_review');
        $jetHelper->getProductByStatus('Missing Listing Data', 'missing_listing_data');
        $jetHelper->getProductByStatus('Unauthorized', 'unauthorized');
        $jetHelper->getProductByStatus('Excluded', 'excluded');
        $jetHelper->getProductByStatus('Available for Purchase', 'available_for_purchase');
        $jetHelper->getProductByStatus('Archived', 'archived');
        $this->_redirect('jet/jetrequest/uploadproduct');
    }
}
