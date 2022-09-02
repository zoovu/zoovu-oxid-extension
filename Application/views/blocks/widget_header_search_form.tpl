[{$smarty.block.parent}]

<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/css/sxproductsearch.css')}]" />
<script type="text/javascript">

    [{assign var="projectId" value=$oViewConf->getSxConfigValue('projectId')}]
    [{assign var="userGroup" value=$oViewConf->getSxConfigValue('userGroup')}]
    [{assign var="apiUrl" value=$oViewConf->getSxConfigValue('apiUrl')}]
    [{assign var="dataPoints" value=$oViewConf->getSxConfigValue('dataPoints','{}')}]
    [{assign var="currentStoreUrl" value=$oViewConf->getHomeLink()}]
    [{assign var="sxproductsearchVersion" value=$oViewConf->getSxConfigValue('version','undefined')}]
    [{assign var="sxproductsearchTitle" value=$oViewConf->getSxConfigValue('title','sxproductsearch')}]

    [{assign var="masterStoreUrl" value=$oViewConf->getSxConfigValue('masterStoreUrl','EMPTY')}]
    [{assign var="replaceMasterStoreUrlInSS360Result" value=$oViewConf->getSxConfigValue('replaceMasterStoreUrlInSS360Result',false)}]

    /* eslint-disable */
    (function () {
        var siteId = '[{$projectId}]'; // the project id
        window.ss360Config = {
            siteId: siteId,
            baseUrl: '[{$apiUrl}]search?projectId=' + siteId + '&userGroup=[{$userGroup}]',
            suggestBaseUrl: '[{$apiUrl}]search/suggestions?projectId=' + siteId + '&userGroup=[{$userGroup}]',
            suggestions: {
                dataPoints: [{$dataPoints}]
            },
            searchBox: {
                selector: 'input[name="searchparam"]', // search box css selector
                searchButton: '.form.search .btn-primary', // search button css selector (makes the search suggestions extend over the full search form width)
                preventFormParentSubmit: false // prevents the search plugin from preventing search form submit
            },
            results: {
                ignoreEnter: true // search plugin will ignore enter keys (won't submit search on enter)
            },
            callbacks: {
                preSearch: function (query) { // handle query suggestions

                    var searchForm = document.querySelectorAll('form.form.search')[0];
                    //var searchForm = document.getElementsByName('search')[0];

                    var searchBox = document.querySelectorAll('form.form.search input[name=searchparam]')[0]; 
                    //var searchBox = document.getElementsByName('searchparam')[0];

                    searchBox.value = query;
                    searchForm.submit();
                    return false; // prevent search
                },
                suggestLine: function (suggestLine, key, index, suggest) {

                    // shop specific url-slug
                    var specificShopUrl = '';
                    if(suggest.dataPoints != undefined){
                        for (i = 0; i < suggest.dataPoints.length; i++) {
                            if(suggest.dataPoints[i].key == 'shop-specific-url'){

                                suggestLine = suggestLine.replace(/href="(.*?)"/, function(m, $1) {
                                    return 'href="' + suggest.dataPoints[i].value + '"';
                                });

                            }
                        } 
                    }

                    // shop specific master URL
                    if( !"[{$replaceMasterStoreUrlInSS360Result}]" || "[{$masterStoreUrl}]"=="EMPTY" ) return suggestLine;

                    var replaceUrl = "[{$currentStoreUrl}]";
                    replaceUrl = replaceUrl.substr(0, replaceUrl.lastIndexOf("/") +1);

                    suggestLine = suggestLine.replace(/[{$masterStoreUrl}]/g, replaceUrl);
                    return suggestLine;
                }
            }
        };	var e = document.createElement('script');
        e.src = 'https://cdn.sitesearch360.com/v13/sitesearch360-v13.min.js';
        document.getElementsByTagName('body')[0].appendChild(e);
    }());

    console.info('[{$sxproductsearchTitle}] OXID-Module [{$sxproductsearchVersion}]');
</script>
[{if $oViewConf->getSxConfigValue('additionalCss')}]
    <style>[{$oViewConf->getSxConfigValue('additionalCss')}]</style>
[{/if}]