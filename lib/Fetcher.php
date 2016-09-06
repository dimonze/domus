<?php

class Fetcher {

  const
    ERROR_FETCH           = 1,
    ERROR_ROBOTSTXT       = 2,
    ERROR_METAS           = 3,
    ERROR_CLEANUP         = 10,
    ERROR_BODY_NOT_FOUND  = 11,
    ERROR_FRAMESET        = 12,

    PROXY_HOST            = '192.168.1.3',
    PROXY_PORT            = 8192;

  private static $_cache = array();

  private $http_client = null;

  protected
    $options = array(
      'use_proxy'             => true,

      'track_referrer'        => true,
      'use_cookies'           => true,
      'check_robotstxt'       => false,
      'check_metas'           => false,
      'use_cache'             => false,
      'retry_on_error'        => 3,
      'retry_on_empty'        => 3,
      'check_https_redirect'  => false,
      'method'                => 'GET',

      'no_frameset'           => true,
      'cleanup'               => null,
      'strip_comments'        => false,
      'strip_html'            => false,
      'strip_html_options'    => null,
      'only_body'             => false,
    ),
    $current_referrer = null;

  public function __construct(array $options = array())
  {
    if ($options) {
      $this->setDefaults($options);
    }
  }

  public function setDefaults(array $options)
  {
    $this->options = array_merge($this->options, $options);
  }

  public function setDefault($name, $value)
  {
    $this->options[$name] = $value;
  }

  /**
   * Returns page data. Cache exists for script execution.
   * @param string $url Url to fetch
   * @param array $options
   * @return string
   * @throws Exception
   */
  public function fetch($url, array $options = array()) {
    $options = array_merge($this->options, $options);

    $key = md5($url.json_encode($options));

    if (!$options['use_cache'] || !isset(self::$_cache[$key])) {
      try {
        $data = $this->doFetch($url, $options);
      }
      catch (Exception $e) {
        if ($e->getCode() == self::ERROR_FETCH && $options['retry_on_error'] > 0) {
          $options['retry_on_error']--;
          return $this->fetch($url, $options);
        }
        throw $e;
      }


      if ($options['cleanup']) {
        if (is_callable($options['cleanup'],true)) {
          $data = call_user_func($options['cleanup'], $data);
        }
        elseif (is_callable(array($this, 'cleanup'), true)) {
          $data = $this->cleanup($data);
        }

        if (!$data) {
          if ($options['retry_on_empty'] > 0) {
            $options['retry_on_empty']--;
            return $this->fetch($url, $options);
          }
          throw new Exception("Empty data after cleanup: $url", self::ERROR_CLEANUP);
        }
      }

      if ($options['use_cache']) {
        self::$_cache[$key] = $data;
      }
    }
    else {
      $data = self::$_cache[$key];
    }

    return $data;
  }

