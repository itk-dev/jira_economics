<?php

namespace GraphicServiceOrder\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Sluggable\Util\Urlizer;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PostUploadEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GsUploadListener
{
  /**
   * @var ObjectManager
   */
  private $om;

  public function __construct(ObjectManager $om, $projectDir)
  {
    $this->om = $om;
    $this->projectDir = $projectDir;
  }

  public function onUpload(PostUploadEvent $event)
  {
    $file = $event->getFile();
    $destination = $this->projectDir . '/private/files/gs';
    $filename = Urlizer::urlize($_FILES["graphic_service_order_form"]["name"]["files"][0]) . '-'.uniqid() . '.' . $file->guessExtension();
    $file->move(
      $destination,
      $filename
    );
    $response = $event->getResponse();
    $response['success'] = true;
    $response['files'] = [
      'file' => [
        'old_name' => $_FILES["graphic_service_order_form"]["name"]["files"][0],
        'name' => $filename,
      ]
    ];
    return $response;
  }
}