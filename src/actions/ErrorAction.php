<?

namespace studio451\yii2jsonrpc2api\actions;

use Yii;
use studio451\yii2jsonrpc2api\Exception;

class ErrorAction extends \yii\base\Action {

    public function run() {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception != null) {
            throw new \app_jsonrpc2API\jsonrpc2\Exception($exception . message, Exception::INTERNAL_ERROR);
        }
    }

}
