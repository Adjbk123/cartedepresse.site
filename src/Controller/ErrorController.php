<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route('/erreur', name: 'error')]
    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($statusCode === Response::HTTP_FORBIDDEN) {
            return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [
                'status_code' => $statusCode,
                'message' => $exception->getMessage(),
            ]);
        }
        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'message' => $exception->getMessage(),
        ]);
    }
}
