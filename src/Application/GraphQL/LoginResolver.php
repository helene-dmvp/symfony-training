<?php

declare(strict_types=1);
namespace Application\GraphQL;


use Application\Security\JWTEncoder;
use Application\Security\User;
use Domain\Collection\UsersCollection;
use GraphQL\Error\UserError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final readonly class LoginResolver
{

    public function __construct(
        private UsersCollection $users,
        private JWTEncoder $jwtEncoder,
        private UserPasswordHasherInterface $userPasswordHasher,


    ) {
    }
    public function loginUser(string $email, string $password): string
    {
        // on cherche le user dans la DB avec son mail
        $userLogged = $this->users->findOneByEmail($email);

//        if ($userLogged instanceof \Domain\Model\User === false) {
//            throw new \Exception('User not found');
//        }
        if (null === $userLogged) {
            throw new UserError('Identifiants invalides');
        }

        $securityUser = new User($userLogged);
        if ($this->userPasswordHasher->isPasswordValid($securityUser, $password) === false) {
            throw new \Exception('Invalid password');
        }
        return $this->jwtEncoder->encode($securityUser);
    }
}
