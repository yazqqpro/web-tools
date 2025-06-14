<?php
$page_title = "Generator Kartu Undian";
$path_prefix = '../../';
include $path_prefix . 'header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Lobster&family=Playfair+Display:wght@700&family=Oswald:wght@500&display=swap" rel="stylesheet">

<style>
    /* Styling khusus untuk tool ini */
    .ticket-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        box-sizing: border-box;
        overflow: hidden;
        position: relative;
    }

    .ticket-header {
        font-weight: bold;
        text-align: center;
        margin-bottom: 5px; /* Tambahkan sedikit margin jika ada logo dan header */
    }

    .ticket-logo {
        max-width: 80%;
        max-height: 40px; /* Sesuaikan jika perlu lebih besar/kecil */
        margin-bottom: 5px;
        object-fit: contain;
    }

    .ticket-number {
        font-weight: bold;
        margin-top: auto; /* Dorong nomor ke bawah jika ada logo dan header */
        margin-bottom: auto; /* Pusatkan vertikal jika tidak ada QR */
    }

    .ticket-qr-code-container { /* Selector diperbaiki untuk positioning */
        width: 40px;
        height: 40px;
        position: absolute;
        bottom: 5px;
        right: 5px;
    }

    .paper-sheet {
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin: 20px auto;
        display: grid;
        gap: 0; /* Tidak ada gap antar kartu */
        page-break-after: always; /* Page break saat print */
    }

    /* Style untuk ukuran kertas A4 Portrait */
    .a4-portrait {
        width: 210mm;
        min-height: 297mm; /* min-height untuk tampilan layar */
        padding: 10mm; /* Margin kertas untuk tampilan layar, @page akan menangani margin print */
        box-sizing: border-box;
    }

    #floatingPrintBtn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: none; /* Muncul setelah kartu dibuat */
    }
    
    /* Styles untuk proses print */
    @media print {
        body, html {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Sembunyikan semua elemen yang tidak perlu dicetak */
        header,
        footer,
        nav.navbar, /* Umumnya ada di header.php */
        .navbar, /* Selector navbar yang lebih umum */
        #floatingPrintBtn,
        .tool-page-container > .text-center, /* Judul dan deskripsi tool */
        .tool-page-container > .card /* Form pengaturan kartu */
        {
            display: none !important;
        }

        /* Pastikan container utama dan container tool tidak mengganggu layout print */
        main.container, 
        .tool-page-container {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important; /* Override max-width Bootstrap */
            box-shadow: none !important;
            border: none !important;
        }
        
        #outputArea {
            display: block !important;
            margin: 0 !important; /* Hapus margin atas untuk print */
            padding: 0 !important;
            width: 100% !important;
        }

        .paper-sheet {
            box-shadow: none !important;
            margin: 0 !important; /* Margin antar sheet diatur oleh @page */
            width: 100% !important; 
            min-height: initial !important; /* Biarkan tinggi natural sesuai konten */
            height: auto !important;
            padding: 0 !important;
            border: none !important;
            page-break-after: always !important; /* Pindah halaman setelah setiap sheet */
        }

        @page {
            size: A4 portrait;
            margin: 10mm; /* Margin aktual halaman saat dicetak */
        }
    }
</style>

