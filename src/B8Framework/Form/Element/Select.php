<?php

namespace b8\Form\Element;

use b8\View;
use b8\Form\Input;

class Select extends Input
{
    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * @param View $view
     */
    protected function onPreRender(View &$view)
    {
        parent::onPreRender($view);

        $view->options = $this->_options;
    }
}
