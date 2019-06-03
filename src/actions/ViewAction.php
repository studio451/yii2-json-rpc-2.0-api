<?
namespace studio451\yii2jsonrpc2api\actions;

class ViewAction extends Action
{
    public function run($id)
    {
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        return $model;
    }
}
