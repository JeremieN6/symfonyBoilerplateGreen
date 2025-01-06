<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiService
{
    private $apiKey;
    private $httpClient;

    public function __construct(ParameterBagInterface $parameterBag, HttpClientInterface $httpClient)
    {
        $this->apiKey = $parameterBag->get('OPENAI_API_KEY');
        $this->httpClient = $httpClient;
    }

    public function generateReport(array $headers, array $rows, string $objective): string
    {
        // Construire un résumé des données pour le prompt
        $dataSummary = "Voici les données fournies :\n\n";
        $dataSummary .= implode(" | ", $headers) . "\n";
        $dataSummary .= str_repeat("-", strlen(implode(" | ", $headers))) . "\n";

        foreach ($rows as $row) {
            $dataSummary .= implode(" | ", $row) . "\n";
        }

        // Construire le prompt pour OpenAI
        $prompt = <<<TEXT
        Tu es un assistant qui analyse des données et génère des rapports. Ton objectif est : "{$objective}".

        Données d'entrée :
        {$dataSummary}

        Génère un rapport qui inclut :
        - Le mois avec les ventes les plus élevées et le profit le plus élevé.
        - Le mois avec les ventes les plus faibles.
        - Les tendances générales (augmentation/diminution).
        - Les recommandations pour améliorer les ventes et les profits.
        TEXT;

        // Préparer les données pour l'API OpenAI
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un assistant qui analyse des données.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $responseContent = $response->getContent();
            $decodedResponse = json_decode($responseContent, true);

            if (isset($decodedResponse['choices'][0]['message']['content'])) {
                return $decodedResponse['choices'][0]['message']['content'];
            }

            return 'Erreur : La réponse de l\'API OpenAI est vide ou invalide.';
        } catch (\Exception $e) {
            return 'Erreur lors de la communication avec l\'API OpenAI : ' . $e->getMessage();
        }
    }
}
