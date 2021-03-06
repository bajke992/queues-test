<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>
        body {
            font-family: 'Lato';
        }

        ul {
            list-style: none;
        }

        li {
            display: inline;
            margin-right: 20px;
        }

        .fa-btn {
            margin-right: 6px;
        }

        td, th {
            border: 1px solid;
            text-align: center;
        }
        table { width: 100%; }
    </style>
</head>
<body id="app-layout">

    <ul>
        <li>
            <a href="{{ URL::route('home') }}">Finished</a>
        </li>
        <li>
            <a href="{{ URL::route('live') }}">Live</a>
        </li>
        <li>
            <a href="{{ URL::route('ready') }}">Ready</a>
        </li>
    </ul>

    @yield('content')

    <!-- JavaScripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/1.4.0/jquery.floatThead.js"></script>
    {{--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>--}}
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
    @yield('js')
</body>
</html>
