<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Member Class
 *
 * @class   YITH_WCMBS_Activity
 * @package Yithemes
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Activity {

    /**
     * activity name
     *
     * @var string
     * @since 1.0.0
     */
    public $activity;

    /**
     * status
     *
     * @var string
     * @since 1.0.0
     */
    public $status;

    /**
     * timestamp
     *
     * @var string
     * @since 1.0.0
     */
    public $timestamp;

    /**
     * note
     *
     * @var string
     * @since 1.0.0
     */
    public $note;

    /**
     * Constructor
     *
     * @access public
     * @since  1.0.0
     */
    public function __construct( $activity, $status, $timestamp, $note ) {
        $this->activity  = $activity;
        $this->status    = $status;
        $this->timestamp = $timestamp;
        $this->note      = $note;
    }

}