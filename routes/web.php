<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/posts', function(){
  $posts =  \App\Post::latest()->orderBy('created_at', 'desc')->get();
  return view('posts', compact(['posts']));
});

Route::get('/post/{id}', function($id)
{
  $post =  \App\post::findOrFail($id);
  return view('post',compact(['post']));
});
