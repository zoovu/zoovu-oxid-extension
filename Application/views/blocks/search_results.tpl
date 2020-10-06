[{if $oViewConf->getSxConfigValue('moveFilterToSidebar')}]
    [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true}]

    [{capture append="oxidBlock_sidebar"}]
        <div class="box well well-sm hidden-sm hidden-xs    [{* <= default classes/styling of flow-theme*}]
                    card bg-light d-none d-lg-block         [{* <= default classes/styling of wave-theme*}]
                    sxFilterBoxSidebar" id="filterBox">
            <div class="page-header h3   [{* <= default classes/styling of flow-theme*}]
                        card-header      [{* <= default classes/styling of wave-theme*}]"
                        >Filter</div>
  
            [{include file="widget/locator/listlocator.tpl" attributes=$oView->getAttributes()}]
        </div>
    [{/capture}]


[{else}]
    [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true attributes=$oView->getAttributes()}]
[{/if}]

[{$smarty.block.parent}]
