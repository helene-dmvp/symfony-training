<?php

declare(strict_types=1);
namespace Application\Command;

use Application\Security\JWTEncoder;
use Application\Security\User;
use Domain\Collection\UsersCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'jwt:encode', description: 'créé le JWT pour un user')]
final class JwtEncodeCommand extends Command
{
    //  récupère le service JWTEncoder dont la commande a besoin
    public function __construct(
        private readonly JWTEncoder $encoder,
        private readonly UsersCollection $users,
    ) {
        parent::__construct();
    }

    // déclarer les arguments de la commande
    protected function configure(): void
    {
        $this->addArgument('le_user', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('le_user') ?? 'admin@admin.com';

        $le_user = $this->users->findOneByEmail($email);

        if (null === $le_user) {
            $output->writeln("<error>pas de user avec l'email $email</error>");

            return Command::FAILURE;
        }

        $user = new User($le_user);

        $output->writeln($this->encoder->encode($user));

        return Command::SUCCESS;
    }
}
