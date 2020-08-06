[{$smarty.block.parent}]

<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.js')}]"></script>
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.css')}]" />
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/css/sxproductsearch.css')}]" />
[{if $oView->getRangeAttributes()}]
    <form method="post" action="[{$oViewConf->getSelfActionLink()}]" name="_filterlistSx" id="filterListSx">
        <div class="listFilter js-fnSubmit clear">
        [{foreach from=$oView->getRangeAttributes() item=oFilterAttr key=sAttrID name=attr}]
            <div class="sxRangeFilter" id="attributeFilter[[{$sAttrID}]]">
                <label>[{$oFilterAttr->getTitle()}]: </label>
                <div class="slider" id="attributeFilter[[{$sAttrID}]]"></div>
                <script type="text/javascript">
                    [{foreach from=$oFilterAttr->getValues() item=sValue}]
                        [{assign var="valueRange" value="___"|explode:$sValue}]
                    [{/foreach}]
                    [{assign var="activeValueRange" value="___"|explode:$oFilterAttr->getActiveValue()}]
                    noUiSlider.create(document.getElementById("attributeFilter[[{$sAttrID}]]"), {
                        start: [[{$activeValueRange[0]}], [{$activeValueRange[1]}]],
                        connect: true,
                        range: {
                            'min': [{$valueRange[0]}],
                            'max': [{$valueRange[1]}]
                        },
                        tooltips: true,
                    });
                </script>
            </div>
        [{/foreach}]
        </div>
    </form>
[{/if}]