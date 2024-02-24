<?php

$sLangName = 'English';

$aLangEn = array(
  'charset%s'                     => 'UTF-8',

  'SHOP_MODULE_GROUP_SemknoxProductsearchLanguageSettings%s' => 'Settings',
  'SHOP_MODULE_sxProjectId%s' => 'Project ID',
  'SHOP_MODULE_sxApiKey%s' => 'API Key',

  'SHOP_MODULE_GROUP_SemknoxProductsearchSettings' => 'Settings - Global',

  'SHOP_MODULE_sxFrontendActive%s' => 'Produktsearch in frontend active',
  'SHOP_MODULE_sxUploadActive%s' => 'Produktupload to Site Search 360 active',
  'SHOP_MODULE_sxAnswerActive%s' => 'display Site Search 360 search interpretation sentence',
  'SHOP_MODULE_sxFilterOptionCounterActive%s' => 'show expected number of results behind filter options',
  'SHOP_MODULE_sxIncrementalUpdatesActive%s' => 'incremental Productupdates to Site Search 360 active',
  'SHOP_MODULE_sxMoveFilterToSidebar%s' => 'move filter to sidebar',
  'SHOP_MODULE_sxHideRangeInRangeSliderTitle%s' => 'hide range in Range-Slider title',
  'SHOP_MODULE_sxResultProduct%s' => 'Search result product',
  'SHOP_MODULE_sxFilterGroupUnfoldCount%s' => 'Number of unfolded filtergroups',
  'SHOP_MODULE_sxSendInactiveArticles%s' => 'also send inactive products to Site Search 360',
  'SHOP_MODULE_sxIgnoreOutOfStockArticles%s' => 'send only products with positive stock to Site Search 360',
  'SHOP_MODULE_sxSeoUrlsActive%s' => 'Use SEO- article URLs in the search suggestions',
  'SHOP_MODULE_sxResultProduct%s_parentProduct' => 'Parent-product as search result',
  'SHOP_MODULE_sxResultProduct%s_individualVariantProduct' => 'variant-product as search result',
  'SHOP_MODULE_sxCategoryQuery%s' => 'show category products via Site Search 360',
  'SHOP_MODULE_sxIsSandbox%s' => 'Sandbox-Mode active',
  'SHOP_MODULE_sxRelevanceSortingTranslation%s' => 'Designation of the "Relevance" sorting',

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

  foreach($aLangEn as $langKey => $langText){
    if (sprintf($langKey, $oxid) == $langKey) {
      $aLang[sprintf($langKey, $oxid)] = $aLangEn[$langKey];
    } else {
      $aLang[sprintf($langKey, $oxid)] = $aLangEn[$langKey] . ' - Shop-Language: ' . $language;
    }
  }
}