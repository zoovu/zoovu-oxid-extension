[{if $oView->isSearchPage()}]
    [{if $oViewConf->getSxConfigValue('moveFilterToSidebar')}]
        [{assign var="sidebar" value="left"}]
    [{/if}]

    [{if $oView->getSearchHeader() != ""}]
        [{assign var="search_head" value=$oView->getSearchHeader()}]
    [{/if}]
[{/if}]

[{if $oViewConf->getSxConfigValue('categoryQuery') && $oViewConf->getSxConfigValue('moveFilterToSidebar')}]
    [{assign var="sidebar" value="left"}]
[{/if}]

[{$smarty.block.parent}]
