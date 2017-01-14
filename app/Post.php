<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use BrianFaust\Commentable\HasCommentsTrait;
use BrianFaust\Commentable\Interfaces\HasComments;

class Post extends Model implements HasComments
{
  use HasCommentsTrait;
}