<div class='tool-page-container'>
    <div class="text-center mb-4">
        <h1><i class="fas fa-ticket-alt me-2"></i><?php echo $page_title; ?></h1>
        <p class="lead text-muted">Buat dan cetak kartu undian kustom Anda dalam sekejap.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
             <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Pengaturan Kartu Undian</h5>
        </div>
        <div class="card-body p-4">
            <form id="generatorForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="headerText" class="form-label">Teks Header Kartu</label>
                        <input type="text" class="form-control" id="headerText" value="KARTU UNDIAN HUT RI">
                    </div>
                    <div class="col-md-6">
                        <label for="logoFile" class="form-label">Logo (Opsional, untuk kartu besar)</label>
                        <input class="form-control" type="file" id="logoFile" accept="image/png, image/jpeg">
                    </div>
                </div>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="totalNumbers" class="form-label">Jumlah Nomor</label>
                        <input type="number" class="form-control" id="totalNumbers" value="20" min="1">
                    </div>
                    <div class="col-md-3">
                        <label for="startNumber" class="form-label">Mulai Nomor Dari</label>
                        <input type="number" class="form-control" id="startNumber" value="1" min="0">
                    </div>
                    <div class="col-md-3">
                        <label for="digitCount" class="form-label">Jumlah Digit Nomor</label>
                        <input type="number" class="form-control" id="digitCount" value="3" min="1" max="10">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="useRandom">
                            <label class="form-check-label" for="useRandom">Gunakan Nomor Acak</label>
                        </div>
                    </div>
                    <div class="col-12">
                         <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="includeQR" checked>
                            <label class="form-check-label" for="includeQR">Sertakan QR (untuk kartu besar)</label>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="paperSize" class="form-label">Ukuran Kertas</label>
                        <select id="paperSize" class="form-select">
                            <option value="A4 (Portrait)" selected>A4 (Portrait)</option>
                            </select>
                    </div>
                    <div class="col-md-4">
                        <label for="columns" class="form-label">Kolom per Lembar</label>
                        <input type="number" class="form-control" id="columns" value="3" min="1">
                    </div>
                    <div class="col-md-4">
                        <label for="rows" class="form-label">Baris per Lembar</label>
                        <input type="number" class="form-control" id="rows" value="9" min="1">
                    </div>
                </div>
                 <hr class="my-4">
                 <div class="row g-3">
                    <div class="col-md-6">
                        <label for="fontFamily" class="form-label">Jenis Font Nomor</label>
                        <select id="fontFamily" class="form-select">
                            <option value="'Roboto Mono', monospace" selected>Roboto Mono</option>
                            <option value="'Oswald', sans-serif">Oswald</option>
                            <option value="'Playfair Display', serif">Playfair Display</option>
                            <option value="'Lobster', cursive">Lobster</option>
                            <option value="serif">Serif (Default)</option>
                            <option value="sans-serif">Sans-serif (Default)</option>
                        </select>
                    </div>
                     <div class="col-md-6">
                        <label for="fontColor" class="form-label">Warna Font Nomor</label>
                        <input type="color" class="form-control form-control-color" id="fontColor" value="#DE007A" title="Pilih warna">
                    </div>
                    <div class="col-md-4">
                        <label for="bgColor" class="form-label">Warna Latar Kartu</label>
                        <input type="color" class="form-control form-control-color" id="bgColor" value="#FFFFFF" title="Pilih warna">
                    </div>
                    <div class="col-md-4">
                        <label for="borderColor" class="form-label">Warna Border Kartu</label>
                        <input type="color" class="form-control form-control-color" id="borderColor" value="#000000" title="Pilih warna">
                    </div>
                    <div class="col-md-4">
                        <label for="borderStyle" class="form-label">Jenis Border Kartu</label>
                        <select id="borderStyle" class="form-select">
                            <option value="solid">Solid</option>
                            <option value="double" selected>Double</option>
                            <option value="dashed">Dashed</option>
                            <option value="dotted">Dotted</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic me-2"></i>Buat Kartu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="outputArea" class="mt-5"></div>

    <button id="floatingPrintBtn" class="btn btn-lg btn-success">
        <i class="fas fa-print me-2"></i>Cetak Kartu
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('generatorForm');
    const outputArea = document.getElementById('outputArea');
    const floatingPrintBtn = document.getElementById('floatingPrintBtn');
    let logoDataUrl = null;

    document.getElementById('logoFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                logoDataUrl = event.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            logoDataUrl = null;
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // 1. Get all values from form
        const settings = {
            headerText: document.getElementById('headerText').value,
            totalNumbers: parseInt(document.getElementById('totalNumbers').value),
            startNumber: parseInt(document.getElementById('startNumber').value),
            digitCount: parseInt(document.getElementById('digitCount').value),
            useRandom: document.getElementById('useRandom').checked,
            includeQR: document.getElementById('includeQR').checked,
            columns: parseInt(document.getElementById('columns').value),
            rows: parseInt(document.getElementById('rows').value),
            fontFamily: document.getElementById('fontFamily').value,
            fontColor: document.getElementById('fontColor').value,
            bgColor: document.getElementById('bgColor').value,
            borderColor: document.getElementById('borderColor').value,
            borderStyle: document.getElementById('borderStyle').value,
            paperSizeClass: 'a4-portrait' // Based on selection, for now static
        };

        // 2. Generate numbers
        let numbers = [];
        if (settings.useRandom) {
            let randomSet = new Set();
            const maxRandomNumber = Math.pow(10, settings.digitCount) -1; // Batas atas nomor acak
            if (settings.totalNumbers > (maxRandomNumber + 1)) {
                alert(`Jumlah nomor (${settings.totalNumbers}) melebihi kemungkinan nomor unik (${maxRandomNumber + 1}) untuk ${settings.digitCount} digit.`);
                return;
            }
            while(randomSet.size < settings.totalNumbers) {
                randomSet.add(Math.floor(Math.random() * (maxRandomNumber + 1) ));
            }
            numbers = Array.from(randomSet);
        } else {
            for(let i = 0; i < settings.totalNumbers; i++) {
                numbers.push(settings.startNumber + i);
            }
        }
        
        // 3. Start rendering
        outputArea.innerHTML = '';
        const ticketsPerSheet = settings.columns * settings.rows;
        let currentTicketCount = 0;
        let currentSheet = createSheet(settings);

        numbers.forEach(num => {
            if (currentTicketCount > 0 && currentTicketCount % ticketsPerSheet === 0) {
                outputArea.appendChild(currentSheet);
                currentSheet = createSheet(settings);
                currentTicketCount = 0; // Reset untuk lembar baru
            }

            const paddedNumber = String(num).padStart(settings.digitCount, '0');
            const ticket = createTicket(paddedNumber, settings);
            currentSheet.appendChild(ticket);
            currentTicketCount++;
        });

        // Append the last sheet if it has tickets
        if (currentTicketCount > 0) {
            outputArea.appendChild(currentSheet);
        }
        
        // Generate QR codes after tickets are in the DOM
        if (settings.includeQR) {
            document.querySelectorAll('.ticket-qr-code-container').forEach(el => {
                // Hapus QR code lama jika ada (untuk regenerasi)
                while (el.firstChild) {
                    el.removeChild(el.firstChild);
                }
                new QRCode(el, {
                    text: el.dataset.qrText,
                    width: 38, // Sedikit lebih kecil agar pas dengan padding container
                    height: 38,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.L
                });
            });
        }
        
        floatingPrintBtn.style.display = 'block';
    });
    
    floatingPrintBtn.addEventListener('click', () => {
        window.print();
    });

    function createSheet(settings) {
        const sheet = document.createElement('div');
        sheet.className = `paper-sheet ${settings.paperSizeClass}`;
        sheet.style.gridTemplateColumns = `repeat(${settings.columns}, 1fr)`;
        sheet.style.gridTemplateRows = `repeat(${settings.rows}, 1fr)`;
        return sheet;
    }

    function createTicket(number, settings) {
        const ticket = document.createElement('div');
        ticket.className = 'ticket-card';
        ticket.style.backgroundColor = settings.bgColor;
        ticket.style.border = settings.borderStyle !== 'none' ? `2px ${settings.borderStyle} ${settings.borderColor}` : 'none';

        let logoHtml = '';
        if(logoDataUrl) {
            logoHtml = `<img src="${logoDataUrl}" class="ticket-logo" alt="Logo">`;
        }

        // Penyesuaian ukuran font nomor agar lebih dinamis dan tidak terlalu besar/kecil
        const baseFontSize = 30; // Ukuran dasar font nomor
        const reductionFactor = settings.columns * 2.5; // Faktor pengurangan berdasarkan jumlah kolom
        let dynamicFontSize = Math.max(8, baseFontSize - reductionFactor); // Minimal 8px
        // Jika baris juga banyak, kurangi lagi sedikit
        if (settings.rows > 5) {
            dynamicFontSize = Math.max(8, dynamicFontSize - (settings.rows * 0.5) )
        }
        
        ticket.innerHTML = `
            ${logoHtml}
            <div class="ticket-header" style="font-size: ${Math.max(8, dynamicFontSize * 0.4)}px; color: ${settings.fontColor};">${settings.headerText}</div>
            <div class="ticket-number" style="font-family: ${settings.fontFamily}; color: ${settings.fontColor}; font-size: ${dynamicFontSize}px;">
                ${number}
            </div>
            ${settings.includeQR ? `<div class="ticket-qr-code-container" data-qr-text="${settings.headerText}-${number}"></div>` : '<div style="height:40px;"></div>' /* Placeholder jika tidak ada QR */}
        `;

        return ticket;
    }

});
</script>

<?php include $path_prefix . 'footer.php'; ?>