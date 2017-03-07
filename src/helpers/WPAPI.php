<?php

namespace meesoverdevest\wp_on_laravel\helpers;

use meesoverdevest\wp_on_laravel\models\Post;
use meesoverdevest\wp_on_laravel\models\Category;
use Carbon\Carbon;

class WPAPI {
	protected $url = '';
	
	public function syncWP()
  {
    $this->buildUrl();
  	$this->syncCategories();
  	$this->syncPosts();
  }

  protected function syncCategories($page = 1) {
  	$categories = collect($this->getJson($this->url . 'categories?page=' . $page));
		// dd($categories);
  	// Sync categories
  	foreach ($categories as $category) {
      $this->syncCategory($category);
    }

    if(count($categories) > 0){
    	$this->syncCategories($page + 1);
    }
  }

  protected function syncPosts($page = 1) {
  	$posts = collect($this->getJson($this->url . 'posts/?_embed&filter[orderby]=modified&page=' . $page));

  	// Sync posts
  	foreach ($posts as $post) {
      $this->syncPost($post);
    }

    if(count($posts) > 0){
    	$this->syncPosts($page + 1);
    }
  }

  protected function getJson($url)
  {
    $response = file_get_contents($url, false);
    return json_decode( $response );
  }

  protected function syncPost($data)
	{
    $found = Post::where('wp_id', $data->id)->first();

    if (! $found) {
      return $this->createPost($data);
    }

    if ($found and $found->updated_at->format("Y-m-d H:i:s") < $this->carbonDate($data->modified)->format("Y-m-d H:i:s")){	
      return $this->updatePost($found, $data);
    }
	}

	protected function syncCategory($data)
	{
    $found = Category::where('wp_id', $data->id)->first();

    if (! $found) {
      return $this->createCategory($data);
    }

    if ($found and $found->updated_at->format("Y-m-d H:i:s") < $this->carbonDate(Carbon::now())->format("Y-m-d H:i:s")){	
      return $this->updateCategory($found, $data);
    }
	}

	protected function carbonDate($date)
	{
	  return Carbon::parse($date);
	}

	protected function createPost($data)
	{
    $post = new Post();
    $post->id = $data->id;
    $post->wp_id = $data->id;
    $post->user_id = $this->getAuthor($data->_embedded->author);
    $post->title = $data->title->rendered;
    $post->slug = $data->slug;
    $post->featured_image = $this->featuredImage($data->_embedded);
    $post->featured = ($data->sticky) ? 1 : null;
    $post->excerpt = $data->excerpt->rendered;
    $post->content = $data->content->rendered;
    $post->format = $data->format;
    $post->status = 'publish';
    $post->publishes_at = $this->carbonDate($data->date);
    $post->created_at = $this->carbonDate($data->date);
    $post->updated_at = $this->carbonDate($data->modified);
    $post->save();

    $this->alterExcerptLink( $post );

    // sync categories

    $categories = [];
    foreach($data->categories as $key => $value){
        $categories[] = $value;
    }
    $post->categories()->sync($categories);

    if(!empty($data->_embedded->{"wp:term"})){
    	$this->syncTags($post, $data->_embedded->{"wp:term"});
    }

    return $post;
	}

	protected function updatePost(Post $found, $data)
	{
    $found->id = $data->id;
    $found->wp_id = $data->id;
    $found->user_id = $this->getAuthor($data->_embedded->author);
    $found->title = $data->title->rendered;
    $found->slug = $data->slug;
    $found->featured_image = $this->featuredImage($data->_embedded);
    $found->featured = ($data->sticky) ? 1 : null;
    $found->excerpt = $data->excerpt->rendered;
    $found->content = $data->content->rendered;
    $found->format = $data->format;
    $found->status = 'publish';
    $found->publishes_at = $this->carbonDate($data->date);
    $found->created_at = $this->carbonDate($data->date);
    $found->updated_at = $this->carbonDate($data->modified);
    $found->save();

    $this->alterExcerptLink( $found );

    // $found->category_id = $this->getCategory($data->_embedded->{"wp:term"});

    // sync categories
    $categories = [];
    foreach($data->categories as $key => $value){
        $categories[] = $value;
    }
    $found->categories()->sync($categories);

    if(!empty($data->_embedded->{"wp:term"})){
      $this->syncTags($found, $data->_embedded->{"wp:term"});
    }
    
    return $found;
	}

	protected function createCategory($data)
	{
    $category = new Category();
    $category->id = $data->id;
    $category->wp_id = $data->id;
    $category->name = $data->name;
    $category->slug = $data->slug;
    $category->description = $data->description;
    $category->parent = $data->parent;
    $category->created_at = $this->carbonDate(Carbon::now());
    $category->updated_at = $this->carbonDate(Carbon::now());
    $category->save();
    return $category;
	}

	protected function updateCategory(Category $found, $data)
	{
    $found->id = $data->id;
    $found->wp_id = $data->id;
    $found->name = $data->name;
    $found->slug = $data->slug;
    $found->description = $data->description;
    $found->parent = $data->parent;
    $found->updated_at = $this->carbonDate(Carbon::now());
    $found->save();
    return $found;
	}

	public function featuredImage($data)
	{
    if (property_exists($data, "wp:featuredmedia")) {
      $data = head($data->{"wp:featuredmedia"});
      if (isset($data->source_url)) {
        return $data->source_url;
      }
    }
    return null;
	}

	public function getAuthor($data) {
		return $data[0]->id;
	}

	private function syncTags(Post $post, $tags)
	{
    $tags = collect($tags)->collapse()->where('taxonomy', 'post_tag')->pluck('name')->toArray();
    if (count($tags) > 0) {
      $post->setTags($tags);
    }
	}

  private function alterExcerptLink( Post $post ) {
    // Check whether read-more link exists in excerpt
    if( strpos( $post->excerpt, '<a href="'. url('/') . '/blog/' ) !== false ){

      $new_excerpt = $this->replace_between($post->excerpt, '<a href="', '" class="more-link"', url('/') . '/posts/' . $post->id);

      $post->excerpt = $new_excerpt;
      $post->save();
    }

    return;
  }

  private function replace_between($str, $needle_start, $needle_end, $replacement) {
    $pos = strpos($str, $needle_start);
    $start = $pos === false ? 0 : $pos + strlen($needle_start);

    $pos = strpos($str, $needle_end, $start);
    $end = $pos === false ? strlen($str) : $pos;

    return substr_replace($str, $replacement, $start, $end - $start);
  }

  private function buildUrl() {
    $base = url('/');
    $blog = '/blog/wp-json/wp/v2/';

    $this->url = $base . $blog;
    
    return;
  }
}