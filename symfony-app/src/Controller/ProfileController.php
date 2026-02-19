<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ImportPhotoService;
use App\Application\PhotoService;
use App\Application\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private PhotoService $photoService,
        private ImportPhotoService $importPhotoService
    ) {}

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $this->userService->getCurrentUser($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'photoCount' => $this->photoService->countUserPhotos($user->getId()),
        ]);
    }
    
    #[Route('/profile/token', name: 'profile_save_token', methods: ['POST'])]
    public function profileToken(Request $request): Response
    {
        $user = $this->userService->getCurrentUser($request->getSession()->get('user_id'));

        if (!$user) {
            return $this->redirectToRoute('profile');
        }

        $token = $request->request->get('phoenix_token');

        if (!$token) {
            $this->addFlash('error', 'Token cannot be empty');
            return $this->redirectToRoute('profile');
        }

        $this->userService->savePhoenixApiToken($user->getId(), $token);
        $this->addFlash('success', 'Token saved');

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/import', name: 'profile_import_photos', methods: ['POST'])]
    public function profileImport(Request $request): Response
    {
        $user = $this->userService->getCurrentUser($request->getSession()->get('user_id'));

        if (!$user) {
            return $this->redirectToRoute('profile');
        }

        try {
            $numberImportedPhotos = $this->importPhotoService->importPhotos($user);        
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Import error: ' . $exception->getMessage());
            return $this->redirectToRoute('profile');
        }

        if ($numberImportedPhotos > 0) {
            $this->addFlash('success', "Imported {$numberImportedPhotos} photos");
        } else {
            $this->addFlash('error', 'No Photos imported');
        }

        return $this->redirectToRoute('profile');
    }

}
