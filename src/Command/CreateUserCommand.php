<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crea un nuevo usuario en la base de datos',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Correo electrónico del usuario');
        $plainPassword = $io->askHidden('Contraseña');
        $isAdmin = $io->confirm('¿Es un usuario Administrador?', true);

        $user = new User();
        $user->setEmail($email);

        // Hashear la contraseña de forma segura
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Asignar roles
        $roles = ['ROLE_USER'];
        if ($isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);

        // Guardar en la base de datos
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Usuario %s creado correctamente.', $email));

        return Command::SUCCESS;
    }
}
