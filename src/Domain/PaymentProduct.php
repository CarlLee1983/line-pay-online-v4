<?php

declare(strict_types=1);

namespace LinePay\Online\Domain;

/**
 * Payment Product.
 *
 * Represents a single product item within a package.
 */
class PaymentProduct
{
    /**
     * Create a new PaymentProduct instance.
     *
     * @param string      $name          Product Name
     * @param int         $quantity      Quantity
     * @param int         $price         Price per unit
     * @param string|null $id            Product ID (Optional)
     * @param string|null $imageUrl      Product Image URL (Optional)
     * @param int|null    $originalPrice Original Price for display (Optional)
     */
    public function __construct(
        public readonly string $name,
        public readonly int $quantity,
        public readonly int $price,
        public readonly ?string $id = null,
        public readonly ?string $imageUrl = null,
        public readonly ?int $originalPrice = null
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->imageUrl !== null) {
            $data['imageUrl'] = $this->imageUrl;
        }

        if ($this->originalPrice !== null) {
            $data['originalPrice'] = $this->originalPrice;
        }

        return $data;
    }
}
