#How to create a comment system using Laravel and Vue.js#
In this tutorial, I would be showing you how to build an comment system using Laravel to build a simple API for the backend and a Vue.Js App as the frontend to consume the API.

**Create new project:**

```
composer create-project laravel/laravel=5.3.* laravel-vue-comment
```

or

```
laravel new laravel-vue-comment
```

then cd into the project directory and install dependencies to setup Vue.Js, run

```
laravel-vue-comment
```

`npm install`

What happens?
* npm handles the installation of Vue.js and Gulp
* Gulp is used to run tasks such as compiling assest which enables us to merge all our JS and CSS into one file respectively. Therefore, we only need to link one CSS and Js file. 
To make Gulp work just open a new command line,cd to the project directory and run:

```
gulp watch
```

Next we want to grab a laravel that helps handle the comment class.  It's called [Laravel Commentable](https://github.com/faustbrian/Laravel-Commentable). Install it by running

```
composer require faustbrian/laravel-commentable
```

Add the service provider class to `app/config/app.php`:

```
BrianFaust\Commentable\CommentableServiceProvider::class
```

**Database Setup and Migrations:**
create posts  migration
```
php artisan make:migration create_posts_table --create=posts
```
open `database/migrations/_date_create_posts_table.php`, *type* the following 
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
```
then switch to your command line and run the commands one after the other
```
artisan vendor:publish --provider="BrianFaust\Commentable\CommentableServiceProvider
&&&
php artisan migrate
```

**Models Set Up:**
We setup the post model by running the command
```
php artisan make:model Post
```
Open up the `App/Post.php` and add this 
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use BrianFaust\Commentable\HasCommentsTrait;
use BrianFaust\Commentable\Interfaces\HasComments;

class Post extends Model implements HasComments
{
  use HasCommentsTrait;
}
```

Now that our post model is setup we need to seed some test data into the database. 
Open `database\factories\ModelFactory.php` and add the following:
```php
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Post::class, function (Faker\Generator $faker){
  return [
    'title' => $faker->sentence,
    'content' => $faker->paragraph,
  ];
});

```
Now we can seed data into the database by running the commands:
```
	php artisan tinker
	&&&
	factory('App\Post',10)->create();
	&&&
	factory('App\User',1)->create()
```
We just create 10 dummy posts and 1 dummy user which come in handy later.
**Routes Setup**:
open up `routes\web.php` and add:
```php
Route::get('/posts', function(){
  $posts =  \App\Post::latest()->orderBy('created_at', 'desc')->get();
  return view('posts', compact(['posts']));
});

Route::get('/post/{id}', function($id)
{
  $post =  \App\post::findOrFail($id);
  return view('post',compact(['post']));
});
```
We just created two routes. So that when we go to the url `posts` we would see our posts, and `post/post_id` to view a post, and this page is where we would load the comment functionality.

Finally we need to create an API for our Vue.Js comment app
then `routes/api.php` would be:
```php
Route::group(['middleware' => 'api'], function(){
  
    Route::get('post/{id}/comments', function($id){
      return \App\Post::findOrFail($id)->comments;
    });

    Route::post('post/{id}/comment',function($id, Request $request){
      $user = \App\User::first();

      return \App\Post::findOrFail($id)->comment($request->all(),$user);
    });

    Route::patch('post/{id}/comment/{comment_id}', function($id,$comment_id,Request $request){
      return \App\Post::findOrFail($id)->updateComment($comment_id, $request->all());
    });

    Route::delete('post/{id}/comment/{comment_id}', function($id,$comment_id){
       \App\Post::findOrFail($id)->deleteComment($comment_id);
       return 'deleted';
    });

});

```

Here we defined 4 routes:
1. GET: This route is where we request for the comments of a specific post using the post id. `return \App\Post::findOrFail($id)->comments;`. This line finds a Post model of a specific id, the gets its comment and returns and array of comment objects. This comment property is possible because the Post model extends a Commentable interface.

2. POST: This route handles the creation of a new comment. 
`$user = \App\User::first();` This line of code get the first user in our database.  `return \App\Post::findOrFail($id)->comment($request->all(),$user);` then we call the comment method on the Post Model passing the request data and the user model as parameters.

3. PATCH: This route handles the editing of specific comment. 
`return \App\Post::findOrFail($id)->updateComment($comment_id, $request->all());` This line of code calls the updateComment on our Post model while passing the comment id of the specific comment we want to edit, it then returns the new comment data to the route caller.

4. DELETE: This route deletes a post. 
`\App\Post::findOrFail($id)->deleteComment($comment_id);` This line of code calls the deleteComment on the Post model with the comment id as a parameter.
 `return 'deleted';` *self explanatory*

**Views:**
We make use of two views. Create a new file `resources\views\post.blade.php` and type:
```php
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
```

If the `<comments></comments>` looks strange, dont stress, its Vue thing as you'll see in a minute.

then create another file `resources\views\posts.blade.php`:
```php
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
```
**JS and Vue:**
Now for the main event, create a new file `resources\assets\js\components\comments.vue` and add the following:
```html
<template>
  <div class="container">
    <h4>Add Comment</h4>
    <form action="" @submit.prevent="edit ? editComment(comment.id) : createComment()">
      <div class="input-group">
        <textarea name="body" v-model="comment.body" ref="textarea"  class="form-control"></textarea>
      </div>
        <div class="input-group">
          <button type="submit" class="btn btn-primary" v-show="!edit">Add Comment</button>
          <button type="submit" class="btn btn-primary" v-show="edit">Edit Comment</button>
        </div>
    </form>
    <h4>Comments</h4>
      <ul class="list-group">
        <li class="list-group-item" v-for="comment in comments">
          {{comment.body}}
          <a class="btn btn-default" v-on:click=" showComment(comment.id)">Edit</a>
          <a class="btn btn-danger" v-on:click=" deleteComment(comment.id)">Delete</a>
        </li>
      </ul>
  </div>
</template>
```
Okay don't panic here's a breakdown of what's going on:
**<template> </template>:** Everything in between these tags is a view template that is rendered by Vue.

