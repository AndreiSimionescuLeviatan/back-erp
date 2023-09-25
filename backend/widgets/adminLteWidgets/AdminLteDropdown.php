<?php

namespace backend\widgets\adminLteWidgets;

use yii\base\InvalidConfigException;
use yii\bootstrap4\Dropdown;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

class AdminLteDropdown extends Dropdown
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
//        parent::init();
        Html::addCssClass($this->options, ['widget' => 'nav nav-treeview']);
    }

    /**
     * Renders menu items.
     * @param array $items the menu items to be rendered
     * @param array $options the container HTML attributes
     * @return string the rendering result.
     * @throws InvalidConfigException if the label option is not specified in one of the items.
     * @throws \Exception
     */
    protected function renderItems($items, $options = [])
    {
        $lines = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $lines[] = ($item === '-')
                    ? Html::tag('div', '', ['class' => 'dropdown-divider'])
                    : $item;
                continue;
            }
            if (isset($item['visible']) && !$item['visible']) {
                continue;
            }
            if (!array_key_exists('label', $item)) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $itemOptions = ArrayHelper::getValue($item, 'options', []);
            $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
            $active = ArrayHelper::getValue($item, 'active', false);
            $disabled = ArrayHelper::getValue($item, 'disabled', false);

            Html::addCssClass($linkOptions, ['widget' => 'nav-link']);
            if ($disabled) {
                ArrayHelper::setValue($linkOptions, 'tabindex', '-1');
                ArrayHelper::setValue($linkOptions, 'aria-disabled', 'true');
                Html::addCssClass($linkOptions, ['disable' => 'disabled']);
            } elseif ($active) {
                Html::addCssClass($linkOptions, ['activate' => 'active']);
            }

            $url = array_key_exists('url', $item) ? $item['url'] : null;
            if (empty($item['items'])) {
                if ($url === null) {
                    $content = Html::tag('h6', $label, ['class' => 'dropdown-header']);
                } else {
                    $content = Html::beginTag('li', ['class' => 'nav-item']);
                    $content .= Html::a($label, $url, $linkOptions);
                    $content .= Html::endTag('li');
                }
                $lines[] = $content;
            } else {
                $submenuOptions = $this->submenuOptions;
                if (isset($item['submenuOptions'])) {
                    $submenuOptions = array_merge($submenuOptions, $item['submenuOptions']);
                }
//                Html::addCssClass($submenuOptions, ['widget' => 'dropdown-submenu dropdown-menu']);
                Html::addCssClass($submenuOptions, ['widget' => 'nav nav-treeview']);
//                Html::addCssClass($linkOptions, ['toggle' => 'dropdown-toggle']);

//                $lines[] = Html::beginTag('div', array_merge_recursive(['class' => ['dropdown'], 'aria-expanded' => 'false'], $itemOptions));
                $lines[] = Html::beginTag('li', array_merge_recursive(['class' => ['nav-item']], $itemOptions));
                $lines[] = Html::a($label, $url, array_merge([
//                    'data-toggle' => 'dropdown',
//                    'aria-haspopup' => 'true',
//                    'aria-expanded' => 'false',
//                    'role' => 'button'
                ], $linkOptions));
                $lines[] = static::widget([
                    'items' => $item['items'],
                    'options' => $submenuOptions,
                    'submenuOptions' => $submenuOptions,
                    'encodeLabels' => $this->encodeLabels
                ]);
                $lines[] = Html::endTag('li');
            }
        }

        return Html::tag('ul', implode("\n", $lines), $options);
    }
}