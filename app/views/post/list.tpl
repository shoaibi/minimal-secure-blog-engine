<?php
if (empty($posts))
{
    echo "No posts found";
}
foreach ($posts as $post)
{
    var_dump(strval($post));
}