<?php

$sLangName = 'Deutsch';

$aLang = array(
  'charset'                     => 'UTF-8',

  'SHOP_MODULE_GROUP_SemknoxProductsearchLogin' => 'Zugangsdaten',
  'SHOP_MODULE_sxProjectId' => 'Projekt ID',
  'SHOP_MODULE_sxApiKey' => 'API Schlüssel (API-Key)',

  'SHOP_MODULE_GROUP_SemknoxProductsearchSettings' => 'Einstellungen',

  'SHOP_MODULE_sxFrontendActive' => 'Produktsuche im Frontend aktivieren',
  'SHOP_MODULE_sxUploadActive' => 'Produktupload an SEMKNOX aktivieren',
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

  $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLogin' . $oxid] = $aLang['SHOP_MODULE_GROUP_SemknoxProductsearchLogin'] . ' - Sprache: ' . $language;
  $aLang['SHOP_MODULE_sxProjectId' . $oxid] = $aLang['SHOP_MODULE_sxProjectId'] . ' - Sprache: ' . $language;
  $aLang['SHOP_MODULE_sxApiKey' . $oxid] = $aLang['SHOP_MODULE_sxApiKey'] . ' - Sprache: ' . $language;

  $aLang['SHOP_MODULE_sxIsSandbox' . $oxid] = $aLang['SHOP_MODULE_sxIsSandbox'] . ' - Sprache: ' . $language;
  $aLang['SHOP_MODULE_sxFrontendActive' . $oxid] = $aLang['SHOP_MODULE_sxFrontendActive'] . ' - Sprache: ' . $language;
  $aLang['SHOP_MODULE_sxUploadActive' . $oxid] = $aLang['SHOP_MODULE_sxUploadActive'] . ' - Sprache: ' . $language;
}
