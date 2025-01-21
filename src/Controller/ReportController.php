<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\OpenAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ReportController extends AbstractController
{
    #[Route('/api/generate-report', name: 'generate_report', methods: ['POST'])]
    public function generateReport(
        Request $request,
        OpenAiService $openAiService,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['dataSummary'], $data['objective'])) {
            return new JsonResponse(['error' => 'Données invalides fournies.'], 400);
        }

        $headers = $data['dataSummary']['headers'] ?? [];
        $rows = $data['dataSummary']['dataRows'] ?? [];
        $objective = $data['objective'];

        if (empty($headers) || empty($rows)) {
            return new JsonResponse(['error' => 'Données de tableau manquantes.'], 400);
        }

        // Appeler le service pour générer le rapport
        $report = $openAiService->generateReport($headers, $rows, $objective);

        // Décrémentation des essais restants
        /** @var Users $loggedInUser */
        $loggedInUser = $this->getUser();
        $connectedUser = $loggedInUser ? $usersRepository->find($loggedInUser->getId()) : null;

        if ($connectedUser) {
            $roles = $connectedUser->getRoles();
            $isAdmin = in_array('ROLE_ADMIN', $roles, true);
            $userReportAttempts = $connectedUser->getReportAttempts();

            if (!$isAdmin) {
                // Log the current value for debugging
                $logger->info('Current report attempts value: ' . $userReportAttempts);

                // Décrémenter les tentatives de l'utilisateur
                $newReportAttempts = $userReportAttempts - 1;
                $connectedUser->setReportAttempts($newReportAttempts);
                $entityManager->persist($connectedUser);
                $entityManager->flush();

                // Log the new value for debugging
                $logger->info('New report attempts value: ' . $newReportAttempts);
            }
        }

        return new JsonResponse(['report' => $report]);
    }
}