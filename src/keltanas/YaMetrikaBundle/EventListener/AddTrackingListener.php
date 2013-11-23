<?php
/**
 * Tracking referals
 * @author: Nikolay Ermin <keltanas@gmail.com>
 */

namespace keltanas\YaMetrikaBundle\EventListener;

use Doctrine\ORM\EntityManager;
use keltanas\TrackingBundle\Entity\History;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class AddTrackingListener
{
    /**
     * @var boolean
     */
    private $debug;

    /** @var array */
    private $params;

    /** @var string */
    private $ya_tracking;

    /** @var TwigEngine */
    private $templating;


    public function __construct(TwigEngine $templating, $ya_tracking, array $params = [], $debug = false)
    {
        $this->templating = $templating;
        $this->ya_tracking = $ya_tracking;
        $this->params = $params;
        $this->debug = $debug;
    }

    /**
     * Store value to cookie
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $config = [
            'id' => $this->ya_tracking,
            'accurateTrackBounce' => 5000, // показатель отказов: true | false | 10000 (ms)
            'clickmap' => true, // Включает карту кликов
            'defer' => false, // Не отправлять хит при инициализации счетчика
            'enableAll' => false, // Включает отслеживание внешних ссылок, карту кликов и точный показатель отказов
            'onlyHttps' => false, // true — данные в Метрику передаются только по https-протоколу;
            'params' => $this->params, // Параметры, передаваемые вместе с хитом
            'trackHash' => true, // Включает отслеживание хеша в адресной строке браузера
            'trackLinks' => true, // Включает отслеживание внешних ссылок
            'type' => 0, // Тип счетчика. Для РСЯ равен 1
            'ut' => 0, // Запрет отправки страниц на индексацию http://help.yandex.ru/metrika/code/stop-indexing.xml#stop-indexing
            'webvisor' => true, // Включает Вебвизор
        ];

        $htmlCode = $this->templating->render('keltanasYaMetrikaBundle:Metrika:tracker.html.twig', [
                'ya_tracking' => $this->ya_tracking,
                'config' => array_filter($config),
            ]);

        $event->getResponse()->setContent(str_replace(
            '</body>',
            $htmlCode . '</body>',
            $event->getResponse()->getContent()
        ));
    }
}
