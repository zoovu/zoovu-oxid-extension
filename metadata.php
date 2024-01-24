<?php

$sMetadataVersion = '2.1';

// extension version:
$composerJson = file_get_contents(__DIR__. DIRECTORY_SEPARATOR.'composer.json');
$composerJson = str_replace("\\", "", $composerJson);
$composerArray = json_decode($composerJson,true);
$version = isset($composerArray['version']) ? $composerArray['version'] : '1.0.0';

/**
 * Module information
 */
$aModule = [
    'version' => $version,
    'id' => 'sxproductsearch',
    'title' => 'Site Search 360',
    'description' => [
        'en' =>
        'OXID module for integration and use of the Site Search 360.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Requirements</b>
        <b style="display: block; margin: 0 0 5px 0">Cronjob</b>
        The following cronjob is required for the initial product upload and product synchronization with the Site Search 360 backend. 
        <br/><br/>
        Example: Crontab
        <code style="background-color: #eee; display: block; padding: 5px">* * * * * wget quiet no-cache -O - [https://www.yourdomain.com]/index.php?cl=sxproductsearch_cron > /dev/null
</code>
        Ideally, the execution should take place every minute, as shown in the example.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Important notes</b>
        <b style="display: block; margin: 0 0 5px 0">Language configuration</b>
        You need separate login data for Site Search 360 for each language. You can enter these in the module settings of this plugin. 
        After you make a change to the language configuration of your system (Menu <i>Master Settings</i> > <i>Languages</i>), e.g. adding a new language, it may be necessary to reinstall the module to enter the Site Search 360 access data for this language.
        Your previous configuration <b>WONT</b> be lost during this process!
        <br/><br/>
        <b style="display: block; margin: 0 0 5px 0">Global configuration (<i>masterConfig.json</i>)</b>
        Via the file <i>/export/semknox/masterConfigLANGUAGE.json</i>, while LANGUAGE has to be replaced with the Language shortcut (e.g. <i>masterConfigEn.json</i>), module settings can be globally overwritten <b>per language</b>. The values set there apply to <b>ALL active shops and languages</b> (in which the module is activated). 
        If the values <i>apiKey</i> and <i>projectId</i> are set in this file, all records are synchronized with only one account at Site Search 360. In this case, different values are realized via different <i>userGroups</i> (corresponds to the oxide <i>ShopIds</i>). The same applies to the search in the frontend.',
        'de' =>
        'OXID Modul zur Integration und Verwendung der Site Search 360.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Voraussetzungen</b>
        <b style="display: block; margin: 0 0 5px 0">Cronjob</b>
        Der folgende Cronjob wird für den initialen Produktupload und die Produktsynchronisation mit dem Site Search 360 Backend benötigt. 
        <br/><br/>
        Beispiel: Crontab Eintrag
        <code style="background-color: #eee; display: block; padding: 5px">* * * * * wget quiet no-cache -O - [https://www.yourdomain.com]/index.php?cl=sxproductsearch_cron > /dev/null</code>
        Die Ausführung sollte idealerweise, wie im Beispiel gezeigt, jede Minute erfolgen.
        <b style="color:#000;font-size: 15px;padding: 10px 0 10px 0;display: block;">Wichtige Hinweise</b>
        <b style="display: block; margin: 0 0 5px 0">Sprachkonfiguration</b>
        Sie benötigen für jede Sprache seperate Zugangsdaten für die Site Search 360. Diese können Sie unter den Moduleinstellungen dieses Plugins eingeben. 
        Nachdem Sie eine Veränderung an der Sprachkonfiguration ihres Systems (Menüpunkt <i>Stammdaten</i> > <i>Sprachen</i>) vornehmen (z.B. Hinzufügen einer neuen Sprache), kann es notwendig sein das Plugin erneut zu installieren, um die Site Search 360 Zugangsdaten für diese Sprache eingeben zu können.
        Ihre bisherige Konfiguration geht bei diesem Vorgang <b>NICHT</b> verloren!
        <br/><br/>
        <b style="display: block; margin: 0 0 5px 0">Globale Konfiguration auf Dateiebene (<i>masterConfig.json</i>)</b>
        Über die Datei <i>/export/semknox/masterConfigSPRACHE.json</i>, wobei SPRACHE mit dem Sprachkürzel der Sprache ersetzt werden muss (z.B. <i>masterConfigDe.json</i>), können Moduleinstellungen global <b>für jede Sprache</b> überschrieben werden. Die dort gesetzen Werte gelten für <b>ALLE aktiven Shops und Sprachen</b> (in denen das Modul aktiviert ist). 
        <br/>Werden in dieser Datei die Werte <i>apiKey</i> und <i>projectId</i> gesetzt, so werden die Datensätze mit nur einem einzigen Account bei Site Search 360 synchronisiert. Abweiche Werte werden in diesem Fall über verschieden <i>userGroups</i> (entspricht den Oxid <i>ShopIds</i>) realisiert. Gleiches gilt für die Suche im Frontend.'
    ],
    'thumbnail' => 'logo.png',
    'author' => 'Zoovu (Deutschland) GmbH',
    'url' => 'https://www.sitesearch360.com',
    'email' => 'oxid@sitesearch360.com',
    'controllers'  => [
        'sxproductsearch_ajax' => Semknox\Productsearch\Application\Controller\Admin\AjaxController::class,
        'sxproductsearch_cron' => Semknox\Productsearch\Application\Controller\CronController::class
    ],
    'extend' => [
        \OxidEsales\Eshop\Application\Model\Article::class => \Semknox\Productsearch\Application\Model\Article::class,
        \OxidEsales\Eshop\Application\Model\ArticleList::class => \Semknox\Productsearch\Application\Model\ArticleList::class,
        \OxidEsales\Eshop\Application\Model\Search::class => \Semknox\Productsearch\Application\Model\Search::class,
        \OxidEsales\Eshop\Application\Controller\SearchController::class => \Semknox\Productsearch\Application\Controller\SearchController::class,
        \OxidEsales\Eshop\Application\Controller\ArticleListController::class => \Semknox\Productsearch\Application\Controller\ArticleListController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => \Semknox\Productsearch\Core\ViewConfig::class,
        \OxidEsales\Eshop\Core\Language::class => \Semknox\Productsearch\Core\Language::class
    ],
    'templates' => [
        'admin_sxproductsearch_ajax.tpl'   => 'semknox/semknox-oxid/Application/views/admin/tpl/admin_sxproductsearch_ajax.tpl',
        'sxproductsearch_cron.tpl'   => 'semknox/semknox-oxid/Application/views/tpl/sxproductsearch_cron.tpl',
    ],
    'blocks' => [

        /*
        * BACKEND
        */ 

        [
            'template' => 'bottomnaviitem.tpl',
            'block'=>'admin_bottomnavicustom',
            'file'=>'Application/views/admin/blocks/admin_bottomnavicustom.tpl'
        ],


        /*
        * FRONTEND
        */     
        [
            'template' => 'page/search/search.tpl',
            'block' => 'search_header',
            'file' => 'Application/views/blocks/header.tpl'
        ],
        [
            'template' => 'page/list/list.tpl',
            'block' => 'page_list_listhead',
            'file' => 'Application/views/blocks/header.tpl'
        ],
        [
            'template' => 'page/search/search.tpl',
            'block' => 'search_top_listlocator',
            'file' => 'Application/views/blocks/top_listlocator.tpl'
        ],
        [
            'template' => 'page/list/list.tpl',
            'block' => 'page_list_upperlocator',
            'file' => 'Application/views/blocks/top_listlocator.tpl'
        ],
        [
            'template' => 'layout/sidebar.tpl',
            'block' => 'sidebar_categoriestree',
            'file' => 'Application/views/blocks/sidebar_categoriestree.tpl'
        ],
        [
            'template' => 'widget/locator/attributes.tpl',
            'block' => 'widget_locator_attributes',
            'file' => 'Application/views/blocks/widget_locator_attributes.tpl'
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
            'value' => 'true',
            'position' => 4
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxUploadActive' . $oxid,
            'type' => 'bool',
            'value' => 'true',
            'position' => 5
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxIncrementalUpdatesActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 6
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxAnswerActive' . $oxid,
            'type' => 'bool',
            'value' => 'true',
            'position' => 7
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxFilterOptionCounterActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 8
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxMoveFilterToSidebar' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 9
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxHideRangeInRangeSliderTitle' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 10
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxCategoryQuery' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 11
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxResultProduct' . $oxid,
            'type' => 'select',
            'value' => 'individualVariantProduct',
            'constraints' => 'individualVariantProduct|parentProduct',
            'position' => 12
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxFilterGroupUnfoldCount' . $oxid,
            'type' => 'str',
            'value' => '5',
            'position' => 13
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxSendInactiveArticles' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 14
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxIgnoreOutOfStockArticles' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 15
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings' . $oxid,
            'name' => 'sxSeoUrlsActive' . $oxid,
            'type' => 'bool',
            'value' => 'false',
            'position' => 16
        );

        $settings[] = array(
            'group' => 'SemknoxProductsearchLanguageSettings'. $oxid,
            'name' => 'sxRelevanceSortingTranslation' . $oxid,
            'type' => 'str',
            'value' => '',
            'position' => 17
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