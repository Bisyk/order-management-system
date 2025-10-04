<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\DTO\CreateOrderRequestDto;
use App\DTO\UpdateStatusDto;

class OrdersService
{
  public function __construct(
    private readonly OrderRepository $orderRepository
  ) {}

  public function create(CreateOrderRequestDto $orderData): void
  {
    $order = new Order();
    $this->populateOrderData($order, $orderData);
    $order->setStatus(OrderStatus::PENDING);
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setUpdatedAt(new \DateTimeImmutable());

    $this->populateOrderItems($order, $orderData->order_items);

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

  public function delete(int $id): void
  {
    $order = $this->orderRepository->find($id);

    if ($order) {
      $this->orderRepository->remove($id, true);
    }
  }

  public function updateStatus(int $id, UpdateStatusDto $statusDto): ?array
  {
    $order = $this->orderRepository->find($id);

    if (!$order) {
      return null;
    }

    $order->setStatus(OrderStatus::from($statusDto->status));
    $order->setUpdatedAt(new \DateTimeImmutable());
    $this->orderRepository->save($order, true);

    return $this->serializeOrder($order, true);
  }

  public function update(int $id, CreateOrderRequestDto $orderData): ?array
  {
    $order = $this->orderRepository->find($id);

    if (!$order) {
      return null;
    }

    $this->populateOrderData($order, $orderData);

    // Clear existing items
    foreach ($order->getOrderItems() as $existingItem) {
      $order->removeOrderItem($existingItem);
    }

    // Add new items
    $this->populateOrderItems($order, $orderData->order_items);

    $this->orderRepository->save($order, true);

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

  private function populateOrderData(Order $order, CreateOrderRequestDto $orderData): void
  {
    $order->setCustomerName($orderData->customer_name);
    $order->setCustomerEmail($orderData->customer_email);
    $order->setTotalAmount($orderData->total_amount);
    $order->setUpdatedAt(new \DateTimeImmutable());

    $this->populateOrderItems($order, $orderData->order_items);
  }

  private function populateOrderItems(Order $order, array $orderItemsData): void
  {
    foreach ($orderItemsData as $itemData) {
      $orderItem = new OrderItem();
      $orderItem->setProductName($itemData['product_name']);
      $orderItem->setQuantity($itemData['quantity']);
      $orderItem->setPrice($itemData['price']);
      $order->addOrderItem($orderItem);
    }
  }
}
