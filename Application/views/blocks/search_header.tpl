[{if $oView->getSearchHeader() != ""}]
    [{assign var="search_head" value=$oView->getSearchHeader()}]
[{/if}]

[{$smarty.block.parent}]
