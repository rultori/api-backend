<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ProductController extends Controller
{
    /**
     * Show the top selling products
     *
     * @param  int  $start_date (optional)
     * @param  int  $end_date (optional)
     * @param  int  $sort (optional)
     * 
     * @return array
     */

    public function topSelling(Request $request)
    {
        // Validate input data
        $request->validate([
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d',
            'sort' => 'in:ASC,DESC',
        ]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $sort = $request->input('sort', 'DESC');
        $data = [];
        $quantityPerId = [];

        $client = new Client(['base_uri' => env('IXAYA_BASE_URL')]);
        $res = $client->request('GET', env('IXAYA_API_URL') . 'orders/list_record', [
            'headers' => ['X-Api-Key' => env('IXAYA_API_TOKEN')]
        ]);
        $orders = json_decode($res->getBody()->getContents())->response;

        $orders_filtered = array_filter($orders, function ($order) use ($start_date, $end_date) {
            $order = (array) $order;
            $order['date'] = date('Y-m-d', strtotime($order['last_update']));
            return ($start_date ? $order['date'] >= $start_date : true) && ($end_date ? $order['date'] <= $end_date : true);
        });

        foreach ($orders_filtered as $order) {
            $order = (array) $order;
            $products_ids = array_column($order['products'], 'qty', 'id');
            $products_names = array_column($order['products'], 'title', 'id');

            foreach ($products_ids as $product_id => $qty) {
                if (!$qty) continue;
                if (array_key_exists($product_id, $quantityPerId)) {
                    $quantityPerId[$product_id]['qty'] += ($qty === null ? 0 : $qty);
                    array_push($quantityPerId[$product_id]['orders'], array('id' => $order['id'], 'qty' => $qty));
                } else {
                    $quantityPerId[$product_id]['qty'] = (int) ($qty === null ? 0 : $qty);
                    $quantityPerId[$product_id]['title'] = $products_names[$product_id];
                    $quantityPerId[$product_id]['orders'] = [array('id' => $order['id'], 'qty' => $qty)];
                }
            }
        }

        uasort($quantityPerId, function ($a, $b) use ($sort) {
            if ($sort === 'DESC') {
                return $a['qty'] < $b['qty'];
            } else {
                return $a['qty'] > $b['qty'];
            }
        });

        $topFiveSelledProducts = array_slice($quantityPerId, 0, 5, true);

        foreach ($topFiveSelledProducts as $key => $topFiveSelledProduct) {
            array_push($data, [
                'type' => 'products',
                'id' => $key,
                'attributes' => [
                    'title' => $topFiveSelledProduct['title'],
                    'qty' => $topFiveSelledProduct['qty'],
                    'orders' => $topFiveSelledProduct['orders'],
                ],
            ]);
        }

        return [
            'data' => $data,
            'jsonapi' => [
                'version' => '1.0',
            ],
            'meta' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'sort' => $sort,
            ],
        ];
    }
}
