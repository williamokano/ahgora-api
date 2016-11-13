<?php
namespace Katapoka\Ahgora;

use DOMDocument;
use InvalidArgumentException;
use Katapoka\Ahgora\Contracts\IHttpResponse;

class HtmlPageParser
{
    /**
     * @param \Katapoka\Ahgora\Contracts\IHttpResponse $punchsPageResponse
     *
     * @return mixed
     */
    public function getPunchsTableHtml(IHttpResponse $punchsPageResponse)
    {
        $tables = $this->getPageTables($punchsPageResponse);

        //The first table is the data summary
        return $tables['punchs'];
    }

    public function getPunchsRows($punchsPageResponse)
    {
        $punchsTableHtml = $this->getPunchsTableHtml($punchsPageResponse);

        $dom = new DOMDocument();
        if (!@$dom->loadHTML($punchsTableHtml)) {
            throw new InvalidArgumentException('Failed to parse punchsTable');
        }

        $rows = $dom->getElementsByTagName('tr');
        $rowsCollection = [];
        /** @var \DOMElement $row */
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            if ($cols->length !== 8) {
                continue;
            }

            $rowsCollection[] = [
                'date'   => trim($cols->item(0)->nodeValue),
                'punchs' => trim($cols->item(2)->nodeValue),
            ];
        }

        return $rowsCollection;
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

}
