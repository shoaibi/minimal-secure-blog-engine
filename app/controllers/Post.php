<?php
namespace GGS\Controllers;
use GGS\Components\Application;
use GGS\Components\Controller;
use GGS\Helpers\FormUtils;
use GGS\Helpers\StringUtils;
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
        $post                   = static::getModelByRequest('Post');
        $pageTitle              = $post->title;
        $comments               = Models\Comment::getByCriteria(array('postId' => $post->id));
        $commentForm            = new Models\Comment();
        $commentForm->postId    = $post->id;
        $formName               = StringUtils::getNameWithoutNamespaces(get_class($commentForm));
        $this->handleCommentAddition($commentForm);
        Application::$view->render('post/show', compact('post', 'comments', 'commentForm', 'formName', 'pageTitle'));
    }

    public function actionCreate()
    {
        $post           = new Models\Post();
        $pageTitle      = 'Add Post';
        $this->_renderPostEdit($post, $pageTitle);
    }

    public function actionEdit()
    {
        $model          = static::getModelByRequest('Post');
        $pageTitle      = 'Edit Post';
        $this->_renderPostEdit($model, $pageTitle);
    }

    public function _renderPostEdit(\GGS\Models\Post $model, $pageTitle)
    {
        $formName       = StringUtils::getNameWithoutNamespaces(get_class($model));
        if (static::isPostRequest() && $attributes = static::getPostParameter($formName))
        {
            $model->setAttributes($attributes);
            if ($model->validate())
            {
                if ($id = $model->save())
                {
                    $redirectUrl = static::createUrl('post', 'show', array('id' => $id));
                    static::redirect($redirectUrl);
                }
                else
                {
                    static::existWithException('Failed to save Post record.');
                }
            }
        }
        Application::$view->render('post/create', compact('model', 'formName', 'pageTitle'));
    }

    protected function handleCommentAddition(\GGS\Models\Comment $commentForm)
    {
        $formName       = StringUtils::getNameWithoutNamespaces(get_class($commentForm));
        if (static::isAjaxRequest() && static::isPostRequest() && $attributes = static::getPostParameter($formName))
        {
            $response       = array();
            $commentForm->setAttributes($attributes);
            if ($commentForm->validate())
            {
                $response['status']     = 'success';
                $response['message']    = 'Comment successfully added.';
                if ($id = $commentForm->save())
                {
                    $response['id'] = $id;

                }
                else
                {
                    $response['status']     = 'error';
                    $response['message']    = 'Failed to save Comment record.';
                }


            }
            else
            {
                $response['errors']     = $commentForm->getQualifiedErrorMessageWithInputIds();
                $response['status']     = 'error';
                $response['message']    = 'Please check form for invalid data.';
            }
            echo json_encode($response);
            exit;
        }
    }
}