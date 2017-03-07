<?php 

namespace meesoverdevest\wp_on_laravel\models;
// require __DIR__ . '/../../vendor/autoload.php';

use Cartalyst\Tags\TaggableTrait;
use Cartalyst\Tags\TaggableInterface;

use Illuminate\Database\Eloquent\Model;

class WPPost extends Model
{
    use TaggableTrait;

    protected $table = "wp_posts";

    protected $fillable = [
      'wp_id', 'title', 'slug', 'featured_image', 'featured', 
      'excerpt', 'user_id', 'format', 'status', 'publishes_at',
      'content'
    ];

    public function categories() {
    	return $this->belongsToMany(WPCategory::class, 'wp_post_category', 'post_id', 'category_id');
    }
}

