<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user with email and a generated password',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The user email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null !== $existing) {
            $io->error(sprintf('A user with email "%s" already exists.', $email));

            return Command::FAILURE;
        }

        $plainPassword = bin2hex(random_bytes(16));

        $user = new User($email, '');
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('User "%s" created. Password: %s', $email, $plainPassword));

        return Command::SUCCESS;
    }
}
