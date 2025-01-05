<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
    ): Response
    {

        return $this->render('main/main.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/application', name: 'app_application')]
    public function application(): Response
    {
        return $this->render('application/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/fonctionalités', name: 'app_feature')]
    public function features(): Response
    {
        return $this->render('features/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }


    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('about/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/tarifs', name: 'app_pricing')]
    public function pricing(): Response
    {
        return $this->render('pricing/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/blog/detail', name: 'app_blog_detail')]
    public function blogDetail(): Response
    {
        return $this->render('blog/detail.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/error_404', name: 'app_error_404')]
    public function error_404(): Response
    {
        return $this->render('error/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/conditions-d-utilisation', name: 'app_terms')]
    public function usingConditions(): Response
    {
        return $this->render('legal-pages/index-terms.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/politique-de-confidentialié', name: 'app_privacy_page')]
    public function privacy(): Response
    {
        return $this->render('legal-pages/index-privacy_page.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
