<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

/**
 * Payment Package.
 *
 * Represents a package containing products.
 * The total amount of products must match the package amount.
 *
 * Supports both mutable (addProduct) and immutable (withProduct) patterns:
 * - addProduct(): Fluent interface for builder pattern
 * - withProduct(): Returns new instance for immutable pattern
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
     * @param string                 $id       Unique Package ID
     * @param int                    $amount   Total Amount for this package
     * @param string|null            $name     Name of the package (Optional)
     * @param int|null               $userFee  User Fee (Optional)
     * @param PaymentProduct[]|null  $products Initial products (Optional)
     */
    public function __construct(
        public readonly string $id,
        public readonly int $amount,
        public readonly ?string $name = null,
        public readonly ?int $userFee = null,
        ?array $products = null
    ) {
        if ($products !== null) {
            $this->products = $products;
        }
    }

    /**
     * Add a product to the package (mutable, returns $this for fluent interface).
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
     * Create a new package instance with an additional product (immutable pattern).
     *
     * @param PaymentProduct $product
     *
     * @return self A new PaymentPackage instance
     */
    public function withProduct(PaymentProduct $product): self
    {
        return new self(
            id: $this->id,
            amount: $this->amount,
            name: $this->name,
            userFee: $this->userFee,
            products: [...$this->products, $product]
        );
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
