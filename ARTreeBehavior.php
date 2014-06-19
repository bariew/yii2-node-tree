<?php

namespace bariew\nodeTree;
use yii\db\ActiveRecord;

class ARTreeBehavior extends PTARBehavior
{
    /* ATTRIBUTES AND LISTS */

    public $id          = 'id';
    public $parent_id   = 'pid';
    public $title       = 'title';
    public $rank        = 'rank';
    public $url         = 'url';
    public $name        = 'name';
    public $content     = 'content';
    public $actionPath  = '/page/item/update';
    
    public static $uniqueKey = 0;

    public function events() 
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT    => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE    => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE    => 'afterDelete',
        ];
    }
    
    public function nodeAttributes($model = false, $pid = '')
    {
        $uniqueKey = self::$uniqueKey++;
        $children = (array) @$model['children'];
        $model = ($model) ? $model['model'] : $this->owner;
        $id    = $model[$this->id];
        $nodeId = $uniqueKey . '-id-' . $id;
        return array(
            'id'    => $nodeId,
            'model'  => $model,
            'children'=>$children,
            'text'  => $model['title'],
            'type'  => 'folder',
            //'li_attr'=>[],
            'a_attr'=> array(
                'data-id'   => $nodeId,
                'href'      => $this->actionPath . "?{$this->id}={$id}&pid={$pid}"
            )
        );
    }
    
    public function getJsTree()
    {
        $tree = $this->childrenTree();
        foreach ($tree as $key=>$data){
            $tree[$key] = $this->setJsData($data);
        }
        return reset($tree);
    }
    
    private function setJsData($data)
    {
        $result = $this->nodeAttributes($data['model']);
        foreach($data['children'] as $child){
            $result['children'][] = $this->setJsData($child);
        }
        return $result;
    }
    
    public function getParent()
    {
        return $this->owner->findOne(array(
            $this->id => $this->get('parent_id')
        ));
    }
    
    public function getParentsCriteria($addCriteria = array())
    {
        $query = new CDbCriteria();
        $urls = array();
        while($this->url){
            $urls[] = $this->url = preg_replace('/(\d+)?\/$/','',$this->url);
        }
        $query->addInCondition('url', $urls ? $urls : array(0));
        $query->mergeWith($addCriteria);
        return $query;
    }
    
    public function getChildren($addCriteria=array())
    {
        if($this->owner->isNewRecord){
            return array();
        }
        $query = new CDbCriteria();
        $query->order = $this->rank;
        $query->addColumnCondition(array($this->parent_id  => $this->owner->primaryKey))
            ->mergeWith($addCriteria);
        return $this->find($query, true);
    }
	
    public function getDescendants($query)
    {
        return $query->andWhere($this->getDescendantCondition())->all();
    }

    public function getDescendantCondition()
    {
        if(!$url = $this->get('url')){
            $url = '/';
        }
        return ['like', 'url', $url];
    }

    
    public function menuWidget($view='node', $attributes=array(), $return=false)
    {
        $items = $this->childrenTree($attributes);
        $behavior = $this;
        $widget =  new ARTreeMenuWidget(compact('view', 'items', 'behavior'), $return);
        return $widget->run();
    }

	
	/* TREE SERVICE */

    public function childrenTree($attributes=array())
    {
        $query = $this->owner->find();
        if($attributes){
            $query->andFilterWhere($attributes);
        }
        $items = $this->getDescendants($query);
        return $this->toTree($items);
    }
		
    public function toTree($items)
    {
        $id     = $this->id;
        $result = array();
        $list   = array();
        foreach($items as $item){
            $thisref = &$result[$item[$id]];
            $children = isset($result[$item[$id]]['children'])
                ? $result[$item[$id]]['children']
		: array();
            $thisref = array('model'=>$item, 'children'=>$children);
            if($item[$id] == $this->get('id')){
                $list[$item[$id]] = &$thisref;
            }else{
                $result[$item[$this->parent_id]]['children'][$item[$id]] = &$thisref;
            }
        }
        
        return $this->rangeTree($list);
    }
	
    private function rangeTree($items)
    {
        $result = array();
        foreach($items as $item){
            $item['children'] = $this->rangeTree($item['children']);
            $count = $item['model'][$this->rank];
            while(isset($result[$count])){
                $count++;
            }
            $result[$count] = $item;
        }
        ksort($result);
        return $result;
    }

    public function cloneTo($pid)
    {
        $className = get_class($this->owner);
        $new = new $className;
        $new->attributes = $this->owner->attributes;
        $this->ownerBehavior($new)->move($pid);
        foreach($this->getChildren() as $child){
            $this->ownerBehavior($child)->cloneTo($new->id);
        }
        return true;
    }


	/* SYSTEM SERVICE */
		
    public function createUrl()
    {
        $model  = $this->owner;
        $oldUrl = $this->get('url');
        $newUrl = (($parent = $this->getParent()) ? $this->ownerBehavior($parent)->get('url') : '/')
            . $this->get('name') . "/";
        if($newUrl == $oldUrl){
            return true;
        }
        if($oldUrl && $this->get('parent_id')){
            \Yii::$app->db->createCommand("
                UPDATE {$model->tableName()}
                  SET {$this->url} = REPLACE({$this->url}, '{$oldUrl}', '{$newUrl}')
                WHERE {$this->url} LIKE '{$oldUrl}%'
            ")->execute();
        }
        $attributes = $this->get('parent_id') 
            ? [$this->url  => $newUrl]
            : [$this->name => ''];
        return $model->updateAll($attributes, ['id'=>$model->primaryKey]);
    }
    
    public function move($pid, $position=false)
    {
        if($position === false){
            $position = ($lastChild = $this->owner->findOne(array(
                $this->parent_id => $pid
            ), array(
                'order'     => 'rank DESC',
                'condition' => 'id != '.$this->get('id') * 1
            ))) ? $lastChild->rank+1 : 0;
        }
        return $this->treeResort(-1)    // resort old parent children
            ->set('rank', $position)
            ->set('parent_id', $pid)
            ->treeResort()        // resort new parent children
            ->owner->save(false);
    }

    private function treeResort($increment = 1)
    {
        if(!$pid = $this->get('parent_id')){
            return $this;
        };
        $condition = "{$this->parent_id} = {$pid}
            AND {$this->rank} >= {$this->get('rank')}"
            . (($id = $this->get('id')) ? " AND id != {$id}" : "");
        \Yii::$app->db->createCommand("
            UPDATE {$this->owner->tableName()} SET {$this->rank} = ({$this->rank}+{$increment})
            WHERE {$condition}
        ")->execute();
        return $this;
    }
	
	
	/* SYSTEM */

    public static function afterSave($event)
    {
        $behavior = self::fromEvent($event);
        if($behavior->attributesChanged(array('parent_id'))){
            $behavior->createUrl();
        }
    }

    public static function afterDelete($event)
    {
        $behavior = self::fromEvent($event);
        $behavior->treeResort(-1);
        $behavior->owner->deleteAll($behavior->getDescendantCondition());
    }
}