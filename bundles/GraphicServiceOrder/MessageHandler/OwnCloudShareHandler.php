<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\MessageHandler;

use GraphicServiceOrder\Message\OwnCloudShare;
use GraphicServiceOrder\Repository\GsOrderRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

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
        // ... do some work - like sending an SMS message!
    //print '---' . $order->getOrderId() . '---';
    }
}
