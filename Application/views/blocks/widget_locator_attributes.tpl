[{$smarty.block.parent}]

<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.js')}]"></script>
<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/js/sxproductsearch.js')}]"></script>
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.css')}]" />
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/css/sxproductsearch.css')}]" />
[{if $oView->isSxSearch && $oView->getRangeAttributes()}]
    <script type="text/javascript">
        var sxRangeFilter = [];
    </script>
    [{foreach from=$oView->getRangeAttributes() item=oFilterAttr key=sAttrID name=attr}]
        <div class="sxRangeFilter">
            <label>[{$oFilterAttr->getTitle()}]: </label>
            <div class="slider" id="attrfilter[[{$sAttrID}]]"></div>

            [{foreach from=$oFilterAttr->getValues() item=sValue}]
                [{assign var="valueRange" value="___"|explode:$sValue}]
            [{/foreach}]
            [{assign var="activeValueRange" value="___"|explode:$oFilterAttr->getActiveValue()}]

            <script type="text/javascript">
                sxRangeFilter["[{$sAttrID}]"] = noUiSlider.create(document.getElementById("attrfilter[[{$sAttrID}]]"), {
                    start: [[{$activeValueRange[0]}], [{$activeValueRange[1]}]],
                    connect: true,
                    range: {
                        'min': [{$valueRange[0]}],
                        'max': [{$valueRange[1]}]
                    },
                    tooltips: true,
                });
                sxRangeFilter["[{$sAttrID}]"].on('end', sxRangeFilterAction);
            </script>

            [{if $valueRange[0] !== $activeValueRange[0] || $valueRange[1] !== $activeValueRange[1] }]
                <input type="hidden" name="attrfilter[[{$sAttrID}]]" id="attrfilter[[{$sAttrID}]]_input" value="[{$activeValueRange[0]}]___[{$activeValueRange[1]}]">
            [{else}]
                <input type="hidden" name="attrfilter[[{$sAttrID}]]" id="attrfilter[[{$sAttrID}]]_input">
            [{/if}]

        </div>
    [{/foreach}]
    <script type="text/javascript">
        var filterForm = document.getElementById("filterList"); 
        var rangeFilters = document.getElementsByClassName("sxRangeFilter"); 

        for (var i = 0; i < rangeFilters.length; i++) {
            filterForm.appendChild(rangeFilters[i]);
        }
    </script>
[{/if}]
