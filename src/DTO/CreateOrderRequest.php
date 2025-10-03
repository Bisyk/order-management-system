<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;


class CreateOrderRequest
{
  #[Assert\NotBlank]
  public string $customer_name;

  #[Assert\NotBlank]
  #[Assert\Email]
  public string $customer_email;

  #[Assert\NotBlank]
  #[Assert\Positive]
  public float $total_amount;

  #[Assert\NotBlank]
  #[Assert\Count(min: 1)]
  #[Assert\Valid]
  public array $order_items = [];
}
