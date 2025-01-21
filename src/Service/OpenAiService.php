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

        Génère un rapport structuré en HTML qui inclut les sections suivantes :
        <h3>Résumé</h3>
        <p>Un résumé général des données et des points clés.</p>

        <h3>Analyse des données</h3>
        <p>Analyse des données fournies, y compris les tendances, les anomalies et les points importants.</p>

        <h3>Points forts</h3>
        <p>Les points forts des données, tels que les meilleures performances, les valeurs maximales, etc.</p>

        <h3>Points faibles</h3>
        <p>Les points faibles des données, tels que les performances les plus faibles, les valeurs minimales, etc.</p>

        <h3>Recommandations</h3>
        <p>Des recommandations basées sur l'analyse des données pour améliorer les performances ou atteindre les objectifs.</p>

        Assure-toi que chaque section est bien formatée en HTML et facile à lire.
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
