<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Application\Controller\ArticleListController;
use OxidEsales\Eshop\Core\Registry;

use Semknox\Productsearch\Application\Model\SxHelper;


class SearchController extends SearchController_parent
{
    /**
     * get search page headline
     * 
     * @return string 
     */
    public function getSearchHeader()
    {
        // fallback, if not active for current shop
        if (!$this->_aArticleList->isSxArticleList) return '';

        return $this->_aArticleList->getArticleListInterpretation();
    }


    /**
     * Template variable getter. Returns sorting columns
     *
     * @return array
     */
    public function getSortColumns()
    {
        // fallback, if not active for current shop
        if (!$this->_aArticleList->isSxArticleList) return parent::getSortColumns();

        return $this->_aArticleList->getAvailableSortingOptions();
    }

   

    /**
     * Returns default category sorting for selected category
     *
     * @return array
     */
    public function getUserSelectedSorting()
    {
        $request = Registry::get(\OxidEsales\Eshop\Core\Request::class);
        $sortBy = $request->getRequestParameter($this->getSortOrderByParameterName());
        $sortOrder = $request->getRequestParameter($this->getSortOrderParameterName());

        if ($sortBy && $sortOrder && SxHelper::isEncodedOption($sortBy)) {
            return ['sortby' => $sortBy, 'sortdir' => $sortOrder];
        }

        return parent::getUserSelectedSorting();
    }



    public function getSortingSql($ident)
    {
        $sorting = $this->getSorting($this->getSortIdent());
        $sortBy = $sorting['sortby'];

        if (SxHelper::isEncodedOption($sortBy)) {
            // acitve semknox search shop
            return $sorting;
        }

        // fallback, if not active for current shop
        $sortingSql = parent::getSortingSql($ident);
        return $sortingSql == '``' ? false : $sortingSql;
    }


    /**
     * get attributeList for filtering
     * 
     * @return mixed 
     */
    public function getAttributes()
    {
        return $this->_aArticleList->getAvailableFilters();
    }



    /**
     * Stores chosen category filter into session.
     *
     * Session variables:
     * <b>session_attrfilter</b>
     */
    public function executefilter()
    {
        return ArticleListController::executefilter();
    }

}
