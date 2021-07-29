<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    {{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">--}}

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins" rel="stylesheet">
        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #000000 !important;
                font-family: 'Poppins', sans-serif;
                height: 100vh;
                margin: 0;
            }
            /*

            .full-height {
                height: 100vh;
            }
            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }
            .position-ref {
                position: relative;
            }
            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }
            .content {
                text-align: center;
            }
            .title {
                font-size: 84px;
            }
            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
            .m-b-md {
                margin-bottom: 30px;
            }*/

            html,body { height: 100%; }

            body{
                display: -ms-flexbox;
                display: -webkit-box;
                display: flex;
                -ms-flex-align: center;
                -ms-flex-pack: center;
                -webkit-box-align: center;
                align-items: center;
                -webkit-box-pack: center;
                justify-content: center;
                background-color: #f5f5f5;
            }

            form{
                padding-top: 10px;
                font-size: 14px;
                margin-top: 30px;
            }

            .card-title{ font-weight:300; }

            .btn{
                font-size: 14px;
                margin-top:20px;
            }

            .login-form{
                width:320px;
                margin:20px;
            }

            .sign-up{
                text-align:center;
                padding:20px 0 0;
            }

            span{
                font-size:14px;
            }
            #submit{
                background-color:#70ccb0;
                border: none;
            }
            #submit:focus{
                outline: none;
                box-shadow: 0 0 0 0.2rem #a3ffe3;
            }

        </style>
    </head>
    <body>
    <div class="card login-form">
        <br>
        <div class="container">
            @include('flash::message')
        </div>
        @if (empty($success))
        <div style="margin: 20px">
       @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if ($errors->has('email'))
                @endif
            </div>
        @endif
        </div>
            <div class="card-body">
            <h3 class="card-title text-center">Reset password</h3>

            <div class="card-text">
                <form method="post" action="{{route('resetpassword')}}">
                    @csrf
                    <div class="form-group">

                        <label for="exampleInputEmail1">Enter your email address and we will send you a link to reset your password.</label>
                        <input type="hidden" name="email" value="{{ $email }}" class="form-control form-control-sm" placeholder="password"><br>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="password"><br>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="password confirmation">
                        <input type="hidden" name="token" value="{{ $token }}" class="form-control form-control-sm" placeholder="Enter your email address">
                    </div>

                    <button id="submit" type="submit" class="btn btn-primary btn-block">Send password reset email</button>
                </form>
            </div>
        </div>
        @endif
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        $('#flash-overlay-modal').modal();
    </script>
{{--
    <script src="//code.jquery.com/jquery.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>--}}

    <script>
        $('#flash-overlay-modal').modal();
    </script>
    </body>
</html>
