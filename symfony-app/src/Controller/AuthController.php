<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private AuthService $authService
    ) {}

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Request $request): Response
    {
        try {
            $user = $this->authService->authenticate($username, $token);
        } catch (\DomainException $exception) {
            return new Response('Invalid credentials', 401);
        }

        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->set('username', $user->getUsername());

        $this->addFlash('success', 'Welcome back, ' . $user->getUsername() . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
