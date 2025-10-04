<?php

namespace Tests\Api;

use Tests\Support\ApiTester;

class CreateOrderCest
{
    public function _before(ApiTester $I)
    {
    }

    public function tryToCreateOrder(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john.doe@example.com',
            'total_amount' => 99.99,
            'order_items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 2,
                    'price' => 49.99
                ]
            ]
        ]);
        
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Order created'
        ]);
    }

    public function tryToCreateOrderWithInvalidData(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/orders', [
            'customer_name' => '',
            'customer_email' => 'invalid-email',
            'total_amount' => -10
        ]);
        
        $I->seeResponseCodeIs(422);
    }
}
