<?php
$post = $post_comment->Post;
switch($post->post_type) {
  case 'news':
    echo link_to($post->title, '/news/' . $post->id);
    break;
  case 'author_article':
    echo link_to($post->title, '/authors/article/'.$post->author_id.'/'.$post->id);
    break;
  case 'expert_article':
    echo link_to($post->title, '/experts/article/'.$post->author_id.'/'.$post->id);
    break;
  case 'events':
  case 'article':
  case 'analytics':
    echo link_to($post->title, '/posts/'.$post->post_type.'/'.$post->id);
    break;
  case 'qa':
    echo link_to($post->title, '/qa/'.$post->id);
    break;
}