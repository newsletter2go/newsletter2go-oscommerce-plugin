<<<<<<< HEAD:plugins/osCommerce/admin/newsletter2gohistory.php
<?php
require('includes/application_top.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['deleteAll'])) {
        tep_db_query('TRUNCATE newsletter2go_log');
    } else if (isset($_POST['deleteSelected']) && isset($_POST['deleteSelected'])) {
        $checks = (isset($_POST['checkBoxGroup']) ? $_POST['checkBoxGroup'] : '');
        if (tep_not_null($checks)) {
            foreach ($checks as $check) {
                tep_db_query('DELETE FROM newsletter2go_log WHERE id='. $check); 
            }
        }
    }
}

$limit = 50;
$offset = 0;
$page = (isset($_GET['page']) ? $_GET['page'] : 0);
if (tep_not_null($page)) {
    $offset = $page * 50;
}

$totalQuery = tep_db_query('SELECT count(*) total FROM newsletter2go_log');
$total = tep_db_fetch_array($totalQuery);
$results = $total['total'];
if ($results) {
    $pages = (int)($results / 50);
}

$logsQuery = tep_db_query('SELECT * FROM newsletter2go_log ORDER BY id DESC LIMIT ' . $offset . ', ' . $limit);
$logs = array();
while ($log = tep_db_fetch_array($logsQuery)) {
    $logs[] = $log;
}

require(DIR_WS_INCLUDES . 'template_top.php');
?>
<form action="<?php echo FILENAME_NEWSLETTER2GO_HISTORY; ?>" method="POST" >
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
            <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                        <td>
                            <button name="deleteSelected" type="submit" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" 
                                    role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-key"></span><span class="ui-button-text"><?php echo BUTTON_DELETE_SELECTED; ?></span></button>
                        </td>
                        <td>
                            <button name="deleteAll" type="submit" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" 
                                    role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-key"></span><span class="ui-button-text"><?php echo BUTTON_DELETE_ALL; ?></span></button>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top">
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                <tr class="dataTableHeadingRow">
                                    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
                                    <td class="dataTableHeadingContent">&nbsp;</td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_ID; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_TIME; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_APIUSER; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_INFO; ?></td>
                                </tr>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="dataTableRow">
                                        <td></td>
                                        <td><input type="checkbox" name="checkBoxGroup[]" value="<?php echo $log['id']; ?>" /></td>
                                        <td><?php echo $log['id']; ?></td>
                                        <td><?php echo $log['created_on']; ?></td>
                                        <td><?php echo $log['user_id']; ?></td>
                                        <td><?php echo $log['info']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php if ($page) { ?>
                                <a href="<?php echo tep_href_link(FILENAME_NEWSLETTER2GO_HISTORY, 'page=' . ($page - 1)) ?>" class="splitPageLink">&lt;&lt;</a>
                            <?php }
                            if ($page < $pages) {
                                ?>
                                <a href="<?php echo tep_href_link(FILENAME_NEWSLETTER2GO_HISTORY, 'page=' . ($page + 1)) ?>" class="splitPageLink">&gt;&gt;</a>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
<?php
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
=======
<?php
require('includes/application_top.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['deleteAll'])) {
        tep_db_query('TRUNCATE newsletter2go_log');
    } else if (isset($_POST['deleteSelected']) && isset($_POST['deleteSelected'])) {
        $checks = (isset($_POST['checkBoxGroup']) ? $_POST['checkBoxGroup'] : '');
        if (tep_not_null($checks)) {
            foreach ($checks as $check) {
                tep_db_query('DELETE FROM newsletter2go_log WHERE id='. $check); 
            }
        }
    }
}

$limit = 50;
$offset = 0;
$page = (isset($_GET['page']) ? $_GET['page'] : 0);
if (tep_not_null($page)) {
    $offset = $page * 50;
}

$totalQuery = tep_db_query('SELECT count(*) total FROM newsletter2go_log');
$total = tep_db_fetch_array($totalQuery);
$results = $total['total'];
if ($results) {
    $pages = (int)($results / 50);
}

$logsQuery = tep_db_query('SELECT * FROM newsletter2go_log ORDER BY id DESC LIMIT ' . $offset . ', ' . $limit);
$logs = array();
while ($log = tep_db_fetch_array($logsQuery)) {
    $logs[] = $log;
}

require(DIR_WS_INCLUDES . 'template_top.php');
?>
<form action="<?php echo FILENAME_NEWSLETTER2GO_HISTORY; ?>" method="POST" >
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
            <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                        <td>
                            <button name="deleteSelected" type="submit" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" 
                                    role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-key"></span><span class="ui-button-text">Delete Selected</span></button>
                        </td>
                        <td>
                            <button name="deleteAll" type="submit" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary ui-priority-secondary" 
                                    role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-key"></span><span class="ui-button-text">Delete All</span></button>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top">
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                <tr class="dataTableHeadingRow">
                                    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
                                    <td class="dataTableHeadingContent">&nbsp;</td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_ID; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_TIME; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_APIUSER; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_INFO; ?></td>
                                </tr>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="dataTableRow">
                                        <td></td>
                                        <td><input type="checkbox" name="checkBoxGroup[]" value="<?php echo $log['id']; ?>" /></td>
                                        <td><?php echo $log['id']; ?></td>
                                        <td><?php echo $log['created_on']; ?></td>
                                        <td><?php echo $log['user_id']; ?></td>
                                        <td><?php echo $log['info']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php if ($page) { ?>
                                <a href="<?php echo tep_href_link(FILENAME_NEWSLETTER2GO_HISTORY, 'page=' . ($page - 1)) ?>" class="splitPageLink">&lt;&lt;</a>
                            <?php }
                            if ($page < $pages) {
                                ?>
                                <a href="<?php echo tep_href_link(FILENAME_NEWSLETTER2GO_HISTORY, 'page=' . ($page + 1)) ?>" class="splitPageLink">&gt;&gt;</a>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
<?php
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
>>>>>>> master:plugins/old/osCommerce/admin/newsletter2gohistory.php
