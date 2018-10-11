<?php
/**
 * Register all actions and filters for the plugin
 *
 * @since 1.0.0
 * @package sst-calendar
 * @subpackage sst-calendar/includes
 */

namespace SST\Calendar;

class PostType
{
    protected $pluginName;
    protected $postType;
    protected $prefix;

    public function __construct(string $pluginName, string $postType, string $prefix)
    {
        $this->pluginName = $pluginName;
        $this->postType = $postType;
        $this->prefix = $prefix;
    }

    public function registerPostType()
    {
        $labels = [
            'name' => __('Calendar', 'sst-calendar'),
            'singular_name' => __('Event', 'sst-calendar'),
            'add_new' => _x('Create new', 'Event', 'sst-calendar'),
            'add_new_item' => __('Add new Event', 'sst-calendar'),
            'edit_item' => __('Edit Event', 'sst-calendar'),
            'new_item' => __('New Event', 'sst-calendar'),
            'view_item' => __('View Event', 'sst-calendar'),
            'view_items' => __('View Calendar', 'sst-calendar'),
            'search_items' => __('Search Calendar', 'sst-calendar'),
            'not_found' => __('No events found.', 'sst-calendar'),
            'not_found_in_trash' => __('No events found in Trash', 'sst-calendar'),
            'all_items' => __('All Events', 'sst-calendar'),
            'archives' => __('Calendar Archive', 'sst-calendar'),
            'attributes' => __('Event Attributes', 'sst-calendar'),
            'insert_into_item' => __('Insert into event', 'sst-calendar'),
            'uploaded_to_this_item' => __('Uploaded to this event', 'sst-calendar'),
        ];

        \register_post_type($this->postType, [
            'label' => __('Calendar', 'sst-calendar'),
            'labels' => $labels,
            'description' => __('A calendar for adding events', 'sst-calendar'),
            'public' => true,
            'menu_icon' => 'dashicons-tickets-alt',
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'revisions',
                'author' => false,
                'custom-fields' => false,
                'comments' => false,
                'page-attributes' => false,
                'post-formats' => false,
            ],
            'has_archive' => true,
            'show_in_rest' => false,
            'rewrite' => [
                'slug' => _x('calendar', 'url slug', 'sst-calendar'),
            ],
        ]);
    }

    public function registerMetaboxes()
    {
        $this->createDateFields();
        $this->createInfoFields();
        $this->createLocationFields();
    }

    private function createDateFields()
    {
        $prefix = $this->prefix;
        $cmb = \new_cmb2_box([
            'id' => $this->pluginName . '-dates',
            'title' => __('Dates', 'sst-calendar'),
            'object_types' => [$this->postType],
            'context' => 'side',
            'priority' => 'core',
        ]);

        $startDate = date('Y-m-d', strtotime('+1 day'));
        $endDate = date('Y-m-d', strtotime('+2 days'));

        $cmb->add_field([
            'id' => $prefix . 'start_date',
            'name' => __('Start date', 'sst-calendar'),
            'desc' => __('Date the event starts (required)', 'sst-calendar'),
            'type' => 'text_datetime_timestamp',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'attributes' => [
                'required' => 'true',
                'autocomplete' => 'off',
            ],
            'sanitization_cb' => [$this, 'sanitizeDateFields'],
        ]);

        $cmb->add_field([
            'id' => $prefix . 'end_date',
            'name' => __('End date', 'sst-calendar'),
            'desc' => __('Date the event end', 'sst-calendar'),
            'type' => 'text_datetime_timestamp',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'attributes' => [
                'autocomplete' => 'off',
            ],
            'sanitization_cb' => [$this, 'sanitizeDateFields'],
        ]);
    }

    private function createInfoFields()
    {
        $prefix = $this->prefix;
        $cmb = \new_cmb2_box([
            'id' => $this->pluginName . '-info',
            'title' => __('Information', 'sst-calendar'),
            'object_types' => [$this->postType],
            'priority' => 'core',
            'context' => 'normal',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'organizer',
            'name' => __('Organizer', 'sst-calendar'),
            'desc' => __('Who\'s organizing this event', 'sst-calendar'),
            'type' => 'text',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'email',
            'name' => __('Email', 'sst-calendar'),
            'desc' => __('Email for getting in contact with the organizers', 'sst-calendar'),
            'type' => 'text_email',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'url',
            'name' => __('Homepage', 'sst-calendar'),
            'desc' => __('Url to get more information', 'sst-calendar'),
            'type' => 'text_url',
        ]);
    }

    private function createLocationFields()
    {
        $prefix = $this->prefix;
        $cmb = \new_cmb2_box([
            'id' => $this->pluginName . '-location',
            'title' => __('Location', 'sst-calendar'),
            'object_types' => [$this->postType],
            'priority' => 'core',
            'context' => 'normal',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'location_name',
            'name' => __('Name', 'sst-calendar'),
            'type' => 'text',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'location_address',
            'name' => __('Address', 'sst-calendar'),
            'type' => 'text',
        ]);

        $cmb->add_field([
            'id' => $prefix . 'location_city',
            'name' => __('City', 'sst-calendar'),
            'type' => 'text',
        ]);
    }

    public function sanitizeDateFields(array $value, array $fieldArgs, object $field)
    {
        $currentDate = strtotime(join(' ', $value));

        switch ($fieldArgs['id']) {
            case $this->prefix . 'start_date':
                return $currentDate;

            case $this->prefix . 'end_date':
                $startDateValue = $field->data_to_save[$this->prefix . 'start_date'];
                $startDate = strtotime(join(' ', $startDateValue));

                if (empty($value['date']) || $currentDate < $startDate) {
                    return $startDate;
                }

                return $currentDate;

            default:
                return $currentDate;
        }
    }

    public function manageEventsColumns(array $columns)
    {
        return [
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'organizer' => __('Organizer', 'sst-calendar'),
            'start_date' => __('Start date', 'sst-calendar'),
            'end_date' => __('End date', 'sst-calendar'),
            'date' => $columns['date'],
        ];
    }

    public function renderEventsColumns(string $column, string $postId)
    {
        $prefix = $this->prefix;
        $notDefined = '[' . __('Not defined', 'sst-calendar') . ']';

        switch ($column) {
            case 'organizer':
                $organizer = get_post_meta($postId, $prefix . 'organizer', true);
                echo $organizer ? esc_html($organizer) : $notDefined;
                break;

            case 'start_date':
                $startDate = get_post_meta($postId, $prefix . 'start_date', true);
                echo $startDate ? esc_html(date('Y-m-d H:i', $startDate)) : $notDefined;
                break;

            case 'end_date':
                $endDate = get_post_meta($postId, $prefix . 'end_date', true);
                echo $endDate ? esc_html(date('Y-m-d H:i', $endDate)) : $notDefined;
                break;
        }
    }
}
