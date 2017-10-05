<?php

function publish_post_webhook($post_ID) {
  
  $url = "'. env('APP_URL') .'/wp/sync";
  $data = array("wp_id" => $post_ID);

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

function delete_category_webhook($category_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("wp_id" => $category_ID, "type" => "category");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

// Change to other webhooks
function delete_tag_webhook($tag_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("wp_id" => $tag_ID, "type" => "tag");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

function delete_post_webhook($post_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("wp_id" => $post_ID, "type" => "post");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}


add_action("delete_category", "delete_category_webhook");
add_action("delete_post", "delete_post_webhook");
add_action("delete_tag", "delete_tag_webhook");
add_action("publish_post", "publish_post_webhook");
add_action("save_post", "publish_post_webhook");