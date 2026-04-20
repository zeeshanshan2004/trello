<?php
$file = file_get_contents(__DIR__ . '/app/Http/Controllers/BoardController.php');

$old = "        return \$board->workspace->users()\r\n            ->where('users.is_active', true)\r\n            ->where('users.role', '!=', 'admin')\r\n            ->whereNotIn('users.id', \$sharedUserIds)\r\n            ->get(['users.id', 'users.name', 'users.email']);";

$new = "        return User::where('is_active', true)\r\n            ->where('role', '!=', 'admin')\r\n            ->whereNotIn('id', \$sharedUserIds)\r\n            ->get(['id', 'name', 'email']);";

$result = str_replace($old, $new, $file, $count);

if ($count === 0) {
    echo "NOT FOUND - trying LF line endings\n";
    $old2 = "        return \$board->workspace->users()\n            ->where('users.is_active', true)\n            ->where('users.role', '!=', 'admin')\n            ->whereNotIn('users.id', \$sharedUserIds)\n            ->get(['users.id', 'users.name', 'users.email']);";
    $new2 = "        return User::where('is_active', true)\n            ->where('role', '!=', 'admin')\n            ->whereNotIn('id', \$sharedUserIds)\n            ->get(['id', 'name', 'email']);";
    $result = str_replace($old2, $new2, $file, $count);
}

if ($count === 0) {
    echo "STILL NOT FOUND\n";
}
else {
    file_put_contents(__DIR__ . '/app/Http/Controllers/BoardController.php', $result);
    echo "DONE - replaced $count occurrence(s)\n";
}
