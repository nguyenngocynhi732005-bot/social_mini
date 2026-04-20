<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = Illuminate\Support\Facades\DB::connection();
$driver = $db->getDriverName();

if ($driver === 'mysql') {
    $deleted = $db->delete(
        'DELETE s1 FROM songs s1 INNER JOIN songs s2 ON s1.title = s2.title AND s1.id > s2.id'
    );
} else {
    $deleted = $db->delete(
        'DELETE FROM songs WHERE id NOT IN (SELECT keep_id FROM (SELECT MIN(id) AS keep_id FROM songs GROUP BY title) t)'
    );
}

$duplicates = $db->select('SELECT title, COUNT(*) AS c FROM songs GROUP BY title HAVING COUNT(*) > 1');
$total = (int) $db->table('songs')->count();

echo 'Deleted rows: ' . (int) $deleted . PHP_EOL;
echo 'Total songs: ' . $total . PHP_EOL;
echo 'Remaining duplicate groups: ' . count($duplicates) . PHP_EOL;

if (!empty($duplicates)) {
    foreach ($duplicates as $row) {
        echo '- ' . $row->title . ': ' . $row->c . PHP_EOL;
    }
}
