<?php

namespace Buzz\Client;

use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

class Cid
{
    public function addCid($request){
    	if(! empty($request)){
	        $headers = $request->getHeaders();
	        if(empty($headers)){
	            $cid=$this->generateCid();
	            $cid="Cid: ".$cid;   
	            $headers=array($cid);
	            $request->setHeaders($headers);
	        }else{
	            $cidPresent=false;
	            foreach ($headers as $value) {
	                if(strpos(strtolower($value), 'cid:')  !== false){
	                    $cidPresent = true;
	                    break;
	                }
	            }
	            if(! $cidPresent){
	                $cid=$this->generateCid();  
	                $cid="Cid: ".$cid;    
	                array_push($headers, $cid);
	                $request->setHeaders($headers);
	             }
	        }
   		}
        return $request;
    }

    public function generateCid(){
        try {
            $uuid4 = Uuid::uuid4();
            $cid= $uuid4->toString(); // 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
            return $cid;

    	} catch (UnsatisfiedDependencyException $e) {
        // Some dependency was not met. Either the method cannot be called on a
        // 32-bit system, or it can, but it relies on Moontoast\Math to be present.
        echo 'Caught exception: ' . $e->getMessage() . "\n";

    	}
    }
}
