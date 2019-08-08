<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\MessageHandler;

use GraphicServiceOrder\Entity\GsOrder;
use GraphicServiceOrder\Message\OwnCloudShareMessage;
use GraphicServiceOrder\Repository\GsOrderRepository;
use GraphicServiceOrder\Service\OrderService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OwnCloudShareMessageHandler implements MessageHandlerInterface
{
    private $gsOrderRepository;
    private $orderService;

    public function __construct(GsOrderRepository $gsOrderRepository, OrderService $orderService)
    {
        $this->gsOrderRepository = $gsOrderRepository;
        $this->orderService = $orderService;
    }

    public function __invoke(OwnCloudShareMessage $ownCloudShareMessage)
    {
        /* @var GsOrder */
        $order = $this->gsOrderRepository->findOneBy(['id' => $ownCloudShareMessage->getOrderId()]);

        $this->orderService->handleOrderMessage($order);
    }
}
