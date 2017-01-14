<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Blogpost</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <style media="screen">
      a {
          text-decoration: none;
          color : black;
      }
      h1>a,h1>a:hover{
        text-decoration: none;
        color: grey;
      }
    </style>
    </style>
  </head>
  <body id="body">
    <div class="container">
    <h1> <a href="{{url('posts')}}">Welcome to my blog</a></h1>

    @foreach ($posts as $post )
      <div><a href="{{url('post')}}/{{$post->id}}">
        <h2>{{$post->title}}</h2>
        <p>{{$post->content}}</p>
        </a>
      </div>
    @endforeach
    </div>


  </body>
</html>
<script src="{{asset('js/app.js')}}" charset="utf-8"></script>
