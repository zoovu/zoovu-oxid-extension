<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use Semknox\Productsearch\Application\Model\SxHelper;


class FrontendController extends FrontendController_parent
{

    public function getSortingSql($ident)
    {
        $sorting = $this->getSorting($this->getSortIdent());
        $sortBy = $sorting['sortby'];

        if (SxHelper::isEncodedOption($sortBy)) {
            // acitve semknox search shop
            return $sorting;
        }

        // fallback, if not active for current shop
        $sortingSql = parent::getSortingSql($ident);
        return $sortingSql == '``' ? false : $sortingSql;
    }
    
}
