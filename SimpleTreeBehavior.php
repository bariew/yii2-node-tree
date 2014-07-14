<?php
/**
 * SimpleTreeBehavior class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\nodeTree;

use yii\base\Behavior;

/**
 * Generates jstree tree view from parent children nodes.
 * 
 * 1. Attach behavior in yii2 common way, define $actionPath and $id attribute name.
 * 2.1 Call widget from model like self::findOne(1)->menuWidget()
 * 2.2 Call checkbox widget like self::findOne(1)->checkboxWidget('node', $selectedItemIds)
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class SimpleTreeBehavior extends Behavior
{
    /**
     * @var string url to model update action. Ends with '/update'
     */
    public $actionPath = '/path/to/update-action';
    
    /**
     * @var string primaryKey attribute name
     */
    public $id         = 'id';
    
    /**
     * @var array selected node ids for checkbox widget.
     */
    protected $selectedNodes = [];
    
    /**
     * @var integer incremential key for jstree items unique ids. 
     */
    public static $uniqueKey = 0;
    
    /**
     * @var array jstree icon item types. 
     * @link http://www.jstree.com/api/#/?f=$.jstree.defaults.types
     */
    public $types = [
        "folder" => ["icon" => "glyphicon glyphicon-folder"],
        "user" => ["icon" => "glyphicon glyphicon-user"],
        "flag" => ["icon" => "glyphicon glyphicon-flag"],
    ];

    /**
     * Generates attributes for jstree item from owner model.
     * @param mixed $model model.
     * @param integer $pid view item parent id.
     * @return array attributes
     */
    public function nodeAttributes($model = false, $pid = '', $uniqueKey = false)
    {
        $uniqueKey = $uniqueKey ? $uniqueKey : self::$uniqueKey++;
        $model = ($model) ? $model : $this->owner;
        $id    = $model[$this->id];
        $nodeId = $uniqueKey . '-id-' . $id;
        return array(
            'id'     => $nodeId,
            'model'   => $model,
            'children'=>$model->children,
            'text'   => $model['title'],
            'type'   => 'folder',
            'selected'  => in_array($model->{$this->id}, $this->selectedNodes),
            'a_attr' => array(
                'class' => 'jstree-clicked',
                'data-id' => $nodeId,
                'href'    => $this->actionPath . "?{$this->id}={$id}&pid={$pid}"
            )
        );
    }
    
    /**
     * Generates jstree menu from owner children.
     * @param array $data data for widget
     * @param string $callback $this method name for data processing
     * - define it for variable options and bind functions attaching.
     * @return Widget menu widget
     */
    public function menuWidget($data = [], $callback = false)
    {
        $data = array_merge([
            'items'     => [$this->owner],
            'behavior'  => $this,
            'view'      => 'node'
        ], $data);
        if ($callback) {
            $data = $this->$callback($data);
        }
        $widget   = new ARTreeMenuWidget($data);
        return $widget->run();
    }
    
    /**
     * Callback example for $this->menuWidget() method.
     * @param array $data data to process.
     * @return array processed data.
     */
    public function defaultCallback($data)
    {
        $data['options'] = [
            'types'     => $this->types,
            'plugins'   => ['checkbox', 'search', 'types'],
            'checkbox' => ["keep_selected_style" => false]
        ];
        return $data;
    }
}
