<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\MessageHandler;

use GraphicServiceOrder\Message\OwnCloudShareMessage;
use GraphicServiceOrder\Repository\GsOrderRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OwnCloudShareMessageHandler implements MessageHandlerInterface
{
    private $gsOrderRepository;

    public function __construct(GsOrderRepository $gsOrderRepository)
    {
        $this->gsOrderRepository = $gsOrderRepository;
    }

    public function __invoke(OwnCloudShareMessage $ownCloudShareMessage)
    {
        $order = $this->gsOrderRepository->findById($ownCloudShareMessage->getOrderId());

        $files = $order->getFiles();

        // If no files on order, consider all files received.
        if (empty($files)) {
            $order->setOrderStatus('received');
        } else {
            // Create a folder with issue key as name.
            $this->createFolder($order->getIssueKey());

            // Get all files on the order that have already been shared.
            $sharedFiles = $order->getOwnCloudSharedFiles();
            foreach ($files as $file) {
                // if a file exists on the entity that has not yet been shared.
                if (!\in_array($file, $sharedFiles)) {
                    // Attempt to share the file in owncloud.
                    $response = $this->shareFile($file,
                        $order->getIssueKey());
                    $success = [201, 204];  // Successful responses;
                    // if file was shared successfully add to shared files array.
                    if (\in_array($response, $success)) {
                        $sharedFiles[] = $file;
                        $order->setOwnCloudSharedFiles($sharedFiles);
                    }
                }
            }
        }

        // If all files are considered shared change status to "received".
        $diff = array_diff($files, $order->getOwnCloudSharedFiles());
        if (empty($diff)) {
            $order->setOrderStatus('received');
            // Remove local files.
            foreach ($order->getOwnCloudSharedFiles() as $file) {
                $files_dir = $this->appKernel->getProjectDir().'/private/files/gs/';
                unlink($files_dir.$file);
            }
        }
        // Update entity.
        $this->entityManager->flush();
        // --- Move this to message END -- //
    }
}
