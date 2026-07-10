<?php

namespace App\Utility\Pathao;

use App\Exceptions\PathaoCourierValidationException;
use App\Exceptions\PathaoException;
use App\Utility\Pathao\BaseAPIUtility;
use GuzzleHttp\Exception\GuzzleException;

class OrderAPIUtility extends BaseAPIUtility
{
    /**
     * Order Create
     *
     * @param array $array
     *
     * @return mixed
     * @throws PathaoException
     * @throws GuzzleException|PathaoCourierValidationException
     */
    public function create($array)
    {
        $this->validation($array, [
            "store_id",
            "merchant_order_id",
            "sender_name",
            "sender_phone",
            "recipient_name",
            "recipient_phone",
            "recipient_address",
            "recipient_city",
            "recipient_zone",
            "recipient_area",
            "delivery_type",
            "item_type",
            "special_instruction",
            "item_quantity",
            "item_weight",
            "amount_to_collect",
            "item_description"
        ]);

        $response = $this->authorization()->send("POST", "aladdin/api/v1/orders", $array);
        return $response->data;
    }

    /**
     * Order Details
     *
     * @param string $consignmentId
     *
     * @return mixed
     * @throws GuzzleException
     * @throws PathaoException
     */
    public function orderDetails($consignmentId)
    {
        $response = $this->authorization()->send("GET", "aladdin/api/v1/orders/{$consignmentId}");
        return $response->data;
    }

    /**
     * Delivery price calculation
     *
     * @param array $array
     *
     * @return mixed
     * @throws GuzzleException
     * @throws PathaoException|PathaoCourierValidationException
     */
    public function priceCalculation($array)
    {
        $this->validation($array, [
            "store_id",
            "item_type",
            "delivery_type",
            "item_weight",
            "recipient_city",
            "recipient_zone"
        ]);

        $response = $this->authorization()->send("POST", "aladdin/api/v1/merchant/price-plan", $array);
        return $response->data;
    }
}