<?php
namespace GGS\Controllers;
use GGS\Components\Application;
use GGS\Components\Controller;
use GGS\Models;

class Post extends Controller
{
    public function actionList()
    {
        $posts      = Models\Post::getAll();
        $pageTitle  = 'Show all posts';
        Application::$view->render('post/list', compact('posts', 'pageTitle'));
    }

    public function actionShow()
    {
        $post           = static::getModelByRequest('Post');
        $pageTitle      = $post->title;
        $comments       = Models\Comment::getByCriteria(array('postId' => $post->id));
        $commentForm    = new Models\Comment();
        Application::$view->render('post/show', compact('post', 'comments', 'commentForm', 'pageTitle'));
    }
}