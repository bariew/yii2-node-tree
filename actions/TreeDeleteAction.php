<?php
namespace bariew\nodeTree\actions;

class TreeDeleteAction extends TreeAction
{
    public function run($id, $pid=false)
    {
        $model = $this->controller->getModel($id);
        if(!$model->isNewRecord && $model->delete()){
            \Yii::$app->session->setFlash('success', 'Removed.');
        }
        if (!\Yii::$app->request->isAjax) {
            $this->controller->redirect($pid
                    ? array('read', 'id'=>$pid)
                    : array('index')
            );
        }
    }
}
