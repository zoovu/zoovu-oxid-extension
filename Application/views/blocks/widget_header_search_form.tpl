[{$smarty.block.parent}]

<script type="text/javascript">

    [{assign var="projectId" value=$oViewConf->getSxProjectId()}]
    [{assign var="userGroup" value=$oViewConf->getSxUserGroup()}]
    [{assign var="apiUrl" value=$oViewConf->getSxApiUrl()}]

    /* eslint-disable */
    (function () {
        var siteId = '[{$projectId}]'; // the project id
        window.ss360Config = {
            siteId: siteId,
            baseUrl: '[{$apiUrl}]search?projectId=' + siteId + '&userGroup=[{$userGroup}]',
            suggestBaseUrl: '[{$apiUrl}]search/suggestions?projectId=' + siteId + '&userGroup=[{$userGroup}]',
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
                    var searchForm = document.getElementsByName('search')[0];
                    var searchBox = document.getElementsByName('search')[0];
                    searchBox.value = query;
                    searchForm.submit();
                    return false; // prevent search
                }
            }
        };	var e = document.createElement('script');
        e.src = 'https://cdn.sitesearch360.com/v13/sitesearch360-v13.min.js';
        document.getElementsByTagName('body')[0].appendChild(e);
    }());
</script>