  public function getCurl($url, array $options = array())
  {
    $options = array_merge($this->options, $options);
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HEADER         => false,
    ));

    if ($options['use_proxy']) {
      curl_setopt($ch, CURLOPT_PROXY, self::PROXY_HOST);
      curl_setopt($ch, CURLOPT_PROXYPORT, self::PROXY_PORT);
    }

    if (!empty($options['referer'])) {
      curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
    }
    elseif ($options['track_referrer'] && $this->current_referrer) {
      curl_setopt($ch, CURLOPT_REFERER, $this->current_referrer);
    }

    if ('GET' != $options['method']) {
      curl_setopt($ch, constant('CURLOPT_HTTP' . strtoupper($options['method'])), true);
    }

    return $ch;
  }


  /**
   * Internal method for 'real' fetch
   * @param string $url
   * @return string
   * @throws Exception
   */
  private function doFetch($url, array $options) {
    $client = $this->getHttpClient($options);

    if ($options['use_proxy']) {
      $host = strtolower(parse_url($url, PHP_URL_HOST));
      if (false === strpos($host, 'yandex') && false === strpos($host, 'google')) {
        $client->setHeaders('Sape-Bind: random-no-stat');
      }
    }

    if ($options['method'] == Zend_Http_Client::POST && strpos($url, '?')) {
      list($url, $post_params) = explode('?', $url);
      parse_str($post_params, $post_data);
      $client->setMethod(Zend_Http_Client::POST);
      $client->setParameterPost($post_data);
    }

    $client->setUri($url);
    $this->current_referrer = $url;

    if ($options['check_robotstxt']) {
      if (!$this->isAllowedInRobotsTXT($url)) {
        throw new Exception("Url [$url] not allowed in robots.txt", self::ERROR_ROBOTSTXT);
      }
    }

    try {
      if ($options['check_https_redirect']) {
        $client->setConfig(array('maxredirects' => 0));
        $response = $client->request();
        if ($response->isRedirect()) {
          $location = $response->getHeader('location');
          if (0 === strpos($location, 'http://pass.pronto.ru/client/controller.php?suid=')) {
            $location = preg_replace('/return_uri.+$/',  urlencode('return_uri='.urlencode('http://irr.ru/')), $location);
            $client->setConfig(array('maxredirects' => 10));
            $client->setUri($location);
            $client->request();

            $client->setUri($url);
            $response = $client->request();
          }
          elseif (false !== strpos($location, 'irr.ru/')) {
            $client->setConfig(array('maxredirects' => 10));
            $client->setUri($location);
            $response = $client->request();
          }
          else {
            throw new Exception('Do not know how to handle redirects to url ' . $location);
          }
        }
      }
      else {
        $response = $client->request();
      }

      $body = $this->guessCharset($response->getBody(), $response->getHeaders());
    }
    catch (Zend_Exception $e) {
      throw new Exception("Fetch failed [$url] - ".$e->getMessage(), self::ERROR_FETCH);
    }

    unset($client);

    if ($options['no_frameset']) {
      if (preg_match('/<frameset.+>/si', $body)) {
          throw new Exception('Framesets not allowed!', self::ERROR_FRAMESET);
      }
    }

    if ($options['strip_comments']) {
      $body = preg_replace('/<!--.*-->/sU', '', $body);
    }

    if ($options['strip_html']) {
      $body = $this->stripUnusedTags($body, $options['strip_html_options']);
    }

    if ($options['check_metas']) {
      if (!$this->isAllowedInMetas($body)) {
        throw new Exception("Url [$url] not allowed in metas", self::ERROR_METAS);
      }
    }

    if ($options['only_body']) {
      if (!($body = $this->extractBody($body))) {
        throw new Exception("Can't extract body for [$url]", self::ERROR_BODY_NOT_FOUND);
      }
    }

    return $body;
  }

  /**
   * @param array $options
   * @return Zend_Http_Client
   */
  private function getHttpClient(array $options) {
    if ($options['use_cookies'] || $options['track_referrer']) {
      if (!$options['check_https_redirect']) {
        $single = true;
      }
    }

    if (!empty($single) && $this->http_client !== null) {
      $client = $this->http_client;
    }

    if (empty($client)) {
      ProjectConfiguration::registerZend();
      $client = new Zend_Http_Client();
      $client->setConfig(array(
          'useragent'       => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.14) Gecko/2009090216 Ubuntu/9.04 (jaunty) Firefox/3.0.14',
          'maxredirects'    => 10,
          'strictredirects' => false,
          'httpversion'     => Zend_Http_Client::HTTP_1,
          'keepalive'       => false,
          'strict'          => false,
        ));

      if ($options['use_proxy']) {
        $http_adapter = new Zend_Http_Client_Adapter_Proxy();
        $http_adapter->setConfig(array(
          'proxy_host' => self::PROXY_HOST,
          'proxy_port' => self::PROXY_PORT,
        ));
        $client->setAdapter($http_adapter);
        unset($http_adapter);
      }

      if ($options['use_cookies']) {
        $client->setCookieJar(true);
      }
      if (!empty($single)) {
        $this->http_client = $client;
      }
    }

    if (!empty($options['referer'])) {
      $client->setHeaders('Referer', $options['referer']);
    }
    if ($options['track_referrer'] && $this->current_referrer) {
      $client->setHeaders('Referer', $this->current_referrer);
    }

    return $client;
  }

  /**
   * Strip tags that could damage html parsing
   * @param string $html
   * @param array|null $options tags not to clean
   * @return string
   */
  public function stripUnusedTags($html, $options = null) {
    $regexp = array(
      'script'    => '|<script[^>]*>.*</script>|siU',
      'marquee'   => '|<marquee[^>]*>.*</marquee[^>]*>|siU',
      'style'     => '|<style[^>]*>.*</style>|siU',
      'frame'     => '|<frames[^>]*>.*</frames>|siU',
      'iframe'    => '|<iframe[^>]*>.*</iframe>|siU',
      'applet'    => '|<applet[^>]*>.*</applet>|siU',
      'embed'     => '/<embed[^>]*\/?>(.*<\/embed>)?/siU',
      'object'    => '|<object[^>]*>.*</object>|siU',
      'textarea'  => '|<textarea[^>]*>.*</textarea>|siU',
      'input'     => '/<(input|button)[^>]*>/siU'
    );

    if ($options !== null) {
      foreach ($options as $key) {
        if (isset($regexp[$key])) {
          unset($regexp[$key]);
        }
      }
    }

    return preg_replace(array_values($regexp), '', $html);
  }

  /**
   * Checks for robots.txt
   * @param string $url
   * @return boolean
   */
  protected function isAllowedInRobotsTXT($url) {
    try {
      $robotstxt = $this->fetch('http://'.parse_url($url, PHP_URL_HOST).'/robots.txt');
      $robotstxt = preg_split('/(\r?\n)+/', $robotstxt);

      $parsed = parse_url($url);

      $agent_rules   = array();
      $overall_rules = array();

      $ruleapplies = false;

      foreach ($robotstxt as $line) {
        #delete comments
        $line = preg_replace('/#.*$/', '', trim($line));

        if (empty($line)){
          continue;
        }

        #if is current user-agent rules block - setting referrens on $agent_rules
        if (preg_match('/User-agent: (.*)yandex([ ,]+|$)(.*)/i', $line, $match)) {
          $curr_rules_arr = &$agent_rules;
          continue;
        }
        #if is overall rules block - setting referrens on $overall_rules
        elseif (preg_match('/User-agent:([ ]*)\*/i', $line, $match)) {
          $curr_rules_arr = &$overall_rules;
          continue;
        }
        #if is other user-agent rules block - unsetting reference, skipping
        elseif (preg_match('/User-agent:/i', $line)) {
          unset( $curr_rules_arr );
        }

        #collecting only allow|disallow directivess
        if (isset( $curr_rules_arr ) && preg_match( '/(Allow|Disallow)/i', $line)) {
          $curr_rules_arr[] = $line;
        }
      }

      if (count($agent_rules)) {
        $rules = $agent_rules;
      }
      elseif (count($overall_rules)) {
        $rules = $overall_rules;
      }
      else {
        return true;
      }

      if (empty($parsed['path'])) {
        $uri = '/';
      }
      else {
        $uri = $parsed['path'];
      }
      if (!empty($parsed['query'])) {
        $uri .= '?' . $parsed['query'];
      }

      foreach($rules as $rule) {
        #if malformed rule - skip
        if(!preg_match('/(.*):\s*((\/|\*).*)/iu', $rule, $matches)) {
          continue;
        }

        $directive = strtolower(trim($matches[1]));
        # quoting reg-exp and replacing \* with .* and \$ with $
        $rule = str_replace('\$', '$', str_replace('\*', '.*', preg_quote(trim($matches[2]))));
        $rule_match = preg_match("|^{$rule}|", $uri);

        if ($directive == 'allow' && $rule_match) {
          return true;
        }
        elseif ($directive == 'disallow' && $rule_match) {
          return false;
        }
      }
    }
    catch (Exception $e) {}

    return true;
  }

  /**
   * Check for meta noindex/nofollow tag
   * @param string $html
   * @return boolean
   */
  protected function isAllowedInMetas($html) {
    $metas = $this->getMetas($html);

    if (!isset($metas['robots']['value'])) {
      return true;
    }

    $meta_robots = $metas['robots']['value'];
    if (stripos($meta_robots, 'nofollow') === false && stripos($meta_robots, 'noindex') === false) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Parse meta tags
   * @param string $html
   * @return array
   */
  public function getMetas($html) {
    $metas = array();

    $html = preg_replace('/<!--.*-->/s', '', $html);
    preg_match_all('/<meta\s[^>]*(name|http-equiv)=([\"\']??)([^\" >]*?)\\2[^>]*content=([\"\']??)([^\">]*?)\\4[^>]*>/siU', $html, $match);

    if (isset($match) && is_array($match) && count($match) == 6) {
      $originals = $match[0];
      $names = $match[3];
      $values = $match[5];

      if(count($originals) == count($names) && count($names) == count($values)) {
        for ($i=0, $limiti=count($names); $i < $limiti; $i++) {
          if (!isset($metas[strtolower($names[$i])])) {
            $metas[strtolower($names[$i])] = $values[$i];
          }
        }
      }
    }
    unset($match);

    preg_match_all('/<meta\s[^>]*content=([\"\']??)([^\">]*?)\\1[^>]*(name|http-equiv)=([\"\']??)([^\" >]*?)\\4[^>]*>/siU', $html, $match);
    if (isset($match) && is_array($match) && count($match) == 6) {
      $originals = $match[0];
      $names = $match[5];
      $values = $match[2];

      if (count($originals) == count($names) && count($names) == count($values)) {
        for ($i=0, $limiti=count($names); $i < $limiti; $i++) {
          $metas[strtolower($names[$i])] = $values[$i];
        }
      }
    }

    return $metas;
  }

  /**
   * Trying to extract body content
   * @param string $html
   * @return string|null
   */
  public function extractBody($html) {
    if (preg_match('/<body[^>]*>(.*)<\/body>/imsU', $html, $matches)) {
      return $matches[1];
    }
    elseif (strpos($html, '<body') !== false) {
      $parts = preg_split('/<body[^>]*>/iU', $html);
      return array_pop($parts);
    }
    else {
      return null;
    }
  }

  /**
   * Strip content of noindex tags
   * @param string $html
   * @return string
   */
  public function stripNoindex($html) {
    $result = '';
    $blockss = preg_split('/(<\/?noindex[^>]*>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    $opened = 0;
    foreach ($blockss as $block) {
      if (mb_strtolower(mb_substr($block, 0, 8)) == '<noindex') {
        $opened++;
      }
      elseif (mb_strtolower(mb_substr($block, 0, 9)) == '</noindex') {
        if ($opened > 0) {
          $opened--;
        }
      }
      else {
        if ($opened == 0) {
          $result .= $block;
        }
      }
    }

    $result = preg_replace('/<a.+rel=.?no(index|follow).*>.*<\/a>/isU', '', $result);

    return $result;
  }

  /**
   * Try to guess html source charset by looking in response headers and <meta/>
   * @param string $body
   * @param array $headers
   * @return string
   */
  protected function guessCharset($body, array $headers = array()) {
    $charset = null;

    // trying to get from headers
    if (!empty($headers)) {
      foreach ($headers as $name => $value) {
        if (strtolower($name) == 'content-type') {
          $value = preg_split('/;\s*/', $value);
          if (isset($value[1])) {
            $value = preg_split('/\s*=\s*/', $value[1]);
            if (strtolower($value[0]) == 'charset') {
              $charset = $value[1];
            }
          }
          break;
        }
      }
    }

    // searching in meta
    if (preg_match('/<meta.+content-type.+>/isU', $body, $matches)) {
      preg_match('/charset="?\'?([a-z0-9-]+)/is', $matches[0], $matches);
      if (isset($matches[1])) {
        $charset = $matches[1];
      }
    }

    $charset = strtolower($charset);
    if ($charset && $charset != 'utf8' && $charset != 'utf-8') {
      $body = iconv($charset, 'utf8', $body);
    }

    return $body;
  }

  /**
   * Write current progress
   * @param string $text = null
   * @return void
   */
  public function progress($text = '')
  {
    static
      $bar = array('|', '/', '-', '\\'),
      $prev = -1,
      $prev_text = '';

    if (empty($text)) $text = $prev_text;
    else $prev_text = $text;

    $prev = ++$prev >= count($bar) ? 0 : $prev;
    echo $bar[$prev].' '.$text."\r";
  }
}
