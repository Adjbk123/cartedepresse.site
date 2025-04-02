<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeService
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function generateQrCodeUrl(string $numDemande): string
    {
        $qrCodeUrl = $this->router->generate('app_card_detail', ['numDemande' => $numDemande], UrlGeneratorInterface::ABSOLUTE_URL);
        $qrCode = new QrCode($qrCodeUrl);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);
        return 'data:image/png;base64,' . base64_encode($qrCodeResult->getString());
    }
}
