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
        //$this->id = $inID; 
        $this->adapter = $adapter;
        //$this->perpage = 100;
        //$this->page = $inPage;
        //$this->pageStart = ($this->perpage*($this->page-1));
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

    ################################################################################ 
    function getLocation($src,$dsc)
    {
        $data = [];
        $sql = "SELECT id,name,lat,lng FROM location where name = '$src'";
        $query = $this->adapter->query($sql);
        $results = $query->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        $return['src'] = $data;

        $sql = "SELECT id,name,lat,lng FROM location where name = '$dsc'";
        $query = $this->adapter->query($sql);
        $results = $query->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        $return['dsc'] = $data;
        return $data;
    }
}
    