<?php

$sLangName = 'English';

$aLang = array(
  'charset'                     => 'UTF-8',

  'SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings' => 'Settings',
  'SHOP_MODULE_sxProjectId' => 'Project ID',
  'SHOP_MODULE_sxApiKey' => 'API Key',

  'SHOP_MODULE_GROUP_SemknoxProductsearchSettings' => 'Settings - Global',

  'SHOP_MODULE_sxFrontendActive' => 'Produktsearch in frontend active',
  'SHOP_MODULE_sxUploadActive' => 'Produktupload to SEMKNOX active',
  'SHOP_MODULE_sxAnswerActive' => 'display SEMKNOX search interpretation sentence',
  'SHOP_MODULE_sxFilterOptionCounterActive' => 'show expected number of results behind filter options',
  'SHOP_MODULE_sxIncrementalUpdatesActive' => 'incremental Productupdates to SEMKNOX active',
  'SHOP_MODULE_sxMoveFilterToSidebar' => 'move filter to sidebar',
  'SHOP_MODULE_sxHideRangeInRangeSliderTitle' => 'hide range in Range-Slider title',
  'SHOP_MODULE_sxResultProduct' => 'Search result product',
  'SHOP_MODULE_sxFilterGroupUnfoldCount' => 'Number of unfolded filtergroups',
  'SHOP_MODULE_sxSendInactiveArticles' => 'also send inactive products to SEMKNOX',
  'SHOP_MODULE_sxIgnoreOutOfStockArticles' => 'send only products with positive stock to SEMKNOX',
  'SHOP_MODULE_sxResultProduct_parentProduct' => 'Parent-product as search result',
  'SHOP_MODULE_sxResultProduct_individualVariantProduct' => 'variant-product as search result',
  'SHOP_MODULE_sxCategoryQuery' => 'show category products via SEMKNOX',
  'SHOP_MODULE_sxIsSandbox' => 'Sandbox-Mode active',

  'SHOP_MODULE_sxStartInitialUpload' => 'start product upload',
  'SHOP_MODULE_sxStopInitialUpload' => 'cancel product upload',
  'SHOP_MODULE_sxCronTester' => 'Cron-Call',

  'SHOP_MODULE_GROUP_SemknoxProductsearchCronjob' => 'Time of Daily product upload',
  'SHOP_MODULE_sxCronjobHour' => 'Hour',
  'SHOP_MODULE_sxCronjobMinute' => 'Minute',

  'SHOP_MODULE_sxUploadBatchSize' => 'Number of products per package (upload)',
  'SHOP_MODULE_sxCollectBatchSize' => 'Number of products per package (reading)',
  'SHOP_MODULE_sxRequestTimeout' => 'Request timeout',
);

$oxLanguage = new \OxidEsales\Eshop\Core\Language;
foreach ($oxLanguage->getLanguageArray() as $lang) {
  $oxid = ucfirst($lang->oxid);
  $language = $lang->name;

  foreach($aLang as $langKey => $langText){
    $aLang[$langKey . $oxid] = $aLang[$langKey] . ' - Shop-Language: ' . $language;
  }
}