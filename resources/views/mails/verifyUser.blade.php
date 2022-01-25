<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
</head>

<body>
<h2>Welcome {{$user->first_name .' '. $user->last_name}}</h2>
<br/>
Please click on the below link to verify your email account
<br/>
<br/>
<a href="{{env('FRONTEND_URL')}}/user/verify/{{$user->verifyUser->token}}">Verify Email</a>
</body>

</html>
