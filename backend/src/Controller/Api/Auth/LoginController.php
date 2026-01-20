<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\LoginDto;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
class LoginController extends AbstractController
{
    public function __invoke(
        #[MapRequestPayload] LoginDto $loginDto,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
    ): JsonResponse {
        $user = $userRepository->findByEmail($loginDto->email);

        if (!$user || !$passwordHasher->isPasswordValid($user, $loginDto->password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'email' => $user->getEmail(),
        ]);
    }
}
