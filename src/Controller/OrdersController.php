<?php

namespace App\Controller;

use App\Service\OrdersService;
use App\DTO\CreateOrderRequestDto;
use App\DTO\UpdateStatusDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrdersController
{
  public function __construct(
    private readonly OrdersService $ordersService,
    private readonly ValidatorInterface $validator
  ) {}

  #[Route('/api/orders', name: 'app_api_orders_get', methods: ['GET'])]
  public function index(): JsonResponse
  {
    $orders = $this->ordersService->get();

    return new JsonResponse($orders, JsonResponse::HTTP_OK);
  }

  #[Route('/api/orders', name: 'app_api_orders_create', methods: ['POST'])]
  public function create(#[MapRequestPayload()] CreateOrderRequestDto $orderData): JsonResponse
  {
    $errors = $this->validator->validate($orderData);

    if (count($errors) > 0) {
      throw new ValidationFailedException($orderData, $errors);
    }

    $this->ordersService->create($orderData);

    return new JsonResponse(['message' => 'Order created'], JsonResponse::HTTP_CREATED);
  }

  #[Route('/api/orders/{id}', name: 'app_api_orders_get_id', methods: ['GET'])]
  public function show(int $id): JsonResponse
  {
    $order = $this->ordersService->getById($id);

    if (!$order) {
      return new JsonResponse(['message' => 'Order not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    return new JsonResponse($order, JsonResponse::HTTP_OK);
  }

  #[Route('/api/orders/{id}', name: 'app_api_orders_delete', methods: ['DELETE'])]
  public function delete(int $id): JsonResponse
  {
    $this->ordersService->delete($id);

    return new JsonResponse(['message' => 'Order deleted'], JsonResponse::HTTP_OK);
  }

  #[Route('/api/orders/{id}/status', name: 'app_api_orders_update_status', methods: ['PATCH'])]
  public function update(int $id, #[MapRequestPayload()] UpdateStatusDto $statusDto): JsonResponse
  {
    $errors = $this->validator->validate($statusDto);

    if (count($errors) > 0) {
      throw new ValidationFailedException($statusDto, $errors);
    }

    $updatedOrder = $this->ordersService->updateStatus($id, $statusDto);

    if (!$updatedOrder) {
      return new JsonResponse(['message' => 'Order not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    return new JsonResponse($updatedOrder, JsonResponse::HTTP_OK);
  }
}
