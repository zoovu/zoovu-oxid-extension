<?php

namespace Semknox\Productsearch\Core;


class Language extends Language_parent
{

    public function translateString($sStringToTranslate, $iLang = null, $blAdminMode = null)
    {
        // check if semknox string
        if(stripos($sStringToTranslate, 'sxtranslated') === 0){

            $translated = explode('_', $sStringToTranslate, 3);
            if(count($translated) === 3) return $translated[2];
        }

        return parent::translateString($sStringToTranslate, $iLang, $blAdminMode);
    }

}