<?php
require_once('controller/check_user_auth.php');
require_once('controller/database.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Staatliches&display=swap');
    </style>
</head>

<body>
    <div class="flex bg-gray-100 h-screen justify-center p-10 font-[Poppins]">
        <div class="flex flex-col items-center justify-center bg-white p-10 rounded-lg
        shadow-lg w-full min-w-md max-w-2xl">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class='bg-green-100 text-green-800 p-3 rounded mb-4'>" . htmlspecialchars($_SESSION['success']) . "</div>";
                unset($_SESSION['success']);
            } else 
            if (isset($_SESSION['error'])) {
                echo "<div class='bg-red-100 text-red-800 p-3 rounded mb-4'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);
            }
            echo "<p>
                </p>";
            ?>
            <div class="w-full  max-w-lg">
                <div class="flex flex-row justify-between items-center mb-3">
                    <h1 class="text-2xl font-bold py-2">Dashboard</h1>
                    <a href="controller/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                </div>
                <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>
                <a href="attendance_record.php" class="text-blue-500 hover:underline">Cek Absensi</a>
                <p class="font-bold text-lg mt-5">Status Hari ini:</p>
                <hr>
                <div>
                    <p>
                        Absen Masuk:
                        <?php
                        $query = "SELECT * FROM attendance WHERE user_id = :user_id AND type = 'in' AND DATE(created_at) = CURDATE()";
                        $stmt = $handle->prepare($query);
                        $stmt->bindParam(":user_id", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            echo "<span class='text-green-500'>Sudah Absen Masuk</span>";
                        } else {
                            echo "<span class='text-red-500'>Belum Absen Masuk</span>";
                        }
                        ?>
                        <br>
                        Absen Keluar:
                        <?php
                        $query = "SELECT * FROM attendance WHERE user_id = :user_id AND type = 'out' AND DATE(created_at) = CURDATE()";
                        $stmt = $handle->prepare($query);
                        $stmt->bindParam(":user_id", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            echo "<span class='text-green-500'>Sudah Absen Keluar</span>";
                        } else {
                            echo "<span class='text-red-500'>Belum Absen Keluar</span>";
                        }
                        ?>
                    </p>
                </div>
                <p class="text-2xl font-bold mt-5">Actions:</p>

                <hr>
                <div class="py-2">
                    <form action="controller/attendance_in.php" method="POST" onsubmit="return setLocationAndSubmit(this)">
                        <input type="hidden" name="latitude" id="latitude_in">
                        <input type="hidden" name="longitude" id="longitude_in">
                        <button type="submit" class="block w-full bg-blue-500 text-white text-center px-4 py-2 rounded hover:bg-blue-600 mb-2">
                            Absen Masuk
                        </button>
                    </form>

                    <form action="controller/attendance_out.php" method="POST" onsubmit="return setLocationAndSubmit(this)">
                        <input type="hidden" name="latitude" id="latitude_out">
                        <input type="hidden" name="longitude" id="longitude_out">
                        <button type="submit" class="block w-full bg-blue-500 text-white text-center px-4 py-2 rounded hover:bg-blue-600">
                            Absen Pulang
                        </button>
                    </form>

                    <script>
                        function setLocationAndSubmit(form) {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(function(position) {
                                    form.latitude.value = position.coords.latitude;
                                    form.longitude.value = position.coords.longitude;
                                    form.submit(); // submit form setelah koordinat didapat
                                }, function() {
                                    alert("Gagal mendapatkan lokasi");
                                });
                            } else {
                                alert("Browser tidak mendukung geolocation");
                            }
                            return false; // cegah submit langsung
                        }
                    </script>

                </div>

                <!-- Time -->
                <div class="flex flex-col items-center justify-center mt-5">
                    <p class="text-lg">Waktu Saat Ini:</p>
                    <p id="current-time" class="text-2xl font-bold"></p>
                    <script>
                        function updateTime() {
                            const now = new Date();
                            const options = {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            };
                            document.getElementById('current-time').textContent = now.toLocaleTimeString([], options);
                        }
                        setInterval(updateTime, 1000);
                        updateTime(); // initial call
                    </script>
                </div>

                <!-- Current Location Name -->
                <div class="flex flex-col items-start justify-center mt-5">

                    <p class="text-lg">Lokasi Saat Ini:</p>
                    <p id="current-location" class="text-md font-bold"></p>
                    <script>
                        function updateLocation() {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(function(position) {
                                    const lat = position.coords.latitude;
                                    const long = position.coords.longitude;

                                    // Get location name using OpenStreetMap Nominatim
                                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${long}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            const address = data.display_name;
                                            document.getElementById('current-location').textContent = address;
                                        })
                                        .catch(() => {
                                            document.getElementById('current-location').textContent =
                                                `Lat: ${lat.toFixed(4)}, Long: ${long.toFixed(4)}`;
                                        });
                                }, function() {
                                    document.getElementById('current-location').textContent = "Gagal mendapatkan lokasi";
                                });
                            } else {
                                document.getElementById('current-location').textContent = "Geolocation tidak didukung oleh browser ini.";
                            }
                        }
                        updateLocation(); // initial call
                    </script>
                </div>


            </div>

        </div>
</body>

<script>
    function getLocationAndSend() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var long = position.coords.longitude;

                // kirim otomatis ke PHP backend
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "save_location.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send("latitude=" + lat + "&longitude=" + long);
                console.log("Lokasi berhasil dikirim: ", lat, long);
            }, function(error) {
                console.log("Gagal mendapatkan lokasi: ", error.message);
            });
        } else {
            alert("Browser tidak mendukung geolocation.");
        }
    }

    // panggil saat halaman load
    window.onload = getLocationAndSend;
</script>

</html>