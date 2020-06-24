@component('mail::message')
    # Introduction

    The body of supplier message.

    @isset($supplier)
        | {{ $supplier['name'] }} | {{ $supplier['email'] }} | {{ $supplier['phone'] }} |
    @endisset

    Thanks,
    {{ config('app.name') }}
@endcomponent