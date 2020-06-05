<?php

$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = array(
    'id' => 'sxproductsearch',
    'title' => 'SEMKNOX Product Search',
    'description' => array(
        'en' =>
        'Oxide modules for integration and use of the SEMKNOX product search.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Requirements</b>
        <b style="display: block; margin: 0 0 5px 0">Cronjob</b>
        The following cronjob is required for the initial product upload and product synchronization with SEMKNOX. 
        <br/><br/>
        Example: Crontab
        <code style="background-color: #eee; display: block; padding: 5px">* * * * * wget quiet no-cache -O - [https://www.yourdomain.com]/index.php?cl=sxproductsearch_cron > /dev/null
</code>
        Ideally, the execution should take place every minute, as shown in the example.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Important notes</b>
        <b style="display: block; margin: 0 0 5px 0">Language configuration</b>
        You need separate login data for SEMKNOX for each language. You can enter these in the module settings of this plugin. 
        After you make a change to the language configuration of your system (Menu <i>Master Settings</i> > <i>Languages</i>), e.g. adding a new language, it may be necessary to reinstall the module to enter the SEMKNOX access data for this language.
        Your previous configuration <b>WONT</b> be lost during this process!',
        'de' =>
        'Oxid Modul zur Integration und Verwendung der SEMKNOX Produktsuche.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Voraussetzungen</b>
        <b style="display: block; margin: 0 0 5px 0">Cronjob</b>
        Der folgende Cronjob wird für den initialen Produktupload und die Produktsynchronisation mit SEMKNOX benötigt. 
        <br/><br/>
        Beispiel: Crontab Eintrag
        <code style="background-color: #eee; display: block; padding: 5px">* * * * * wget quiet no-cache -O - [https://www.yourdomain.com]/index.php?cl=sxproductsearch_cron > /dev/null</code>
        Die Ausführung sollte idealerweise, wie im Beispiel gezeigt, jede Minute erfolgen.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Wichtige Hinweise</b>
        <b style="display: block; margin: 0 0 5px 0">Sprachkonfiguration</b>
        Sie benötigen für jede Sprache seperate Zugangsdaten für SEMKNOX. Diese können Sie unter den Moduleinstellungen dieses Plugins eingeben. 
        Nachdem Sie eine Veränderung an der Sprachkonfiguration ihres Systems (Menüpunkt <i>Stammdaten</i> > <i>Sprachen</i>) vornehmen (z.B. Hinzufügen einer neuen Sprache), kann es notwendig sein das Plugin erneut zu installieren, um die SEMKNOX Zugangsdaten für diese Sprache eingeben zu können.
        Ihre bisherige Konfiguration geht bei diesem Vorgang <b>NICHT</b> verloren!'
    ),
    'thumbnail' => 'logo.png',
    'version' => '3.0.0',
    'author' => 'SEMKNOX',
    'url' => 'https://www.semknox.com',
    'email' => 'info@semknox.com',
    'controllers'  => array(
        'sxproductsearch_ajax' => Semknox\Productsearch\Application\Controller\Admin\AjaxController::class,
        'sxproductsearch_cron' => Semknox\Productsearch\Application\Controller\CronController::class
    ),
    'extend' => array(
        \OxidEsales\Eshop\Application\Model\Search::class => \Semknox\Productsearch\Application\Model\Search::class,
        \OxidEsales\Eshop\Application\Controller\SearchController::class => \Semknox\Productsearch\Application\Controller\SearchController::class
    ),
    'templates' => array(
        'admin_sxproductsearch_ajax.tpl'   => 'semknox/semknox-oxid/Application/views/admin/tpl/admin_sxproductsearch_ajax.tpl',
        'sxproductsearch_cron.tpl'   => 'semknox/semknox-oxid/Application/views/tpl/sxproductsearch_cron.tpl',
    ),
    'blocks' => array(
        array(
            'template' => 'bottomnaviitem.tpl',
            'block'=>'admin_bottomnavicustom',
            'file'=>'Application/views/admin/blocks/admin_bottomnavicustom.tpl'
        ),
        array(
            'template' => 'page/search/search.tpl',
            'block' => 'search_header',
            'file' => 'Application/views/blocks/search_header.tpl'
        ),
    ),
);

$settings = array();

$oxLanguage = new \OxidEsales\Eshop\Core\Language;

if(function_exists('oxNew')){
    foreach ($oxLanguage->getLanguageArray() as $lang) {

        $oxid = ucfirst($lang->oxid);

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings'. $oxid,
            'name' => 'sxProjectId' . $oxid,
            'type' => 'str',
            'value' => '',
            'position' => 1
        );
        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxApiKey' . $oxid,
            'type' => 'str',
            'value' => '',
            'position' => 2
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxIsSandbox' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 3
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxFrontendActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 1
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxUploadActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 1
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxAnswerActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 1
        );
    }
}

$settings[] = array(
    'group' => 'SemknoxProductsearchSettings',
    'name' => 'sxUploadBatchSize',
    'type' => 'str',
    'value' => '100',
    'position' => 4
);

$settings[] = array(
    'group' => 'SemknoxProductsearchSettings',
    'name' => 'sxCollectBatchSize',
    'type' => 'str',
    'value' => '100',
    'position' => 4
);

$settings[] = array(
    'group' => 'SemknoxProductsearchSettings',
    'name' => 'sxRequestTimeout',
    'type' => 'str',
    'value' => '15',
    'position' => 5
);

$settings[] = array(
    'group' => 'SemknoxProductsearchCronjob',
    'name' => 'sxCronjobHour',
    'type' => 'str',
    'value' => rand(0,5),
    'position' => 99
);

$settings[] = array(
    'group' => 'SemknoxProductsearchCronjob',
    'name' => 'sxCronjobMinute',
    'type' => 'str',
    'value' => rand(0, 59),
    'position' => 100
);

$aModule['settings'] = $settings;