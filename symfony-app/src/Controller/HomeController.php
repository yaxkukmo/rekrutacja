<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\PhotoService;
use App\Application\UserService;
use App\Domain\Model\PhotoFilter;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private PhotoService $photoService,
        private UserService $userService
    ) {}

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $currentUser = $this->userService->getCurrentUser(
            $request->getSession()->get('user_id')
        );

        $query = $request->query;

        $filter = new PhotoFilter(
            $query->get('location'),
            $query->get('camera'),
            $query->get('description'),
            $query->get('username'),
            $query->get('takenFrom') ? new DateTimeImmutable($query->get('takenFrom')) : null,
            $query->get('takenTo') ? new DateTimeImmutable($query->get('takenTo')) : null
        );

        $data = $this->photoService->getPhotosWithLikeStatus($currentUser, $filter);

        return $this->render('home/index.html.twig', [
            'currentUser' => $currentUser,
            'photos' => $data['photos'],
            'userLikes' => $data['userLikes'],
        ]);
    }
}
