<?php

namespace bariew\nodeTree;

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
    public $actionPath  = '/page/admin/update';

    public static $receivedItems = array();

    public function nodeAttributes($model=false)
    {
        $model = ($model) ? $model : $this->owner;
        $id = $model['id'];
        return array(
            'id'    => "node-{$id}",
            'text'  => $model['title'],
            'type'  => @$model['branch'] ? 'folder' : 'folder-ext',
            //'li_attr'=>[],
            'a_attr'=> array(
                'data-id'   => "node-{$id}",
                'href'      => $this->actionPath . "?{$this->id}={$id}"
            )
        );
    }

    public function getBranch()
    {
        $criteria = new CDbCriteria(array(
            'condition' => "pid = {$this->get('id')}",
            'order'     => 'rank',
            'select'    => 'id, pid, title, rank'
        ));
        $result = $this->nodeAttributes();
        if(!$children = $this->find($criteria, true, true)){
            return $result;
        }
        $ids = CHtml::listData($children, 'id', 'id');
        $criteria = new CDbCriteria(array('select'=>'pid'));
        $criteria->addInCondition('pid', array_values($ids));
        $grandChildrenExist = CHtml::listData($this->find($criteria, true, true), 'pid', 'pid');
        foreach($children as $child){
            $result['children'][] = array_merge(
                $this->nodeAttributes($child),
                array('children'=>isset($grandChildrenExist[$child['id']]))
            );
        }
        return $result;
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
        return $this->owner->findByAttributes(array(
            $this->id => $this->get('parent_id')
        ));
    }
    
    public function getParentsCriteria($addCriteria = array())
    {
        $criteria = new CDbCriteria();
        $urls = array();
        while($this->url){
            $urls[] = $this->url = preg_replace('/(\d+)?\/$/','',$this->url);
        }
        $criteria->addInCondition('url', $urls ? $urls : array(0));
        $criteria->mergeWith($addCriteria);
        return $criteria;
    }
    
    public function getChildren($addCriteria=array())
    {
        if($this->owner->isNewRecord){
            return array();
        }
        $criteria = new CDbCriteria();
        $criteria->order = $this->rank;
        $criteria->addColumnCondition(array($this->parent_id  => $this->owner->primaryKey))
            ->mergeWith($addCriteria);
        return $this->find($criteria, true);
    }
	
	public function getDescendants($withParent = false, $addCriteria = array(), $asArray=true)
	{
        $criteria = new CDbCriteria();
        $criteria->addCondition($this->getDescendantCondition($withParent))
            ->mergeWith($addCriteria);
		return $this->find($criteria, true, $asArray);
	}

    public function getDescendantCondition($withParent=false)
    {
        $url = $this->get('url');
        $result = ($withParent) ? "{$this->url} = '{$url}' OR " : "";
        $result .= "{$this->url} LIKE '{$url}%'";
        return $result;
    }

    
	public function menuWidget($view='admin', $attributes=array(), $return=false)
	{
        $items = $this->childrenTree($attributes);
        $behavior = $this;
        return Yii::app()->controller->widget(
            'ext.artree.ARTreeMenuWidget', 
        	compact('view', 'items', 'behavior'),
            $return
        );
	}

	
	/* TREE SERVICE */

    public function childrenTree($attributes=array())
    {
        $criteria = new CDbCriteria();
        if($attributes){
            $criteria->addColumnCondition($attributes);
        }
        $items = $this->getDescendants(true, $criteria, true);
        return $this->toTree($items);
    }
		
    public function toTree($items)
    {
        $id     = $this->id;
        $result = array();
        $list   = array();
        foreach($items as $item){
            self::$receivedItems[$item[$id]] = $item[$id];
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
        $new->tree->move($pid);
        foreach($this->getChildren() as $child){
            $child->tree->cloneTo($new->id);
        }
        return true;
    }


	/* SYSTEM SERVICE */
		
    public function createUrl()
    {
        $model  = $this->owner;
        $oldUrl = $this->get('url');
        $newUrl = (($parent = $this->getParent()) ? $parent->tree->get('url') : '/')
            . $this->get('name') . "/";
        if($newUrl == $oldUrl){
            return true;
        }
        if($oldUrl){
            Yii::app()->db->createCommand("
                UPDATE {$model->tableName()}
                  SET {$this->url} = REPLACE({$this->url}, '{$oldUrl}', '{$newUrl}')
                WHERE {$this->url} LIKE '{$oldUrl}%'
            ")->execute();
        }
        return $model->updateByPk($model->primaryKey, array($this->url => $newUrl));
    }
    
    public function move($pid, $position=false)
    {
        if($position === false){
            $position = ($lastChild = $this->owner->findByAttributes(array(
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
        Yii::app()->db->createCommand("
            UPDATE {$this->owner->tableName()} SET {$this->rank} = ({$this->rank}+{$increment})
            WHERE {$condition}
        ")->execute();
        return $this;
    }
	
	
	/* SYSTEM */

    public function afterSave($event)
    {
        parent::afterSave($event);
        if($this->attributesChanged(array('parent_id'))){
            $this->createUrl();
        }
    }

    public function afterDelete($event)
    {
        parent::afterDelete($event);
        $this->treeResort(-1);
        $this->owner->deleteAll($this->getDescendantCondition());
    }
}