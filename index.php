<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Maalum Natural Swimming Pool - Waiver Form</title>
    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="assets/styles.css">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>
    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="success-modal">
            <div class="success-icon">
                <i class="material-icons">check_circle</i>
            </div>
            <h2>Success!</h2>
            <p>Your waiver form has been submitted successfully. A confirmation PDF has been generated and sent via email.</p>
            <button class="btn-close" onclick="closeModal()">Got it!</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal-overlay" id="errorModal">
        <div class="error-modal">
            <div class="error-icon">
                <i class="material-icons">error</i>
            </div>
            <h2>Oops!</h2>
            <p id="errorMessage">Something went wrong. Please try again.</p>
            <button class="btn-close" onclick="closeModal()">Try Again</button>
        </div>
    </div>

    <div class="main-container">
        <div class="header-section">
            <div class="logo-container">
                <img src="MAALUM.png" alt="Maalum Logo">
            </div>
            <h3>Maalum Natural Swimming Pool</h3>
            <h4>Terms & Conditions</h4>
        </div>

        <div class="content-section">
            <div class="terms-intro">
                In consideration for being allowed to access the Maalum Natural Swimming Pool and its facilities,
                the sufficiency of which is hereby acknowledged:
            </div>

            <div class="terms-list">
                <ul>
                    <li>1. In order to preserve the natural resource and in the interests of hygiene, swimmers must visit the toilet
                        and shower before entering the water.</li>
                    <li>2. No use of sun creams, chemical products or insect repellents before swimming.</li>
                    <li>3. No diving from above the cave area if so, management will hold no responsibility.</li>
                    <li>4. No food is allowed on-premises.</li>
                    <li>5. Children under 15 must be accompanied by an adult for supervision and they must sign this form on their
                        behalf.</li>
                    <li>6. Management hold no liability for any loss or damage for personal items, or any injuries (minor or major).
                    </li>
                    <li>7. All rubbish must be disposed of in allocated bins.</li>
                    <li>8. Please be aware there is no lifeguard enter pool at your own risk.</li>
                    <li>9. No drones are allowed on-premises unless previously notified when booking.</li>
                    <li>10. The duration of your slot is 1h30 inside the cave area.</li>
                    <li>11. Shouting or making loud noise inside the cave is strictly prohibited.</li>
                </ul>
            </div>

            <div class="notice-box">
                <p>
                    All visitors of Maalum are requested to treat the facilities with respect and as intended.
                    We kindly ask you to report any observed defect and any accidents immediately.
                </p>
            </div>

            <div class="form-section">
                <form id="waiverForm" action="send.php" method="post" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="checkbox-container">
                        <label>
                            <input type="checkbox" class="filled-in" name="agree_terms" required>
                            <span>I have read and understood the terms mentioned above, and I am aware that by signing this
                                form I agree to abide by the rules.</span>
                        </label>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">book</i>
                            <input id="bookname" name="bookname" type="text" class="validate" required>
                            <label for="bookname">Booking Name</label>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">account_circle</i>
                            <input id="Supervissor-1" name="Supervissor" type="text" class="validate" required>
                            <label for="Supervissor-1">Supervisor Name</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">email</i>
                            <input id="email" name="email" type="email" class="validate">
                            <label for="email">Email Address</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">phone</i>
                            <input id="telephone" name="telephone" type="tel" class="validate">
                            <label for="telephone">Telephone</label>
                        </div>
                    </div>

                    <p id="contact-error" class="error-message">
                        Please provide at least an Email or a Telephone number.
                    </p>

                    <div class="signature-container">
                        <div class="signature-title">
                            <i class="material-icons" style="vertical-align: middle;">create</i>
                            Please sign below
                        </div>
                        
                        <p id="signature-error" class="error-message">
                            Please provide a signature before submitting.
                        </p>

                        <!-- Signature mode selector -->
                        <div style="margin: 10px 0; text-align:center;">
                            <label style="margin-right: 12px;">
                                <input name="sig_mode" type="radio" value="draw" id="sigModeDraw" checked>
                                <span>Draw</span>
                            </label>
                            <label style="margin-right: 12px;">
                                <input name="sig_mode" type="radio" value="type" id="sigModeType">
                                <span>Type</span>
                            </label>
                            <label>
                                <input name="sig_mode" type="radio" value="upload" id="sigModeUpload">
                                <span>Upload</span>
                            </label>
                        </div>

                        <!-- Draw section -->
                        <div id="sig-draw-section">
                            <canvas id="signature-pad"></canvas>
                            <div class="btns">
                                <button type="button" class="btn red waves-effect waves-light" onclick="clearPad()">
                                    <i class="material-icons left">clear</i>Clear
                                </button>
                            </div>
                            <input type="hidden" name="signature" id="signature">
                        </div>

                        <!-- Type section -->
                        <div id="sig-type-section" style="display:none;">
                            <div class="input-field" style="margin-top:20px;">
                                <i class="material-icons prefix">edit</i>
                                <input id="signature_text" name="signature_text" type="text" placeholder="Type your full name as signature">
                                <label for="signature_text">Typed Signature</label>
                            </div>
                            <p id="signature-text-error" class="error-message">Please type your signature.</p>
                        </div>

                        <!-- Upload section -->
                        <div id="sig-upload-section" style="display:none; text-align:center;">
                            <input id="signature_file" name="signature_file" type="file" accept="image/*" style="margin-top:10px;">
                            <p style="font-size: 12px; color:#666;">Accepted: JPG, PNG. Max 3MB.</p>
                            <p id="signature-file-error" class="error-message">Please upload a signature image (JPG/PNG).</p>
                        </div>
                    </div>

                    <div class="spinner-container" id="loadingSpinner">
                        <div class="spinner"></div>
                        <p style="margin-top: 10px; color: #666;">Submitting your form...</p>
                    </div>

                    <div class="submit-container">
                        <button class="btn btn-submit waves-effect waves-light" type="submit" name="action" id="submitBtn">
                            Submit Form
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="js/materialize.min.js"></script>

    <script>
        let canvas = document.getElementById('signature-pad');
        let ctx = canvas.getContext('2d');
        let drawing = false;
        const sigModeDrawEl = document.getElementById('sigModeDraw');
        const sigModeTypeEl = document.getElementById('sigModeType');
        const sigModeUploadEl = document.getElementById('sigModeUpload');
        const sigDrawSection = document.getElementById('sig-draw-section');
        const sigTypeSection = document.getElementById('sig-type-section');
        const sigUploadSection = document.getElementById('sig-upload-section');
        const signatureTextInput = document.getElementById('signature_text');
        const signatureFileInput = document.getElementById('signature_file');

        // Adjust canvas resolution for crisp drawing
        function fillCanvasWhite() {
            // Paint a white background so exported image is not transparent (prevents black background in JPEG/PDF)
            const prev = ctx.getTransform();
            ctx.save();
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.restore();
            // Restore transform
            ctx.setTransform(prev);
        }

        function resizeCanvas() {
            let ratio = window.devicePixelRatio || 1;
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(ratio, ratio);
            fillCanvasWhite();
        }
        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        function updateSigModeUI() {
            const mode = document.querySelector('input[name="sig_mode"]:checked').value;
            sigDrawSection.style.display = (mode === 'draw') ? '' : 'none';
            sigTypeSection.style.display = (mode === 'type') ? '' : 'none';
            sigUploadSection.style.display = (mode === 'upload') ? '' : 'none';
        }
        [sigModeDrawEl, sigModeTypeEl, sigModeUploadEl].forEach(el => {
            el.addEventListener('change', updateSigModeUI);
        });
        updateSigModeUI();

        function startDraw(x, y) {
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function drawLine(x, y) {
            if (!drawing) return;
            ctx.lineWidth = 3;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.strokeStyle = "black";
            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.moveTo(x, y);
        }

        function stopDraw() {
            drawing = false;
            ctx.beginPath();
        }

        // Mouse events
        canvas.addEventListener('mousedown', e => startDraw(e.offsetX, e.offsetY));
        canvas.addEventListener('mousemove', e => drawLine(e.offsetX, e.offsetY));
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        // Touch events
        canvas.addEventListener('touchstart', e => {
            e.preventDefault();
            let rect = canvas.getBoundingClientRect();
            let touch = e.touches[0];
            startDraw(touch.clientX - rect.left, touch.clientY - rect.top);
        });
        canvas.addEventListener('touchmove', e => {
            e.preventDefault();
            let rect = canvas.getBoundingClientRect();
            let touch = e.touches[0];
            drawLine(touch.clientX - rect.left, touch.clientY - rect.top);
        });
        canvas.addEventListener('touchend', stopDraw);

        function clearPad() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            fillCanvasWhite();
        }

        function isCanvasBlank(canvas) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            const bctx = blank.getContext('2d');
            // Fill white to match our canvas background
            bctx.fillStyle = '#ffffff';
            bctx.fillRect(0, 0, blank.width, blank.height);
            return canvas.toDataURL('image/png') === blank.toDataURL('image/png');
        }

        function showSuccessModal() {
            document.getElementById('successModal').classList.add('active');
        }

        function showErrorModal(message) {
            document.getElementById('errorMessage').innerHTML = message;
            document.getElementById('errorModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('successModal').classList.remove('active');
            document.getElementById('errorModal').classList.remove('active');
        }

        // AJAX Form Submission
        document.getElementById('waiverForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let email = document.getElementById("email").value.trim();
            let telephone = document.getElementById("telephone").value.trim();
            let contactError = document.getElementById("contact-error");
            let signatureError = document.getElementById("signature-error");
            let signatureTextError = document.getElementById("signature-text-error");
            let signatureFileError = document.getElementById("signature-file-error");
            let hasError = false;

            // Reset specific error messages
            if (signatureTextError) signatureTextError.style.display = "none";
            if (signatureFileError) signatureFileError.style.display = "none";

            const mode = document.querySelector('input[name="sig_mode"]:checked').value;
            // Prepare signature based on mode
            if (mode === 'draw') {
                // Export as PNG to preserve visibility and avoid transparency -> black background
                let dataURL = canvas.toDataURL('image/png');
                document.getElementById("signature").value = dataURL;
            } else {
                // Clear hidden data URL when not drawing
                document.getElementById("signature").value = '';
            }

            // Validation
            if (email === "" && telephone === "") {
                contactError.style.display = "block";
                hasError = true;
            } else {
                contactError.style.display = "none";
            }

            // Signature validation by mode
            if (mode === 'draw') {
                if (isCanvasBlank(canvas)) {
                    signatureError.style.display = "block";
                    hasError = true;
                } else {
                    signatureError.style.display = "none";
                }
            } else if (mode === 'type') {
                const txt = (signatureTextInput?.value || '').trim();
                if (!txt) {
                    if (signatureTextError) signatureTextError.style.display = "block";
                    hasError = true;
                }
            } else if (mode === 'upload') {
                const file = signatureFileInput?.files?.[0];
                if (!file) {
                    if (signatureFileError) signatureFileError.style.display = "block";
                    hasError = true;
                } else {
                    const allowed = ['image/png','image/jpeg','image/jpg'];
                    if (!allowed.includes(file.type) || file.size > 3 * 1024 * 1024) {
                        if (signatureFileError) signatureFileError.textContent = 'Please upload a JPG or PNG image up to 3MB.';
                        if (signatureFileError) signatureFileError.style.display = "block";
                        hasError = true;
                    } else {
                        if (signatureFileError) signatureFileError.style.display = "none";
                    }
                }
            }

            if (hasError) {
                return;
            }

            // Show loading spinner
            document.getElementById('loadingSpinner').classList.add('active');
            document.getElementById('submitBtn').disabled = true;

            // Prepare form data
            let formData = new FormData(this);
            formData.set('sig_mode', mode);

            // Submit via AJAX
            fetch('send.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Hide loading spinner
                document.getElementById('loadingSpinner').classList.remove('active');
                document.getElementById('submitBtn').disabled = false;

                if (response.ok) {
                    return response.text();
                } else {
                    return response.text().then(text => {
                        throw new Error(text || 'Submission failed');
                    });
                }
            })
            .then(data => {
                // Success
                showSuccessModal();
                // Reset form
                document.getElementById('waiverForm').reset();
                clearPad();
            })
            .catch(error => {
                // Error
                showErrorModal(error.message);
            });
        });
    </script>
</body>

</html>