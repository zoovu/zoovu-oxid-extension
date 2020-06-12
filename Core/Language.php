<?php

namespace Semknox\Productsearch\Core;

use Semknox\Productsearch\Application\Model\SxHelper;

class Language extends Language_parent
{

    public function translateString($sStringToTranslate, $iLang = null, $blAdminMode = null)
    {
        // check if semknox string
        $sxHelper = new SxHelper();
        
        if (is_array($sStringToTranslate)) die;

        if($sxHelper->isEncodedOption($sStringToTranslate))
        {
            $sxSortOption = $sxHelper->decodeSortOption($sStringToTranslate);
            return $sxSortOption->getName();
        }

        return parent::translateString($sStringToTranslate, $iLang, $blAdminMode);
    }

}