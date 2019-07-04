<?php

namespace GraphicServiceOrder\Message;

class OwnCloudShare
{
  private $orderId;

  public function __construct(int $orderId)
  {
    $this->orderId = $orderId;
  }

  public function getOrderId(): string
  {
    return $this->orderId;
  }
}