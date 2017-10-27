<?php
require('includes/application_top.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])) {
    $email = tep_db_prepare_input($_POST['email']);
    $username = tep_db_prepare_input($_POST['username']);
    $password = tep_db_prepare_input($_POST['password']);
    $name = tep_db_prepare_input($_POST['name']);
    $lastname = tep_db_prepare_input($_POST['lastname']);

    $usersQuery = tep_db_query("SELECT id FROM newsletter2go_user WHERE username = '$username' OR email = '$email'");
    if (!tep_db_num_rows($usersQuery)) {
        $usersQuery = tep_db_query("INSERT INTO newsletter2go_user VALUES(NULL, '$name', '$lastname', '$username', '$password', NULL, 0, '$email')");
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : '';
$id = tep_db_prepare_input($id);
$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (tep_not_null($id)) {
    if (tep_not_null($action)) {
        $usersQuery = tep_db_query('SELECT * FROM newsletter2go_user WHERE id=' . $id);
        $user = tep_db_fetch_array($usersQuery);
        switch ($action) {
            case 'Enable':
                if (!$user['apikey']) {
                    $random = $user['username'] . $user['password'] . time();
                    $random = md5($random);
                }
                tep_db_query('UPDATE newsletter2go_user SET enabled=1' . (isset($random) ? ', apikey="' . $random . '"' : '') . ' WHERE id=' . $id);
                break;
            case 'Generate':
                $random = $user['username'] . $user['password'] . time();
                $random = md5($random);
                tep_db_query('UPDATE newsletter2go_user SET apikey="' . $random . '" WHERE id=' . $id);
                break;
            case 'Disable':
                tep_db_query('UPDATE newsletter2go_user SET enabled=0 WHERE id=' . $id);
                break;
            case 'Delete':
                tep_db_query('DELETE FROM newsletter2go_user WHERE id=' . $id);
                break;
        }
    }
}

$usersQuery = tep_db_query('SELECT * FROM newsletter2go_user');
$users = array();
while ($user = tep_db_fetch_array($usersQuery)) {
    $users['' . $user['id']] = $user;
}

require(DIR_WS_INCLUDES . 'template_top.php');
if ($action === 'Create') {
    ?>
    <div style="text-align: center">
        <form method="POST" action="<?php echo FILENAME_NEWSLETTER2GO; ?>">
            <br />
            <table>
                <tr>
                    <td><?php echo N2G_FORM_FIELD_NAME; ?>: </td><td><input type="text" name="firstname"/></td>
                </tr>
                <tr>
                    <td><?php echo N2G_FORM_FIELD_LASTNAME; ?>: </td><td><input type="text" name="lastname"/></td>
                </tr>
                <tr> 
                    <td><?php echo HEADING_TABLE_TITLE_USERNAME; ?>: </td><td><input type="text" name="username" required/></td>
                </tr>
                <tr>
                    <td><?php echo HEADING_TABLE_TITLE_EMAIL; ?>: </td><td><input type="text" name="email" required/></td>
                </tr>
                <tr>
                    <td><?php echo N2G_FORM_FIELD_PASSWORD; ?>: </td><td><input type="text" name="password" required/></td>
                </tr>
                <tr>
                    <td>
                        <?php echo '<input type="button" value="Cancel" onclick="document.location.href = \'' . tep_href_link(FILENAME_NEWSLETTER2GO, '') . '\'"/>'; ?>
                    </td>
                    <td><input type="submit" value="Create" /></td>
                </tr>
            </table>
        </form>
    </div>
<?php } else { ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
            <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                        <td>
                            <?php echo tep_draw_button(BOX_IMAGE_CREATE, 'plus', tep_href_link(FILENAME_NEWSLETTER2GO, 'action=Create')); ?>
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
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_ID; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_USERNAME; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_EMAIL; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_APIKEY; ?></td>
                                    <td class="dataTableHeadingContent"><?php echo HEADING_TABLE_TITLE_ENABLED; ?>&nbsp;</td>
                                </tr>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    if (isset($id) && $id == $user['id']) {
                                        echo '<tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
                                    } else {
                                        echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href = \'' . tep_href_link(FILENAME_NEWSLETTER2GO, 'id=' . $user['id']) . '\'">' . "\n";
                                    }
                                    ?>
                                    <td></td>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['apikey']; ?></td>
                                    <td><?php echo $user['enabled']; ?></td>
                        </tr>                   
                    <?php endforeach; ?>
                </table>
            </td>
            <?php
            if (tep_not_null($id)) {
                $heading = array();
                $contents = array();
                $heading[] = array('text' => '<strong>' . $users[$id]['username'] . '</strong>');
                $str = ($users[$id]['enabled'] ? BOX_IMAGE_DISABLE : BOX_IMAGE_ENABLE);
                $contents[] = array('align' => 'center', 'text' => tep_draw_button($str, 'document', tep_href_link(FILENAME_NEWSLETTER2GO, 'id=' . $id . '&action=' . $str)) . tep_draw_button(BOX_IMAGE_GENERATE, 'minus', tep_href_link(FILENAME_NEWSLETTER2GO, 'id=' . $id . '&action=Generate')));
                $contents[] = array('align' => 'center', 'text' => tep_draw_button(BOX_IMAGE_DELETE, 'minus', tep_href_link(FILENAME_NEWSLETTER2GO, 'id=' . $id . '&action=Delete')));
                if ((tep_not_null($heading)) && (tep_not_null($contents))) {
                    echo '            <td width="25%" valign="top">' . "\n";
                    $box = new box;
                    echo $box->infoBox($heading, $contents);
                    echo '            </td>' . "\n";
                }
            }
            ?>
        </tr>
    </table>
            </td>
        </tr>
    </table>

    <?php
}
require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
