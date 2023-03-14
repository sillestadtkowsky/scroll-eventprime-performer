<?php
/**
* Plugin Name: Scroll Widget for EventPrime
* Plugin URI: https://osowsky-webdesign.de/plugins/scroll-widget-for-eventprime/
* Description: This plugin generates links from posts eventprime with a specific post_type and displays them in a scrolling widget. IMPORTANT! This is clearly NOT an official plugin from EventTime
* Version: 1.3.0
* Requires at least: 5.8.0
* Requires PHP:      8.0
* Author: Silvio Osowsky
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Author URI: https://osowsky-webdesign.de/
*/

// Register plugin settings page
add_action('admin_menu', 'sw_so_scroll_plugin_settings');

function sw_so_scroll_plugin_settings() {
  add_options_page(
    'Scroll Widget for EventPrime', // Page title
    'Scroll Widget for EventPrime', // Menu title
    'manage_options', // Capability
    'sep-scroll-plugin-settings', // Menu slug
    'sw_so_scroll_plugin_options' // Callback function
  );
}

function sw_so_scroll_plugin_options() {
  // Get the saved values for the shortcode attributes
  $options = sw_so_get_option('sw_so_scroll_plugin_options');
  $label = isset($options['label']) ? wp_strip_all_tags($options['label']) : 'Featured';
  $posttype = isset($options['posttype']) ? wp_strip_all_tags($options['posttype']) : 'em_performer';
  $timeOut = isset($options['timeOut']) ? wp_strip_all_tags($options['timeOut']) : '500';
  $interVal = isset($options['interVal']) ? wp_strip_all_tags($options['interVal']) : '4000';

  $posttypes = array(
    'em_performer' => 'Performers',
    'em_event' => 'Events',
    'post' => 'Posts'
  );
  
  ?>
    <div class="wrap">
      <h1>Scroll Widget for EventPrime</h1>
      <p><?php _e( 'This plugin generates links from posts of the official EventPrime-Plugin with a specific post_type and displays them in a scrolling widget.', 'sw_so_scroll-widget-for-eventprime' ); ?></p>
      <h2>ShortCode</h2>
      <p><?php _e( '<code>[sw_so_widget]</code>', 'sw_so_scroll-widget-for-eventprime' ); ?></p>
      <form method="post" action="options.php">
        <?php settings_fields('sw_so_scroll_plugin_options_group'); ?>
        <?php do_settings_sections('sw_so_scroll_plugin_settings'); ?>
        <h2><?php _e( 'Shortcode Steuerung:', 'sw_so_scroll-widget-for-eventprime' ); ?></h2>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label for="label"><?php _e( 'Label', 'sw_so_scroll-widget-for-eventprime' ); ?></label>
              </th>
              <td>
                <input type="text" id="label" name="sw_so_scroll_plugin_options[label]" value="<?php echo esc_attr($label); ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="posttype"><?php _e( 'PostType', 'sw_so_scroll-widget-for-eventprime' ); ?></label>
              </th>
              <td>
                <select id="posttype" name="sw_so_scroll_plugin_options[posttype]">
                  <?php foreach ($posttypes as $value => $name) { ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($posttype, $value); ?>><?php echo esc_html($name); ?></option>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="timeOut"><?php _e( 'TimeOut', 'sw_so_scroll-widget-for-eventprime' ); ?> (ms)</label>
              </th>
              <td>
                <input type="text" id="timeOut" name="sw_so_scroll_plugin_options[timeOut]" value="<?php echo esc_attr($timeOut); ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="interVal"><?php _e( 'Interval', 'sw_so_scroll-widget-for-eventprime' ); ?> (ms)</label>
              </th>
              <td>
                <input type="text" id="interVal" name="sw_so_scroll_plugin_options[interVal]" value="<?php echo esc_attr($interVal); ?>">
              </td>
            </tr>
          </tbody>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php
}

// Register widget
add_action( 'widgets_init', function() {
  register_widget( 'sw_so_Scroll_Eventprime_Widget' );
});

function sw_so_scroll_plugin_register_options() {
  register_setting( 'sw_so_scroll_plugin_options_group', 'sw_so_scroll_plugin_options' );
}
add_action( 'admin_init', 'sw_so_scroll_plugin_register_options' );

// Create shortcode
function sw_so_widget_shortcode($atts) {
  // Get the saved values for the shortcode attributes
  $options = sw_so_get_option('sw_so_scroll_plugin_options');
  $atts = shortcode_atts( array(
    'label' => isset($options['label']) ? wp_strip_all_tags($options['label']) : 'Featured',
    'type' => isset($options['posttype']) ? wp_strip_all_tags($options['posttype']) : 'em_performer',
    'timeOut' => isset($options['timeOut']) ? wp_strip_all_tags($options['timeOut']) : '500',
    'interVal' => isset($options['interVal']) ? wp_strip_all_tags($options['interVal']) : '4000',
  ), $atts, 'sw_so_widget' );

  $links = sw_so_generate_links($atts['type']);

  // Add featured text container
  $output = "<div class='sw_so_widget-outer-container'>";
  $output .= "<div class='sw_so_widget-featured'><span>{$atts['label']}</span></div>";

  $output .= '<div class="sw_so_widget-container">';
  $output .= '<div class="sw_so_widget-links">';
  foreach ( $links as $link ) {
    $output .= '<div class="sw_so_widget-link">' . $link . '</div>';
  }
  $output .= '</div>';
  $output .= '</div>';
  $output .= '</div>';

  $output .= "<style>
      .sw_so_widget-outer-container{
          padding: 15px;
      }
      .sw_so_widget-link {
        margin-right: 20px;
        height: 30px;
        line-height: 30px;
        width: auto;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: none;
        position: absolute;
        bottom: 0;
        animation-duration: 1s;
        animation-timing-function: ease-in-out;
      }

      .sw_so_widget-link:first-child {
        display: block;
      }

      @keyframes scroll-link-in {
        0% {
          transform: translateY(30px);
        }
        50% {
          transform: translateY(0);
        }
        100% {
          transform: translateY(0);
        }
      }

      @keyframes scroll-link-out {
        0% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(0);
        }
        100% {
          transform: translateY(-30px);
        }
      }

      .sw_so_widget-container {
        height: 30px;
        overflow: hidden;
        position: relative;
        padding-left: 20px;
        vertical-align: middle;
      }

      .sw_so_widget-featured {
        position: relative;
        display: inline-block;
        padding: 0 15px;
        margin-right: 20px;
        color: white;
        background-color: #1BB6D8;
        float: left;
      }

      .sw_so_widget-featured:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        right: -15px;
        width: 0;
        border-bottom: 30px solid transparent;
        border-left: 15px solid #1BB6D8;
      }
  </style>";

  $output .= "<script>
      var scrollEventPrimePerformer = function() {
        var links = document.querySelectorAll('.sw_so_widget-container .sw_so_widget-link');

        if (links.length < 2) {
          return;
        }

        var linkHeight = links[0].clientHeight;
        var linkIndex = 0;

        setInterval(function() {
          var firstLink = links[linkIndex];
          var nextIndex = (linkIndex + 1) % links.length;
          var nextLink = links[nextIndex];

          firstLink.style.animation = 'scroll-link-out 1s linear forwards';

          setTimeout(function() {
            firstLink.style.display = 'none';
            firstLink.style.animation = '';

            nextLink.style.display = 'block';
            nextLink.style.animation = 'scroll-link-in 1s linear forwards';

            nextLink.parentNode.appendChild(firstLink);

            linkIndex = nextIndex;
          }, {$atts['timeOut']});

        }, {$atts['interVal']});
      };

      document.addEventListener('DOMContentLoaded', scrollEventPrimePerformer);
  </script>";

  return $output;
}

