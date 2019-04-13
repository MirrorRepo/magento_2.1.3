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

namespace Ced\Jet\Block\Adminhtml\OrderReturn\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */

    public function _construct()
    {
        parent::_construct();
        $this->setId('id');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Return Type Information'));

    }

    /**
     * Tabs constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return $this
     */

    public function _beforeToHtml()
    {
        $this->addTab('return', [
            'label'     => __('Return Details'),
            'content'   => $this->getLayout()->createBlock(
                'Ced\Jet\Block\Adminhtml\OrderReturn\Edit\Tab\Main')->toHtml()]);


        return parent::_beforeToHtml();
    }
}
