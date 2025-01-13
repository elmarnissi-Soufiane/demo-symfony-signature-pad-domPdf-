<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfController extends AbstractController
{
    // Route pour afficher le formulaire de signature
    #[Route('/signature', name: 'app_signature')]
    public function showSignatureForm(): Response
    {
        return $this->render('pdf/sample.html.twig', [
            'companyName' => 'Votre Société SARL',
            'clientName' => 'M. John Doe',
            'contractDate' => (new \DateTime())->format('d/m/Y'),
            'contractDetails' => 'Ce contrat formalise l’accord entre les deux parties pour une prestation de services informatique.',
        ]);
    }

    // Route pour générer le PDF avec la signature
    #[Route('/generate-contract', name: 'app_generate_contract', methods: ['POST'])]
    public function generateContract(Request $request): Response
    {
        // Récupérer la signature envoyée (en base64)
        $signatureData = $request->request->get('signature');

        // Données pour le contrat
        $contractData = [
            'companyName' => 'Votre Société SARL',
            'clientName' => 'M. John Doe',
            'contractDate' => (new \DateTime())->format('d/m/Y'),
            'contractDetails' => 'Ce contrat formalise l’accord entre les deux parties pour une prestation de services informatique.',
            'signatureData' => $signatureData, // Passe les données de la signature à la vue
        ];

        // Utiliser un fichier Twig dédié au PDF
        $html = $this->renderView('pdf/contract.html.twig', $contractData);

        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true); // Important pour les images en base64
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF généré
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contract.pdf"',
            ]
        );
    }
}