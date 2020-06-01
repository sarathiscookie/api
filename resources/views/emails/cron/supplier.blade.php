@component('mail::message')
# Introduction

The body of supplier message.

@component('mail::table')
| Name       | Email         | Contact  |
| ------------- |:-------------:| --------:|
@foreach($user as $userList)
| {{ $userList['name'] }} | {{ $userList['email'] }} | {{ $userList['phone'] }} |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
