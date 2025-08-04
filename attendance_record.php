<?php
require_once("controller/database.php");
session_start();

function getAddress($lat, $lon)
{
    if (!$lat || !$lon) return '-';
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=18&addressdetails=1";
    $opts = [
        'http' => ['header' => "User-Agent: my-app"]
    ];
    $context = stream_context_create($opts);
    $json = file_get_contents($url, false, $context);
    $data = json_decode($json, true);
    return $data['display_name'] ?? '-';
}

$user_id = $_SESSION['user_id'];
$query = "SELECT 
                        a.user_id,
                        u.name,
                        a.created_at,
                        DATE(a.created_at) AS date,
                        MIN(CASE WHEN a.type = 'in'  THEN a.time END) AS time_in,
                        MAX(CASE WHEN a.type = 'out' THEN a.time END) AS time_out,
                        MIN(CASE WHEN a.type = 'in'  THEN a.longitude END) AS longitude_in,
                        MAX(CASE WHEN a.type = 'out' THEN a.longitude END) AS longitude_out,
                        MIN(CASE WHEN a.type = 'in'  THEN a.latitude END) AS latitude_in,
                        MAX(CASE WHEN a.type = 'out' THEN a.latitude END) AS latitude_out
                    FROM attendance a
                    JOIN users u ON a.user_id = u.id
                    GROUP BY a.user_id, DATE(a.created_at)
                    ORDER BY date DESC";
$stmt = $handle->prepare($query);
$stmt->execute();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record - Sistem Absensi Karyawan</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Staatliches&display=swap');
    </style>
</head>

<body>
    <div class="flex bg-gray-100 h-screen justify-center p-10 font-[Poppins]">
        <div class="bg-white shadow-md rounded-lg p-6 w-full mt-10">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold mb-4">Rekaman Absensi</h1>
                <a href="index.php" class="text-blue-500 hover:underline">Kembali ke Dashboard</a>
            </div>
            <p class="text-gray-700 mb-4">Berikut adalah rekaman absensi:</p>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Date</th>
                            <th class="py-2 px-4 border-b">User ID</th>
                            <th class="py-2 px-4 border-b">Nama</th>
                            <th class="py-2 px-4 border-b">Jam Masuk</th>
                            <th class="py-2 px-4 border-b">Jam Pulang</th>
                            <th class="py-2 px-4 border-b">Lokasi Absen Masuk</th>
                            <th class="py-2 px-4 border-b">Lokasi Absen Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars(date('d-m-Y', strtotime($row['date']))) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['user_id']) ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="py-2 px-4 border-b"><?= $row['time_in'] ? date('H:i:s', strtotime($row['time_in'])) : '-' ?></td>
                                <td class="py-2 px-4 border-b"><?= $row['time_out'] ? date('H:i:s', strtotime($row['time_out'])) : '-' ?></td>
                                <td class="py-2 px-4 border-b"><?= getAddress($row['latitude_in'], $row['longitude_in']) ?></td>
                                <td class="py-2 px-4 border-b"><?= getAddress($row['latitude_out'], $row['longitude_out']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
</body>

</html>