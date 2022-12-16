<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly CommentRepository $commentRepository,
        private readonly EntityManagerInterface $manager
    ){}
    #[Route('/admin', name: 'admin')]
    // #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Accès refuser aux nom-admins')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "accès refusé");
        // if(!$this->getUser()) {
        //     $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');

        //     return $this->redirectToRoute('home');
        // }

        // if(!$this->isGranted('ROLE_ADMIN')) {
        //     $this->addFlash('danger', 'Vous devez être admin pour accéder à cette page');

        //     return $this->redirectToRoute('home');
        // }

        return $this->render('admin/index.html.twig', [
            'posts' => $this->postRepository->findAll()
        ]);
    }

    #[Route('/admin/post/delete/{id<[0-9]+>}', name: 'post_delete')]
    public function delete(Post $post,Request $request): Response
    {
        $token = $request->query->get('token');

        if($this->isCsrfTokenValid('post' . $post->getId(), $token)) {
            foreach ($post->getComments() as $comment){
                $this->commentRepository->remove($comment, true);
            }
            $this->postRepository->remove($post, true);
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/post/publish/{id<[0-9]+>}', name: 'admin_published')]
    public function publish(Post $post, Request $request): Response
    {
        $token = $request->query->get('token');

        if($this->isCsrfTokenValid('publish' . $post->getId(), $token)) {
            $post->setIsPublished(!$post->isIsPublished());
            $this->postRepository->save($post, true);
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/post/create', name: 'post_add')]
    public function create(Request $request): Response
    {
        $post = new Post;
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new DateTime())
                ->setUser($this->getUser())
            ;
            $this->manager->persist($post);
            $this->manager->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/admin/post/edit/{id<[0-9]+>}', name: 'post_edit')]
    public function edit(Post $post, Request $request): Response
    {
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($post);
            $this->manager->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }
}
