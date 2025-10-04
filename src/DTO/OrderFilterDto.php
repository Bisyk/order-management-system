<?php

namespace App\DTO;

use App\Enum\OrderStatus;
use Symfony\Component\Validator\Constraints as Assert;


class OrderFilterDto
{
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public ?int $page = 1;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    public ?int $limit = 10;

    #[Assert\Choice(callback: [OrderStatus::class, 'values'])]
    public ?string $status = null;

    #[Assert\Date]
    public ?string $date_from = null;

    #[Assert\Date]
    public ?string $date_to = null;

    #[Assert\Email]
    public ?string $email = null;
}
