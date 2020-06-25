<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxHelper;

class SxQueue {

    protected $_sxHelper;
    
    protected $_queuePath;
    protected $_queue = 'update';


    public function __construct()
    {
        $this->_sxHelper = new SxHelper();
        $this->set($this->_queue);
    }

    /**
     * set current queue 
     * 
     * @param string $queue delete|update
     * @return void 
     */
    public function set($queue = 'update')
    {
        $this->_queue = $queue;

        if($this->_queue == 'delete'){
            $this->_queuePath = $this->_sxHelper->get('sxDeleteQueuePath');
        } else {
            $this->_queuePath = $this->_sxHelper->get('sxUpdateQueuePath');
        }
    }

    /**
     * add article to queue
     * 
     * @param mixed $articleIds int|array
     * @return void 
     */
    public function addArticle($articleIds)
    {
        $path = $this->_queuePath;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if(!is_array($articleIds)) $articleIds = [$articleIds];

        foreach($articleIds as $articleId){
            touch($path . $articleId);
        }
    }

    /**
     * get articles from queue
     * 
     * @param int $qty 
     * @return array 
     */
    public function getArticles($qty = 20)
    {
        $path = $this->_queuePath;

        if (!file_exists($path)) {
            return [];
        }

        $articleIds = array_diff(scandir($path), array('..', '.'));

        return array_slice($articleIds, 0, $qty);
    }

    /**
     * remove articles from queue
     * 
     * @param mixed $articleIds  int|array
     * @return void 
     */
    public function removeArticle($articleIds)
    {
        $path = $this->_queuePath;

        if (!is_array($articleIds)) $articleIds = [$articleIds];

        foreach ($articleIds as $articleId) {
            if (file_exists($path . $articleId)) {
                unlink($path . $articleId);
            }
        }

    }

    /**
     * empty queue
     * 
     * @return void 
     */
    public function empty()
    {
        $path = $this->_queuePath;

        if (file_exists($path)) {
            array_map('unlink', glob("$path*"));
        }
    }

}