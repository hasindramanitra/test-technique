<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\PromoCode;
use DateTime;

class PromoCodeService
{
    /** * validation et calcul de remise 
     * @param PromoCode|null $promoCode 
     * @param float $productOriginalPrice 
     * @return array 
    */ 
    public function validatePromoCode(?PromoCode $promoCode, Product $product): array
    {
        if (!$this->isPromoCodeValid($promoCode, $product)) {
            return $this->buildErrorResult("Code promo invalide pour ce produit.");
        }
        if ($this->isPromoCodeExpired($promoCode)) {
            return $this->buildErrorResult("Ce code promo est expiré.");
        }
        return $this->buildSuccessResult($promoCode, $product);
    }




    /** * verifie si le code promo existe
     * @param PromoCode|null $promoCode
     *  @return boolean 
    */ 
    private function isPromoCodeExist(?PromoCode $promoCode): bool
    {
        return $promoCode !== null;
    }


    /** * 
     * Verifie si le code promo est active
     * @param PromoCode $promoCode
     * @return boolean 
    */ 
    private function isPromoCodeExpired(PromoCode $promoCode): bool
    {
        $today = new DateTime();
        return $promoCode->getExpiresAt() < $today;
    }


    /** * Valide un code promo pour un produit donné.
     * @param PromoCode $promoCode 
     * @param Product $product
     * @return boolean 
     */ 
    private function isPromoCodeForProduct(PromoCode $promoCode, Product $product): bool
    {
        return $promoCode->getProduct()->getId() === $product->getId();
    }


    /** * verifie si le code promo est valide
     * @param PromoCode|null $promoCode
     * @param Product $product 
     * @return boolean 
     */ 
    private function isPromoCodeValid(?PromoCode $promoCode, Product $product): bool
    {
        return $this->isPromoCodeExist($promoCode) && $this->isPromoCodeForProduct($promoCode, $product);
    }



    /** * calcul le prix remisee * * 
     * @param PromoCode $promoCode
     * @param Product $product
     * @return float
    */ 
    private function calculateDiscountedPrice(PromoCode $promoCode, Product $product): float
    {
        $productOriginalPrice = $product->getPrice();
        $discountPercentage = $promoCode->getDiscountPercentage();
        return round($productOriginalPrice - ($productOriginalPrice * $discountPercentage / 100), 2);
    }



    /** * Construit le résultat en cas de succès (code promo valide)
     * @param PromoCode $promoCode
     * @param Product $product
     * @return array 
    */ 
    private function buildSuccessResult(PromoCode $promoCode, Product $product): array
    {
        $discountedPrice = $this->calculateDiscountedPrice($promoCode, $product);
        return ['success' => true, 'message' => sprintf('Code promo appliqué ! Vous avez économisé %d %%', $promoCode->getDiscountPercentage()), 'discountedPrice' => $discountedPrice];
    }



    /** * Construit le résultat en cas d'erreur (code promo invalide, expiré, etc.)
     * @param string $message
     * @return array 
    */ 
    private function buildErrorResult(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
