<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\LikeService;
use App\Application\PhotoService;
use App\Application\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private LikeService $likeService, 
        private UserService $userService,
        private PhotoService $photoService
    ) { }

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like(int $id, Request $request): Response
    {
        $session = $request->getSession();
        $user = $this->userService->getCurrentUser($session->get('user_id'));

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to like photos.');
            return $this->redirectToRoute('home');
        }

        $photo = $this->photoService->getPhotoById($id);

        if ($this->likeService->hasUserLikedPhoto($user, $photo)) {
            $this->likeService->unlikePhoto($user, $photo);
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $this->likeService->likePhoto($user, $photo);
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }
}
