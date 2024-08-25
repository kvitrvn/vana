<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

final readonly class UserManager
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
    )
    {}

    /**
     * @throws Exception
     */
    public function createUser(string $username, string $email): void
    {
        $usernameExists = $this->userRepository->findOneBy(['username' => $username]);
        if (null !== $usernameExists) {
            throw new Exception(sprintf("User with username '%s' already exists", $username));
        }
        $emailExists = $this->userRepository->findOneBy(['email' => $email]);
        if (null !== $emailExists) {
            throw new Exception(sprintf("User with email '%s' already exists", $email));
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);

        $plainPassword = ByteString::fromRandom(32)->toString();
        $password = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

}
