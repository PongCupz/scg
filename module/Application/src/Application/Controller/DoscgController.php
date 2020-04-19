<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Models\Doscg;
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
class DoscgController extends AbstractActionController
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
            $data =  file_get_contents('php://input');
            return new JsonModel(array('data' => $data));
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }

    public function isStringAction()
    {
        try
        {
            $json =  file_get_contents('php://input');
            $data = json_decode($json);
            $return = [];
            foreach($data as $item){
                if(is_string($item))
                    array_push($return,$item);
            }
            return new JsonModel($return);
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }

    public function calculateAction()
    {
        try
        {
            $json =  file_get_contents('php://input');
            $data = json_decode($json);
            $A = $data->A;
            $B = $data->AB - $data->A;
            $C = $data->AC - $data->A;
            return new JsonModel(array('A' => $A,'B' => $B,'C' => $C));
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }

    public function mapAction()
    {
        try
        {
            $json =  file_get_contents('php://input');
            $data = json_decode($json);
            $models = new Doscg($this->adapter);
            return new JsonModel($models->getLocation($data->src,$data->dsc));
        }
        catch( Exception $e )
        {
            print_r($e);
        }
    }
}