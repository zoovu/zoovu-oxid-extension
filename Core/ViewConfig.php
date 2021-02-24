<?php

namespace Semknox\Productsearch\Core;

use Semknox\Productsearch\Application\Model\Search;
use OxidEsales\Eshop\Core\Module\Module;

class ViewConfig extends ViewConfig_parent
{

    public function __construct()
    {
        $this->_search = new Search();
        $this->_sxConfigValues = $this->_search->getSxConfigValues();

        return parent::__construct();
    }

    /*
     * get sc config value by key
     */
    public function getSxConfigValue($key, $default = null)
    {
        $default = $default ? $default : '';

        if(in_array($key,['version','title'])){
            return $this->getSxModuleInfo($key) ? $this->getSxModuleInfo($key) : $default;
        }

        return isset($this->_sxConfigValues[$key]) ? $this->_sxConfigValues[$key] : $default;
    }

    private function getSxModuleInfo($key)
    {
        $oxModule = new Module();
        $oxModule->load('sxproductsearch');

        $value = $oxModule->getInfo($key);

        if(is_array($value)){
            return (isset($value['en']) && is_string($value['en'])) ? $value['en'] : false;
        }

        return is_string($value) ? $value : false;    
    }

}