**@submit.prevent:** The @ symbol is equivalent to on:submit. Using submit.prevent we prevent the default action, which is to submit the form for the URL in the action attribute. Instead, we send the form to the one of the defined functions.

**edit ? editComment(comment.id) : createComment():** Here we have two functions in a ternary operation. If the edit property is true, send the submited data to editComment(). If not, send do createComment(). Both these functions, as well the edit value, will be set in the script section below.

**v-model="comment.body":** v-model allow us to bind a DOM element with a variable in the Javascript code. In this case, the property ‘body’ from the object ‘comment’ is bounded to this input.

**ref="textarea":** This creates a reference to the textarea element that we can access from the Vue methods.

**v-show="!edit" & v-show="edit":** v-show enables the toggle property in the element. In this case, the element will be toggled if the edit variable is false. In the same way, by setting v-show=”edit” in the element below we indicate that we want to display the element when the edit variable is true

**v-for="comment in comments":** v-for directive allows to loop through a variable in a foreach manner. In this we create a <li> element for every item in the comments variable. Which is an array that we set in the fetchComments() function below.

**{{comment.body}}:** We access the the properties of an object using the . notation. In this we are getting the value of the body property of a comment object.

**v-on:click=" showComment(comment.id)":** v-on:click is an event listener that specied for a click event and a specified callback function is called when the event is fired. In this case when we click the button the showComment() function is called with the comment.id as a parameter.

**Now in the same file after the template add the following:**
```html
<script>
  export default{
    data: function(){
      return {
        edit:false,
        comments:[],
        comment: {
          title:'',
          body: '',
          id: '',
        },
      }
    },


created: function(){
    this.fetchComments();
},
  ready: function(){
    this.fetchComments();
  },

  methods: {
    fetchComments: function(){
      this.$http.get("../api/post/"+window.Laravel.post_id+"/comments")
        .then(function (response){
          this.comments = response.data;
      });
    },

    createComment: function(){
      this.$http.post("../api/post/"+window.Laravel.post_id+"/comment", this.comment)
        .then( function (response){
          this.comment.body= '';
          this.fetchComments();
      });
    },

    editComment: function(comment_id){
      this.$http.patch("../api/post/"+window.Laravel.post_id+"/comment/"+comment_id, this.comment)
        .then( function (response){
          this.comment.body= '';
          this.comment.id= '';
          this.fetchComments();
          this.edit = false;
      });
    },

    deleteComment: function(comment_id){
      this.$http.delete("../api/post/"+window.Laravel.post_id+"/comment/"+comment_id)
        .then( function (response){
          this.comment.body= '';
          this.fetchComments();
      });
    },

    showComment: function(comment_id){      
      for (var i = 0; i < this.comments.length; i++) {
        if (this.comments[i].id == comment_id) {
          this.comment.body = this.comments[i].body;
          this.comment.id = this.comments[i].id;
          this.edit = true;
        }
      }
    }
  }
}
</script>
```

Okay, the breakdown is as follows:
**data: function(){}**: Holds our variable declarations.

**edit:** A boolean that indicates when we should display the edit task option or the create task option.

**comments[]:** An array that holds the list of comments retrieved from the database.

**comment:** An object that has a title,body,and id of a comment .

**created:** This is a function that is called when the vue component/template is created in memory. In this case we make a call to the fetchComments() function.

**methods:** this holds the methods available for our vue instance.

**fetchComments:** First `this.$http.get` makes a GET request to the url `"../api/post/"+window.Laravel.post_id+"/comments"` where `window.Laravel.post_id` contains the id of the post receiving the comment. Then we assign the list of comments received from the server and we assign it to the comments array.

**createComment:** We make a POST request to the server passing along the comment object to stored in the database, then we empty the comment object and call the fetchComments function to update our list of comments.

**showComment:** This function recieves a comment_id as a parameter and this id is used to search the comments array. If a match is made the comment object is assigned the data of the comment and this comment is displayed in the text area for editing. Then `edit` is set to true which enables the form to change to edit mode. 

**editComment:** This function requires a comment_id as a parameter. We make a PATCH reqeust to the server by making a call to the url `../api/post/"+window.Laravel.post_id+"/comment/"+comment_id` while passing in the comment object containg the new data for an existing comment. Then the fetchComments() function is called again to update the comments in the comments array. After this `edit` is set to false to disable edit functionality of the comment form.

**deleteComment:** This function requires a comment_id as a parameter which is passed along in the DELETE request made to the server to identify the specific comment to be deleted. Then the fetchComments() function is called again to update the comments in the comments array.


Finally open `resources\assets\js\app.js` and type:
```
require('./bootstrap');

Vue.component('comments',require('./components/comments.vue'));

const app = new Vue({
    el: '#app'

});
```

open the command line and run `gulp` to compile assets.

**Results:** Go to the url `/posts` in the browser to use  the application.
Thats all, if you had any problem with any step, leave a comment below and i will try to answer as soon as i can.

[**The source code is available here.**](https://github.com/tomiiide/laravel-vue-comment)