<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
 
class Doscg
{ 
    protected $doscg; 
################################################################################ 
	function __construct($adapter) 
    {
        $this->adapter = $adapter;
        $this->now = date('Y-m-d H:i');
        $this->ip = '';
        if (getenv('HTTP_CLIENT_IP'))
        {
            $this->ip = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR'))
        {
            $this->ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED'))
        {
            $this->ip = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR'))
        {
            $this->ip = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED'))
        {
            $this->ip = getenv('HTTP_FORWARDED');
        }
        else if(getenv('REMOTE_ADDR'))
        {
            $this->ip = getenv('REMOTE_ADDR');
        }
        else
        {
            $this->ip = 'UNKNOWN';
        }
    } 

    function getLocation()
    {
        $data = [];
        $sql = "SELECT id,name,lat,lng FROM location";
        $query = $this->adapter->query($sql);
        $results = $query->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        return $data;
    }

    function addLine($message)    
    {
        $sql = $this->adapter->query("INSERT INTO `line` (message, receive_date) VALUES ( '$message', now())");
        return($sql->execute());
    }

    function getLine()
    {
        $data = [];
        $sql = "select id,message,receive_date from line where receive_date <= DATE_SUB(now(), INTERVAL 10 SECOND) and noti = false";
        $query = $this->adapter->query($sql);
        $results = $query->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();

        return $data;
    }

    function updateLine($id)
    {        
        $data = [];
        $sql = $this->adapter->query("update line set noti=true where id in (".implode(",",$id).")");
        return($sql->execute());
    }
}
    