<?php

namespace gaurav\tagging\Test;

use Illuminate\Database\Eloquent\Model;
use gaurav\tagging\Traits\HasMentions;

class TestCommentModel extends Model
{
    use HasMentions;

    protected $table = 'test_mention_comments';
    protected $guarded = [];
    public $timestamps = false;
}
