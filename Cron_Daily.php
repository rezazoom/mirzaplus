<?php

// Set this cronjob daily
// 0 9 * * * /usr/bin/php /path/to/your/Cron_Daily.php

require_once 'config.php';
require_once 'apipanel.php';
require_once 'botapi.php';

#-------------[  Notification to the user ]-------------#
$list_service = mysqli_query($connect, "SELECT * FROM invoice");

while ($row = mysqli_fetch_assoc($list_service)) {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$row['Service_location']}'"));
    $get_username_Check = getuser($row['username'], $marzban_list_get['name_panel']);

    if (isset($get_username_Check['status'])) {
        $timeservice = $get_username_Check['expire'] - time();
        $day = floor($timeservice / 86400) + 1;
        $output = $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $RemainingVolume = formatBytes($output);
        if ($output <= 1073741824 && $output > 0 && isset($get_username_Check['data_limit']) && $get_username_Check['status'] == "active") {
            $text = sprintf("⭕️ کاربر گرامی، از حجم سرویس شما %s باقی مانده است.
شما برای تمدید سرویس می‌توانید از منوی «سرویس‌های من» اقدام کنید.

نام سرویس: <code> %s</code>
سرویس خریداری شده: %s", $RemainingVolume, $row['username'], $row['name_product']);
            sendmessage($row['id_user'], $text, null, 'HTML');
        }
        if ($timeservice <= "167000" && $timeservice > 0) {
            $text = sprintf("⭕️ کاربر گرامی، به پایان دوره‌ی سرویس شما %s روز مانده است. در صورت عدم تمدید سرویس تا ۷۲ ساعت بصورت معلق باقی می‌ماند و پس از آن سرویس حذف و غیرقابل تمدید خواهد شد.
شما برای تمدید سرویس می‌توانید از منوی «سرویس‌های من» اقدام کنید.

نام سرویس: <code> %s</code>
سرویس خریداری شده: %s", $day, $row['username'], $row['name_product']);
            sendmessage($row['id_user'], $text, null, 'HTML');
        }
        if ($day == "-3") {
            removeuser($marzban_list_get['name_panel'], $row['username']);
            $stmt = $connect->prepare("DELETE FROM invoice WHERE username = ?");
            $stmt->bind_param("s", $row['username']);
            $stmt->execute();

            $text = sprintf("<b>🗑 — اطلاعیه حذف سرویس:</b>

سرویس %s خریداری شده توسط شما با نام <code>%s</code> به علت عدم تمدید از سرور حذف شد.", $row['name_product'], $row['username']);
            sendmessage($row['id_user'], $text, null, 'HTML');
        }
    }
}

