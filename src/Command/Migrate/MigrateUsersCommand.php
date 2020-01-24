<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\Migrate;

use ItkDev\UserManagementBundle\Doctrine\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * A temporary hack to migrate users.
 */
class MigrateUsersCommand extends Command
{
    protected static $defaultName = 'app:migrate:users';

    /** @var \Symfony\Component\Serializer\SerializerInterface */
    private $serializer;

    /** @var \ItkDev\UserManagementBundle\Doctrine\UserManager */
    private $userManager;

    public function __construct(
        SerializerInterface $serializer,
        UserManager $userManager
    ) {
        parent::__construct();
        $this->serializer = $serializer;
        $this->userManager = $userManager;
    }

    protected function configure()
    {
        $this->addArgument(
            'csv-path',
            InputArgument::REQUIRED,
            'File path to csv data'
        )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Additional message'
            )
            ->addOption('portal-app', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Portal app');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('csv-path');
        $message = $input->getArgument('message');
        $message = $this->getMessage($message);
        $apps = $input->getOption('portal-app');
        if (empty($apps)) {
            throw new RuntimeException('No apps specified');
        }

        // Set the message in user manager configuration.
        $property = new \ReflectionProperty(
            $this->userManager,
            'configuration'
        );
        $property->setAccessible(true);
        $configuration = $property->getValue($this->userManager);
        // Make sure that user manager notifies users on create.
        $configuration['notify_user_on_create'] = true;
        $configuration['user_created']['body'] = $message;
        $property->setValue($this->userManager, $configuration);

        $rows = $this->getData($path);
        foreach ($rows as $row) {
            if (!isset($row['email'])) {
                continue;
            }
            $email = $row['email'];
            /** @var \App\Entity\User $user */
            $user = $this->userManager->findUserBy(['email' => $email]);
            $isNew = null === $user;
            if (null === $user) {
                $user = $this->userManager->createUser();
                $user->setEmail($email);
            }
            $user->setPortalApps(array_unique(array_merge($user->getPortalApps(), $apps)));

            $this->userManager->updateUser($user);
            $output->writeln(sprintf($isNew ? 'User %s created' : 'User %s updated', $email));
        }
    }

    private function getData(string $path)
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Invalid path: %s', $path));
        }

        return $this->serializer->decode(file_get_contents($path), 'csv', [
            'as_collection' => true,
        ]);
    }

    private function getMessage(string $message)
    {
        // Use NotifyUsersCreatedCommand to get the actual message from a file.
        $notifyUsersCommand = $this->getApplication()
            ->find('itk-dev:user-management:notify-users-created');
        $getMessage = new \ReflectionMethod($notifyUsersCommand, 'getMessage');
        $getMessage->setAccessible(true);

        return $getMessage->invoke($notifyUsersCommand, '@'.$message);
    }
}
