<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use craft\db\Paginator;
use craft\web\twig\variables\Paginate;

class PaginateVariable extends Paginate
{
    /**
     * @param Paginator $paginator
     * @return PaginateVariable
     */
    public static function create(Paginator $paginator): Paginate
    {
        $paginateVariable = parent::create($paginator);
        $paginateVariable->pageResults = $paginator->getPageResults();

        return $paginateVariable;
    }

    /**
     * @var array
     */
    public $pageResults = [];

    /**
     * Returns a range of page numbers as an array.
     *
     * @param int $start
     * @param int $end
     * @return int[]
     * @see Paginate::getRangeUrls()
     */
    public function getRange(int $start, int $end): array
    {
        if ($start < 1) {
            $start = 1;
        }

        if ($end > $this->totalPages) {
            $end = $this->totalPages;
        }

        $range = [];

        for ($page = $start; $page <= $end; $page++) {
            $range[] = $page;
        }

        return $range;
    }

    /**
     * Returns a dynamic range of page numbers that surround (and include) the current page as an array.
     *
     * @param int $max The maximum number of links to return
     * @return int[]
     * @see Paginate::getDynamicRangeUrls()
     */
    public function getDynamicRange($max = 10)
    {
        $start = max(1, $this->currentPage - floor($max / 2));
        $end = min($this->totalPages, $start + $max - 1);

        if ($end - $start < $max) {
            $start = max(1, $end - $max + 1);
        }

        return $this->getRange($start, $end);
    }
}
