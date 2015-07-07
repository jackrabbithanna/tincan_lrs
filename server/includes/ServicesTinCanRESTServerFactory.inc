<?php
/**
 * Factory class to build RESTServer object.
 *
 * If you want to change it
 */

class ServicesTinCanRESTServerFactory extends ServicesRESTServerFactory{

  static $class_name = 'TinCanRESTServer';

  /**
   * We need data property to pass additional arguments to methods.
   *
   * Required property is 'endpoint_path' -- base path of endpoint.
   * Example: GET rest/node/1.php -- "rest" is endpoint path.
   */
  public function __construct($data = array()) {
    if (!isset($data['endpoint_path'])) {
      throw new Exception('ServicesTinCanRESTServerFactory constructor requires "endpoint_data" property.');
    }
    $this->data = $data;
  }

  public function getRESTServer() {
    $content_type_negotiator = $this->getContentTypeNegotiator();
    $context = $this->getContext();
    $resources = $this->getResources();
    $parsers = $this->getParsers();
    $formatters = $this->getFormatters();

    $class_name = static::$class_name;
    return new $class_name($context, $content_type_negotiator, $resources, $parsers, $formatters);
  }

  
  protected function getEndpointSettings() {
    static $settings;

    if (empty($settings)) {
      $endpoint = $this->getEndpoint();

      // Get the server settings from the endpoint.
      $settings = !empty($endpoint->server_settings) ? $endpoint->server_settings : array();
      // Normalize the settings so that we get the expected structure
      // and sensible defaults.
      $settings = tincan_server_setup_settings($settings);
    }

    return $settings;
  }

  protected function getParsers() {
    $settings = $this->getEndpointSettings();

    $parsers = tincan_server_request_parsers();
    // Remove parsers that have been disabled for this endpoint.
    foreach (array_keys($parsers) as $key) {
      if (!$settings['parsers'][$key]) {
        unset($parsers[$key]);
      }
    }

    return $parsers;
  }

  protected function getFormatters() {
    $settings = $this->getEndpointSettings();

    $formatters = tincan_server_response_formatters();
    // Remove formatters that have been disabled for this endpoint.
    foreach (array_keys($formatters) as $key) {
      if (!$settings['formatters'][$key]) {
        unset($formatters[$key]);
      }
    }

    return $formatters;
  }
  
}

