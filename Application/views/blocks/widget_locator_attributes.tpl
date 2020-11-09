[{$smarty.block.parent}]

[{if $oView->isSxSearch && $oView->getAttributeOptions()}]
    <script type="text/javascript">
        var sxAttributeOptions = [{$oView->getAttributeOptions()}];
    </script>
[{/if}]

<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.js')}]"></script>
<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/js/sxproductsearch.js')}]"></script>
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.css')}]" />
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/css/sxproductsearch.css')}]" />

[{if $inSidebar}]
    [{assign var="idSuffix" value="Sidebar"}]
[{else}]
    [{assign var="idSuffix" value=""}]
[{/if}]


[{if $oView->isSxSearch && $oView->getRangeAttributes()}]
    [{foreach from=$oView->getRangeAttributes() item=oFilterAttr key=sAttrID name=attr}]
        <div class="sxRangeFilter sxIn[{$idSuffix}]">

            [{foreach from=$oFilterAttr->getValues() item=sValue}]
                [{assign var="valueRange" value="___"|explode:$sValue}]
            [{/foreach}]
            [{assign var="activeValueRange" value="___"|explode:$oFilterAttr->getActiveValue()}]

            <label>[{$oFilterAttr->getTitle()}]: </label>
            <div class="slider-wrapper">
                <div class="slider" id="[{$sAttrID}][{$idSuffix}]"></div>
                <div class="slider-helper">
                    <input class="js-style form-control form-control-sm" data-input-type="min" value="[{$activeValueRange[0]}]" min="[{$valueRange[0]}]" type="number" name="num1">
                    <span>-</span>
                    <input class="js-style form-control form-control-sm" data-input-type="max" value="[{$activeValueRange[1]}]" max="[{$valueRange[1]}]" type="number" name="num2">
                    <span class="unit">[{$oFilterAttr->unit}]</span>
                    <button class="js-style-btn btn btn-default btn-sm" type="button"><i class="fa fa-angle-right"></i></button>
                </div>
            </div>

            <script type="text/javascript">
                sxRangeFilter["[{$sAttrID}][{$idSuffix}]"] = noUiSlider.create(document.getElementById("[{$sAttrID}][{$idSuffix}]"), {
                    start: [[{$activeValueRange[0]}], [{$activeValueRange[1]}]],
                    connect: true,
                    range: {
                        'min': [{$valueRange[0]}],
                        'max': [{$valueRange[1]}]
                    },
                    tooltips: true,
                    [{if $valueRange[2] == 'integer'}]
                        step: 1,
                    [{/if}]
                });
                sxRangeFilter["[{$sAttrID}][{$idSuffix}]"].on('end', sxRangeFilterAction);

                sxFilterToHide.push("attrfilter[[{$sAttrID}]]");
            </script>

        </div>
    [{/foreach}]
[{/if}]

