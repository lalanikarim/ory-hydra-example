<html>
<head>
  <title>Logout</title>
</head>
<body>
  <form action="/logout" method="post">
    @csrf
    <input type="hidden" name="challenge" value="{{ $challenge }}"/>
    <input type="submit" name="logout" id="accept" value="Yes"/>
    <input type="submit" name="logout" id="reject" value="No"/>
  </form>
</body>
</html>
