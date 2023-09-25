<?php

namespace api\modules\v2;

/**
 * v2 module definition class
 */
class Module extends \api\modules\v1\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\v2\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
