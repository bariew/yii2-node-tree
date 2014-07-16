<?php

namespace bariew\nodeTree;
use yii\base\Behavior;


class PTARBehavior extends Behavior
{
    protected $_name;

    public function find($criteria, $all=false, $array=false)
    {
        $get = ($array)
            ? ($all ? 'queryAll'    : 'queryRow')
            : ($all ? 'findAll'     : 'find');
        return ($array)
            ? $this->owner->getCommandBuilder()
                ->createFindCommand($this->owner->tableSchema, $criteria)
                ->$get()
            : $this->owner->$get($criteria);
    }

    protected function get($attributeName)
    {
        return $this->owner->{$this->attr($attributeName)};
    }
	
	protected function getAll($attributeNames)
	{
		$result = array();
		foreach($attributeNames as $attributeName){
			$result[$attributeName] = $this->get($attributeName);
		}
		return $result;
	}
	
    protected function set($attributeName, $value)
    {
        $this->owner->{$this->attr($attributeName)} = $value;
        return $this;
    }

    public function attr($attributeName)
    {
        return isset($this->$attributeName) ? $this->$attributeName : $attributeName;
    }

    public function attributesChanged($attributes)
    {
        foreach($attributes as $attribute){
            if($this->get($attribute) !== $this->owner->getOldAttribute($attribute)){
                return true;
            }
        }
        return false;
    }
    /**
     * gets name of the behavior, listing its owner behaviors
     * and comparing ro itself
     * @return string name of this behavior
     */
    public function getName()
    {
        return array_search($this, $this->owner->getBehaviors());
    }
    /**
     * refreshes its attributes from owner behavior method data
     * @return \ActiveRecordBehavior this
     */
    public function refresh()
    {
        $behaviors = $this->owner->behaviors();
        $settings = $behaviors[$this->getName()];
        foreach($settings as $attribute=>$value){
            if(in_array($attribute, array('class'))){
                continue;
            }
            $this->$attribute = $value;
        }
        return $this;
    }
    
    public static function fromEvent($event)
    {
        $className = get_called_class();
        $model = new $className();
        $model->attach($event->sender);
        return $model;
    }
    
    public function ownerBehavior($owner)
    {
        return $owner->getBehavior($this->getName());
    }
}