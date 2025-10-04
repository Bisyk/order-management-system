<?php

namespace App\DTO;

use App\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateStatusDto
{
  #[Assert\NotBlank]
  #[Assert\Choice(callback: [OrderStatus::class, 'values'])]
  public string $status;
}
