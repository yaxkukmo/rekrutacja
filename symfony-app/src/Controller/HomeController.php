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

        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $query->get('takenFrom') ?? '');
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $query->get('takenTo') ?? '');

        $filter = new PhotoFilter(
            $query->get('location') ?: null,
            $query->get('camera') ?: null,
            $query->get('description') ?: null,
            $query->get('username') ?: null,
            $dateFrom ?: null,
            $dateTo ?: null
        );

        $data = $this->photoService->getPhotosWithLikeStatus($currentUser, $filter);

        return $this->render('home/index.html.twig', [
            'currentUser' => $currentUser,
            'photos' => $data['photos'],
            'userLikes' => $data['userLikes'],
        ]);
    }
}
