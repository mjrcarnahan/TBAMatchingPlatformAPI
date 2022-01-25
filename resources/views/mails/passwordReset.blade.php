<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Email</title>
</head>

<body>
<h2>Password Reset</h2>
<br/>
<a href="{{env('FRONTEND_URL')}}/user/password-reset/{{$token}}">
	Reset Password Link
</a>
</body>

</html>
