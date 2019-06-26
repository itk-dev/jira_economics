<?php

namespace GraphicServiceOrder\MessageHandler;

use GraphicServiceOrder\Message\OwnCloudShare;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OwnCloudShareHandler implements MessageHandlerInterface
{
  public function __invoke(OwnCloudShare $message)
  {
    // ... do some work - like sending an SMS message!
    print 'abc';
  }
}
