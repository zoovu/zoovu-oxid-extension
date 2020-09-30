[{if $oViewConf->getSxConfigValue('moveFilterToSidebar')}]
    [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true}]

    [{capture append="oxidBlock_sidebar"}]
        <div class="box well well-sm hidden-sm hidden-xs sxFilterBox" id="filterBox">
            <div class="page-header h3">Filter</div>
            [{include file="widget/locator/listlocator.tpl" attributes=$oView->getAttributes()}]
        </div>
    [{/capture}]


[{else}]
    [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true attributes=$oView->getAttributes()}]
[{/if}]

[{$smarty.block.parent}]
