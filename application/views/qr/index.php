<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Booking Pass Mobile Replica</title>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
*{
  box-sizing:border-box;
  margin:0;
  padding:0;
}

body{
  background:#efefef;
  font-family:Arial,sans-serif;
  display:flex;
  justify-content:center;
  gap:20px;
  padding:20px;
}

.controls{
  width:260px;
  background:white;
  padding:20px;
  border-radius:10px;
  box-shadow:0 1px 4px rgba(0,0,0,.08);
}

.controls h3{
  margin-bottom:15px;
}

.controls label{
  display:block;
  margin-top:12px;
  margin-bottom:5px;
  font-size:14px;
}

.controls input,
.controls button{
  width:100%;
  padding:8px;
  border:1px solid #ccc;
  border-radius:6px;
}

.controls button{
  margin-top:15px;
  background:#4caf50;
  color:white;
  border:none;
  cursor:pointer;
}

.phone{
  width:350px;
  height:700px;
  background:#efefef;
  position:relative;
  overflow:hidden;
}

.top-gradient{
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:58px;
  background:linear-gradient(135deg,#d7f1d6 0%, #efefef 38%, #efefef 100%);
}

.status-bar{
  position:absolute;
  top:4px;
  left:8px;
  right:8px;
  display:flex;
  justify-content:space-between;
  font-size:12px;
  color:#444;
}

.header{
  position:absolute;
  top:26px;
  left:0;
  right:0;
  padding:0 14px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  color:#666;
  font-size:13px;
}

.card{
  position:absolute;
  top:60px;
  left:4px;
  right:4px;
  height:420px;
  background:#fff;
  border-radius:8px;
  box-shadow:0 1px 1px rgba(0,0,0,.04);
}

.divider{
  position:absolute;
  left:0;
  right:0;
  top:150px;
  border-top:2px dashed #e2e2e2;
  z-index:1;
}

.notch-left,
.notch-right{
  position:absolute;
  top:142px;
  width:8px;
  height:16px;
  background:#efefef;
  border-radius:50%;
  z-index:2;
}

.notch-left{
  left:-4px;
}

.notch-right{
  right:-4px;
}

.date{
  padding-top:20px;
  text-align:center;
  font-size:18px;
  color:#222;
}

.time{
  margin-top:4px;
  text-align:center;
  font-size:16px;
  color:#666;
}

.qr-wrap{
  position:relative;
  z-index:3;
  margin:34px auto 0;
  width:115px;
  height:115px;
  background:#f2f2f2;
  border-radius:4px;
  padding:8px;
  display:flex;
  justify-content:center;
  align-items:center;
}

.ref{
  margin-top:30px;
  text-align:center;
  font-size:16px;
  color:#333;
}

.avatar{
  margin:18px auto 8px;
  width:42px;
  height:42px;
  border-radius:50%;
  background:#efefef;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#444;
  font-size:24px;
}

.you{
  text-align:center;
  font-size:13px;
  color:#555;
  margin-top:35px;
}

.notice{
  text-align:center;
  margin-top:15px;
  font-size:12px;
  color:#555;
}

.cancel{
  text-align:center;
  margin-top:35px;
  font-size:12px;
  color:#d27b70;
  text-decoration:underline;
}

.warning{
  position:absolute;
  bottom:72px;
  left:0;
  right:0;
  text-align:center;
  font-size:11px;
  color:#666;
}

.gate-btn{
  position:absolute;
  bottom:12px;
  left:20px;
  right:20px;
  height:28px;
  background:#979797;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#eee;
  font-size:12px;
}
</style>
</head>
<body>

<div class="controls">
  <h3>Edit Ticket</h3>

  <label>Booking ID</label>
  <input type="text" id="bookingInput" value="1145360165" readonly>

  <label>Barcode Data</label>
  <input type="text" id="barcodeInput"
         value="1145360165AF2026040916002026040916209703397a6ca6a70024942d48bb2bc99a6afa5b5f8a653994ed84cd5171e30c98">

  <label>Tanggal</label>
  <input type="text" id="dateInput" value="09 Apr 2026">

  <label>Waktu</label>
  <input type="text" id="timeInput" value="16:00">

  <button onclick="saveCapture()">Simpan Capture PNG</button>
</div>

<div class="phone" id="captureArea">
  <!-- <div class="top-gradient"></div>

  <div class="status-bar">
    <div>19.09</div>
    <div>◌ ⌁ 📶 🔋</div>
  </div>

  <div class="header">
    <div>×</div>
    <div>You</div>
  </div> -->

  <div class="card">
    <div class="date" id="ticketDate">09 Apr 2026</div>
    <div class="time" id="ticketTime">16:00</div>

    <div class="divider"></div>
    <div class="notch-left"></div>
    <div class="notch-right"></div>

    <div class="qr-wrap">
      <div id="qrCode"></div>
    </div>

    <div class="ref" id="ticketRef">1145360165</div>
    <div class="avatar">س</div>
    <div class="you">You</div>
    <div class="notice">Please stay close. Your visit time is approaching.</div>
    <div class="cancel">Cancel booking</div>
  </div>

  <div class="warning">⚠ Please proceed now.</div>
  <div class="gate-btn">➜ Directions to the Gate</div>
</div>

<script>
const bookingInput = document.getElementById('bookingInput');
const barcodeInput = document.getElementById('barcodeInput');
const dateInput = document.getElementById('dateInput');
const timeInput = document.getElementById('timeInput');

let qr;

function generateQR(data) {
    const qrContainer = document.getElementById("qrCode");
    qrContainer.innerHTML = "";

    qr = new QRCode(qrContainer, {
        text: data,
        width: 100,
        height: 100,
        correctLevel: QRCode.CorrectLevel.M
    });
}

function updateTicket() {
    const barcodeValue = barcodeInput.value;

    // ambil 10 digit pertama dari barcode
    const bookingId = barcodeValue.substring(0, 10);

    // update booking input
    bookingInput.value = bookingId;

    // update tampilan ticket
    document.getElementById('ticketRef').textContent = bookingId;
    document.getElementById('ticketDate').textContent = dateInput.value;
    document.getElementById('ticketTime').textContent = timeInput.value;

    generateQR(barcodeValue);
}

barcodeInput.addEventListener('input', updateTicket);
dateInput.addEventListener('input', updateTicket);
timeInput.addEventListener('input', updateTicket);

function saveCapture() {
    const bookingId = bookingInput.value || "ticket";

    html2canvas(document.getElementById("captureArea"), {
        scale: 2
    }).then(canvas => {
        const link = document.createElement("a");

        // nama file otomatis sesuai booking id
        link.download = bookingId + ".png";

        link.href = canvas.toDataURL("image/png");
        link.click();
    });
}

updateTicket();
</script>

</body>
</html>