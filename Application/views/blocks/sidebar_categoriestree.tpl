[{if $oViewConf->getSxConfigValue('categoryQuery')}]
    <!-- remove categories tree --> 
[{else}]
    [{$smarty.block.parent}]
[{/if}]