@component('mail::message')
Hello {{ $supplier['name'] }}, {{ $moduleSetting->mail_body }}

@component('mail::panel')
# Order Details
* Order No: {{ $orderList['order_no'] }}
* Product Id: {{ $item['product_id'] }}
@component('mail::table')

| Name               | Qty                | Price              | Price Sum              |
| ------------------ |:------------------:|:------------------:| ----------------------:|
| {{ $item['name'] }}|{{ $item['qty'] }}  |{{ $item['price'] }}|{{ $item['price_sum'] }}|

@endcomponent
@endcomponent

@component('mail::panel')
# Client Details
@component('mail::table')


| Client Id                              | Client Email                           |
| -------------------------------------- | --------------------------------------:|
| {{ $orderList['client']['client_id'] }}|{{ $orderList['client']['email'] }}     |

@endcomponent
@endcomponent

@component('mail::panel')
# Delivery Details

* Name: {{ $orderList['delivery_address']['gender'] }}, {{ $orderList['delivery_address']['first_name'] }} {{ $orderList['delivery_address']['last_name'] }}
* Company: {{ $orderList['delivery_address']['company'] }}
* Street: {{ $orderList['delivery_address']['street'] }}
* Street No: {{ $orderList['delivery_address']['street_no'] }}
* Address Add: {{ $orderList['delivery_address']['address_add'] }}
* Zip Code: {{ $orderList['delivery_address']['zip_code'] }}
* City: {{ $orderList['delivery_address']['city'] }}
* Country: {{ $orderList['delivery_address']['country'] }}
@endcomponent

@isset($apiUrlForEmails[0])
@component('mail::button', ['url' => $apiUrlForEmails[0], 'color' => 'success'])
Set Order Shipped
@endcomponent
@endisset

@isset($apiUrlForEmails[1])
@component('mail::button', ['url' => $apiUrlForEmails[1], 'color' => 'success'])
Set Order Logistic
@endcomponent
@endisset

@isset($apiUrlForEmails[2])
@component('mail::button', ['url' => $apiUrlForEmails[2], 'color' => 'success'])
Get Order Delivery Note
@endcomponent
@endisset

Thanks,
{{ config('app.name') }}
@endcomponent 