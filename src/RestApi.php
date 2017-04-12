<?php

namespace Katapoka\Ahgora;

use Katapoka\Ahgora\Contracts\IHttpClient;
use Mockery\Exception;

class RestApi extends AbstractApi
{
    /**
     * The API base url.
     */
    const API_BASE_URL = 'https://www.ahgora.com.br/externo';

    /**
     * The endpoint to get the punches.
     */
    const ENDPOINT_APURACAO = '%s/getApuracao';

    /**
     * The company id.
     * string @var
     */
    private $companyId;

    /**
     * The account username.
     * string @var
     */
    private $username;

    /**
     * The account password.
     * @var
     */
    private $password;

    /**
     * @var \Katapoka\Ahgora\Contracts\IHttpClient
     */
    private $httpClient;

    /**
     * Api constructor.
     *
     * @param IHttpClient $httpClient
     */
    public function __construct(IHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
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
        $params = [
            'company'   => $this->companyId,
            'matricula' => $this->username,
            'senha'     => $this->password,
            'mes'       => date('m'),
            'ano'       => date('Y'),
        ];
        try {
            $response = $this->httpClient->post($this->buildUrl(static::ENDPOINT_APURACAO), $params)->json();

            return isset($response->empresa->empresa) && $response->empresa->empresa === $this->companyId;
        } catch (Exception $e) {
            $this->error($e->getMessage(), $params);
            return false;
        }
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
        $params = [
            'company'   => $this->companyId,
            'matricula' => $this->username,
            'senha'     => $this->password,
            'mes'       => str_pad($month, 2, '0', STR_PAD_LEFT),
            'ano'       => $year,
        ];
        try {
            $response = $this->httpClient->post($this->buildUrl(static::ENDPOINT_APURACAO), $params)->json();
            if (isset($response->error)) {
                throw new \BadMethodCallException($response->error);
            }

            return [
                'punches' => $this->parsePunches($response->dias),
                'extra'   => $this->parseExtra($response->dias),
            ];
        } catch (Exception $e) {
            $this->error($e->getMessage(), $params);

            return [];
        }
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
        return $this->getEmployeeData('nome');
    }

    /**
     * Gets the employer name.
     *
     * @return string
     */
    public function getEmployeeRole()
    {
        return $this->getEmployeeData('cargo');
    }

    /**
     * Get the employer department.
     *
     * @return string
     */
    public function getDepartment()
    {
        return $this->getEmployeeData('departamento');
    }

    /**
     * Gets the user data and punches.
     *
     * @param null $field
     *
     * @return mixed
     */
    private function getEmployeeData($field = null)
    {
        if (!empty($field) && property_exists($this->getData()->funcionario, $field)) {
            return $this->getData()->funcionario->{$field};
        }

        return $this->getData()->funcionario;
    }

    /**
     * Gets all the data and punches. Cached method.
     *
     * @return mixed
     */
    private function getData() {
        return once(function () {
            $params = [
                'company'   => $this->companyId,
                'matricula' => $this->username,
                'senha'     => $this->password,
                'mes'       => date('m'),
                'ano'       => date('Y'),
            ];
            $response = $this->httpClient->post($this->buildUrl(static::ENDPOINT_APURACAO), $params)->json();
            if (isset($response->error)) {
                throw new \BadMethodCallException($response->error);
            }

            return $response;
        });
    }

    private function buildUrl($endpoint, array $params = []) {
        return once(function () use ($endpoint, $params) {
            return vsprintf($endpoint, array_merge([static::API_BASE_URL], $params));
        });
    }

    private function parsePunches($dias)
    {
        $tmp = [];

        foreach ($dias as $dia => $diaConfig) {
            foreach ($diaConfig->batidas as $batida) {
                $hora = substr($batida->hora, 0, 2);
                $minuto = substr($batida->hora, 2, 2);
                $tmp[] = new \DateTime(sprintf('%s %s:%s:00', $dia, $hora, $minuto));
            }
        }

        return $tmp;
    }

    private function parseExtra($dias)
    {
        $tmp = [];

        foreach ($dias as $dia => $dadosDia) {
            // Parse falta
            $falta = array_filter($dadosDia->resultado, function ($item) { return $item->tipo == 'FALTA'; });

            // Parse extra
            $extra = array_filter($dadosDia->resultado, function ($item) { return $item->tipo == 'Extra'; });

            $tmp[$dia][] = [
                'falta' => empty($falta) ? '00:00' : preg_replace('/^(-)?(\d{2})(\d{2})$/i', '\1\2:\3', array_shift($falta)->valor),
                'extra' => empty($extra) ? '00:00' : preg_replace('/^(-)?(\d{2})(\d{2})$/i', '\1\2:\3', array_shift($extra)->valor),
            ];
        }

        return $tmp;
    }

}
