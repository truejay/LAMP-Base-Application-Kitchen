<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Doctrine_profiler
{
    public static $profiler;
    public static $disabled; 
    
    public function __construct()
    {
        $this->ci =& get_instance();
        
        if ($this->ci->config->item('app_mode') != 'dev')
            self::$disabled = TRUE;  
    }
    
    public function profiler_start() 
    {
        if ( ! self::$disabled)
        {
            self::$profiler = new Doctrine_Connection_Profiler();
            foreach (Doctrine_Manager::getInstance()->getConnections() as $conn) 
                $conn->setListener(self::$profiler);
        }
    }

    public function profiler_end() 
    {
        if ( ! self::$disabled)
        {
            $time = 0;
            $output = '';
            $events = array();
            
            foreach(self::$profiler as $event) 
            {
                $time += $event->getElapsedSecs();
                if ($event->getName() == 'query' || $event->getName() == 'execute') 
                {
                    $event_details = array(
                        "type" => $event->getName(),
                        "query" => $event->getQuery(),
                        "time" => sprintf("%f", $event->getElapsedSecs())
                    );
                    
                    if (count($event->getParams())) 
                        $event_details["params"] = $event->getParams();
                        
                    $events[]= $event_details;
                }
            }

            $output .= print_r($events,1);
            $output .= "\nTotal Doctrine time: " . $time  . "\n";
            $output .= "Peak Memory: " . memory_get_peak_usage() . "";

            file_put_contents(BASEPATH . "logs/doctrine_profiler.php", $output);
        }
    }
}
