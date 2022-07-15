<?php

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

if (!function_exists('apiresponse')) {
    /**
     * @param boolean $status
     * @param string $msg
     * @param array|null $data
     * @param integer $http_status
     * @return \Illuminate\Http\JsonResponse
     */
    function apiresponse($status, $msg, $data = null, $http_status = 200)
    {
        return response()->json(['success' => $status, 'message' => $msg, 'data' => $data], $http_status);
    }
}

if (!function_exists('apiresponse_two')) {
    /**
     * @param boolean $status
     * @param string $msg
     * @param array|null $data
     * @param integer $http_status
     * @return \Illuminate\Http\JsonResponse
     */
    function apiresponse_two($data = null, $http_status = 200)
    {
        return response()->json($data, $http_status);
    }
}


if (!function_exists('convertAddressToLatLng')) {
    /**
     *
     * Convert Address to lat lng
     * @param string $address
     * @return array|boolean
     */
    function convertAddressToLatLng($address)
    {
        $params = http_build_query([
            'key' => env("GOOGLE_MAPS_API_KEY"),
            'address' => $address
        ]);
        $link = 'https://maps.googleapis.com/maps/api/geocode/json?' . $params;
        $res = json_decode(file_get_contents($link), true);
        if ($res['status'] == "OK" and $res['results'][0]) {
            return [
                'lat' => $res['results'][0]['geometry']['location']['lat'],
                'lng' => $res['results'][0]['geometry']['location']['lng']
            ];
        } else {
            return false;
        }
    }
}



if (!function_exists('convertLatLngToAddress')) {
    /**
     *
     * Convert Address to lat lng
     * @param string $address
     * @return array|boolean
     */
    function convertLatLngToAddress($lat, $lng)
    {
        $params = http_build_query([
            'key' => env("GOOGLE_MAPS_API_KEY"),
            'latlng' => $lat . ',' . $lng
        ]);
        $link = 'https://maps.googleapis.com/maps/api/geocode/json?' . $params;
        $res = json_decode(file_get_contents($link), true);
        if ($res['status'] == "OK" and $res['results'][0]) {
            return $res['results'][0]['formatted_address'];
        } else {
            return false;
        }
    }
}

if (!function_exists('date_compare')) {
    /**
     *
     * Convert Address to lat lng
     * @param string $address
     * @return array|boolean
     */
    function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }
}



if (!function_exists('SendNotification')) {
    /**
     * Send Notification to Device
     * @param string $device_id
     * @param string $title
     * @param string $body
     * @param null $data
     */
    function SendNotification($device_id, $title, $body, $data = null)
    {
        try {
            if ($device_id) {
                $factor = (new Factory())->withServiceAccount('firebase.json');
                $messaging = $factor->createMessaging();
                $message = CloudMessage::withTarget('token', $device_id)
                    ->withNotification(Notification::create($title, $body));
                if ($data) {
                    $message = CloudMessage::withTarget('token', $device_id)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);
                }
                $messaging->send($message);
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}


?>
