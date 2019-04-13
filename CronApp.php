<?php
class CronApp
    extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface {
    public function launch()
    {
        //dirty code goes here. 
        //the example below just prints a class name
        echo get_class($this->_objectManager->create('\Magento\Catalog\Model\Category'));
        //the method must end with this line
        return $this->_response;
    }

    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }

}