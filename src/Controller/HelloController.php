<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\Userprofile;
use App\Repository\CommentRepository;
use App\Repository\MicroPostRepository;
use App\Repository\UserprofileRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController extends AbstractController
{

    private array $messages = [
        ['message' => 'Hello', 'created' => '2022/06/12'],
        ['message' => 'Hi', 'created' => '2022/04/12'],
        ['message' => 'Bye!', 'created' => '2021/05/12']
    ];

    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em, MicroPostRepository $posts, CommentRepository $comments): Response
    {
//        $post = new MicroPost();
//        $post->setTitle('Hello');
//        $post->setText('Hello');
//        $post->setCreated(new DateTime());

//        $comment = new Comment();
//
//        $comment->setText('Hello');
////        $comment->setPost($post);
//        $post->addComment($comment);
//        $em->persist($post);
//        $em->flush();

        $post = $posts->find(12);
        $comment = $post->getComments()[0];
        $comment->setPost($post);
        $em->persist($comment);
        $em->flush();

//        dd($post);


        return $this->render(
            'hello/index.html.twig',
            [
                'messages' => $this->messages,
                'limit' => 3
            ]
        );
    }

    #[Route('/messages/{id<\d+>}', name: 'app_show_one')]
    public function showOne(int $id): Response
    {
        return $this->render(
            'hello/show_one.html.twig',
            [
                'message' => $this->messages[$id]
            ]
        );
        //return new Response($this->messages[$id]);
    }
}
