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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('csv-path');
        $message = $input->getArgument('message');
        $message = $this->getMessage($message);

        // Set the message in user manager configuration.
        $property = new \ReflectionProperty(
            $this->userManager,
            'configuration'
        );
        $property->setAccessible(true);
        $configuration = $property->getValue($this->userManager);
        // Make sure that user manager notifies users on create.
        $configuration['notify_user_on_create'] = true;
        $configuration['user_created']['message'] = $message;
        $property->setValue($this->userManager, $configuration);

        $rows = $this->getData($path);
        foreach ($rows as $row) {
            if (!isset($row['email'])) {
                continue;
            }
            $email = $row['email'];
            $user = $this->userManager->findUserBy(['email' => $email]);
            if (null !== $user) {
                $output->writeln(sprintf('User %s already exists', $email));
                continue;
            }
            $user = $this->userManager->createUser();
            $user->setEmail($email);
            $this->userManager->updateUser($user);
            $output->writeln(sprintf('User %s created', $email));
        }
    }

    private function getData(string $path)
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid path: %s',
                $path
            ));
        }

        return $this->serializer->decode(file_get_contents($path), 'csv');
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
