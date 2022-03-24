<?php

$sLangName = 'Deutsch';

$aLang = array(
  'charset'                     => 'UTF-8',

  'SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings' => 'Einstellungen',
  'SHOP_MODULE_sxProjectId' => 'Projekt ID',
  'SHOP_MODULE_sxApiKey' => 'API Schlüssel (API-Key)',

  'SHOP_MODULE_GROUP_SemknoxProductsearchSettings' => 'Einstellungen - Global',

  'SHOP_MODULE_sxFrontendActive' => 'Produktsuche im Frontend aktivieren',
  'SHOP_MODULE_sxUploadActive' => 'Produktupload an SEMKNOX aktivieren',
  'SHOP_MODULE_sxAnswerActive' => 'SEMKNOX-Suchinterpretationssatz anzeigen',
  'SHOP_MODULE_sxFilterOptionCounterActive' => 'erwartete Ergebnisanzahl hinter den Filteroptionen anzeigen',
  'SHOP_MODULE_sxIncrementalUpdatesActive' => 'inkrementelle Produktupdates an SEMKNOX senden',
  'SHOP_MODULE_sxMoveFilterToSidebar' => 'Filter in die Sidebar verschieben',
  'SHOP_MODULE_sxHideRangeInRangeSliderTitle' => 'Werte im Slider-Filter Titel ausblenden',
  'SHOP_MODULE_sxCategoryQuery' => 'Kategorieprodukte über SEMKNOX ausspielen',
  'SHOP_MODULE_sxResultProduct' => 'Suchergebnisprodukt',
  'SHOP_MODULE_sxFilterGroupUnfoldCount' => 'Anzahl ausgeklappter Filtergruppen',
  'SHOP_MODULE_sxSendInactiveArticles' => 'auch inaktive Produkte an SEMKNOX senden',
  'SHOP_MODULE_sxIgnoreOutOfStockArticles' => 'nur Produkte mit positiven Lagerbestand an SEMKNOX senden',
  'SHOP_MODULE_sxResultProduct_parentProduct' => 'Elternprodukt in Suchergebnis anzeigen',
  'SHOP_MODULE_sxResultProduct_individualVariantProduct' => 'Variantenprodukt in Suchergebnis anzeigen',
  'SHOP_MODULE_sxIsSandbox' => 'Sandbox-Modus aktivieren',

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

  foreach ($aLang as $langKey => $langText) {
    $aLang[$langKey . $oxid] = $aLang[$langKey] . ' - Shopsprache: ' . $language;
  }
}
