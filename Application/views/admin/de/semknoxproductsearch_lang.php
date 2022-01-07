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

  $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings' . $oxid] = $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings'] . ' - Shopsprache ' . $language;
  $aLang['SHOP_MODULE_sxProjectId' . $oxid] = $aLang['SHOP_MODULE_sxProjectId'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxApiKey' . $oxid] = $aLang['SHOP_MODULE_sxApiKey'] . ' - Shopsprache: ' . $language;

  $aLang['SHOP_MODULE_sxIsSandbox' . $oxid] = $aLang['SHOP_MODULE_sxIsSandbox'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxFrontendActive' . $oxid] = $aLang['SHOP_MODULE_sxFrontendActive'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxUploadActive' . $oxid] = $aLang['SHOP_MODULE_sxUploadActive'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxAnswerActive' . $oxid] = $aLang['SHOP_MODULE_sxAnswerActive'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxFilterOptionCounterActive' . $oxid] = $aLang['SHOP_MODULE_sxFilterOptionCounterActive'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxIncrementalUpdatesActive' . $oxid] = $aLang['SHOP_MODULE_sxIncrementalUpdatesActive'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxMoveFilterToSidebar' . $oxid] = $aLang['SHOP_MODULE_sxMoveFilterToSidebar'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxHideRangeInRangeSliderTitle' . $oxid] = $aLang['SHOP_MODULE_sxHideRangeInRangeSliderTitle'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxCategoryQuery' . $oxid] = $aLang['SHOP_MODULE_sxCategoryQuery'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxResultProduct' . $oxid. '_parentProduct'] = $aLang['SHOP_MODULE_sxResultProduct_parentProduct'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxResultProduct' . $oxid . '_individualVariantProduct'] = $aLang['SHOP_MODULE_sxResultProduct_individualVariantProduct'] . ' - Shopsprache: ' . $language;
  $aLang['SHOP_MODULE_sxResultProduct' . $oxid] = $aLang['SHOP_MODULE_sxResultProduct'] . ' - Shopsprache: ' . $language;

}
