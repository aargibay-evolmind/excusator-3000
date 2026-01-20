<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\RegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
class RegisterController extends AbstractController
{
    public function __invoke(
        #[MapRequestPayload] RegisterDto $registerDto,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
    ): JsonResponse {
        // Check if user already exists
        $existingUser = $userRepository->findByEmail($registerDto->email);

        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already in use'], 409);
        }

        // Create new user
        $user = new User();
        $user->setEmail($registerDto->email);
        $hashedPassword = $passwordHasher->hashPassword($user, $registerDto->password);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        // Generate token for immediate login
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'email' => $user->getEmail(),
        ], 201);
    }
}
