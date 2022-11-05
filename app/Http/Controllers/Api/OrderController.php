<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show all orders
     *
     * @return \Illuminate\View\View
     */
    public function list_record(Request $request)
    {
        // Validate input data
        $request->validate([
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d',
            'currency' => 'in:MXN,USD,EUR',
        ]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $currency = $request->input('currency', 'MXN');
        // $sort = $request->input('sort', 'DESC');
        $data = [];
        $currency_rate = 1;

        $clientIxaya = new Client(['base_uri' => env('IXAYA_BASE_URL')]);
        $clientCurrency = new Client(['base_uri' => env('IXAYA_BASE_URL')]);

        $resIxaya = $clientIxaya->request('GET', env('IXAYA_API_URL') . 'orders/list_record', [
            'headers' => ['X-Api-Key' => env('IXAYA_API_TOKEN')]
        ]);
        $orders = json_decode($resIxaya->getBody()->getContents())->response;

        if ($currency !== 'MXN') {
            $resCurrency = $clientCurrency->request('GET', env('CURRENCY_API_URL'), [
                'headers' => ['apikey' => env('CURRENCY_API_TOKEN')],
                'query' => ['from' => 'MXN', 'to' => $currency, 'amount' => 1]
            ]);

            $currency_rate = json_decode($resCurrency->getBody()->getContents())->result;
        }

        $orders_filtered = array_filter($orders, function ($order) use ($start_date, $end_date) {
            $order = (array) $order;
            $order['date'] = date('Y-m-d', strtotime($order['last_update']));
            return ($start_date ? $order['date'] >= $start_date : true) && ($end_date ? $order['date'] <= $end_date : true);
        });

        foreach ($orders_filtered as $order) {
            $order = (array) $order;

            array_push($data, [
                'type' => 'orders',
                'id' => $order['id'],
                'attributes' => [
                    'total' => round($order['total'] * $currency_rate, 2),
                    'products_count' => count($order['products']),
                    'last_update' => $order['last_update'],
                    'created_date' => $order['create_date'],
                ]
            ]);
        }

        return [
            'data' => $data,
            'meta' => [
                'currency' => $currency,
                'currency_rate' => $currency_rate,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_amount' => array_sum(array_map(function ($order) {
                    return $order['attributes']['total'];
                }, $data)),
                'total_orders' => count($data),
            ],
            'jsonapi' => [
                'version' => '1.0'
            ]
        ];
    }

    /**
     * Show details of an order
     *
     * @return \Illuminate\View\View
     */

    public function show($id)
    {
        $clientIxaya = new Client(['base_uri' => env('IXAYA_BASE_URL')]);

        $resIxaya = $clientIxaya->request('POST', env('IXAYA_API_URL') . 'orders/detail', [
            'headers' => ['X-Api-Key' => env('IXAYA_API_TOKEN')],
            'form_params' => ['order_id' => $id]
        ]);
        $order = json_decode($resIxaya->getBody()->getContents())->response;

        return [
            'data' => [
                'type' => 'orders',
                'id' => $order->id,
                'attributes' => [
                    'user_id' => $order->user_id,
                    'phone' => $order->phone,
                    'address' => $order->address,
                    'city' => $order->city,
                    'state' => $order->state,
                    'street_name' => $order->street_name,
                    'zip_code' => $order->zip_code,
                    'discount' => $order->discount,
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'order_code' => $order->order_code,
                    'paid' => $order->paid,
                    'enabled' => $order->enabled,
                    'create_date' => $order->create_date,
                    'last_update' => $order->last_update,
                    'products_count' => count($order->products),
                ],
                'relationships' => [
                    'products' => [
                        'data' => array_map(function ($product) {
                            return [
                                'type' => 'products',
                                'id' => $product->id,
                            ];
                        }, $order->products)
                    ]
                ],
                'jsonapi' => [
                    'version' => '1.0'
                ]
            ],
        ];
    }
}
