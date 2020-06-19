<?php declare(strict_types=1);

namespace Semknox\Productsearch\Events;

use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelInsertEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelUpdateEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelDeleteEvent;
use OxidEsales\Eshop\Application\Model\Article;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;

class ArticleUpdateEventSubscriber extends AbstractShopAwareEventSubscriber
{

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logDatabaseActivity(Event $event)
    {
        $model = $event->getModel();

        if($model instanceof Article){
            $id = $model->getId();

            $action = 'add/update';
            if($event instanceof AfterModelDeleteEvent){
                $action = 'delete';
            }

            $this->logger->info("Added article to $action queue: $id");
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            AfterModelUpdateEvent::NAME => 'logDatabaseActivity',
            AfterModelInsertEvent::NAME => 'logDatabaseActivity',
            AfterModelDeleteEvent::NAME => 'logDatabaseActivity'
        ];
    }
}
