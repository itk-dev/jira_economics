<?php

namespace GraphicServiceOrder\MessageHandler;

use GraphicServiceOrder\Message\OwnCloudShare;
use GraphicServiceOrder\Repository\GsOrderRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use GraphicServiceOrder\Entity\GsOrder;

class OwnCloudShareHandler implements MessageHandlerInterface
{
  private $gsOrderRepository;

  public function __construct(GsOrderRepository $gsOrderRepository)
  {
    $this->gsOrderRepository = $gsOrderRepository;
  }


  public function __invoke(OwnCloudShare $order)
  {
    $order = $this->gsOrderRepository->find($order->getOrderId());
    $a = 1;
    // ... do some work - like sending an SMS message!
    //print '---' . $order->getOrderId() . '---';
  }
}
