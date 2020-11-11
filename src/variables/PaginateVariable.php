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
     * @var Paginator|null
     */
    public $paginator;

    /**
     * @inheritdoc
     */
    public static function create(Paginator $paginator): Paginate
    {
        $self = parent::create($paginator);
        $self->paginator = $paginator;

        return $self;
    }

    /**
     * Returns the paginator results.
     *
     * @return array
     */
    public function getPageResults(): array
    {
        if ($this->paginator === null) {
            return [];
        }

        return $this->paginator->getPageResults();
    }

    /**
     * Returns a range of page numbers.
     *
     * @param int $start
     * @param int $end
     * @return int[]
     */
    public function getRange(int $start, int $end): array
    {
        return array_keys($this->getRangeUrls($start, $end));
    }

    /**
     * Returns a dynamic range of page numbers that surround (and include) the current page.
     *
     * @param int $max The maximum number of links to return
     * @return int[]
     */
    public function getDynamicRange($max = 10)
    {
        return array_keys($this->getDynamicRangeUrls($max));
    }
}
