<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxHelper;
use OxidEsales\Eshop\Core\Registry;

class Article extends Article_parent
{

    /**
     * This function is triggered whenever article is saved or deleted or after the stock is changed.
     * Originally we need to update the oxstock for possible article parent in case parent is not buyable
     * Plus you may want to extend this function to update some extended information.
     * Call \OxidEsales\Eshop\Application\Model\Article::onChange($sAction, $sOXID) with ID parameter when changes are executed over SQL.
     * (or use module class instead of oxArticle if such exists)
     *
     * @param string $action          Action constant
     * @param string $articleId       Article ID
     * @param string $parentArticleId Parent ID
     *
     * @return null
     */
    public function onChange($action = null, $articleId = null, $parentArticleId = null)
    {
        parent::onChange($action, $articleId, $parentArticleId);

        // from parent function
        if (!isset($articleId)) {
            if ($this->getId()) {
                $articleId = $this->getId();
            }
            if (!isset($articleId)) {
                $articleId = $this->oxarticles__oxid->value;
            }
            if ($this->oxarticles__oxparentid && $this->oxarticles__oxparentid->value) {
                $parentArticleId = $this->oxarticles__oxparentid->value;
            }
        }

        if(!isset($articleId)) return;

        $sxHelper = new SxHelper();
        if($action == ACTION_DELETE){
            $sxHelper->addToDeleteQueue($articleId);
        } else {
            $sxHelper->addToUpdateQueue($articleId);
        }

    }


}
