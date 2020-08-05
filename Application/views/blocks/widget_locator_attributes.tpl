[{$smarty.block.parent}]

<script type="text/javascript" src="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.js')}]"></script>
<link rel="stylesheet" type="text/css" href="[{$oViewConf->getModuleUrl('sxproductsearch','out/lib/nouislider/nouislider.min.css')}]" />
[{if $oView->getRangeAttributes()}]
    <form method="post" action="[{$oViewConf->getSelfActionLink()}]" name="_filterlistSx" id="filterListSx">
    <div class="listFilter js-fnSubmit clear">
        [{foreach from=$oView->getRangeAttributes() item=oFilterAttr key=sAttrID name=attr}]
            <div class="dropDown js-fnSubmit" id="attributeFilter[[{$sAttrID}]]">
                <p>
                    <label>[{$oFilterAttr->getTitle()}]: </label>
                    <div class="slider" id="attributeRangeFilter[[{$sAttrID}]]"></div>
                </p>
                <script type="text/javascript">
                    noUiSlider.create(document.getElementById("attributeRangeFilter[[{$sAttrID}]]"), {
                        start: [20, 80],
                        connect: true,
                        range: {
                            'min': 0,
                            'max': 100
                        }
                    });
                </script>
                <!--input type="hidden" name="attrfilter[[{$sAttrID}]]" value="[{$oFilterAttr->getActiveValue()}]">
                <ul-- class="drop FXgradGreyLight shadow">
                    [{if $oFilterAttr->getActiveValue()}]
                        <li><a data-selection-id="" href="#">[{oxmultilang ident="PLEASE_CHOOSE"}]</a></li>
                    [{/if}]
                    [{foreach from=$oFilterAttr->getValues() item=sValue}]
                        <li><a data-selection-id="[{$sValue}]" href="#" [{if $oFilterAttr->getActiveValue() == $sValue}]class="selected"[{/if}] >[{$sValue}]</a></li>
                    [{/foreach}]
                </ul-->
            </div>
        [{/foreach}]
    </div>
    </form>
[{/if}]