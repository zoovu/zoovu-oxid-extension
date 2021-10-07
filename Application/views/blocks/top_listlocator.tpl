[{if $oView->isSearchPage() || $oViewConf->getSxConfigValue('categoryQuery')}]

    [{capture append="oxidBlock_head"}]
        <script type="text/javascript">
            var sxRangeFilter = [];
            var sxFilterToHide = [];
        </script>
    [{/capture}]

    [{if $oViewConf->getSxConfigValue('moveFilterToSidebar')}]

        [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true attributes=$oView->getAttributes()}]

        [{capture append="oxidBlock_sidebar"}]

            [{if count($oView->getAttributes()) gt 0 }]
                <div class="box well well-sm hidden-sm hidden-xs    [{* <= default classes/styling of flow-theme*}]
                            card bg-light d-none d-lg-block         [{* <= default classes/styling of wave-theme*}]
                            sxFilterBoxSidebar" id="filterBox">
                    <div class="page-header h3   [{* <= default classes/styling of flow-theme*}]
                                card-header      [{* <= default classes/styling of wave-theme*}]"
                                >[{oxmultilang ident="DD_LISTLOCATOR_FILTER_ATTRIBUTES"}]</div>
        
                    [{include file="widget/locator/listlocator.tpl" attributes=$oView->getAttributes() inSidebar=true}]
                </div>
            [{/if}]

        [{/capture}]

    [{else}]
        [{include file="widget/locator/listlocator.tpl" locator=$oView->getPageNavigationLimitedTop() listDisplayType=true itemsPerPage=true sort=true attributes=$oView->getAttributes()}]
    [{/if}]

    <script type="text/javascript">

        // hide all replaced range-filter
        for (var i = 0; i < sxFilterToHide.length; i++) {
            sxFoundFilters = document.getElementsByName(sxFilterToHide[i]);
            for (var j = 0; j < sxFoundFilters.length; j++) {
                sxFoundFilters[j].parentNode.setAttribute('style','display:none !important;');
            }
        }

        // find sidebar and topbar filter container
        var filterListForms = document.getElementsByName('_filterlist');
        var sxForms = [];
        for (var i = 0; i <  filterListForms.length; i++) {
            if(hasSomeParentTheClass(filterListForms[i],'sxFilterBoxSidebar')){
                sxForms['sidebar'] = filterListForms[i];
            } else {
                sxForms['topbar'] = filterListForms[i];

                // add large-hidden class
                [{if $oViewConf->getSxConfigValue('moveFilterToSidebar')}]

                    // wave theme
                    var sxFilterAttributeContainer = getParentWithClass(sxForms['topbar'],'filter-attributes');

                    // flow theme
                    if(!sxFilterAttributeContainer){
                        sxFilterAttributeContainer = getParentWithClass(sxForms['topbar'],'list-filter');
                    }

                    if(sxFilterAttributeContainer){
                        sxFilterAttributeContainer.classList.add('d-lg-none'); // wave theme

                        sxFilterAttributeContainer.classList.add('hidden-md'); // flow theme
                        sxFilterAttributeContainer.classList.add('hidden-lg'); // flow theme
                    }

                [{/if}]
            }
        }

        // move range sliders to correct form
        var sidebarRangeSliders = document.getElementsByClassName("sxInSidebar");
        for (var i = 0; i <  sidebarRangeSliders.length; i++) {
            sxForms['sidebar'].appendChild(sidebarRangeSliders[i]);
        }

        var topbarRangeSliders = document.getElementsByClassName("sxIn");
        for (var i = 0; i <  topbarRangeSliders.length; i++) {
            sxForms['topbar'].appendChild(topbarRangeSliders[i]);
        }


        // add toggle list feature
        if(sxForms['sidebar']){
            var filterLists = sxForms['sidebar'].getElementsByClassName("dropdown-menu");
            for (var i = 0; i < filterLists.length; i++) {

                if(!filterLists[i].classList.contains('showAll')){
                    filterListsItems = filterLists[i].getElementsByTagName("LI");
                    if(filterListsItems.length > 8){

                        button = document.createElement('button');
                        button.classList.add("show-more");
                        button.setAttribute("type", "button");
                        button.innerHTML = '[{oxmultilang ident="SX_show_more"}]';
                        filterLists[i].parentNode.appendChild(button);
                    }
                }
            }
        }

        document.addEventListener('click', function (event) {

            // If the clicked element doesn't have the right selector, bail
            if (!event.target.matches('button.show-more')) return;
            event.preventDefault();

            parent = event.target.parentNode.getElementsByClassName('dropdown-menu')[0];
            if(event.target.innerHTML == '[{oxmultilang ident="SX_show_more"}]'){
                parent.classList.add('showAll');
                event.target.innerHTML = '[{oxmultilang ident="SX_show_less"}]';
            } else {
                parent.classList.remove('showAll');
                event.target.innerHTML = '[{oxmultilang ident="SX_show_more"}]';
            }

        }, false);


    </script>


[{else}]
    <!-- do not remove listlocator -->
    [{$smarty.block.parent}]
[{/if}]
