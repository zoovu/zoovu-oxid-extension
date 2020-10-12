[{if $oView->getSearchHeader() != ""}]
    [{assign var="search_head" value=$oView->getSearchHeader()}]
[{/if}]

[{if $oView->showSearch() && $oViewConf->getSxConfigValue('moveFilterToSidebar')}]
    [{assign var="sidebar" value="left"}]
[{/if}]

[{$smarty.block.parent}]
