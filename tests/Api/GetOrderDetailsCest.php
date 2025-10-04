<?php

namespace Tests\Api;

use Tests\Support\ApiTester;

class GetOrderDetailsCest
{
    public function _before(ApiTester $I)
    {
    }

    public function tryToGetOrderDetails(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/orders', [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane.smith@example.com',
            'total_amount' => 150.50,
            'order_items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => 3,
                    'price' => 50.17
                ]
            ]
        ]);
        $I->seeResponseCodeIs(201);

        $I->sendGet('/api/orders/1');
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.id');
        $I->seeResponseJsonMatchesJsonPath('$.customer_name');
        $I->seeResponseJsonMatchesJsonPath('$.customer_email');
        $I->seeResponseJsonMatchesJsonPath('$.total_amount');
    }

    public function tryToGetNonExistentOrder(ApiTester $I)
    {
        $I->sendGet('/api/orders/999999');
        
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Order not found'
        ]);
    }
}
