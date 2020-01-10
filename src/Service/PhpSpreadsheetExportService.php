<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PhpSpreadsheetExportService.
 */
class PhpSpreadsheetExportService
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    /**
     * PhpSpreadsheetExportService constructor.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get output from a writer as a string.
     *
     * @param \PhpOffice\PhpSpreadsheet\Writer\IWriter $writer
     *
     * @return false|string
     *                      The writer output or false
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getOutputAsString(IWriter $writer)
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->kernel->getProjectDir().'/var/tmp_files');
        $tempFilename = $filesystem->tempnam($this->kernel->getProjectDir().'/var/tmp_files', 'export_');

        // Save to temp file.
        $writer->save($tempFilename);

        $output = file_get_contents($tempFilename);
        $filesystem->remove($tempFilename);

        return $output;
    }
}