function sw_so_generate_links($postType) {
  $args = array(
    'post_type' => $postType,
    'fields' => 'ids',
    'posts_per_page' => -1,
  );

  $query = new WP_Query( $args );
  $links = array();

  while ( $query->have_posts() ) {
    $query->the_post();
    $link = '<div class="sw_so_widget-link-wrapper">';
    if($postType == 'em_performer'){
      $link .= '<a href="' . site_url( '/performer/?performer=' . get_the_ID() ) . '">' . get_the_title() . '</a>';
    }else if($postType == 'em_event'){
      $link .= '<a href="' . site_url( '/events/?event=' . get_the_ID() ) . '">' . get_the_title() . '</a>';
    } else{
      $link = '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
    }
    $link .= '</div>';
    $links[] = $link;
  }

  wp_reset_postdata();

  return $links;
}

class sw_so_Scroll_Eventprime_Widget extends WP_Widget {
  private $widget_id;

  // Widget-Konstruktor
  function __construct() {
    parent::__construct(
      'sw_so_scroll_event_prime_widget', // Widget-Identifikator
      __( 'Scroll EventPrime Performer', 'text_domain' ), // Widget-Name
      array( 'description' => __( 'Displays links from posts with a specific post_type from EventPrim in a scrolling widget.', 'text_domain' ), // Widget-Beschreibung
      )
    );
    $this->widget_id = $this->id_base . '-' . $this->number;
  }

  // Ausgabe des Widgets
  public function widget( $args, $instance ) {
    $links = sw_so_generate_links();

    if ( empty( $links ) ) {
      return;
    }

    $output = '';

    if ( count( $links ) > 1 ) {
      $output .= '<div id="' . $this->widget_id . '" class="sw_so_widget-container">';
      $output .= '<div class="sw_so_widget-links">';

      foreach ( $links as $link ) {
        $output .= '<div class="sw_so_widget-link">' . $link . '</div>';
      }

      $output .= '</div>';
      $output .= '</div>';
    } else {
      $output .= '<div class="sw_so_widget-link">' . $links[0] . '</div>';
    }

    echo $args['before_widget'] . $output . $args['after_widget'];

    echo $args['after_widget'];
  }
}

// ShortCode
add_shortcode( 'sw_for_eventprime', 'sw_so_widget_shortcode' );