<?php

declare(strict_types=1);

namespace Application\Security;

use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class BearerAuthenticator extends AbstractAuthenticator
{
    private const string PREFIX = 'Bearer';
    public function __construct(
        private readonly JWTEncoder $jwtEncoder,
    ) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->headers->get('Authorization') ?? '', self::PREFIX);
    }

    #[Override]
    public function authenticate(Request $request): Passport
    {
        $token = substr($request->headers->get('Authorization') ?? '', strlen(self::PREFIX));

        try {
            $user = $this->jwtEncoder->decode($token);
        } catch (\Throwable) {
            throw new CustomUserMessageAuthenticationException('Token invalide ou expiré');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn () => $user)
        );
    }

    // Token valide : on laisse la requête continuer normalement
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    // 4. Token invalide : on répond directement 401
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['message' => $exception->getMessageKey()],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
