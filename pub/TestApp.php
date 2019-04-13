<?php
class TestApp extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface {
    public function launch()
    {
        echo "Hello World! My Dirty Playground";
        return $this->_response;
    }
    public function catchException(
        \Magento\Framework\App\Bootstrap $bootstrap,
        \Exception $exception
    )
    {
        return false;
    }
}
