<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\variables;

use craft\db\Paginator;
use craft\web\twig\variables\Paginate;
use yii\base\BaseObject;

/**
 * This class is based on the Paginate class in Craft.
 * @see Paginate::class
 */
class PaginateVariable extends BaseObject
{
    /**
     * Creates a new instance based on a Paginator object
     *
     * @param Paginator $paginator
     * @return PaginateVariable
     * @see Paginate::create()
     */
    public static function create(Paginator $paginator): self
    {
        $pageResults = $paginator->getPageResults();
        $pageOffset = $paginator->getPageOffset();

        return new static([
            'pageResults' => $pageResults,
            'first' => $pageOffset + 1,
            'last' => $pageOffset + count($pageResults),
            'total' => $paginator->getTotalResults(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getTotalPages(),
        ]);
    }

    /**
     * @var array
     */
    public $pageResults = [];

    /**
     * @var int
     */
    public $first;

    /**
     * @var int
     */
    public $last;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var int
     */
    public $currentPage;

    /**
     * @var int
     */
    public $totalPages = 0;

    /**
     * Returns a range of page numbers.
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
     * Returns a dynamic range of page numbers that surround (and include) the current page.
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
