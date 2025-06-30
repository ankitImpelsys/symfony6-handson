<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Form\CommentTypeForm;
use App\Repository\MicroPostRepository;
use App\Form\MicroPostTypeForm;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MicroPostController extends AbstractController
{
    #[Route('/micro-post', name: 'app_micro_post')]
    public function index(MicroPostRepository $posts, EntityManagerInterface $em): Response
    {
        // Example of creating a new MicroPost
        // $microPost = new MicroPost();
        // $microPost->setTitle('It comes with the controller');
        // $microPost->setText('Hi!');
        // $microPost->setCreated(new DateTime());
        // $em->persist($microPost);
        // $em->flush();

        // Show all posts
//        dd($posts->findAll());

        return $this->render(
            'micro_post/index.html.twig',
            [
                'posts' => $posts->findAllWithComments(),
            ]
        );
    }

    #[Route('/micro-post/top-liked', name: 'app_micro_post_topliked')]
    public function topLiked(MicroPostRepository $posts): Response
    {
        return $this->render(
            'micro_post/top_liked.html.twig',
            [
                'posts' => $posts->findAllWithComments(),
            ]
        );
    }

    #[Route('/micro-post/follows', name: 'app_micro_post_follows')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(MicroPostRepository $posts): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->render(
            'micro_post/follows.html.twig',
            [
                'posts' => $posts->findAllByAuthors(
                    $currentUser->getFollows()
                ),
            ]
        );
    }

    #[Route('/micro-post/{post}', name: 'app_micro_post_show')]
    #[IsGranted(MicroPost::VIEW, 'post')]
    //this method will only work if you have to fetch one data otherwise use the above method
    public function showOne(MicroPost $post): Response
    {
        return $this->render(
            'micro_post/show.html.twig',
            [
                'post' => $post,
            ]
        );
    }

    #[Route('/micro-post/add', name: 'app_micro_post_add', priority: 2)]
    #[IsGranted('ROLE_WRITER')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
//        $microPost = new MicroPost();
//        $form = $this->createFormBuilder($microPost)
//            ->add('title')
//            ->add('text')
//            ->getForm();

        $form = $this->createForm(MicroPostTypeForm::class, new MicroPost());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            $post->setAuthor($this->getUser());

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post added successfully!');
            return $this->redirectToRoute('app_micro_post');
        }

        return $this->render(
            'micro_post/add.html.twig',
            [
                'form' => $form
            ]
        );
    }

    #[Route('/micro-post/{post}/edit', name: 'app_micro_post_edit')]
    #[IsGranted(MicroPost::EDIT, 'post')]
    public function edit(
        EntityManagerInterface $em,
        MicroPost              $post,
        Request                $request,
        MicroPostRepository    $posts
    ): Response
    {
//        $form = $this->createFormBuilder($post)
//            ->add('title')
//            ->add('text')
//            ->getForm();

        $form = $this->createForm(MicroPostTypeForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();

            // $em->persist($microPost);
            // no need for persist here
            $em->flush();

            // Add a flash
            $this->addFlash('success', 'Your micro post have been updated.');

            return $this->redirectToRoute('app_micro_post');
            // Redirect
        }

        return $this->render(
            'micro_post/edit.html.twig',
            [
                'form' => $form,
                'post' => $post
            ]
        );
    }

    #[Route('/micro-post/{post}/comment', name: 'app_micro_post_comment')]
    #[IsGranted('ROLE_COMMENTER')]
    public function addComment(
        EntityManagerInterface $em,
        MicroPost              $post,
        Request                $request,
        CommentRepository      $comments
    ): Response
    {
        $form = $this->createForm(CommentTypeForm::class, new Comment());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());

            $em->persist($comment);
            $em->flush();

            // Add a flash
            $this->addFlash('success', 'Your comment have been updated.');

            return $this->redirectToRoute(
                'app_micro_post_show',
                ['post' => $post->getId()]
            );
            // Redirect
        }

        return $this->render(
            'micro_post/comment.html.twig',
            [
                'form' => $form,
                'post' => $post
            ]
        );
    }


    #[Route('/api/micro-post', name: 'api_micro_post_list', methods: ['GET'])]
    public function apiList(MicroPostRepository $posts): Response
    {
        $allPosts = $posts->findAll();

        $data = [];

        /** @var MicroPost $post */
        foreach ($allPosts as $post) {
            $commentsData = [];
            foreach ($post->getComments() as $comment) {
                $commentsData[] = [
                    'id' => $comment->getId(),
                    'text' => $comment->getText(),
                    'author' => $comment->getAuthor()?->getEmail(),
                    'created' => $comment->getCreated()?->format('Y-m-d H:i:s'),
                ];
            }

            $data[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'text' => $post->getText(),
                'author' => $post->getAuthor()?->getEmail(),
                'created' => $post->getCreated()?->format('Y-m-d H:i:s'),
                'comments' => $commentsData,
            ];
        }

        return $this->json($data);
    }

    #[Route('/savePost', name: 'save_post', methods: ['POST'])]
    public function create(
        Request $request,
        MicroPostRepository $posts,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Just debug output for testing
        // dd($data['author_id']);
        //dd($data);

        // Find the user by ID
        $user = $userRepository->find($data['author_id']);
        if (is_null($user)) {
            return $this->json(['message' => 'Invalid user!!']);
        }

        // Create new post
        $post = new MicroPost();
        $post->setAuthor($user);
        $post->setCreated(new DateTime());
        $post->setText($data['text']);
        $post->setTitle($data['title']);

        // Save using repository
        $em->persist($post);
        $em->flush();

        return $this->json(['message' => 'Post added!!']);
    }


}
