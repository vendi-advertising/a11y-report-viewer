<?php

use Vendi\Shared\utils;
use Webmozart\PathUtil\Path;

define('VENDI_A11Y_FILE', __FILE__);
define('VENDI_A11Y_DIR', __DIR__ );

require_once VENDI_A11Y_DIR . '/vendor/autoload.php';
require_once VENDI_A11Y_DIR . '/src/Page.php';
require_once VENDI_A11Y_DIR . '/src/Rule.php';
require_once VENDI_A11Y_DIR . '/src/TableMaker.php';

//Create a list of all of the files in the reports folder
$files_with_hashes = [];
$files = array_diff(scandir(Path::join(VENDI_A11Y_DIR, 'reports')), ['..', '.']);
foreach($files as $idx => $name ){
    $files_with_hashes[\md5($name)] = $name;
}

//See if the user provided a specific report to view
$selected_file_hash = utils::get_get_value('file_hash');
$selected_tag = utils::get_get_value('tag');
$table_maker = null;
if($selected_file_hash && array_key_exists($selected_file_hash, $files_with_hashes)){
    $report_file = Path::join(VENDI_A11Y_DIR, 'reports', $files_with_hashes[$selected_file_hash]);
    $table_maker = TableMaker::create_from_file($report_file);
}

?><!doctype html>
<html lang="en">
<head>
<title>Report</title>
<link rel="stylesheet" href="./css/app.css" />
</head>
<body>
<form method="get">
    <select name="file_hash">
        <option>Select one</option>
        <?php
        foreach($files_with_hashes as $hash => $name ){
            echo sprintf(
                            '<option value="%1$s" %3$s>%2$s</option>',
                            $hash,
                            \htmlspecialchars($name),
                            $selected_file_hash === $hash ? 'selected' : ''
                    );
        }
        ?>
    </select>
    &nbsp;
    <?php
        if($table_maker){
            echo '<select name="tag">';
            echo '<option>Select one</option>';
            foreach($table_maker->get_tags() as $tag){
                echo sprintf(
                                '<option value="%1$s" %2$s>%1$s</option>',
                                htmlspecialchars($tag),
                                $selected_tag === $tag ? 'selected' : ''
                        );
            }
            echo '</select>';
        }
    ?>
    <input type="submit" value="Select file" />
</form>
<?php
if($table_maker){
    echo $table_maker->get_table();
}
?>
<script type="text/javascript" src="./js/app.js"></script>
</body>
</html>
