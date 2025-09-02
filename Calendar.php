<?php
/*
Plugin Name: Calendar Timer
Plugin URI: https://github.com/konradxmalinowski/calendar-wp-extension
Description: Countdown to the nearest event in Hurry Timer style. Multiple countdowns support and automatic year change.
Version: 1.5
Author: Konrad Malinowski
Author URI: https://github.com/konradxmalinowski
License: GPL2

Usage:
- [calendar_countdown] - shows first upcoming event
- [calendar_countdown offset="0"] - first event
- [calendar_countdown offset="1"] - second event
- [calendar_countdown offset="2"] - third event
- All shortcodes without offset show the same event
*/

// Register custom post type
add_action('init', 'calendar_create_post_type');
function calendar_create_post_type()
{
	register_post_type('calendar_event', array(
		'labels' => array(
			'name' => __('Wydarzenia'),
			'singular_name' => __('Wydarzenie')
		),
		'public' => true,
		'has_archive' => true,
		'supports' => array('title', 'editor'),
		'show_in_rest' => true
	));
}

// Add date/time metabox
add_action('add_meta_boxes', 'calendar_add_custom_box');
function calendar_add_custom_box()
{
	add_meta_box(
		'calendar_event_date',
		'Data i godzina wydarzenia',
		'calendar_event_date_box_html',
		'calendar_event',
		'side',
		'default'
	);
}

function calendar_event_date_box_html($post)
{
	wp_nonce_field('update-post_' . $post->ID, '_wpnonce');

	$value = get_post_meta($post->ID, '_calendar_event_datetime', true);
	echo '<label for="calendar_event_datetime">Data i godzina:</label>';
	echo '<input type="datetime-local" id="calendar_event_datetime" name="calendar_event_datetime" value="' . esc_attr($value) . '" style="width:100%"/>';
}

// Save event date
add_action('save_post', 'calendar_save_postdata');
function calendar_save_postdata($post_id)
{
	if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (get_post_type($post_id) !== 'calendar_event') {
		return;
	}

	if (array_key_exists('calendar_event_datetime', $_POST)) {
		$datetime = sanitize_text_field($_POST['calendar_event_datetime']);

		if (!empty($datetime) && strtotime($datetime) !== false) {
			update_post_meta($post_id, '_calendar_event_datetime', $datetime);
		}
	}
}

// Update event years if passed
function calendar_update_all_event_years()
{
	$events = get_posts(array('post_type' => 'calendar_event', 'numberposts' => -1));
	$current_year = date('Y');
	foreach ($events as $event) {
		$datetime = get_post_meta($event->ID, '_calendar_event_datetime', true);
		if ($datetime) {
			$event_year = date('Y', strtotime($datetime));
			if ($event_year < $current_year) {
				$new_datetime = date('Y-m-d H:i:s', strtotime($datetime . ' +' . ($current_year - $event_year) . ' years'));
				update_post_meta($event->ID, '_calendar_event_datetime', $new_datetime);
			}
		}
	}
}

// Countdown shortcode
add_shortcode('calendar_countdown', 'calendar_countdown_shortcode');
function calendar_countdown_shortcode($atts)
{
	$atts = shortcode_atts(array(
		'offset' => 0
	), $atts);

	$today = current_time('Y-m-d H:i:s');

	$args = array(
		'post_type' => 'calendar_event',
		'meta_key' => '_calendar_event_datetime',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => 1,
		'offset' => $atts['offset'],
		'meta_query' => array(
			array(
				'key' => '_calendar_event_datetime',
				'value' => $today,
				'compare' => '>=',
				'type' => 'DATETIME'
			)
		)
	);

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		$query->the_post();
		$datetime = get_post_meta(get_the_ID(), '_calendar_event_datetime', true);
		$datetime = str_replace(' ', 'T', $datetime);
		$title = get_the_title();
		$event_id = get_the_ID();
		wp_reset_postdata();

		$uid = uniqid('calendar_');

		return '<div id="' . esc_attr($uid) . '" class="calendar-countdown" data-datetime="' . esc_attr($datetime) . '" data-event-id="' . esc_attr($event_id) . '" style="text-align:center; font-family:Arial,sans-serif; max-width:400px; margin:auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
			<h3 style="margin-bottom:15px; font-size:24px; color:#2a2b4a;">' . esc_html($title) . '</h3>
			<div class="countdown-timer" style="font-size:28px; font-weight:bold; color:#2a2b4a;"></div>
		</div>';
	} else {
		return '<p>Brak nadchodzących wydarzeń.</p>';
	}
}

