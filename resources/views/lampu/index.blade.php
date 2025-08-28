@extends('default')

@section('title', 'Lampu Control')

@section('content')
<div class="container mx-auto p-4 md:p-6">
<style>
  h1 { color: #333; }
  p { margin-top: 20px; font-size: 1.1em; }
  .button {
    display: inline-block;
    background-color: #007bff;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 20px;
    margin: 10px;
    text-decoration: none;
    min-width: 150px;
  }
  .button.off { background-color: #dc3545; }
  .button.on { background-color: #28a745; }
  .button:hover { opacity: 0.9; }
  #statusInfo { margin-top: 30px; padding: 15px; background-color: #e9e9e9; border-radius: 5px; text-align: left; }
  #statusInfo p { margin: 5px 0; font-size: 0.95em; }
</style>

<div class="container">
  <h1>Kontrol Lampu NodeMCU</h1>
  <p>Status Lampu: <strong id="lampStatus">Memuat...</strong></p>
  <button class="button on" onclick="controlLamp('on')">Turn ON</button>
  <button class="button off" onclick="controlLamp('off')">Turn OFF</button>

  <div id="statusInfo">
    <p>NodeMCU IP: <span id="nodeMcuIp">Belum Terhubung</span></p>
    <p>Koneksi WiFi: <span id="wifiConnectionStatus">Memuat...</span></p>
    <p>Status Pembaruan: <span id="lastUpdated">N/A</span></p>
  </div>
</div>

<script>
  // --- GANTI INI DENGAN ALAMAT IP NodeMCU ANDA SETELAH TERHUBUNG KE WIFI ---
  // Anda akan mendapatkan ini dari Serial Monitor NodeMCU setelah terhubung ke WiFi utama.
  const NODE_MCU_IP = "192.168.1.XXX"; // <-- GANTI DENGAN IP ASLI NODE MCU ANDA

  function controlLamp(state) {
    if (NODE_MCU_IP === "192.168.1.XXX") {
      alert("Harap ganti NODE_MCU_IP di JavaScript dengan alamat IP NodeMCU Anda!");
      return;
    }
    fetch(`http://${NODE_MCU_IP}/lamp/${state}`)
      .then(response => response.json())
      .then(data => {
        console.log("Kontrol Lampu Respon:", data);
        updateStatus(); // Perbarui status setelah kontrol
      })
      .catch(error => {
        console.error("Error mengontrol lampu:", error);
        alert("Gagal mengontrol lampu. Pastikan NodeMCU terhubung dan IP benar.");
      });
  }

  function updateStatus() {
    if (NODE_MCU_IP === "192.168.1.XXX") {
       document.getElementById('lampStatus').innerText = "IP NodeMCU Belum Disetel";
       document.getElementById('wifiConnectionStatus').innerText = "Tidak Diketahui";
       document.getElementById('nodeMcuIp').innerText = "N/A";
       document.getElementById('lastUpdated').innerText = "Setel IP di JS!";
       return;
    }

    fetch(`http://${NODE_MCU_IP}/status`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log("Status NodeMCU:", data);
        document.getElementById('lampStatus').innerText = data.lamp;
        document.getElementById('wifiConnectionStatus').innerText = data.connected === "true" ? "Terhubung" : "Tidak Terhubung";
        document.getElementById('nodeMcuIp').innerText = data.ip;
        document.getElementById('lastUpdated').innerText = new Date().toLocaleTimeString();
      })
      .catch(error => {
        console.error("Error mengambil status NodeMCU:", error);
        document.getElementById('lampStatus').innerText = "Tidak Diketahui";
        document.getElementById('wifiConnectionStatus').innerText = "Tidak Terhubung (Error Koneksi)";
        document.getElementById('nodeMcuIp').innerText = "N/A";
        document.getElementById('lastUpdated').innerText = "Gagal Update";
      });
  }

  // Perbarui status saat halaman dimuat
  updateStatus();
  // Perbarui status setiap 3 detik
  setInterval(updateStatus, 3000);
</script>
</div>


@endsection
