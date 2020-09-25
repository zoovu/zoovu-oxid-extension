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

  $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLogin' . $oxid] = $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLogin'] . ' - Shop-language: ' . $language;
  $aLang['SHOP_MODULE_sxProjectId' . $oxid] = $aLang['SHOP_MODULE_sxProjectId'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxApiKey' . $oxid] = $aLang['SHOP_MODULE_sxApiKey'] . ' - Shop-Language: ' . $language;

  $aLang['SHOP_MODULE_sxIsSandbox' . $oxid] = $aLang['SHOP_MODULE_sxIsSandbox'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxFrontendActive' . $oxid] = $aLang['SHOP_MODULE_sxFrontendActive'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxUploadActive' . $oxid] = $aLang['SHOP_MODULE_sxUploadActive'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxAnswerActive' . $oxid] = $aLang['SHOP_MODULE_sxAnswerActive'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxFilterOptionCounterActive' . $oxid] = $aLang['SHOP_MODULE_sxFilterOptionCounterActive'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxIncrementalUpdatesActive' . $oxid] = $aLang['SHOP_MODULE_sxIncrementalUpdatesActive'] . ' - Shop-Language: ' . $language;
  $aLang['SHOP_MODULE_sxMoveFilterToSidebar' . $oxid] = $aLang['SHOP_MODULE_sxMoveFilterToSidebar'] . ' - Shop-Language: ' . $language;
}
