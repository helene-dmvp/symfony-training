<?php

declare(strict_types=1);

namespace Infrastructure\Lexik;

use Application\Security\JWTEncoder;
use Application\Security\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Override;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final readonly class LexikJwtEncoder implements JWTEncoder
{

    // récuperer l'interface qui génère le token
    public function __construct(
        private JWTTokenManagerInterface $jwtService,
    ) {}

    #[Override]
    public function encode(User $user): string
    {
        return $this->jwtService->create(user: $user);
    }

    #[Override]
    public function decode(string $jwt): User
    {
        $data = $this->jwtService->parse(token:$jwt);

        if (!array_key_exists('username', $data)){
            throw new UserNotFoundException('user non trouvé' . $data['username']);
        }
        dump($data);


        return new User($data['username']);
    }
}
