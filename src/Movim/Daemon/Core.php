<?php
namespace Movim\Daemon;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Movim\Daemon\Session;

class Core implements MessageComponentInterface {
    private $sessions = array();
    public $loop;
    public $baseuri;

    public function __construct($loop, $baseuri, $port)
    {
        echo colorize("Movim daemon launched\n", 'green');
        echo colorize("Base URI :", 'green')." {$baseuri}\n";
        echo colorize("WebSocket URL :", 'green')." http(s)://[your host adress]:{$port}\n";
        
        $this->loop    = $loop;
        $this->baseuri = $baseuri;
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);
        if($sid != null) {
            if(!array_key_exists($sid, $this->sessions)) {
                $this->sessions[$sid] = new Session($this->loop, $sid, $this->baseuri);
            }

            $this->sessions[$sid]->attach($conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $sid = $this->getSid($from);
        if($sid != null) {
            $this->sessions[$sid]->messageIn($from, $msg);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $sid = $this->getSid($conn);
        if($sid != null) {
            $this->sessions[$sid]->detach($conn);

            // No WebSockets and no linker ? We close the whole session
            if($this->sessions[$sid]->countClients() == 0
            && $this->sessions[$sid]->process == null) {
                unset($this->sessions[$sid]);
            }
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
    }

    private function getSid(ConnectionInterface $conn)
    {
        $cookies = $conn->WebSocket->request->getCookies();
        if(array_key_exists('MOVIM_SESSION_ID', $cookies)) {
            return $cookies['MOVIM_SESSION_ID'];
        } else {
            return null;
        }
    }
}
