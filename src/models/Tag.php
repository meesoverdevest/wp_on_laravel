<?php
namespace meesoverdevest\wp_on_laravel\models;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'tags';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['namespace', 'name', 'slug', 'count'];

  public function posts() {
  	return Post::whereTag($this->slug)->orderBy('created_at', 'desc')->get();
  }
}
