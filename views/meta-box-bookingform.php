    <table class="widefat eventlist-edit-table">
        

        <tr valign="top">
            <td scope="row">
                <span class="dashicons dashicons-calendar"></span>
                <label for="sb_date">Date:</label>
            </td>
            <td>
                <input type="text" class="js-datepicker" name="sb_date" value="<?php echo get_sb_date(); ?>" placeholder="02/04/2015" size="10" maxlength="10" autocomplete="off">
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-clock"></span>
                <label for="sb_start_time">Time:</label>
            </td>
            <td>
                <input type="text" name="sb_start_time" value="<?php echo get_sb_start_time('H:i'); ?>" placeholder="19:00">
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-admin-home"></span>
                <label for="sb_room">Room:</label>
            </td>
            <td>
                <select name="sb_room">
                    <?php $rooms = get_rooms(); ?>
                    <?php $selected = get_sb_room(); ?>
                    <?php foreach ($rooms as $room) : ?>
                        <option <?php selected( $selected, $room ); ?> value="<?php echo $room; ?>"><?php echo $room; ?></option>
                    <?php endforeach; ?> 
                </select>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" >
                <span class="dashicons dashicons-businessman"></span>
                <label for="sb_user" class="">user:</label>
            </td>
            <td>
                <select name="sb_user">
                    <?php $users = get_users();  ?>
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
                    <?php endforeach; ?> 
                </select>
            </td>
        </tr>
        
    </table>