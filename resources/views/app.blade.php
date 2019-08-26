<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@3.5.1" rel="stylesheet"
          type="text/css">
</head>
<body style="height:100%">
<div id="app"
     style="height: 100%;position: absolute; top: 0 ; left: 0 ; width: 100%;">
    <router-view></router-view>
</div>
</body>
<script src="{{ mix('js/app.js') }}"></script>
</html>