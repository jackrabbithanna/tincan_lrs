<?php

class ServicesTinCanFormatter extends ServicesJSONFormatter {
  public function render($data) {
    // json_encode doesn't give valid json with data that isn't an array/object.
    
    if (is_scalar($data)) {
      $data = array($data);
    }
    // work around to allow binary non formatted results
    if(isset($data['binary'])) {
      return $data['binary'];
    }
    else {
      return str_replace('\\/', '/', json_encode($data));
    }
    
  }
}
/*
class ServicesTinCanParserJSON extends ServicesParserJSON {
  public function parse(ServicesContextInterface $context) {
    return json_decode($context->getRequestBody(), TRUE);
  }
}*/