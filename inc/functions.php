<?php 
/*
 *
 * Create Countdown Shortcode
 * 
 */
function if_statement($atts, $content) {
    if (empty($atts)) return '';

    extract( shortcode_atts(
		array(
            'expires' => '',
		),
		$atts
	) );
        
    $timer = '<div id="clockdiv" data-time="'.$expires.'">
        <span class="days bigger"></span><span class="note">days</span>
        <span class="hours bigger"></span><span class="note">hrs</span>
        <span class="minutes bigger"></span><span class="note">min</span>
        <span class="seconds bigger"></span><span class="note">s</span>
    </div>';
    
    $else = '[else]';
    if (strpos($content, $else) !== false) {
        $options = explode($else, $content, 2);
        $if = $timer.$options[0];
        $else = $options[1];
    } else {
        $if = $content;
        $else = "";
    }
        
    $condition = is_expired($expires);

    return do_shortcode($condition ? $if : $else);
}
add_shortcode('if_time_sensitive', 'if_statement');

/*
 *
 * Check if time has passed
 * 
 */
function is_expired($deadline) {
    $now = current_time('timestamp');
    $time = strtotime($deadline);
    $time_left = $time - $now;
    if($time_left > 0) {
        return true;
    } else {
        return false;
    }
}
?>