<?php

namespace App\Controller\Product;

use App\Entity\Product;
use App\Form\PromoCodeNameType;
use App\Repository\PromoCodeRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShowProductController extends AbstractController
{
    #[Route('/products/{id}', name: 'product_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function __invoke(
        Product $product,
        Request $request,
        PromoCodeRepository $promoCodeRepository
    ): Response
    {

        $discountedPrice = null;

        $form = $this->createForm(PromoCodeNameType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCodeName = $form->getData()['name'];

            $promoCode = $promoCodeRepository->findOneBy(['name' => $promoCodeName]);

            $today = new DateTime();

            if (!$promoCode || $promoCode->getProduct()->getId() !== $product->getId()) {
                $this->addFlash(
                    'error',
                    "Code promo invalide pour ce produit."
                );
            } else if ($promoCode->getExpiresAt() < $today) {
                $this->addFlash(
                    "error",
                    "Ce code promo est expiré."
                );
            } else {
                $discountedPrice = $product->getPrice() * (1 - $promoCode->getDiscountPercentage() / 100);
                $this->addFlash(
                    'success',
                    sprintf(
                        "Code promo appliqué ! Prix remisé : %.2f€",
                        $discountedPrice
                    )
                    );
            }
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'discountedPrice' => $discountedPrice,
        ]);
    }
}
