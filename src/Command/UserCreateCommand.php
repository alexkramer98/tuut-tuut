<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    protected static $defaultName = 'app:user:create';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $username = $helper->ask($input, $output, new Question('Username?: '));
        $role = $helper->ask($input, $output, new Question('Role?: '));
        $pass = $helper->ask($input, $output, new Question('Password?: '));

        $user = new User();
        $user
            ->setUsername($username)
            ->setRoles(['ROLE_USER', $role])
            ->setPassword($this->encoder->encodePassword($user, $pass))
        ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success('Done!');
        return 0;
    }
}
