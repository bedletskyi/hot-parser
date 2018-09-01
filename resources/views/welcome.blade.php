<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Parser - Hotline</title>

        <link rel="apple-touch-icon-precomposed" sizes="57x57" href="{{ asset('img/favico/apple-touch-icon-57x57.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ asset('img/favico/apple-touch-icon-114x114.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ asset('img/favico/apple-touch-icon-72x72.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ asset('img/favico/apple-touch-icon-144x144.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="60x60" href="{{ asset('img/favico/apple-touch-icon-60x60.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="120x120" href="{{ asset('img/favico/apple-touch-icon-120x120.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="76x76" href="{{ asset('img/favico/apple-touch-icon-76x76.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="152x152" href="{{ asset('img/favico/apple-touch-icon-152x152.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('img/favico/favicon-196x196.png') }}" sizes="196x196">
        <link rel="icon" type="image/png" href="{{ asset('img/favico/favicon-96x96.png') }}" sizes="96x96">
        <link rel="icon" type="image/png" href="{{ asset('img/favico/favicon-16x16.png') }}" sizes="16x16">
        <link rel="icon" type="image/png" href="{{ asset('img/favico/favicon-128.png') }}" sizes="128x128">
        <meta name="application-name" content="&nbsp;">
        <meta name="msapplication-TileColor" content="#FFFFFF">
        <meta name="msapplication-TileImage" content="{{ asset('img/favico/mstile-144x144.png') }}">
        <meta name="msapplication-square70x70logo" content="{{ asset('img/favico/mstile-70x70.png') }}">
        <meta name="msapplication-square150x150logo" content="{{ asset('img/favico/mstile-150x150.png') }}">
        <meta name="msapplication-wide310x150logo" content="{{ asset('img/favico/mstile-310x150.png') }}">
        <meta name="msapplication-square310x310logo" content="{{ asset('img/favico/mstile-310x310.png') }}">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

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
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Домой</a>
                    @else
                        <a href="{{ route('login') }}">Вход</a>
                        <a href="{{ route('register') }}">Регистрация</a>
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    HOTLINE
                </div>

                <div class="links">
                    <p>Parsing the Hotline easy</p>
                </div>
            </div>
        </div>
    </body>
</html>
