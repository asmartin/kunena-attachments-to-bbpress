<?php

//Standalone script
//Execute only after you have imported kunena into bbPress

require( 'wp-load.php' );
require( 'wp-admin/includes/image.php' );

// Database Connection
$host="my_joomla_database_host";
$uname="myuser";
$pass='mypassword';
$database = "mydatabase";
$site_url = 'http://myjoomlasiteurl.com';

$connection = new mysqli($host, $uname, $pass, $database);

if (!$connection) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
echo "Connection established...<br/>";

// Find out how many attachments there are to import
$result = $connection->query("SELECT * FROM `j25_kunena_attachments` ORDER BY `id`");
$num = $result->num_rows;

// get rows to operate on
$end = 10;
if (!isset($_GET['end'])) {
	echo "The best way to use this script is to specify the end row from the<br/>";
	echo "source database (e.g. start by importing rows 0-50, then 0-100, then 0-150, etc.<br/>";
	echo "By splitting it up like this, you can avoid PHP timeout errors that can occur when<br/>";
	echo "attempting to import too much data at once.<br/><br/>";

	echo "To specify the end row, just add it at the end of the URL like this:<br/>";
	echo "<a href='http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?end=50'>http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?end=50</a><br/>";
	echo "Keep increasing this number as the files are successfully imported. Finally, you can run<br/>";
	echo "all of them at once (there were $num found in the $database database) like this to confirm<br/>";
	echo "that they have all been imported:<br/>";
	echo "<a href='http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?end=$num'>http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?end=$num</a><br/>";
	echo "<br/>";
	echo "Since no end value was specified this time, we will end at $end for this run.<br/>";
} else {
	$end = $_GET['end'];
}

// Fetch Record from Database
echo "Fetching rows 0 to $end (of total $num rows found in $database database):<br/>";
$output = "";
$result = $connection->query("SELECT * FROM `j25_kunena_attachments` ORDER BY `id` LIMIT 0,$end");
$count = 0;

// Get Records from the table
while ($row = $result->fetch_assoc()) {

	$old_filename = $site_url.'/'.$row['folder'].'/'.$row['filename'];
	$old_filename = str_replace(" ", "%20", $old_filename); // for filenames with spaces

	//Customize as needed
	$date = strftime('%Y/%m', strtotime('now'));
	$uploads = wp_upload_dir($date);
	$new_upload_dir = $uploads['path'];
	$new_full_filename = $new_upload_dir.'/'.str_replace(" ", "", $row['filename']);

	echo "attempting to import $old_filename...";
	if (is_file($new_full_filename)) {
		echo "<font color='blue'><b>ALREADY IMPORTED</b></font><br/>";
	} else {
		$f = file_put_contents($new_full_filename, fopen($old_filename, 'r'));
		if ($f) {

			$parent_args = array(
				'post_type' => array('topic', 'reply'),
				'meta_key' => '_bbp_post_id',
				'meta_value' => $row['mesid']
			);
			$parent_query = new WP_Query($parent_args);
			$parent_query->get_posts();
			if($parent_query->have_posts()) {
				$parent_query->the_post();
				$attachment_data = array(
					'post_mime_type'	=> $row['filetype'],
					'post_title'		=> $row['filename'],
					'post_status'		=> 'inherit',
					'post_content'		=> '',
				);

				$attach_id = wp_insert_attachment($attachment_data, $new_full_filename, get_the_ID());
				if($attach_id) {
					update_post_meta($attach_id, '_bbp_attachment', 1);
					wp_generate_attachment_metadata($attach_id, $new_full_filename);
				}
			}
			wp_reset_postdata();
			echo "<font color='green'><b>OK!</b></font><br/>";
		} else {
			echo "<b><font color='red'>FAILED!</font></b><br/>";
		}
	}

	$count++;
}

echo "Processed $count attachments<br/>";

$connection->close();
exit;

?>
