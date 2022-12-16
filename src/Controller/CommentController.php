<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/comment/delete/{id<[0-9]+>}', name: 'comment_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Comment $comment, CommentRepository $commentRepository,Request $request): Response
    {
        $token = $request->query->get('token');

        if($this->isCsrfTokenValid('comment' . $comment->getId(), $token)) {

            $commentRepository->remove($comment, true);
        }

        return $this->redirectToRoute('show', ['id' => $comment->getPost()->getId()]);
    }
}
