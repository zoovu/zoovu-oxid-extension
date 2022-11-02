<?php

$sLangName = 'Deutsch';

$aLangDe = array(
  'charset%s'                     => 'UTF-8',

  'SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings%s' => 'Einstellungen',
  'SHOP_MODULE_sxProjectId%s' => 'Projekt ID',
  'SHOP_MODULE_sxApiKey%s' => 'API Schlüssel (API-Key)',

  'SHOP_MODULE_GROUP_SemknoxProductsearchSettings' => 'Einstellungen - Global',

  'SHOP_MODULE_sxFrontendActive%s' => 'Produktsuche im Frontend aktivieren',
  'SHOP_MODULE_sxUploadActive%s' => 'Produktupload an SEMKNOX aktivieren',
  'SHOP_MODULE_sxAnswerActive%s' => 'SEMKNOX-Suchinterpretationssatz anzeigen',
  'SHOP_MODULE_sxFilterOptionCounterActive%s' => 'erwartete Ergebnisanzahl hinter den Filteroptionen anzeigen',
  'SHOP_MODULE_sxIncrementalUpdatesActive%s' => 'inkrementelle Produktupdates an SEMKNOX senden',
  'SHOP_MODULE_sxMoveFilterToSidebar%s' => 'Filter in die Sidebar verschieben',
  'SHOP_MODULE_sxHideRangeInRangeSliderTitle%s' => 'Werte im Slider-Filter Titel ausblenden',
  'SHOP_MODULE_sxCategoryQuery%s' => 'Kategorieprodukte über SEMKNOX ausspielen',
  'SHOP_MODULE_sxResultProduct%s' => 'Suchergebnisprodukt',
  'SHOP_MODULE_sxFilterGroupUnfoldCount%s' => 'Anzahl ausgeklappter Filtergruppen',
  'SHOP_MODULE_sxSendInactiveArticles%s' => 'auch inaktive Produkte an SEMKNOX senden',
  'SHOP_MODULE_sxIgnoreOutOfStockArticles%s' => 'nur Produkte mit positiven Lagerbestand an SEMKNOX senden',
  'SHOP_MODULE_sxSeoUrlsActive%s' => 'sprechende Artikel-URLs in den Suchvorschlägen verwenden',
  'SHOP_MODULE_sxResultProduct%s_parentProduct' => 'Elternprodukt in Suchergebnis anzeigen',
  'SHOP_MODULE_sxResultProduct%s_individualVariantProduct' => 'Variantenprodukt in Suchergebnis anzeigen',
  'SHOP_MODULE_sxIsSandbox%s' => 'Sandbox-Modus aktivieren',

  'SHOP_MODULE_sxStartInitialUpload' => 'Produktupload starten',
  'SHOP_MODULE_sxStopInitialUpload' => 'Produktupload abbrechen',
  'SHOP_MODULE_sxCronTester' => 'Cron-Aufruf',

  'SHOP_MODULE_GROUP_SemknoxProductsearchCronjob' => 'Zeitpunkt des täglichen Produktupload',
  'SHOP_MODULE_sxCronjobHour' => 'Stunde',
  'SHOP_MODULE_sxCronjobMinute' => 'Minute',

  'SHOP_MODULE_sxUploadBatchSize' => 'Produktanzahl pro Paket (Senden)',
  'SHOP_MODULE_sxCollectBatchSize' => 'Produktanzahl pro Paket (Lesen)',
  'SHOP_MODULE_sxRequestTimeout' => 'Timeout Anfrage',
);


$oxLanguage = new \OxidEsales\Eshop\Core\Language;
foreach ($oxLanguage->getLanguageArray() as $lang) {
  $oxid = ucfirst($lang->oxid);
  $language = $lang->name;

  foreach ($aLangDe as $langKey => $langText) {

    if(sprintf($langKey, $oxid) == $langKey){
      $aLang[sprintf($langKey, $oxid)] = $aLangDe[$langKey];
    } else {
      $aLang[sprintf($langKey, $oxid)] = $aLangDe[$langKey] . ' - Shopsprache: ' . $language;
    }
    
  }
}
