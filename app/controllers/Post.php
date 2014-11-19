<?php
namespace GGS\Controllers;
use GGS\Components\WebApplication;
use GGS\Components\Controller;
use GGS\Helpers\CsrfHelper;
use GGS\Models;

class Post extends Controller
{
    /**
     * Dummy action, redirects to actionList
     */
    public function actionIndex()
    {
        $redirectUrl    = WebApplication::$request->createUrl('post', 'list');
        WebApplication::$request->redirect($redirectUrl);
    }

    /**
     * List all posts in a paged manner
     */
    public function actionList()
    {
        $limit          = WebApplication::$request->getQueryStringParameter('limit', static::MAX_RECORDS_PER_PAGE);
        $page           = WebApplication::$request->getQueryStringParameter('page', 1);
        $offset         = ($page-1) * $limit;
        $posts          = Models\Post::getAll($limit, $offset);
        $pageTitle      = 'Show all posts';
        if (WebApplication::$request->isAjaxRequest())
        {
            // if its ajax, we want to render the view without layout, header and etc
            WebApplication::$view->renderPartial('post/_list', compact('posts', 'page'));

        }
        else
        {
            WebApplication::$view->render('post/list', compact('posts', 'page', 'pageTitle'));
        }
    }

    /**
     * Show a single post
     */
    public function actionShow()
    {
        // this would always start at page =1 for comments
        $page                   = 1;
        // get the model or exit with an exception
        $post                   = static::getModelByRequest('Post');
        // fancy stuff, huh?
        $pageTitle              = $post->title;
        // get the first page of comments
        $comments               = Models\Comment::getByCriteria(array('postId' => $post->getPkValue()), static::MAX_RECORDS_PER_PAGE);
        $commentForm            = new Models\Comment();
        // preset the postId
        $commentForm->postId    = $post->getPkValue();
        $formName               = \GGS\Helpers\FormHelper::getName(get_class($commentForm));
        // CSRF, yay!
        $token                  = CsrfHelper::getNewToken(__FUNCTION__);
        // handle ajax posts to comment form in a separate action, don't bloat this action
        $this->handleCommentAddition($commentForm);
        WebApplication::$view->render('post/show', compact('post', 'comments', 'page', 'commentForm', 'formName', 'token', 'pageTitle'));
    }

    /**
     * Render comments provided a postId
     */
    public function actionComments()
    {
        $postId         = WebApplication::$request->getQueryStringParameter('postId');
        if (!isset($postId) || !is_numeric($postId))
        {
            static::exitWithException(new \Exception("Invalid post id supplied to load comments of.", 400));
        }
        $limit          = WebApplication::$request->getQueryStringParameter('limit', static::MAX_RECORDS_PER_PAGE);
        // $page =1 if not set? hmmm, ok...
        $page           = WebApplication::$request->getQueryStringParameter('page', 1);
        $offset         = ($page-1) * $limit;
        $comments       = Models\Comment::getByCriteria(array('postId' => $postId), $limit, $offset);
        if (WebApplication::$request->isAjaxRequest())
        {
            // ajax request? render without layout, header, etc.
            WebApplication::$view->renderPartial('comment/_list', compact('comments', 'page'));

        }
        else
        {
            // not ajax request? WHY?
            //WebApplication::$view->renderPartial('comment/list', compact('comments', 'page'));
            static::exitWithException(new \Exception('Invalid Request.', 400));
        }
    }

    /**
     * Add a new post
     */
    public function actionCreate()
    {
        $post           = new Models\Post();
        $pageTitle      = 'Add Post';
        $token          = CsrfHelper::getNewToken(__FUNCTION__);
        $this->_renderPostEdit($post, $pageTitle, $token);
    }

    /**
     * Edit an existing post
     */
    public function actionEdit()
    {
        $model          = static::getModelByRequest('Post');
        // fancy, right?
        $pageTitle      = 'Edit Post : ' . $model->title;
        $token          = CsrfHelper::getNewToken(__FUNCTION__);
        $this->_renderPostEdit($model, $pageTitle, $token);
    }

    /**
     * Handles display a form to add/edit post and its submissions
     * @param Models\Post $model
     * @param $pageTitle
     * @param $token
     */
    public function _renderPostEdit(\GGS\Models\Post $model, $pageTitle, $token)
    {
        $formName       = \GGS\Helpers\FormHelper::getName(get_class($model));
        if (WebApplication::$request->isPostRequest() && $attributes = WebApplication::$request->getPostParameter($formName))
        {
            // set the data from post
            $model->setAttributes($attributes);
            // validate and save it.
            if ($pk = $model->save())
            {
                // saved? nice, redirect to it.
                $redirectUrl = WebApplication::$request->createUrl('post', 'show', array('id' => $pk));
                WebApplication::$request->redirect($redirectUrl);
            }
        }
        WebApplication::$view->render('post/create', compact('model', 'formName', 'token', 'pageTitle'));
    }

    /**
     * Handle ajax submissions of comments
     * @param Models\Comment $commentForm
     */
    protected function handleCommentAddition(\GGS\Models\Comment $commentForm)
    {
        $formName       = \GGS\Helpers\FormHelper::getName(get_class($commentForm));
        // ensure its ajax, ensure its ajax and ensure form is set
        if (WebApplication::$request->isAjaxRequest() && WebApplication::$request->isPostRequest() &&
                $attributes = WebApplication::$request->getPostParameter($formName))
        {
            $response       = array();
            // get the comment populated
            $commentForm->setAttributes($attributes);
            if ($commentForm->validate())
            {
                // so its valid? lets populate some defaults for response
                $response['status']     = 'success';
                $response['message']    = 'Comment successfully added.';
                // already validated so no need to revalidate in save(), also do not throw exception, we need to handle
                // errors using ajax's error()
                if ($pk = $commentForm->save(false, false))
                {
                    // saved? add the id to response.
                    $response['id'] = $pk;

                }
                else
                {
                    // unable to save record even though its valid?
                    $response['status']     = 'error';
                    $response['message']    = 'Failed to save Comment record.';
                }
            }
            else
            {
                // invalid form? compiler errors in respo,se.
                $response['errors']     = $commentForm->getQualifiedErrorMessageWithInputIds();
                $response['status']     = 'error';
                $response['message']    = 'Please check form for invalid data.';
            }
            // json encode the response and bail out
            echo json_encode($response);
            exit;
        }
    }
}