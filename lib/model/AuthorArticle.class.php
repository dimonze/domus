<?php
class AuthorArticle extends Post
{
  public function getAuthorName()
  {
    return Doctrine::getTable('PostAuthor')->find($this->author_id);
  }  
}