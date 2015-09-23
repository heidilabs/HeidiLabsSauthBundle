<?php
/**
 * Command to create users
 */

namespace HeidiLabs\SauthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sauth:user:create')
            ->setDescription('Creates a new user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'username for this user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
