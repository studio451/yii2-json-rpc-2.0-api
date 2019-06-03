<?

namespace studio451\yii2jsonrpc2api;

use Yii;
use yii\base\InlineAction;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

class Controller extends \yii\web\Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        //CORS
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        return $behaviors;
    }

    public $enableCsrfValidation = false;

    /** @var \stdClass Contains parsed JSON-RPC 2.0 request object */
    protected $requestObject;

    public function actionIndex() {
        
    }

    /**
     * Validates, runs Action and returns result in JSON-RPC 2.0 format
     * @param string $id the ID of the action to be executed.
     * @param array $params the parameters (name-value pairs) to be passed to the action.
     * @throws \Exception
     * @throws \yii\web\HttpException
     * @return mixed the result of the action.
     * @see createAction()
     */
    public function runAction($id, $params = []) {
        $this->initRequest($id);

        try {
            $requestObject = Json::decode(file_get_contents('php://input'), false);
        } catch (InvalidParamException $e) {
            $requestObject = null;
        }
        $isBatch = is_array($requestObject);
        $requests = $isBatch ? $requestObject : [$requestObject];
        $resultData = null;
        if (empty($requests)) {
            $isBatch = false;
            $resultData = [$this->formatResponse(null, new Exception(Yii::t('yii', 'Invalid Request'), Exception::INVALID_REQUEST))];
        } else {
            foreach ($requests as $request) {
                if ($response = $this->getActionResponse($request))
                    $resultData[] = $response;
            }
        }

        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $isBatch || null === $resultData ? $resultData : current($resultData);
        return $response;
    }

    /**
     * Runs and returns method response
     * @param $requestObject
     * @throws \Exception
     * @throws \yii\web\HttpException
     * @return Response|array|null
     */
    private function getActionResponse($requestObject) {
        $this->requestObject = $result = $error = null;
        try {
            $this->parseRequestObject($requestObject);
            ob_start();
            $dirtyResult = parent::runAction($this->requestObject->method);
            ob_clean();
            $result = $this->validateResult($dirtyResult);
        } catch (HttpException $e) {
            throw $e;
        } catch (Exception $e) {
            if ($e->getCode() === Exception::INVALID_PARAMS) {
                $error = new Exception($e->getMessage(), Exception::INTERNAL_ERROR, $e->getData());
            } else {
                $error = $e;
            }
        } catch (\Exception $e) {
            $error = new Exception($e . message, Exception::INTERNAL_ERROR);
        }

        if (!isset($this->requestObject->id) && (empty($error) || !in_array($error->getCode(), [Exception::PARSE_ERROR, Exception::INVALID_REQUEST])))
            return null;

        return $this->formatResponse($result, $error, isset($this->requestObject->id) ? $this->requestObject->id : null, isset($this->requestObject->method) ? $this->requestObject->method : null);
    }

    /**
     * Creates an action based on the given action ID.
     * The method first checks if the action ID has been declared in [[actions()]]. If so,
     * it will use the configuration declared there to create the action object.
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * @param string $id the action ID.
     * @throws Exception
     * @return \yii\base\Action the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createAction($id) {
        $action = parent::createAction($id);
        if (empty($action))
            throw new Exception(Yii::t('yii', 'Method not found') . ' ' . $id, Exception::METHOD_NOT_FOUND);

        $this->prepareActionParams($action);

        return $action;
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[Action]] when it begins to run with the given parameters.
     * @param \yii\base\Action $action the action to be bound with parameters.
     * @param array $params the parameters to be bound to the action.
     * @throws Exception if params are invalid
     * @return array the parameters that the action can run with.
     */
    public function bindActionParams($action, $params) {
        try {

            //code from parent
            if ($action instanceof InlineAction) {
                $method = new \ReflectionMethod($this, $action->actionMethod);
            } else {
                $method = new \ReflectionMethod($action, 'run');
            }

            $params = $this->requestObject->params;

            $args = [];
            $missing = [];
            $actionParams = [];

            foreach ($method->getParameters() as $param) {
                $name = $param->getName();
                if (property_exists($params, $name)) {
                    if ($param->isArray()) {
                        $args[] = $actionParams[$name] = is_array($params->$name) ? $params->$name : [$params->$name];
                    } elseif (!is_array($params->$name)) {
                        $args[] = $actionParams[$name] = $params->$name;
                    } else {
                        throw new Exception(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
                            'param' => $name,
                        ]), Exception::INVALID_REQUEST);
                    }
                    unset($params->$name);
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $actionParams[$name] = $param->getDefaultValue();
                } else {
                    $missing[] = $name;
                }
            }

            if (!empty($missing)) {
                throw new Exception(Yii::t('yii', 'Missing required parameters: {params}', [
                    'params' => implode(', ', $missing),
                ]), Exception::INVALID_REQUEST);
            }

            $this->actionParams = $actionParams;

            return $args;
        } catch (BadRequestHttpException $e) {
            throw new Exception("Invalid Request", Exception::INVALID_REQUEST);
        }
    }

    /**
     * Request has to be sent as POST and with Content-type: application/json
     * @throws \yii\web\HttpException
     */
    private function initRequest($id) {
        list($contentType) = explode(";", Yii::$app->request->getContentType()); //cut charset
        $headers = Yii::$app->request->getHeaders();
        if (!empty($id) ||
                !Yii::$app->request->getIsOptions() && null !== $headers->get('Origin') // CORS Support
                && (!Yii::$app->request->getIsPost() || empty($contentType) || $contentType != "application/json")
        ) {
            throw new Exception('JSON-RPC2: action not found or invalid request', Exception::INVALID_REQUEST);
        }

        //Call beforeActions on modules and controller to run all filters in behaviors() methods
        $action = parent::createAction('');
        // call beforeAction on modules
        foreach ($this->getModules() as $module) {
            if (!$module->beforeAction($action)) {
                break;
            }
        }
        // call beforeAction on controller
        $this->beforeAction($action);
    }

    /**
     * Try to decode input json data for required fields for JSON-RPC 2.0
     * @param $requestObject string
     * @throws Exception
     */
    private function parseRequestObject($requestObject) {
        if (null === $requestObject)
            throw new Exception(Yii::t('yii', 'Parse error'), Exception::PARSE_ERROR);

        if (!is_object($requestObject) || !isset($requestObject->jsonrpc) || $requestObject->jsonrpc !== '2.0' || empty($requestObject->method) || "string" != gettype($requestObject->method)
        )
            throw new Exception(Yii::t('yii', 'Invalid Request'), Exception::INVALID_REQUEST);

        $this->requestObject = $requestObject;
        if (!isset($this->requestObject->params))
            $this->requestObject->params = [];
        
    }

    /**
     * Make associative array where keys are parameters names and values are parameters values
     * @param \yii\base\Action $action
     */
    private function prepareActionParams($action) {
        if (is_object($this->requestObject->params))
            return;

        $method = $this->getMethodFromAction($action);
        $methodParams = new \stdClass();

        $i = 0;
        foreach ($method->getParameters() as $param) {
            if (!isset($this->requestObject->params[$i]))
                continue;
            $methodParams->{$param->getName()} = $this->requestObject->params[$i];
            $i++;
        }
        $this->requestObject->params = $methodParams;
    }

    /**
     * Returns reflected method from action
     * @param $action
     * @return \ReflectionMethod
     */
    private function getMethodFromAction($action) {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
            return $method;
        } else {
            $method = new \ReflectionMethod($action, 'run');
            return $method;
        }
    }

    /**
     * @param $result
     * @return mixed
     */
    private function validateResult($result) {


        return $result;
    }

    /**
     * Formats and returns
     * @param null $result
     * @param \JsonRpc2\Exception|null $error
     * @param null $id
     * @return array
     */
    public function formatResponse($result = null, Exception $error = null, $id = null, $method = null) {
        $resultArray = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
            'timestamp' => time(),
        ];

        if (!empty($error)) {
            \Yii::error($error, 'jsonrpc');
            $resultArray['error'] = $error->toArray();
        } else {            
            $resultArray['result'] = $result;
        }

        return $resultArray;
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'app_jsonrpc2API\jsonrpc2\actions\ErrorAction',
            ],
        ];
    }

}
