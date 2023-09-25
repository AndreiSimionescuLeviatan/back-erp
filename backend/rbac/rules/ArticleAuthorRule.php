<?php

namespace backend\rbac\rules;

use yii\rbac\Item;
use yii\rbac\Rule;
use app\models\Article;

/**
 * Checks if authorID matches user passed via params
 */
class ArticleAuthorRule extends Rule
{
    public $name = 'isArticleAuthor';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['aritcle']) ? $params['article']->added_by == $user : false;
    }
}