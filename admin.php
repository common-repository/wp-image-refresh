<?php
global $wpdb;
$table_name = $wpdb->prefix . "image_refresh";

include_once dirname(__FILE__) . '/functions.php';
$msg = '';

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete') {
    $users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();
	$users = array_map(function($value) {
		return filter_var($value, FILTER_SANITIZE_STRING);
	}, $users);

    if (!empty($users)) {
        $ids = implode(',', $users);

        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id_pk IN ($ids)"));
        $msg = "Record Deleted successfully";

        if (get_option('wp_image_refresh_timestamp')) {
            update_option('wp_image_refresh_timestamp', time());
        } else {
            add_option('wp_image_refresh_timestamp', time());
        }
    } else {
        $msg = "Please select a record to delete";
    }
}

$orderby = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : '';
$sortby = isset($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : '';

if (!$orderby) {
    $orderby = 'asc';
}

if (!$sortby) {
    $sortby = 'slideTitle';
}

$valid_sort = array('slideTitle');
$valid_order = array('desc', 'asc');

if (!in_array($sortby, $valid_sort)) {
    die;
}

if (!in_array($orderby, $valid_order)) {
    die;
}

$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY %s %s", $sortby, $orderby), OBJECT);

if ($sortby === 'slideTitle' && $orderby === 'asc') {
    $slidesort = 'desc';
} else {
    $slidesort = 'asc';
}

if ($sortby === 'slideTitle' && $orderby === 'asc') {
    $titlesort = 'desc';
} else {
    $titlesort = 'asc';
}
?>

<div class="wrap">
    <h2>Images <?php echo '<a class="add-new-h2" href="?page=wp_image_refresh_add">Add</a>'; ?></h2>

    <?php if (isset($_SESSION['msg'])) {
        echo '<div class="updated success" id="message"><p>' . esc_html($_SESSION['msg']) . '</p></div>';
        unset($_SESSION['msg']);
    } ?>

    <?php if (!empty($msg)) {
        echo '<div class="updated" id="message"><p>' . esc_html($msg) . '</p></div>';
    } ?>

    <form method="post" action="">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option selected="selected" value="-1">Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" value="Apply" class="button action" id="doaction" name="">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed users">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="username" class="manage-column column-slideTitle sortable <?php if ($sortby === 'slideTitle' && $orderby === 'asc') {
                        echo 'desc';
                    } else {
                        echo 'desc';
                    } ?>">
                        <a href="<?php echo esc_url(site_url().'/wp-admin/admin.php?page=wp_image_refresh&sort_by=slideTitle&order_by='.$slidesort); ?>">
                            <span>Title</span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th scope="col" id="username" class="manage-column">
                        <span>Image</span>
                        <span class="sorting-indicator"></span>
                    </th>
                </tr>
            </thead>

            <tbody data-wp-lists="list:user" id="the-list">
                <?php $i = 1;
                if (!empty($results)) {
                    foreach ($results as $result) {
                        ?>
                        <tr class="<?php if ($i % 2 !== 0) {
                            echo 'alternate';
                        } ?>" id="user-<?php if (!empty($result->id_pk)) {
                            echo esc_attr($result->id_pk);
                        } ?>">
                            <th class="check-column" scope="row">
                                <label for="cb-select-<?php if (!empty($result->id_pk)) {
                                    echo esc_attr($result->id_pk);
                                } ?>" class="screen-reader-text">Select admin</label>
                                <input type="checkbox" value="<?php if (!empty($result->id_pk)) {
                                    echo esc_attr($result->id_pk);
                                } ?>" class="administrator" id="slider_<?php if (!empty($result->id_pk)) {
                                    echo esc_attr($result->id_pk);
                                } ?>" name="users[]">
                            </th>
                            <td class="username column-username">
                                <strong>
                                    <a href="?page=wp_image_refresh_add&id=<?php echo esc_attr($result->id_pk); ?>">
                                        <?php if (!empty($result->slideTitle)) {
                                            echo esc_html(stripslashes($result->slideTitle));
                                        } ?>
                                    </a>
                                </strong>
                                <br>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="?page=wp_image_refresh_add&id=<?php echo esc_attr($result->id_pk); ?>">Edit</a>
                                    </span>
                                </div>
                            </td>
                            <td class="username column-username">
                                <strong>
                                    <?php if (!empty($result->slideImage)) {
                                        $imgid = get_attachment_id_from_src($result->slideImage);
                                        if (!empty($imgid)) {
                                            echo wp_get_attachment_image($imgid, 'medium', false);
                                        } else {
                                            echo "<a><img width='300' src='" . esc_url($result->slideImage) . "'>";
                                        }
                                    } ?>
                                </strong>
                                <br>
                                <div class="row-actions">
                                    <span class="edit"></span>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="3" align="center" class="username column-username">
                            <strong>No Images Found</strong><br>
                            <div class="row-actions">
                                <span class="edit"></span>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </form>
    <br class="clear">
</div>
