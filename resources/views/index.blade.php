<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>PHP CSV Importer</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style src="{{ asset('css/app.css')}}"></style>
        <script src="{{ asset('js/jquery/jquery-3.7.1.min.js')}}"></script>
        <script src="{{ asset('js/app.js')}}"></script>
    </head>
    <body class="antialiased">
        <button id="uploadBtn" class="btn">
            Upload file
        </button>
        <input type='file' id="fileInput" style="display:none">
        <table id='importTable'>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>File name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
    </body>
    
</html>
