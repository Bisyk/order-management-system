<?php

namespace App\Controller;

use App\Service\OrdersService;
use App\DTO\CreateOrderRequest;
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

    return new JsonResponse($orders);
  }

  #[Route('/api/orders', name: 'app_api_orders_create', methods: ['POST'])]
  public function create(#[MapRequestPayload()] CreateOrderRequest $orderData): JsonResponse
  {
    $errors = $this->validator->validate($orderData);

    if (count($errors) > 0) {
      throw new ValidationFailedException($orderData, $errors);
    }

    $this->ordersService->create($orderData);

    return new JsonResponse(['message' => 'Order created'], JsonResponse::HTTP_CREATED);
  }
}
