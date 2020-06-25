[{$smarty.block.parent}]

[{if $sModuleId == "semknox_semknox-oxid"}]
	[{foreach from=$languages item=language}]
		<li id="sxStatusPending[{$language->oxid}]" class="sxHide">
			<a 	href="javascript: void(0)" 
				class="sxUploadAction"
				onClick="sxAjaxControllerClick('[{$oViewConf->getSelfLink()}]cl=sxproductsearch_ajax&fnc=startFullUpload&shopId=[{$shopid}]&shopLang=[{$language->oxid}]')">
				[{oxmultilang ident="SHOP_MODULE_sxStartInitialUpload"}] ([{$language->name}])
			</a> |
		</li>
		<li id="sxStatusProcessing[{$language->oxid}]" class="sxHide">
			<span class="sxUploadPercentItem" id="sxUploadPercentItem[{$language->oxid}]">0%</span>
			<div class="sxProgressBar">
				<div class="sxBarStatus export" id="sxBarStatus[{$language->oxid}]"></div>
			</div>
			<a 	href="javascript: void(0)"
				class="sxUploadAction"
				onClick="sxAjaxControllerClick('[{$oViewConf->getSelfLink()}]cl=sxproductsearch_ajax&fnc=stopFullUpload&shopId=[{$shopid}]&shopLang=[{$language->oxid}]')">
				[{oxmultilang ident="SHOP_MODULE_sxStopInitialUpload"}] ([{$language->name}])
			</a> |
		</li>
	[{/foreach}]
	<li class="sxHide" id="sxCronCall">
		<a href="/?cl=sxproductsearch_cron" target="blank">
			[{oxmultilang ident="SHOP_MODULE_sxCronTester"}]
		</a> |
	</li>

	<meta name="sxActiveShops" id="sxActiveShops" content='[{$languages|@json_encode nofilter}]'>
	<meta name="sxActiveShopId" id="sxActiveShopId" content='[{$shopid}]'>
	<meta name="sxUploadStatusUrl" id="sxUploadStatusUrl" content="[{$oViewConf->getSelfLink()}]cl=sxproductsearch_ajax&fnc=getStatus">

	[{oxscript include=$oViewConf->getModuleUrl('sxproductsearch','out/admin/src/js/sxproductsearch.js')}]

    <!-- because oxstyle does not work in admin -->
    <link rel="stylesheet" href="[{$oViewConf->getModuleUrl("sxproductsearch")}]out/admin/src/css/sxproductsearch.css"> 
[{/if}]