<html>
<head>
  <title>Consent</title>
</head>
<body>
  <h1>An application requests access to your data!</h1>
  <form method="post" action="{{ $action }}">
    @csrf
    <input type="hidden" name="challenge" value="{{ $challenge }}"/>
    <p>
      Hi {{ $user }}! application <strong>{{ $client["clientName"] || $client["clientId"] }}</strong> wants to access resources on your behalf and to:
      @foreach($requested_scope as $scope)
      <input type="checkbox" class="grant_scope" id="{{$scope}}" value="{{$scope}}" name="grant_scope[]">
      <label>{{$scope}}</label>
      <br/>
      @endforeach
    </p>  
    <p>
      Do you want to be asked next time when this application wants to access your data? The application will not be able to ask more permissions without your consent.
    </p>
    <p>
      <input type="checkbox" id="remember" name="remember" value="1"/>
      <label>Do not ask me again</label>
    </p>
    <p>
      <input type="submit" id="accept" name="submit" value="Allow access"/>
      <input type="submit" id="reject" name="submit" value="Deny access"/>

    </p>
  </form>
</body>
</html>
