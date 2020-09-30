<?php

namespace Semknox\Productsearch\Core;

use Semknox\Productsearch\Application\Model\Search;

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
    public function getSxConfigValue($key)
    {
        return isset($this->_sxConfigValues[$key]) ? $this->_sxConfigValues[$key] : '';
    }

}
