let clientCodeReader;

function openClientScanner() {
    document.getElementById('clientScanModal').classList.remove('hidden');
    startClientScanning();
}

function startClientScanning() {
    clientCodeReader = new ZXing.BrowserQRCodeReader();
    const videoElem = document.getElementById('client-qr-video');

    clientCodeReader.decodeFromVideoDevice(null, videoElem, (result, err) => {
        if (result) {
            console.log('Scanned:', result.text);
            processClientScan(result.text);
        }
    })
}

async function processClientScan(qrText) {
    const match = qrText.match(/ID:\s*(\d+)/);
    if (!match) {
        alert("Invalid QR Code format.");
        return;
    }
    const trackingId = match[1];


    stopClientScanning();
    document.getElementById('clientScanModal').classList.add('hidden');

    try {
        const response = await fetch('/service-pro/actions/user/user_confirm_claim.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tracking_id: trackingId })
        });

        const data = await response.json();
        if (data.success) {
            alert("Success! Your device is marked as Claimed.");
            location.reload();
        } else {
            alert("Error: " + data.message); 
        }
    } catch (e) {
        console.error(e);
    }
}

function stopClientScanning() {
    if (clientCodeReader) {
        clientCodeReader.reset();
    }
}

function closeClientScanner() {
    stopClientScanning();
    document.getElementById('clientScanModal').classList.add('hidden');
}

window.openClientScanner = openClientScanner;
window.closeClientScanner = closeClientScanner;
window.startClientScanning = startClientScanning;
window.stopClientScanning = stopClientScanning;
