<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private PostRepository $postRepository
        ){}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'posts' =>  $this->postRepository->findAll(),
            'categories' => $this->categoryRepository->findall()
        ]);
    }

    #[Route('/Post/category/{id<[0-9]+>}', name:'index_by_category')]
    public function indexByCategory(Category $category)
    {
          return $this->render('home/index.html.twig', [
            'posts' => $category->getPosts(),
            'categories' => $this->categoryRepository->findall()
        ]); 
    }

    #[Route('/Post/search', name: 'index_by_search')]
    public function indexBySearch(Request $request)
    {
        $search = $request->request->get('search');

        return $this->render('home/index.html.twig', [
            'posts' => $this->postRepository->findAllBysearch($search),
            'categories' => $this->categoryRepository->findall()
        ]); 
    }

    #[Route('/post/{id<[0-9]+>}', name: 'show')]
    public function show(Post $post, CommentRepository $commentRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment;
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $comment->setUser($this->getUser())
                ->setCreatedAt(new DateTime())
                ->setPost($post)
            ;
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('show',['id'=>$post->getId()]);
        }

        return $this->render('home/show.html.twig', [
            'post' => $post,
            'comments' => $commentRepository->findBy(['post' => $post], ['createdAt' => 'DESC']),
            'form' => $form
        ]);
    }
}
