<?php
namespace App\Twig\Components;

use App\Entity\Product;
use App\Repository\PromoCodeRepository;
use App\Service\PromoCodeService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('promo_code')]
class PromoCodeComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $name = '';

    #[LiveProp]
    public Product $product;

    public ?float $discountedPrice = null;
    public ?string $message = null;
    public bool $success = false;

    public function __construct(
        private PromoCodeRepository $promoCodeRepository,
        private PromoCodeService $promoCodeService
    ) {}

    #[LiveAction]
    public function apply(): void
    {
        $this->discountedPrice = null;
        $this->message = null;
        $this->success = false;

        $promo = $this->promoCodeRepository->findOneBy(['name' => $this->name]);

        $result = $this->promoCodeService->validatePromoCode($promo, $this->product);

        $this->message = $result['message'];
        $this->success = $result['success'];

        if ($this->success) {
            $this->discountedPrice = $result['discountedPrice'];
        }
    }
}
