<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Form\PostType;
use App\Repository\ArticleRepository;


#[Route('/post', name: 'post.')]

class PostController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ArticleRepository $seeResult): Response
    {
        $post = $seeResult->findAll();
        return $this->render('post/index.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
       // $entityManager = $request->getManager();
        $post = new Article();
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
          
            // tell Doctrine you want to (eventually) save the Product (no queries yet)
           // $entityManager->persist($post);

            // actually executes the queries (i.e. the INSERT query)
          //  $entityManager->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }

        return $this->render('post/create.html.twig', [
            'postForm' => $form->createView()
        ]);
        

        
        // $post->setCreatedAt(new \DateTime(),strtotime('now'));
        // $post->setUpdatedAt(new \DateTime(),strtotime('now'));
        // $post->setNo('');


      

       // return new Response('Saved new post with id ');
        
    }
}
