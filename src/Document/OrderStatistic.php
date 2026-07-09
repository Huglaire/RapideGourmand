<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'order_statistics')]
class OrderStatistic
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'int')]
    private int $orderId;

    // Le nom du menu n'est pas stocké afin d'éviter toute incohérence.
    // En cas de renommage d'un menu, les documents MongoDB conserveraient l'ancien nom.
    // Le titre est récupéré dynamiquement depuis MySQL, qui reste la source de vérité.
    #[ODM\Field(type: 'int')]
    private int $menuId;

    #[ODM\Field(type: 'date')]
    private \DateTime $deliveryDate;

    #[ODM\Field(type: 'int')]
    private int $guestNumber;

    #[ODM\Field(type: 'float')]
    private float $totalPrice;

    #[ODM\Field(type: 'bool')]
    private bool $cancelled = false;

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function setMenuId(int $menuId): static
    {
        $this->menuId = $menuId;

        return $this;
    }

    public function getDeliveryDate(): \DateTime
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTime $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getGuestNumber(): int
    {
        return $this->guestNumber;
    }

    public function setGuestNumber(int $guestNumber): static
    {
        $this->guestNumber = $guestNumber;

        return $this;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): static
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}