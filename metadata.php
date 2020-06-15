<?php

$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id' => 'sxproductsearch',
    'title' => 'SEMKNOX Product Search',
    'description' => [
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
        Your previous configuration <b>WONT</b> be lost during this process!
        <br/><br/>
        <b style="display: block; margin: 0 0 5px 0">Global configuration (<i>masterConfig.json</i>)</b>
        Via the file <i>/log/semknox/masterConfig.json</i> module settings can be globally overwritten. The values set there apply to <b>ALL active shops and languages</b> (in which the module is activated). 
        If the values <i>apiKey</i> and <i>projectId</i> are set in this file, all records are synchronized with only one account at SEMKNOX. In this case, different values are realized via different <i>userGroups</i> (corresponds to the oxide <i>ShopIds</i>). The same applies to the search in the frontend.',
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
        Ihre bisherige Konfiguration geht bei diesem Vorgang <b>NICHT</b> verloren!
        <br/><br/>
        <b style="display: block; margin: 0 0 5px 0">Globale Konfiguration auf Dateiebene (<i>masterConfig.json</i>)</b>
        Über die Datei <i>/log/semknox/masterConfig.json</i> können Moduleinstellungen global überschreiben. Die dort gesetzen Werte gelten für <b>ALLE aktiven Shops und Sprachen</b> (in denen das Modul aktiviert ist). 
        <br/>Werden in dieser Datei die Werte <i>apiKey</i> und <i>projectId</i> gesetzt, so werden die Datensätze mit nur einem einzigen Account bei SEMKNOX synchronisiert. Abweiche Werte werden in diesem Fall über verschieden <i>userGroups</i> (entspricht den Oxid <i>ShopIds</i>) realisiert. Gleiches gilt für die Suche im Frontend.'
    ],
    'thumbnail' => 'logo.png',
    'version' => '3.0.0',
    'author' => 'SEMKNOX',
    'url' => 'https://www.semknox.com',
    'email' => 'info@semknox.com',
    'controllers'  => [
        'sxproductsearch_ajax' => Semknox\Productsearch\Application\Controller\Admin\AjaxController::class,
        'sxproductsearch_cron' => Semknox\Productsearch\Application\Controller\CronController::class
    ],
    'extend' => [
        \OxidEsales\Eshop\Application\Model\ArticleList::class => \Semknox\Productsearch\Application\Model\ArticleList::class,
        \OxidEsales\Eshop\Application\Model\Search::class => \Semknox\Productsearch\Application\Model\Search::class,
        \OxidEsales\Eshop\Application\Controller\FrontendController::class => \Semknox\Productsearch\Application\Controller\FrontendController::class,
        \OxidEsales\Eshop\Application\Controller\SearchController::class => \Semknox\Productsearch\Application\Controller\SearchController::class,
        \OxidEsales\Eshop\Core\Language::class => \Semknox\Productsearch\Core\Language::class
    ],
    'templates' => [
        'admin_sxproductsearch_ajax.tpl'   => 'semknox/semknox-oxid/Application/views/admin/tpl/admin_sxproductsearch_ajax.tpl',
        'sxproductsearch_cron.tpl'   => 'semknox/semknox-oxid/Application/views/tpl/sxproductsearch_cron.tpl',
    ],
    'blocks' => [
        [
            'template' => 'bottomnaviitem.tpl',
            'block'=>'admin_bottomnavicustom',
            'file'=>'Application/views/admin/blocks/admin_bottomnavicustom.tpl'
        ],
        [
            'template' => 'page/search/search.tpl',
            'block' => 'search_header',
            'file' => 'Application/views/blocks/search_header.tpl'
        ],
        [
            'template' => 'widget/header/search.tpl',
            'block' => 'widget_header_search_form',
            'file' => 'Application/views/blocks/widget_header_search_form.tpl'
        ]
    ]
];

$settings = [];

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