<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

/**
 * Payment Package.
 *
 * Represents a package containing products.
 * The total amount of products must match the package amount.
 */
class PaymentPackage
{
    /**
     * @var PaymentProduct[]
     */
    private array $products = [];

    /**
     * Create a new PaymentPackage instance.
     *
     * @param string      $id      Unique Package ID
     * @param int         $amount  Total Amount for this package
     * @param string|null $name    Name of the package (Optional)
     * @param int|null    $userFee User Fee (Optional)
     */
    public function __construct(
        public readonly string $id,
        public readonly int $amount,
        public readonly ?string $name = null,
        public readonly ?int $userFee = null
    ) {
    }

    /**
     * Add a product to the package.
     *
     * @param PaymentProduct $product
     *
     * @return $this
     */
    public function addProduct(PaymentProduct $product): self
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Get all products in this package.
     *
     * @return PaymentProduct[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'amount' => $this->amount,
            'products' => array_map(fn (PaymentProduct $p) => $p->toArray(), $this->products),
        ];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->userFee !== null) {
            $data['userFee'] = $this->userFee;
        }

        return $data;
    }
}
