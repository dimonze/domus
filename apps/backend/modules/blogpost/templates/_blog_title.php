<?php if($blog_id) echo Doctrine::getTable('Blog')->find($blog_id)->getTitle(); ?>
