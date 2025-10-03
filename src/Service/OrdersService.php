<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\DTO\CreateOrderRequest;
use App\Entity\OrderItem;

class OrdersService
{
  public function __construct(
    private readonly OrderRepository $orderRepository
  ) {}

  public function create(CreateOrderRequest $orderData): void
  {
    $order = new Order();
    $order->setCustomerName($orderData->customer_name);
    $order->setCustomerEmail($orderData->customer_email);
    $order->setTotalAmount($orderData->total_amount);
    $order->setStatus(OrderStatus::PENDING);
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setUpdatedAt(new \DateTimeImmutable());

    foreach ($orderData->order_items as $itemData) {
      $orderItem = new OrderItem();
      $orderItem->setProductName($itemData['product_name']);
      $orderItem->setQuantity($itemData['quantity']);
      $orderItem->setPrice($itemData['price']);
      $order->addOrderItem($orderItem);
    }

    $this->orderRepository->save($order, true);
  }

  public function get(): array
  {
    $orders = $this->orderRepository->findAll();
    $orderData = [];

    foreach ($orders as $order) {
      $orderData[] = [
        'id' => $order->getId(),
        'customer_name' => $order->getCustomerName(),
        'customer_email' => $order->getCustomerEmail(),
        'total_amount' => $order->getTotalAmount(),
        'status' => $order->getStatus()->value,
        'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
      ];
    }

    return $orderData;
  }
}
