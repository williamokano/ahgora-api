<?php
namespace Katapoka\Ahgora;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Katapoka\Ahgora\Contracts\IHttpResponse;

class HtmlPageParser
{
    /**
     * @param \Katapoka\Ahgora\Contracts\IHttpResponse $punchesPageResponse
     *
     * @return mixed
     */
    public function getPunchesTableHtml(IHttpResponse $punchesPageResponse)
    {
        $tables = $this->getPageTables($punchesPageResponse);

        //The first table is the data summary
        return $tables['punches'];
    }

    /**
     * Get the punch's rows in array format.
     *
     * @param IHttpResponse $punchesPageResponse
     *
     * @return array
     */
    public function getPunchesRows(IHttpResponse $punchesPageResponse)
    {
        $punchesTableHtml = $this->getPunchesTableHtml($punchesPageResponse);

        $dom = new DOMDocument();
        if (!@$dom->loadHTML($punchesTableHtml)) {
            throw new InvalidArgumentException('Failed to parse punchesTable');
        }

        $rows = $dom->getElementsByTagName('tr');
        $rowsCollection = [];

        foreach ($rows as $row) {
            if ($punchRow = $this->parsePunchRow($row)) {
                $rowsCollection[] = $punchRow;
            }
        }

        return $rowsCollection;
    }

    /**
     * Parse the punch row and return its values.
     *
     * @param \DOMElement $row
     *
     * @return array|bool
     */
    private function parsePunchRow(DOMElement $row)
    {
        $cols = $row->getElementsByTagName('td');
        if ($cols->length !== 8) {
            return false;
        }

        return [
            'date'   => trim($cols->item(0)->nodeValue),
            'punches' => trim($cols->item(2)->nodeValue),
        ];
    }

    /**
     * Get both tables and return the strings into an array with the properties 'summary' and 'punches'.
     *
     * @param IHttpResponse $punchesPageResponse
     *
     * @return array
     */
    private function getPageTables(IHttpResponse $punchesPageResponse)
    {
        $regex = '/<table.*?>.*?<\/table>/si';

        if (!preg_match_all($regex, $punchesPageResponse->getBody(), $matches)) {
            throw new InvalidArgumentException('Pattern not found in the response');
        }

        return [
            'summary' => $matches[0][0],
            'punches'  => $matches[0][1],
        ];
    }
}
