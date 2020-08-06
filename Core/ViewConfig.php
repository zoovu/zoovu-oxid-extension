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
     * get current semknox projectId.
     */
    public function getSxProjectId()
    {
        return isset($this->_sxConfigValues['projectId']) ? $this->_sxConfigValues['projectId'] : '';
    }


    /*
     * get current semknox projectId.
     */
    public function getSxApiUrl()
    {
        return isset($this->_sxConfigValues['apiUrl']) ? $this->_sxConfigValues['apiUrl'] : '';
    }

}
