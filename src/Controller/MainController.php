<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\UsersRepository;
use App\Service\OpenAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Email;

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
    public function application(
        Request $request,
        OpenAiService $openAiService,
        EntityManagerInterface $entityManager,
        FlasherInterface $flasher,
        UsersRepository $usersRepository
    ): Response {
        /** @var \App\Entity\Users|null $connectedUser */
        $connectedUser = $usersRepository->find($this->getUser()->getId());
    
        // Gestion des requêtes GET : Afficher la page
        if ($request->isMethod('GET')) {
            // Si l'utilisateur est connecté, récupérer ses tentatives
            $reportAttempts = $connectedUser ? $connectedUser->getReportAttempts() : 0;
    
            return $this->render('application/index.html.twig', [
                'controller_name' => 'MainController',
                'userIsConnected' => $connectedUser !== null,
                'reportAttempts' => $reportAttempts,
            ]);
        }
    
        // Gestion des requêtes POST
        if (!$connectedUser) {
            return $this->json([
                'error' => 'Vous devez être connecté.',
                'redirectUrl' => $this->generateUrl('app_login'),
            ], 403);
        }
    
        // Vérification des essais restants
        $roles = $connectedUser->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles, true);
        $userReportAttempts = $connectedUser->getReportAttempts();
    
        if (!$isAdmin && $userReportAttempts <= 0) {
            return $this->json([
                'error' => 'Essais épuisés',
                'redirectUrl' => $this->generateUrl('app_pricing'),
            ], 403);
        }
    
        // Validation des données reçues
        $data = json_decode($request->getContent(), true);
        if (!isset($data['headers'], $data['rows'], $data['objective'])) {
            return $this->json(['error' => 'Données invalides.'], 400);
        }
    
        try {
            $report = $openAiService->generateReport($data['headers'], $data['rows'], $data['objective']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la génération du rapport.'], 500);
        }
    
        // Décrémentation des essais restants
        if (!$isAdmin) {
            // Décrémenter les tentatives de l'utilisateur
            $connectedUser->setReportAttempts($userReportAttempts - 1);
            $entityManager->persist($connectedUser);
            $entityManager->flush();
        }
    
        return $this->json([
            'report' => $report,
            'message' => 'Rapport généré avec succès.',
        ]);
    }

    // #[Route('/application', name: 'app_application')]
    // public function application(
    //     Request $request,
    //     OpenAiService $openAiService,
    //     EntityManagerInterface $entityManager,
    //     FlasherInterface $flasher,
    //     UsersRepository $usersRepository
    // ): Response {
    //     /** @var \App\Entity\Users|null $connectedUser */
    //     $connectedUser = $this->getUser();
    
    //     // Gestion des requêtes GET : Afficher la page
    //     if ($request->isMethod('GET')) {
    //         // Si l'utilisateur est connecté, récupérer ses tentatives
    //         $reportAttempts = $connectedUser ? $connectedUser->getReportAttempts() : 0;
    
    //         return $this->render('application/index.html.twig', [
    //             'controller_name' => 'MainController',
    //             'userIsConnected' => $connectedUser !== null,
    //             'reportAttempts' => $reportAttempts,
    //         ]);
    //     }
    
    //     // Gestion des requêtes POST
    //     if (!$connectedUser) {
    //         return $this->json([
    //             'error' => 'Vous devez être connecté.',
    //             'redirectUrl' => $this->generateUrl('app_login'),
    //         ], 403);
    //     }
    
    //     // Vérification des essais restants
    //     $roles = $connectedUser->getRoles();
    //     $isAdmin = in_array('ROLE_ADMIN', $roles, true);
    //     $userReportAttempts = $connectedUser->getReportAttempts();
    
    //     if (!$isAdmin && $userReportAttempts <= 0) {
    //         return $this->json([
    //             'error' => 'Essais épuisés',
    //             'redirectUrl' => $this->generateUrl('app_pricing'),
    //         ], 403);
    //     }
    
    //     // Validation des données reçues
    //     $data = json_decode($request->getContent(), true);
    //     if (!isset($data['headers'], $data['rows'], $data['objective'])) {
    //         return $this->json(['error' => 'Données invalides.'], 400);
    //     }
    
    //     try {
    //         $report = $openAiService->generateReport($data['headers'], $data['rows'], $data['objective']);
    //     } catch (\Exception $e) {
    //         return $this->json(['error' => 'Erreur lors de la génération du rapport.'], 500);
    //     }
    
    //     // Décrémentation des essais restants
    //     if (!$isAdmin) {
    //         $connectedUser->setReportAttempts($userReportAttempts - 1);
    //         $entityManager->persist($connectedUser);
    //         $entityManager->flush();
    //     }
    
    //     return $this->json([
    //         'report' => $report,
    //         'message' => 'Rapport généré avec succès.',
    //     ]);
    // }

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
    public function contact(
        Request $request, 
        MailerInterface $mailer,
        EntityManagerInterface $entityManager): Response
    {
        $contactForm = $this->createForm(ContactFormType::class);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()){
            $contactFormEmail = $contactForm->get('email')->getData();
            $contactFormName = $contactForm->get('name')->getData();
            $contactFormSubject = $contactForm->get('subject')->getData();
            $contactFormMessage = $contactForm->get('message')->getData();

            $contactFormInfo = new Contact();
            $contactFormInfo->setName($contactFormName);
            $contactFormInfo->setEmail($contactFormEmail);
            $contactFormInfo->setSubject($contactFormSubject);
            $contactFormInfo->setMessage($contactFormMessage);

            $entityManager->persist($contactFormInfo);
            $entityManager->flush();

            $this->addFlash('success', 'Votre email a bien été envoyé ✅!');

            $data = $contactForm->getData();
            // dd($data);

            $defaultEmail = 'contact@datagraph.fr'; // Adresse de l'expéditeur par défaut
            $senderName = $data->getName();        // Utilisez les getters
            $senderEmail = $data->getEmail();      // Utilisez les getters
            // $senderName = $data['name'];
            // $senderEmail = $data['email'];
            $emailMessage = 'Email envoyé par : ' . $senderName . "\n\nAdresse email : " .$senderEmail. "\n\n" . $data->getMessage();

            // Version original avec la récupération de l'email de la personne remplissant le formulaire de contact
            // $mailAdress = $data['email'];
            // $emailMessage = $data['message'];

            $email = (new Email())
                // ->from($mailAdress)
                ->from($defaultEmail)
                ->to('contact@datagraph.fr')
                ->subject('Email reçu depuis la page contact de Datagraph')
                ->text($emailMessage);

                $mailer->send($email);

                // Utilisez Flashy pour afficher un message flash de succès
                $this->addFlash('success', 'Votre email a bien été envoyé ✅!');

                // Redirigez l'utilisateur vers la même page (rafraîchissement)
                return $this->redirectToRoute('app_main');
        }elseif ($contactForm->isSubmitted() && !$contactForm->isValid()) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoie du mail. Veuillez réessayer.');

        }

        return $this->render('contact/index.html.twig', [
            'controller_name' => 'HomeController',
            'contactForm' => $contactForm->createView()
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
        return $this->render('legal-pages/_terms.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/politique-de-confidentialié', name: 'app_privacy_page')]
    public function privacy(): Response
    {
        return $this->render('legal-pages/_privacy.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
