<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Sluggable\Util\Urlizer;
use Oneup\UploaderBundle\Event\PostUploadEvent;

class GsUploadListener
{
    /**
     * @var ObjectManager
     */
    private $om;
    private $projectDir;

    public function __construct(ObjectManager $om, $projectDir)
    {
        $this->om = $om;
        $this->projectDir = $projectDir;
    }

    /**
     * Act when file is uploaded.
     *
     * @param \Oneup\UploaderBundle\Event\PostUploadEvent $event
     *
     * @return \Oneup\UploaderBundle\Uploader\Response\ResponseInterface
     */
    public function onUpload(PostUploadEvent $event)
    {
        $file = $event->getFile();
        $destination = $this->projectDir.'/private/files/gs';
        $filename = Urlizer::urlize($_FILES['graphic_service_order_form']['name']['files'][0]).'-'.uniqid().'.'.$file->guessExtension();
        $file->move(
        $destination,
        $filename
    );
        $response = $event->getResponse();
        $response['success'] = true;
        $response['files'] = [
            'file' => [
                'old_name' => $_FILES['graphic_service_order_form']['name']['files'][0],
                'name' => $filename,
            ],
        ];

        return $response;
    }
}
