<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Potato Car Builder</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      width: 100%;
      height: 100%;
      overflow-x: hidden;
    }

    body {
      margin: 0;
      padding: 0;
    }

    #app {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
  </style>
  @vite(['resources/js/app.js'])
</head>
<body>
  <div id="app"></div>
</body>
</html>
