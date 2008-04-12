<?php

namespace naf::auth;
use naf::auth::OpenID::Fault;

class OpenID {
	
	/**
	 * Known OpenID providers.
	 * For these provs, only a username could be provided,
	 * not the complete identity URL.
	 *
	 * @var array
	 */
	private $providers = array(
		'http://www.blogger.com/openid-server.g' => array(
			'description' => 'Google/Blogger',
			'format'      => 'http://{username}.blogspot.com/'
		),
	);
	
	/**
	 * @var string
	 */
	private $baseUrl, $returnUrl, $trustUrl;
	
	/**
	 * @param string $url
	 * @throws naf::auth::OpenID::Fault
	 */
	function setBaseUrl($url)
	{
		$this->baseUrl = $this->checkUrl($url);
	}
	/**
	 * @return string
	 * @throws naf::auth::OpenID::Fault
	 */
	function getTrustUrl()
	{
		if ($this->trustUrl)
		{
			return $this->trustUrl;
		}
		
		$this->checkBaseUrl();
		
		return $this->baseUrl;
	}
	/**
	 * @param string $url
	 * @throws naf::auth::OpenID::Fault
	 */
	function setTrustUrl($url)
	{
		$this->trustUrl = $this->checkUrl($url);
	}
	/**
	 * @return string
	 * @throws naf::auth::OpenID::Fault
	 */
	function getReturnUrl()
	{
		if ($this->returnUrl)
		{
			return $this->returnUrl;
		}
		
		$this->checkBaseUrl();
		
		return $this->baseUrl;
	}
	/**
	 * @param string $url
	 * @throws naf::auth::OpenID::Fault
	 */
	function setReturnUrl($url)
	{
		$this->returnUrl = $this->checkUrl($url);
	}
	/**
	 * perform a redirect to OpenID/setup, where the openid server takes control over
	 * user agent.
	 *
	 * @param string $identity
	 * @param string $provider
	 * @throws naf::auth::OpenID::Fault
	 */
	function setup($identity, $provider = null) {
		
		if (null === $provider)
		{
			throw new Fault("Sorry. Arbitrary identities are not yet supported. You will need to specify a provider");
		}
		
		if (empty($identity) || (! is_string($identity)) || ! trim($identity))
		{
			throw new Fault("Error: empty identity!");
		}
		
		$this->checkUrl($provider, "Invalid provider URL");
		
		$provider = rtrim($provider, '/');
		
		try {
			$this->checkUrl($identity);
		} catch (Fault $e) {
			if (array_key_exists($provider, $this->providers))
			{
				$identity = str_replace('{username}', $identity, $this->providers[$provider]['format']);
			} else {
				throw new Fault($provider . " is not yet supported on the level of usernames. Please specify identity URL.");
			}
		}
		
		$params = array(
			'openid.mode' => 'checkid_setup',
			'openid.identity' => $identity,
			'openid.return_to' => $this->getReturnUrl(),
			'openid.trust_root' => $this->getTrustUrl(),
			'openid.assoc_handle' => uniqid('oida-', true),
		);
		
		header("Location: $provider?" . http_build_query($params, null, '&'));
		exit();
	}
	
	/**
	 * Check user information.
	 *
	 * @return bool
	 * @throws naf::auth::OpenID::Fault
	 */
	function check()
	{
		if ('id_res' != @$_GET['openid_mode'])
		{
			throw new Fault("Sorry. OpendID provider did not authentificate you");
		}

		if (! $signed = trim(filter_input(INPUT_GET, 'openid_signed', FILTER_SANITIZE_STRING)))
		{
			throw new Fault("No signed parameters");
		}
		
		if (! $assoc = trim(filter_input(INPUT_GET, 'openid_assoc_handle', FILTER_SANITIZE_STRING)))
		{
			throw new Fault("openid_assoc_handle not set");
		}
		
		if (! $sig = trim(filter_input(INPUT_GET, 'openid_sig', FILTER_SANITIZE_STRING)))
		{
			throw new Fault("openid_sig not set");
		}
		
		$params = array(
			'openid.mode' => 'check_authentication',
			'openid.signed' => $signed,
			'openid.assoc_handle' => $assoc,
			'openid.sig' => $sig,
			'openid.return_to' => filter_input(INPUT_GET, 'openid_return_to', FILTER_VALIDATE_URL),
			'openid.identity' => filter_input(INPUT_GET, 'openid_identity', FILTER_VALIDATE_URL),
		);
		
		$postData = http_build_query($params);
		
		if (! $fp = fsockopen('www.blogger.com', 80, $errrno, $errstr, 5))
		{
			throw new Fault("Could not connect to openid server. Network problems?");
		}

		$header = "POST /openid-server.g HTTP/1.0\r\n" .
			"Host: www.blogger.com\r\n" . 
			"Content-type: application/x-www-form-urlencoded\r\n" .
			"Content-length: " . strlen($postData) .
			"Connection: Close\r\n";
		
		fwrite($fp, $header . "\r\n" . $postData);
		
		$responseHeaders = '';
		$inHeaders = true;
		$responseBody = '';
		while (! feof($fp))
		{
			$str = fgets($fp, 128);
			if ($inHeaders && ("\r\n" == $str))
			{
				$inHeaders = false;
			}
			elseif ($inHeaders)
			{
				$responseHeaders .= $str;
			}
			else
			{
				$responseBody .= $str;
			}
		}
		fclose($fp);

		if (preg_match("~HTTP/1\.[01]\s(\d+)\s(.+)~i", $responseHeaders, $matches))
		{
			$statusCode = $matches[1];
			$statusString = $matches[2];
		}
		else
		{
			$statusCode = '404';
			$statusString = 'Not Found';
		}
		
		if (200 != $statusCode)
		{
			throw new Fault('HTTP request failed with status ' . $statusCode);
		}
		
		return 'is_valid:true' == trim($responseBody);
		
	}
	
	/*
	 * -------------- Below go private methods --------------
	 */
	
	private function checkUrl($url, $errorMessage = "Invalid URL")
	{
		if ($url = filter_var($url, FILTER_VALIDATE_URL))
		{
			return $url;
		}
		
		throw new Fault($errorMessage);
	}
	
	private function checkBaseUrl()
	{
		if (empty($this->baseUrl))
		{
			throw new Fault("Base URL not set");
		}
	}
	
}