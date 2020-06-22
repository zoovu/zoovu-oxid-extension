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

    public function set($queue = 'update')
    {
        $this->_queue = $queue;

        if($this->_queue == 'delete'){
            $this->_queuePath = $this->_sxHelper->get('sxDeleteQueuePath');
        } else {
            $this->_queuePath = $this->_sxHelper->get('sxUpdateQueuePath');
        }
    }


    public function addArticle($articleId)
    {
        $path = $this->_queuePath;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        touch($path . $articleId);
    }

    public function removeArticle($articleId)
    {
        $path = $this->_queuePath;

        if (file_exists($path . $articleId)) {
            unlink($path . $articleId);
        }
    }

    public function empty()
    {
        $path = $this->_queuePath;

        if (file_exists($path)) {
            array_map('unlink', glob("$path*"));
        }
    }

}