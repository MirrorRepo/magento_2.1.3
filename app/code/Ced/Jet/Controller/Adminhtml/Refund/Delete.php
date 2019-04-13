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

class Delete extends \Magento\Backend\App\Action
{
	public function execute()
	{
		$id = $this->getRequest()->getParam('id');
		$model = $this->_objectManager->create('Ced\Jet\Model\Refund')->load($id);
		$model->setId($id)->delete();
		$this->_redirect('jet/refund/index');
	}
}
