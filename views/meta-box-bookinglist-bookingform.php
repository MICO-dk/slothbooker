    <table class="widefat eventlist-edit-table">
        

        <tr valign="top">
            <td scope="row">
                <span class="dashicons dashicons-calendar"></span>
                <label for="sb_date">Date:</label>
            </td>
            <td>
                <input type="text" class="js-datepicker" name="sb_date" value="<?php echo get_sb_date('d/m/Y'); ?>" placeholder="02/04/2015" size="10" maxlength="10" autocomplete="off">
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-clock"></span>
                <label for="sb_start_time">Time:</label>
            </td>
            <td>
                <input type="text" name="sb_start_time" value="<?php echo get_sb_date('H:i'); ?>" placeholder="19:00">
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-admin-home"></span>
                <label for="sb_room">Room:</label>
            </td>
            <td>
                <?php $rooms = get_rooms();  ?>
                <form action="">
                    <select name="sb_room">
                        <?php foreach ($rooms as $room) : ?>
                            <option value="<?php echo $room; ?>"><?php echo $room; ?></option>
                        <?php endforeach; ?> 
                    </select>
                </form>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-businessman"></span>
                <label for="sb_member" class="">Member:</label>
            </td>
            <td>
                <?php $members = get_users();  ?>
                <form action="">
                    <select name="sb_members">
                        <?php foreach ($members as $member) : ?>
                            <option value="<?php echo $member->display_name; ?>"><?php echo $member->display_name; ?></option>
                        <?php endforeach; ?> 
                    </select>
                </form>
            </td>
        </tr>
        
    </table>