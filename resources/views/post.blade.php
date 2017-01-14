<!DOCTYPE html>
<html>
  <head>
    <script type="text/javascript">
      window.Laravel = {post_id : {{$post->id}}, csrfToken: '{{ csrf_token() }}' };
    </script>

    <style media="screen">
      h1>a,h1>a:hover{
        text-decoration: none;
        color: grey;
      }
    </style>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">

    <title>Posts</title>
  </head>
  <body >
    <div class="container" id="app">
          <h1> <a href="{{url('posts')}}">Blog HomePage</a></h1>
      <div class="container">
              <h2>{{$post->title}}</h2>
              <p>{{$post->content}}</p>
      </div>
      <comments></comments>
    </div>
  </body>
</html>

<script src="{{asset('js/app.js')}}" charset="utf-8"></script>
