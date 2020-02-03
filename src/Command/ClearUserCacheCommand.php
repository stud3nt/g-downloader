<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UsersRepository;
use App\Service\FileCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearUserCacheCommand extends Command
{
    protected static $defaultName = 'app:clear-user-cache';

    /** @var UsersRepository */
    protected $usersRepository;

    public function __construct(string $name = null, UsersRepository $usersRepository = null)
    {
        $this->usersRepository = $usersRepository;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addArgument('user_id', InputArgument::OPTIONAL, "User ID", null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('user_id');
        $usersCount = 0;

        if (!empty($userId)) {
            $users = $this->usersRepository->findBy(['id' => $userId]);
        } else {
            $users = $this->usersRepository->findAll();
        }

        if ($users) {
            /** @var User $user */
            foreach ($users as $user) {
                $cache = new FileCache($user);
                $cache->removeAll();
            }
        }

        $output->writeln('Operation done.');
        $output->write('Cache of '.$usersCount.' users cleared.');

        return 0;
    }
}