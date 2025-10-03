<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\DTO\CreateOrderRequest;

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
    return array_map(
      fn(Order $order) => $this->serializeOrder($order, false),
      $orders
    );
  }

  public function getById(int $id): ?array
  {
    $order = $this->orderRepository->find($id);

    if (!$order) {
      return null;
    }

    return $this->serializeOrder($order, true);
  }

  private function serializeOrder(Order $order, bool $includeItems = false): array
  {
    $orderData = [
      'id' => $order->getId(),
      'customer_name' => $order->getCustomerName(),
      'customer_email' => $order->getCustomerEmail(),
      'total_amount' => $order->getTotalAmount(),
      'status' => $order->getStatus()->value,
      'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
      'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
    ];

    if ($includeItems) {
      $orderData['order_items'] = array_map(
        fn(OrderItem $item) => $this->serializeOrderItem($item),
        $order->getOrderItems()->toArray()
      );
    }

    return $orderData;
  }

  private function serializeOrderItem(OrderItem $item): array
  {
    return [
      'id' => $item->getId(),
      'product_name' => $item->getProductName(),
      'quantity' => $item->getQuantity(),
      'price' => $item->getPrice(),
    ];
  }
}
