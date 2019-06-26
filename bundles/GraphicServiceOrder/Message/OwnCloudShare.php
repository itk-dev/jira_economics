<?php

namespace GraphicServiceOrder\Message;

class OwnCloudShare
{
  private $content;

  public function __construct(string $content)
  {
    $this->content = $content;
  }

  public function getContent(): string
  {
    return $this->content;
  }
}