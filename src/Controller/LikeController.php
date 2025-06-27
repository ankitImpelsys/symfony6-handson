<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/like/{id}', name: 'app_like')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function like(
        EntityManagerInterface $em,
        MicroPost $post,
        MicroPostRepository $posts,
        Request $request
    ): Response {
        $currentUser = $this->getUser();
        $post->addLikedBy($currentUser);
        $em->persist($post);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/unlike/{id}', name: 'app_unlike')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unlike(
        EntityManagerInterface $em,
        MicroPost $post,
        MicroPostRepository $posts,
        Request $request
    ): Response {
        $currentUser = $this->getUser();
        $post->removeLikedBy($currentUser);
        $em->persist($post);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
