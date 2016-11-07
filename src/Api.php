<?php

namespace Katapoka\Ahgora;

use InvalidArgumentException;

/**
 * Class responsible for getting the data from the Ahgora system.
 */
class Api
{
    use Loggable;

    const AHGORA_BASE_URL = 'https://www.ahgora.com.br';
    const AHGORA_COMPANY_URL = '%s/externo/index/%s';
    const AHGORA_LOGIN_URL = '%s/externo/login';

    /** @var \Katapoka\Ahgora\IHttpClient */
    private $httpClient;
    /** @var string */
    private $password;
    /** @var string */
    private $companyId;
    /** @var string */
    private $username;
    /** @var bool */
    private $loggedIn = false;

    /**
     * Api constructor.
     *
     * @param IHttpClient $httpClient
     */
    public function __construct(IHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->debug('Api instance created');
    }

    /**
     * Set the company id of the ahgora system.
     *
     * @param string $companyId
     *
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        $this->debug('Company ID set', ['company_id' => $companyId]);

        return $this;
    }

    /**
     * Set the username of the employee, from the company set at the setCompanyId.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        $this->debug('Username set', ['username' => $username]);

        return $this;
    }

    /**
     * Set the password of the employee, from the company set at the setCompanyId.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->debug('Password set', ['password' => $password]);

        return $this;
    }

    /**
     * Try to execute the login on the page.
     * To execute some actions the user needs to be loggedin.
     * After a successful login, the status loggedin is saved as true.
     *
     * @return bool Returns true if the login was successful and false otherwise
     */
    public function doLogin()
    {
        $hasLoggedIn = false;
        $this->debug('Started login proccess');
        $response = $this->httpClient->get($this->companyUrl());
        $accessEnabled = stripos($response->body, 'Sua Empresa não liberou o acesso a essa ferramenta') === false;

        // If the company haven't enabled external access, or maybe the company id isn't correct, don't even try to continue to the login process
        if ($accessEnabled) {
            $this->debug('Company has external access enabled');

            $response = $this->httpClient->post($this->loginUrl(), [
                'empresa'   => $this->companyId,
                'matricula' => $this->username,
                'senha'     => $this->password,
            ]);

            $hasLoggedIn = $this->checkLoginStatus($response);
        } else {
            $this->debug("Company hasn't external access enabled");
        }

        $this->setLoggedIn($hasLoggedIn);

        return $hasLoggedIn;
    }

    /**
     * Check the return of the login action.
     * How it works: If statusCode 200 and no body, login ok, otherwise, login failed.
     * Should return a json with property "r" with "error" and "text" with the message
     *
     * @param HttpResponse $response
     *
     * @return bool
     */
    private function checkLoginStatus(HttpResponse $response)
    {
        if ($response->httpStatus === IHttpClient::HTTP_STATUS_OK) {
            $json = $this->safeJsonDecode($response->body, true);
                if ($json['r'] === 'success') {
                    return true;
                }
        }
        return false;
    }

    /**
     * Safely decodes a json.
     *
     * @param string $string
     * @param bool $asArray
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    private function safeJsonDecode($string, $asArray = false)
    {
        $json = json_decode($string, $asArray);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->debug('Failed to debug json', ['str' => $string]);
            throw new InvalidArgumentException('Failed to decode json');
        } else {
            return $json;
        }
    }

    /**
     * Safely set if the user is loggedin or not.
     * Did a separate method do eventually trigger events, if necessary.
     *
     * @param bool $loggedIn
     *
     * @return $this
     */
    private function setLoggedIn($loggedIn = true)
    {
        if (!is_bool($loggedIn)) {
            throw new InvalidArgumentException('LoggedIn parameter must be boolean');
        }

        $this->debug('setLoggedIn', ['logged_in' => $loggedIn]);
        $this->loggedIn = $loggedIn;

        return $this;
    }

    /**
     * Build the company url string.
     *
     * @return string
     */
    private function companyUrl()
    {
        $companyUrl = sprintf(self::AHGORA_COMPANY_URL, self::AHGORA_BASE_URL, $this->companyId);
        $this->debug('CompanyURL', ['company_url' => $companyUrl]);

        return $companyUrl;
    }

    /**
     * Build the login url.
     *
     * @return string
     */
    private function loginUrl()
    {
        $loginUrl = sprintf(self::AHGORA_LOGIN_URL, self::AHGORA_BASE_URL);
        $this->debug('loginUrl', ['login_url' => $loginUrl]);

        return $loginUrl;
    }
}
