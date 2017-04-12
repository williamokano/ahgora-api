<?php

namespace Katapoka\Ahgora;

use DateTime;
use InvalidArgumentException;
use Katapoka\Ahgora\Contracts\IHttpClient;
use Katapoka\Ahgora\Contracts\IHttpResponse;
use LogicException;

/**
 * Class responsible for getting the data from the Ahgora system.
 */
class HttpApi extends AbstractApi
{
    const AHGORA_BASE_URL = 'https://www.ahgora.com.br';
    const AHGORA_COMPANY_URL = '%s/externo/index/%s';
    const AHGORA_LOGIN_URL = '%s/externo/login';
    const AHGORA_PUNCHS_URL = '%s/externo/batidas/%s-%s';

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
    /** @var HtmlPageParser */
    private $htmlPageParser;

    /**
     * Api constructor.
     *
     * @param IHttpClient $httpClient
     */
    public function __construct(IHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->htmlPageParser = new HtmlPageParser();
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
     * Get the punches at the given parameters.
     *
     * @param int|null $month The month you want to get the punches - Must be between 01 and 12 (both included)
     * @param int|null $year  The year you want to get the punches
     *
     * @return array
     */
    public function getPunches($month = null, $year = null)
    {
        if (!$this->loggedIn) {
            throw new LogicException('To get punches you need to be loggedIn');
        }

        $month = IntHelper::parseNullableInt($month);
        $year = IntHelper::parseNullableInt($year);

        $month = $month ?: (int)date('m') + (intval(date('d')) >= 20 ? 1 : 0);
        $year = $year ?: (int)date('Y');

        return $this->getPunchesFromPage($this->getPunchesPage($month, $year));
    }

    /**
     * Get the punches from some given day.
     *
     * @param int $day
     * @param int $month
     * @param int $year
     *
     * @return mixed
     */
    public function getPunchesFromDay($day, $month, $year)
    {
        $date = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
        list($year, $month, $day) = explode('-', $date);

        if ($day > 19) {
            $month++;
        }

        if ($month > 12) {
            $month = 1;
            $year++;
        }
        $punches = $this->getPunches($month, $year);

        return array_filter($punches['punches'], function (\DateTime $punchDateTime) use ($day) {
            return (int) $punchDateTime->format('d') === (int) $day;
        });
    }

    /**
     * Gets the employee name.
     *
     * @return string
     */
    public function getEmployeeName()
    {
        return "NOT IMPLEMENTED YET";
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
     * Retrive all the punches for the given string.
     *
     * @param string $punchesStr
     *
     * @return array
     */
    private function parsePunches($punchesStr)
    {
        $punches = [];
        if (!!preg_match_all('/(\d{2}:\d{2})/is', $punchesStr, $matches)) {
            $punches = $matches[0];
        }

        return $punches;
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
     * Get the built punchesUrl with the given month and year.
     *
     * @param int $month
     * @param int $year
     *
     * @return string
     */
    private function punchesUrl($month, $year)
    {
        $month = str_pad(strval($month), 2, '0', STR_PAD_LEFT);
        $punchesUrl = sprintf(self::AHGORA_PUNCHS_URL, self::AHGORA_BASE_URL, $month, $year);
        $this->debug('punchesUrl', [
            'punches_url' => $punchesUrl,
            'month'       => $month,
            'year'        => $year
        ]);

        return $punchesUrl;
    }

    /**
     * Make the request to the punches page of the requested time period.
     *
     * @param int $month
     * @param int $year
     *
     * @return IHttpResponse
     */
    private function getPunchesPage($month, $year)
    {
        return $this->httpClient->get($this->punchesUrl($month, $year));
    }

    /**
     * Get the punches from the given response of the punches page.
     *
     * @param IHttpResponse $punchesPageResponse
     *
     * @return array
     */
    private function getPunchesFromPage(IHttpResponse $punchesPageResponse)
    {
        if ($punchesPageResponse->getHttpStatus() !== IHttpClient::HTTP_STATUS_OK) {
            throw new InvalidArgumentException('The request returned http status ' . $punchesPageResponse->getHttpStatus());
        }

        $punches = $this->parsePunchesPage($punchesPageResponse);

        return [
            'punches' => $punches,
        ];
    }

    private function parsePunchesPage(IHttpResponse $punchesPageResponse)
    {
        $rows = $this->htmlPageParser->getPunchesRows($punchesPageResponse);
        $punchCollection = [];

        foreach ($rows as $row) {
            $punches = $this->parsePunches($row['punches']);
            $punchCollection = array_merge($punchCollection, $this->createPunchesDate($row['date'], $punches));
        }

        return $punchCollection;
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
