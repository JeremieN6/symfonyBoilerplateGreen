<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Service\OpenAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/api/generate-report', name: 'generate_report', methods: ['POST'])]
    public function generateReport(Request $request, OpenAiService $openAiService): JsonResponse
    {
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

        return new JsonResponse(['report' => $report]);
    }
}
