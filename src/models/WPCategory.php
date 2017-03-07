<?php
namespace meesoverdevest\wp_on_laravel\models;
use Illuminate\Database\Eloquent\Model;

class WPCategory extends Model
{
  protected $table = "wp_categories";

  protected $fillable = [
      'wp_id', 'name', 'slug', 'description', 'parent'
  ];

  public function posts() {
  	return $this->belongsToMany(WPPost::class, 'wp_post_category', 'category_id', 'post_id');
  }

  public function parent() {
  	return $this->belongsTo(WPCategory::class, 'parent');
  }

  public function children() {
  	return $this->hasMany(WPCategory::class, 'parent');
  }
}