// JavaScript for countdown functionality
add_action('wp_footer', 'calendar_countdown_script');
function calendar_countdown_script()
{ ?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const countdowns = document.querySelectorAll('.calendar-countdown');

			countdowns.forEach(function (container) {
				let countdownDate = new Date(container.getAttribute('data-datetime')).getTime();
				let timerElem = container.querySelector('.countdown-timer');
				let currentEventId = container.getAttribute('data-event-id');

				function loadNextEvent() {
					let params = new URLSearchParams();
					params.append('exclude', currentEventId);
					params.append('_wpnonce', '<?php echo wp_create_nonce('calendar_countdown_nonce'); ?>');

					fetch("<?php echo esc_url(admin_url('admin-ajax.php?action=get_next_event')); ?>&" + params)
						.then(res => res.json())
						.then(data => {
							if (data.success) {
								container.querySelector("h3").innerText = data.title;
								container.querySelector("h3").style.color = "#2a2b4a";
								countdownDate = new Date(data.datetime.replace(' ', 'T')).getTime();
								currentEventId = data.id;
								updateCountdown();
							} else {
								timerElem.innerHTML = "Brak nadchodzących wydarzeń";
							}
						})
						.catch(err => {
							console.error('AJAX error:', err);
							timerElem.innerHTML = "Błąd ładowania";
							setTimeout(loadNextEvent, 5000);
						});
				}

				function updateCountdown() {
					let now = new Date().getTime();
					let distance = countdownDate - now;

					if (distance < 0) {
						loadNextEvent();
						return;
					} else if (distance < 60000) {
						let minutes = Math.floor(distance / (1000 * 60));
						let seconds = Math.floor((distance % (1000 * 60)) / 1000);
						timerElem.innerHTML = minutes + "m " + seconds + "s";
					} else {
						let days = Math.floor(distance / (1000 * 60 * 60 * 24));
						let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
						let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
						let seconds = Math.floor((distance % (1000 * 60)) / 1000);
						timerElem.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s";
					}
				}

				updateCountdown();
				setInterval(updateCountdown, 1000);
			});
		});
	</script>
<?php }

// AJAX: get next event
add_action('wp_ajax_get_next_event', 'calendar_get_next_event');
add_action('wp_ajax_nopriv_get_next_event', 'calendar_get_next_event');
function calendar_get_next_event()
{
	if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'calendar_countdown_nonce')) {
		wp_send_json_error('Invalid nonce');
	}

	$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$rate_limit_key = 'calendar_rate_limit_' . md5($user_ip);
	$rate_limit_count = get_transient($rate_limit_key);

	if ($rate_limit_count === false) {
		set_transient($rate_limit_key, 1, 60);
	} elseif ($rate_limit_count >= 10) {
		wp_send_json_error('Rate limit exceeded');
	} else {
		set_transient($rate_limit_key, $rate_limit_count + 1, 60);
	}

	$today = current_time('Y-m-d H:i:s');
	$exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;

	if ($exclude < 0) {
		$exclude = 0;
	}

	$args = array(
		'post_type' => 'calendar_event',
		'meta_key' => '_calendar_event_datetime',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => 1,
		'post__not_in' => $exclude ? array($exclude) : array(),
		'meta_query' => array(
			array(
				'key' => '_calendar_event_datetime',
				'value' => $today,
				'compare' => '>=',
				'type' => 'DATETIME'
			)
		)
	);

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		$query->the_post();
		$datetime = get_post_meta(get_the_ID(), '_calendar_event_datetime', true);
		$datetime = str_replace(' ', 'T', $datetime);
		$title = get_the_title();
		$id = get_the_ID();
		wp_reset_postdata();

		wp_send_json(array(
			'success' => true,
			'datetime' => esc_attr($datetime),
			'title' => esc_html($title),
			'id' => intval($id)
		));
	} else {
		calendar_update_all_event_years();
		$args['post__not_in'] = array();
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			$query->the_post();
			$datetime = get_post_meta(get_the_ID(), '_calendar_event_datetime', true);
			$datetime = str_replace(' ', 'T', $datetime);
			$title = get_the_title();
			$id = get_the_ID();
			wp_reset_postdata();

			wp_send_json(array(
				'success' => true,
				'datetime' => esc_attr($datetime),
				'title' => esc_html($title),
				'id' => intval($id)
			));
		} else {
			wp_send_json(array('success' => false));
		}
	}
}
