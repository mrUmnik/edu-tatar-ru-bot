<?php
namespace EduTatarRuBot;

use EduTatarRuBot\Exceptions\AuthException;
use EduTatarRuBot\Exceptions\HttpErrorException;
use EduTatarRuBot\Exceptions\WrongUsernameException;
use EduTatarRuBot\Exceptions\BlockedUserException;

class Client
{
	const HOST = 'https://edu.tatar.ru';
	const WRONG_USERNAME_TEXT = 'Неверный логин или пароль';
	const BLOCKED_USER_TEXT = 'Доступ в систему временно заблокирован';
	protected $clientId;
	protected $login;
	protected $password;

	public function __construct($clientId, $login, $password)
	{
		$this->clientId = $clientId;
		$this->login = $login;
		$this->password = $password;
	}

	public function get($path, $data = array())
	{
		try {
			return $this->request($path, $data);
		} catch (AuthException $e) {
			// if unauthorized
		}
		$this->request('/logon', array(
			'main_login' => $this->login,
			'main_password' => $this->password,
			'autologin' => '1'
		), 'AUTH');
		return $this->request($path, $data);
	}

	protected function request($path, $data = array(), $method = 'GET')
	{
		$url = self::HOST . $path;
		$method = strtoupper($method);
		$curl = curl_init();

		$cookieFile = $this->getCookieFilename();

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0');

		switch ($method) {
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, count($data));
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				break;
			case 'AUTH':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate, br');
				curl_setopt($curl, CURLOPT_REFERER, 'https://edu.tatar.ru/logon');
				curl_setopt($curl, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/x-www-form-urlencoded',
					'Connection: keep-alive',
					'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
					'Upgrade-Insecure-Requests: 1',
					'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
					'Origin: https://edu.tatar.ru',
					'Cache-Control: max-age=0',
				));
				break;
			default:
				if (!empty($data)) {
					$url .= '?' . http_build_query($data);
				}
		}

		curl_setopt($curl, CURLOPT_URL, $url);

		$response = curl_exec($curl);
		if (false === $response) {
			throw new HttpErrorException('Request of ' . $url . ' failed');
		}
		$resultPage = parse_url(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
		curl_close($curl);

		if ($resultPage['path'] == '/logon') {
			preg_match('/<div class="alert alert-danger">(.*?)<\/div>/is', $response, $matches);
			$errorText = (is_array($matches) && isset($matches[1])) ? strip_tags($matches[1]) : '';
			if (!strlen($errorText)) {
				throw new AuthException();
			}
			if (false !== stripos($errorText, self::WRONG_USERNAME_TEXT)) {
				throw new WrongUsernameException($errorText);
			}
			if (false !== stripos($errorText, self::BLOCKED_USER_TEXT)) {
				throw new BlockedUserException($errorText);
			}
			throw new AuthException();
		}

		return $response;
	}

	protected function getCookieFilename()
	{
		$tempDir = ROOT_DIR . DIRECTORY_SEPARATOR . 'tmp';
		if (!is_dir($tempDir)) {
			mkdir($tempDir);
		}
		$cookieDir = $tempDir . DIRECTORY_SEPARATOR . 'cookies';
		if (!is_dir($cookieDir)) {
			mkdir($cookieDir);
		}
		return $cookieDir . DIRECTORY_SEPARATOR . $this->clientId . '.txt';
	}
}