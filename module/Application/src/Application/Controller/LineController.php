<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Models\Line;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
/*
$this->params()->fromPost('paramname');   // From POST
$this->params()->fromQuery('paramname');  // From GET
$this->params()->fromRoute('paramname');  // From RouteMatch
$this->params()->fromHeader('paramname'); // From header
$this->params()->fromFiles('paramname');
*/
class LineController extends AbstractActionController
{
################################################################################ 
    public function __construct()
    {
        $this->cacheTime = 36000;
        $this->now = date("Y-m-d H:i:s");
        $this->config = include __DIR__ . '../../../../config/module.config.php';
        $this->adapter = new Adapter($this->config['Db']);
    }
################################################################################
    public function indexAction() 
    {
        try
        {
            /*Get Data From POST Http Request*/
            $datas = file_get_contents('php://input');
            /*Decode Json From LINE Data Body*/
            $deCode = json_decode($datas,true);

            $replyToken = $deCode['events'][0]['replyToken'];

            $message = $deCode['events'][0]['message']['text'];

            $messages = [];
            $messages['replyToken'] = $replyToken;
            $models = new Line($this->adapter);

            if($message=='สวัสดี'){
                $messages['messages'][0] = $this->getFormatTextMessage("ดีจ้า");
                $encodeJson = json_encode($messages);
                
                $LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
                $LINEDatas['token'] = $models->getToken();

                $results = $this->sentMessage($encodeJson,$LINEDatas);
                if($results['result'] != 'S'){
                    $models->addLine($message);
                }
            }else{
                $models->addLine($message);
            }
            /*Return HTTP Request 200*/
            http_response_code(200);
            return new JsonModel(array());
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }

    public function lineNotiAction()
    {        
        try
        {
            $models = new Line($this->adapter);
            $data = $models->getLine();
            if(count($data)>0){
                return new JsonModel($data);
            }else{
                return new JsonModel(array());
            }
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }
    
    function getFormatTextMessage($text)
    {
        $datas = [];
        $datas['type'] = 'text';
        $datas['text'] = $text;

        return $datas;
    }

    function sentMessage($encodeJson,$datas)
	{
		$datasReturn = [];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $datas['url'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encodeJson,
		  CURLOPT_HTTPHEADER => array(
		    "authorization: Bearer ".$datas['token'],
		    "cache-control: no-cache",
		    "content-type: application/json; charset=UTF-8",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		    $datasReturn['result'] = 'E';
		    $datasReturn['message'] = $err;
		} else {
		    if($response == "{}"){
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		    }else{
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		    }
		}

		return $datasReturn;
	}
    
}