<?php
class SocketUrl {
  private $port = 80;
  private $host;
  private $filename;
  public function __construct($argv) {
    $this->host = $argv[1];
    $this->filename = $argv[2];
  }
  public function getContentFromUrl() {
    try {
      if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
        throw new UnexpectedValueException(sprintf("Unable to create a socket: %s \n", socket_strerror(socket_last_error())));
      }
      $ip = $this->getUrlIp();
      print("Connect to ".$ip.":".$this->port."\n"); 
      if (!socket_connect($socket, $this->host, $this->port)) {
        throw new UnexpectedValueException(sprintf("Unable to connect to server %s: %s \n", $this->host, socket_strerror(socket_last_error())));
      }
      $headers = $this->generateRequest();
      print("Send request \n");
      $this->sendRequest($socket, $headers);
      print("Downloading data \n"); 
      $this->downloadData($socket);
    } catch(Exception $er) {
      print(sprintf("Eror getting content from url at line %d. ", $er->getLine()-1).$er->getMessage()); 
      return false;
    }
    socket_close($socket);
    print("Done! \n"); 
  } 
  private function getUrlIp() {
    print("Resolving " .$this->host. " ... ");
    $ip = gethostbyname($this->host);
    print($ip."\n");
    return $ip;
  }
  private function sendRequest($socket, $headers) {
    
    $length = strlen($headers);
    
    while (true) {
      
      if (!$sent = socket_write($socket, $headers, $length)) {
        throw new UnexpectedValueException(sprintf( "Unable to write to socket: %s \n", socket_strerror(socket_last_error())));
      }  
      
      if ($sent < $length) {
        
        $headers = substr($headers, $sent);
        
        $length -= $sent;
        
      } else {
        
        break;
        
      }
    
    } 
  }
  
  private function downloadData($socket) {
      
    print( "MEMORY USAGE BEFORE READ IS '" . memory_get_usage() . "'\n" );
      
    if (($file = fopen($this->filename, 'w')) === false) {
      throw new Exception("Failed to open file. \n");
    }      
    while(($read = socket_read($socket, 1024)) && $read !== '0') {
      
      if (fwrite($file, $read) === false) {
        throw new Exception("Failed to write to file. \n");
      }
    }
      
    if ($read === false) {
      throw new UnexpectedValueException(sprintf( "Unable to read from socket: %s \n", socket_strerror(socket_last_error())));
    }
      
    if (fclose($file) === false) {
      throw new Exception("Failed to close file. \n");
    } 
      
    print("MEMORY USAGE AFTER READ IS '" . memory_get_usage() . "'\n");     
      
  }
  private function generateRequest() {
    $headers = "GET / HTTP/1.0\r\n";
    $headers .= "Host: ".$this->host."\r\n";
    $headers .= "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0\r\n";
    $headers .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
    $headers .= "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
    $headers .= "\r\n";
    return $headers;
  }  
} 
$contentGetter = new UrlSocket($argv);
$contentGetter->getContentFromUrl();
