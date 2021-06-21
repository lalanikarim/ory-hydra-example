<html>
<head>
  <title>OAuth Login</title>
</head>
<body>
  <h1>Login</h1>
  <form action="/login" method="post">
    @csrf
    <label>Username</label><input type="text" name="username"/>
    <label>Password</label><input type="password" name="password"/>
    <input type="hidden" name="challenge" value="{{ $challenge }}"/>
    <button type="submit">Login</button>
  </form>
</body>
</html>
