<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Listing\Moving;

class GetFailedProductsGrid extends \Ess\M2ePro\Controller\Adminhtml\Listing\Moving
{
    //########################################

    public function execute()
    {
        $block = $this->createBlock('Listing\Moving\FailedProducts\Grid');

        $this->setAjaxContent($block);
        return $this->getResult();
    }

    //########################################
}