<?php

namespace App\Message;

class OrderEmailNotificationMessage
{
    public function __construct(
        private readonly int $orderId,
        private readonly string $customerEmail,
        private readonly string $customerName,
        private readonly string $notificationType,
        private readonly array $orderData = []
    ) {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function getOrderData(): array
    {
        return $this->orderData;
    }
}