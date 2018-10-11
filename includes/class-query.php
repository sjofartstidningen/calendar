<?php
/**
 * Modifies the query on the archive page for events
 *
 * @since 1.0.0
 * @package sst-calendar
 * @subpackage sst-calendar/includes
 */

namespace SST\Calendar;

class Query
{
    protected $postType;
    protected $prefix;

    public function __construct(string $postType, string $prefix)
    {
        $this->postType = $postType;
        $this->prefix = $prefix;
    }

    public function modifyMainQuery(\WP_Query $query)
    {
        $overrides = [
            'post_type' => [$this->postType],
            'order_by' => 'meta_value',
            'order' => 'ASC',
            'meta_key' => $this->prefix . 'end_date',
            'meta_compare' => '>',
            'meta_value' => date('U'),
        ];

        if (!is_admin() && is_post_type_archive($this->postType)) {
            foreach ($overrides as $key => $value) {
                $query->set($key, $value);
            }
        }
    }
}
