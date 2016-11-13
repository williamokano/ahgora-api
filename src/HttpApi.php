<?php

namespace Katapoka\Ahgora;

use DateTime;
use DOMDocument;
use InvalidArgumentException;
use Katapoka\Ahgora\Contracts\IHttpClient;
use Katapoka\Ahgora\Contracts\IHttpResponse;

/**
 * Class responsible for getting the data from the Ahgora system.
 */
class HttpApi extends AbstractApi
{
    const AHGORA_BASE_URL = 'https://www.ahgora.com.br';
    const AHGORA_COMPANY_URL = '%s/externo/index/%s';
    const AHGORA_LOGIN_URL = '%s/externo/login';
    const AHGORA_PUNCHS_URL = '%s/externo/batidas/%d-%d';

    /** @var \Katapoka\Ahgora\Contracts\IHttpClient */
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

        $accessEnabled = $this->checkAccessEnabled();

        if ($accessEnabled) {
            $response = $this->executeLogin();
            $hasLoggedIn = $this->checkLoginStatus($response);
        }

        $this->debug($accessEnabled ? "Company has external access enabled" : "Company hasn't external access enabled");

        $this->setLoggedIn($hasLoggedIn);

        return $hasLoggedIn;
    }

    /**
     * Get the punchs at the given parameters.
     *
     * @param int|null $month The month you want to get the punchs - Must be between 01 and 12 (both included)
     * @param int|null $year  The year you want to get the punchs
     *
     * @return array
     */
    public function getPunchs($month = null, $year = null)
    {
        $month = $month !== null ? $month : (int)date('m');
        $year = $year !== null ? $year : (int)date('Y');

        if (!$this->isValidPeriod($month, $year)) {
            throw new InvalidArgumentException('Invalid period of time');
        }

        $punchsPageResponse = $this->getPunchsPage($month, $year);

        return $this->getPunchsFromPage($punchsPageResponse);
    }

    /**
     * Gets the employer name.
     *
     * @return string
     */
    public function getEmployeeRole()
    {
        return "NOT IMPLEMENTED YET";
    }

    /**
     * Get the employer department.
     *
     * @return string
     */
    public function getDepartment()
    {
        return "NOT IMPLEMENTED YET";
    }

    /**
     * Execute the login on the server and returns the server response.
     *
     * @return IHttpResponse
     */
    private function executeLogin()
    {
        return $this->httpClient->post($this->loginUrl(), [
            'empresa'   => $this->companyId,
            'matricula' => $this->username,
            'senha'     => $this->password,
        ]);
    }

    /**
     * Check if the company has external access on the Ahgora system.
     *
     * @return bool
     */
    private function checkAccessEnabled()
    {
        $response = $this->httpClient->get($this->companyUrl());

        return stripos($response->getBody(), 'Sua Empresa não liberou o acesso a essa ferramenta') === false;
    }

    /**
     * Check the return of the login action.
     * How it works: If statusCode 200 and no body, login ok, otherwise, login failed.
     * Should return a json with property "r" with "error" and "text" with the message
     *
     * @param IHttpResponse $response
     *
     * @return bool
     */
    private function checkLoginStatus(IHttpResponse $response)
    {
        try {
            return $response->getHttpStatus() === IHttpClient::HTTP_STATUS_OK && $this->getResponseLoginStatus($response);
        } catch (InvalidArgumentException $iaex) {
            $this->error('checkLoginStatus', ['expcetion' => $iaex]);

            return false;
        }
    }

    /**
     * Check if the response can be decoded as json, has the property r and r is 'success'.
     *
     * @param IHttpResponse $response
     *
     * @return bool
     */
    private function getResponseLoginStatus(IHttpResponse $response)
    {
        try {
            $json = $response->json();

            return array_key_exists('r', $json) && $json->r === 'success';
        } catch (InvalidArgumentException $iaex) {
            $this->debug('getResponseLoginStatus', ['exception', $iaex]);

            return false;
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

    /**
     * Get the built punchsUrl with the given month and year.
     *
     * @param int $month
     * @param int $year
     *
     * @return string
     */
    private function punchsUrl($month, $year)
    {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $punchsUrl = sprintf(self::AHGORA_PUNCHS_URL, self::AHGORA_BASE_URL, $month, $year);
        $this->debug('punchsUrl', ['punchs_url' => $punchsUrl]);

        return $punchsUrl;
    }

    /**
     * Make the request to the punchs page of the requested time period.
     *
     * @param int $month
     * @param int $year
     *
     * @return IHttpResponse
     */
    private function getPunchsPage($month, $year)
    {
        return $this->httpClient->get($this->punchsUrl($month, $year));
    }

    /**
     * Get the punches from the given response of the punchs page.
     *
     * @param IHttpResponse $punchsPageResponse
     *
     * @return array
     */
    private function getPunchsFromPage(IHttpResponse $punchsPageResponse)
    {
        if ($punchsPageResponse->getHttpStatus() !== IHttpClient::HTTP_STATUS_OK) {
            throw new InvalidArgumentException('The request returned http status ' . $punchsPageResponse->getHttpStatus());
        }

        $punchs = $this->parsePunchsPage($punchsPageResponse);

        return [
            'punchs' => $punchs,
        ];
    }

    private function parsePunchsPage(IHttpResponse $punchsPageResponse)
    {
        $punchsTableHtml = $this->getPunchsTableHtml($punchsPageResponse);

        $dom = new DOMDocument();
        if (!@$dom->loadHTML($punchsTableHtml)) {
            throw new InvalidArgumentException('Failed to parse punchsTable');
        }

        $rows = $dom->getElementsByTagName('tr');

        $punchCollection = [];

        /** @var \DOMElement $row */
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            if ($cols->length !== 8) {
                continue;
            }

            $date = trim($cols->item(0)->nodeValue);
            $punches = $this->parsePunchs($cols->item(2)->nodeValue);

            $punchCollection = array_merge($punchCollection, $this->createPunchesDate($date, $punches));
        }

        return $punchCollection;
    }

    private function getPunchsTableHtml(IHttpResponse $punchsPageResponse)
    {
        $tables = $this->getPageTables($punchsPageResponse);

        //A primeira posição é a table
        return $tables['punchs'];
    }

    /**
     * Get both tables and return the strings into an array with the properties 'summary' and 'punchs'.
     *
     * @param IHttpResponse $punchsPageResponse
     *
     * @return array
     */
    private function getPageTables(IHttpResponse $punchsPageResponse)
    {
        $regex = '/<table.*?>.*?<\/table>/si';

        if (!preg_match_all($regex, $punchsPageResponse->getBody(), $matches)) {
            throw new InvalidArgumentException('Pattern not found in the response');
        }

        return [
            'summary' => $matches[0][0],
            'punchs'  => $matches[0][1],
        ];
    }

    /**
     * Retrive all the punches for the given string.
     *
     * @param string $punchsStr
     *
     * @return array
     */
    private function parsePunchs($punchsStr)
    {
        $punches = [];
        if (!!preg_match_all('/(\d{2}:\d{2})/is', $punchsStr, $matches)) {
            $punches = $matches[0];
        }

        return $punches;
    }

    /**
     * Convert the date string and the datepunch array to an array of DateTime's.
     *
     * @param string $date
     * @param array  $punches
     *
     * @return \DateTime[]
     */
    private function createPunchesDate($date, array $punches = [])
    {
        $dates = [];
        foreach ($punches as $punch) {
            $dates[] = DateTime::createFromFormat($this->datetimeFormat, sprintf('%s %s', $date, $punch));
        }

        return $dates;
    }

}
