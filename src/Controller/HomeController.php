<?php

namespace App\Controller;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        
        $article = new Article();
        $article ->setTitle('learn symfont');

       // $em = $doctrine->getManager();
        // $entityManager = $doctrine->getManager('default');

        // $entityManager->persist($article);
        // $entityManager->flush();

        $getArticle = $doctrine->getRepository(Article::class, 'default')->findOneby([
            'id'=>1,
        ]);

        // $em->remove($getArticle);
        // $em->flush();

        //return new Response('article created');

        return $this->render('home/index.html.twig', [
            'article' => $getArticle,
            
        ]);
    }

    #[Route('/create_form', name: 'create_form')]
    public function create(): Response
    {
        return $this->render('home/create.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
