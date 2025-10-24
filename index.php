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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        .lang-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .lang-fab-button {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: #26a69a;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .lang-fab-button:hover {
            background-color: #2bbbad;
            box-shadow: 0 6px 10px rgba(0,0,0,0.4);
            transform: scale(1.05);
        }

        .lang-fab-button i {
            color: white;
            font-size: 28px;
        }

        .lang-dropdown {
            position: absolute;
            bottom: 70px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            padding: 8px 0;
            min-width: 180px;
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .lang-dropdown.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .lang-option {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .lang-option:hover {
            background-color: #f5f5f5;
        }

        .lang-option.active {
            background-color: #e0f2f1;
            color: #26a69a;
            font-weight: 500;
        }

        .lang-flag {
            font-size: 20px;
        }

        /* Align children checkbox with booking date prefix icon */
        .checkbox-with-prefix {
            display: flex;
            align-items: center;
            position: relative;
        }
        .checkbox-with-prefix .checkbox-container {
            padding-left: 3rem; /* match Materialize prefix spacing */
            margin: 0;
        }
        /* Tweak icon vertical position similar to inputs */
        .checkbox-with-prefix i.prefix {
            top: 0.6rem;
        }

        /* Align checkbox column with the left prefix icon (booking date) */
        .align-with-prefix {
            padding-left: 3rem; /* same left gutter as .prefix inputs */
            min-height: 3.6rem; /* match input height a bit closer */
            display: flex;
            align-items: center; /* vertically center the checkbox text */
            margin-top: 1.0rem; /* base spacing for small screens */
        }
        .align-with-prefix .checkbox-container label { margin: 0; }

        /* Desktop fine-tuning to line up with date field icon/text */
        @media only screen and (min-width: 992px) {
            .align-with-prefix {
                margin-top: 1.85rem; /* nudge down to align with input baseline */
            }
        }
    </style>
</head>

<body>
    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="success-modal">
            <div class="success-icon">
                <i class="material-icons">check_circle</i>
            </div>
            <h2 data-translate="success_title">Success!</h2>
            <p data-translate="success_message">Your waiver form has been submitted successfully. A confirmation PDF has been generated and sent via email.</p>
            <button class="btn-close" onclick="closeModal()" data-translate="got_it">Got it!</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal-overlay" id="errorModal">
        <div class="error-modal">
            <div class="error-icon">
                <i class="material-icons">error</i>
            </div>
            <h2 data-translate="error_title">Oops!</h2>
            <p id="errorMessage" data-translate="error_message">Something went wrong. Please try again.</p>
            <button class="btn-close" onclick="closeModal()" data-translate="try_again">Try Again</button>
        </div>
    </div>

    <div class="main-container">
        <div class="header-section">
            <div class="center" style="margin-bottom:12px;">
                <img src="MAALUM.png" alt="Maalum Logo">
            </div>
            <h3 class="center" style="text-decoration: underline;" data-translate="pool_title">Maalum Natural Swimming Pool</h3>
            <h4 class="center" style="text-decoration: underline;" data-translate="terms_conditions">Terms & Conditions</h4>
        </div>

        <div class="content-section">
            <p class="center" style="margin:16px 0;">
                <span data-translate="consideration">In consideration for being allowed to access the Maalum Natural Swimming Pool and its facilities,
                the sufficiency of which is hereby acknowledged:</span>
            </p>

            <div style="background:#fff; border:1px solid #e9edf3; border-radius:8px; padding:16px;">
                <ul style="margin-left: 20px;">
                    <li data-translate="rule1">1. In order to preserve the natural resource and in the interests of hygiene, swimmers must visit the toilet and shower before entering the water.</li>
                    <li data-translate="rule2">2. No use of sun creams, chemical products or insect repellents before swimming.</li>
                    <li data-translate="rule3">3. No diving from above the cave area if so, management will hold no responsibility.</li>
                    <li data-translate="rule4">4. No food is allowed on-premises.</li>
                    <li data-translate="rule5">5. Children under 15 must be accompanied by an adult for supervision and they must sign this form on their behalf.</li>
                    <li data-translate="rule6">6. Management hold no liability for any loss or damage for personal items, or any injuries (minor or major).</li>
                    <li data-translate="rule7">7. All rubbish must be disposed of in allocated bins.</li>
                    <li data-translate="rule8">8. Please be aware there is no lifeguard enter pool at your own risk.</li>
                    <li data-translate="rule9">9. No drones are allowed on-premises unless previously notified when booking.</li>
                    <li data-translate="rule10">10. The duration of your slot is 1h30 inside the cave area.</li>
                    <li data-translate="rule11">11. Shouting or making loud noise inside the cave is strictly prohibited.</li>
                </ul>
            </div>

            <p class="center" style="margin-top:16px;">
                <span data-translate="respect">All visitors of Maalum are requested to treat the facilities with respect and as intended.
                We kindly ask you to report any observed defect and any accidents immediately.</span>
            </p>

            <div class="form-section">
                <form id="waiverForm" action="send.php" method="post" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="row">
                        <div class="col s12">
                            <div class="checkbox-container" style="margin-bottom: 20px; padding: 10px 0;">
                                <label style="display: flex; align-items: flex-start;">
                                    <input type="checkbox" class="filled-in" name="agree_terms" required style="margin-top: 4px;">
                                    <span class="black-text" data-translate="agree_terms" style="display: inline-block; margin-left: 10px; line-height: 1.4;">
                                        By checking this box, I confirm that I have read, understood, and agree to the terms and conditions outlined in this waiver form I agree to abide by the rules.
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row ">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">book</i>
                            <input id="bookname" name="bookname" type="text" class="validate" required>
                            <label for="bookname" data-translate="booking_reference">Booking Reference</label>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">supervisor_account</i>
                            <input id="Supervissor-1" name="Supervissor" type="text" class="validate" required>
                            <label for="Supervissor-1" data-translate="supervisor_name">Supervisor Name</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">email</i>
                            <input id="email" name="email" type="email" class="validate">
                            <label for="email" data-translate="email">Email</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">phone</i>
                            <input id="telephone" name="telephone" type="tel" class="validate">
                            <label for="telephone" data-translate="telephone">Telephone</label>
                        </div>
                    </div>

                    <p id="contact-error" class="error-message" data-translate="contact_error">Please provide at least an Email or a Telephone number.</p>

                    <!-- Booking Date + Children toggle (desktop side-by-side) -->
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">event</i>
                            <input id="booking_date" name="booking_date" type="date" required>
                            <label for="booking_date" data-translate="booking_date">Booking Date</label>
                            <p id="booking-date-error" class="error-message" style="display:none;" data-translate="booking_date_error">Please choose a valid date that is today or later.</p>
                        </div>
                        <div class="input-field col s12 m6 align-with-prefix">
                            <div class="checkbox-container" style="margin: 0;">
                                <label style="display:flex; align-items:flex-start;">
                                    <input type="checkbox" id="has_children" name="has_children" class="filled-in" style="margin-top:4px;">
                                    <span class="black-text" data-translate="children_in_party" style="display:inline-block; margin-left:10px; line-height:1.4;">I'm visiting with children (under 13)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Children Responsibility & Ages -->
                    <div class="row">
                        <div class="input-field col s12 m6" id="childrenCountContainer" style="display:none;">
                            <i class="material-icons prefix">child_friendly</i>
                            <select id="children_count" name="children_count">
                                <option value="" disabled selected>0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                            <label for="children_count" data-translate="number_of_children">Number of children</label>
                        </div>
                    </div>

                    <div id="childrenAgesContainer" style="display:none;">
                        <div class="row" id="childrenAgesRow"></div>
                        <div class="row">
                            <div class="col s12">
                                <div class="checkbox-container" style="margin: 10px 0;">
                                    <label style="display:flex; align-items:flex-start;">
                                        <input type="checkbox" id="children_responsibility" name="children_responsibility" class="filled-in" style="margin-top:4px;">
                                        <span class="black-text" data-translate="children_responsibility_ack" style="display:inline-block; margin-left:10px; line-height:1.4;">I acknowledge responsibility for the children listed above during our visit.</span>
                                    </label>
                                </div>
                                <p id="children-ages-error" class="error-message" style="display:none;" data-translate="children_ages_error">Please provide the age for each child (0-17).</p>
                                <p id="children-responsibility-error" class="error-message" style="display:none;" data-translate="children_responsibility_error">Please acknowledge responsibility for the listed children.</p>
                            </div>
                        </div>
                    </div>

                    <div class="signature-container">
                        <div class="signature-title">
                            <i class="material-icons" style="vertical-align: middle;">create</i>
                            <span data-translate="sign_below">Please sign below</span>
                        </div>

                        <!-- Signature mode selector -->
                        <div style="margin: 10px 0; text-align:center;">
                            <label style="margin-right: 12px;">
                                <input name="sig_mode" type="radio" value="draw" id="sigModeDraw" checked>
                                <span data-translate="draw_signature">Draw</span>
                            </label>
                            <label style="margin-right: 12px;">
                                <input name="sig_mode" type="radio" value="type" id="sigModeType">
                                <span data-translate="type_signature">Type</span>
                            </label>
                            <label>
                                <input name="sig_mode" type="radio" value="upload" id="sigModeUpload">
                                <span data-translate="upload_signature">Upload</span>
                            </label>
                        </div>

                        <!-- Draw section -->
                        <div id="sig-draw-section">
                            <canvas id="signature-pad"></canvas>
                            <div class="btns">
                                <button type="button" class="btn red waves-effect waves-light" onclick="clearPad()">
                                    <i class="material-icons left">clear</i>
                                    <span data-translate="clear_signature">Clear</span>
                                </button>
                            </div>
                            <input type="hidden" name="signature" id="signature">
                            <p id="signature-error" class="error-message" data-translate="signature_error">Please provide a signature.</p>
                        </div>

                        <!-- Type section -->
                        <div id="sig-type-section" style="display:none;">
                            <div class="input-field" style="margin-top:20px;">
                                <i class="material-icons prefix">edit</i>
                                <input id="signature_text" name="signature_text" type="text" placeholder="Type your full name as signature">
                                <label for="signature_text" data-translate="typed_signature">Typed Signature</label>
                            </div>
                            <p id="signature-text-error" class="error-message" data-translate="signature_text_error">Please type your signature.</p>
                        </div>

                        <!-- Upload section -->
                        <div id="sig-upload-section" style="display:none; text-align:center;">
                            <input id="signature_file" name="signature_file" type="file" accept="image/*" style="margin-top:10px;">
                            <p style="font-size: 12px; color:#666;" data-translate="signature_upload_info">Accepted: JPG, PNG. Max 3MB.</p>
                            <p id="signature-file-error" class="error-message" data-translate="signature_file_error">Please upload a signature image (JPG/PNG).</p>
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

    <!-- Floating Language Selector -->
    <div class="lang-fab">
        <div class="lang-fab-button" id="langFabButton">
            <i class="material-icons">language</i>
        </div>
        <div class="lang-dropdown" id="langDropdown">
            <div class="lang-option" onclick="changeLanguage('en')">
                <span class="lang-flag">🇺🇸</span>
                <span>English</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('es')">
                <span class="lang-flag">🇪🇸</span>
                <span>Español</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('fr')">
                <span class="lang-flag">🇫🇷</span>
                <span>Français</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('de')">
                <span class="lang-flag">🇩🇪</span>
                <span>Deutsch</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('it')">
                <span class="lang-flag">🇮🇹</span>
                <span>Italiano</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('pl')">
                <span class="lang-flag">🇵🇱</span>
                <span>Polski</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('cs')">
                <span class="lang-flag">🇨🇿</span>
                <span>Čeština</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('zh')">
                <span class="lang-flag">🇨🇳</span>
                <span>中文</span>
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

        // Language FAB functionality
        const langFabButton = document.getElementById('langFabButton');
        const langDropdown = document.getElementById('langDropdown');
        
        langFabButton.addEventListener('click', function() {
            langDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!langFabButton.contains(event.target) && !langDropdown.contains(event.target)) {
                langDropdown.classList.remove('active');
            }
        });

        // Booking date and children handling
        function setMinBookingDate() {
            const bookingEl = document.getElementById('booking_date');
            if (!bookingEl) return;
            const tzOffsetMs = (new Date()).getTimezoneOffset() * 60000;
            const todayLocal = new Date(Date.now() - tzOffsetMs).toISOString().slice(0,10);
            bookingEl.min = todayLocal;
        }

        function generateAgeInputs(count) {
            const container = document.getElementById('childrenAgesRow');
            if (!container) return;
            container.innerHTML = '';
            for (let i = 1; i <= count; i++) {
                const col = document.createElement('div');
                col.className = 'input-field col s6 m3';
                const tmpl = (translations[currentLang] && translations[currentLang]['child_age_n'])
                    ? translations[currentLang]['child_age_n']
                    : (translations['en']['child_age_n'] || 'Child {n} Age');
                const labelText = tmpl.replace('{n}', i);
                col.innerHTML = `
                    <i class="material-icons prefix">cake</i>
                    <input type="number" min="0" max="17" step="1" class="validate child-age" name="child_age_${i}" id="child_age_${i}">
                    <label for="child_age_${i}" data-translate="child_age_n" data-n="${i}">${labelText}</label>
                `;
                container.appendChild(col);
            }
        }

        function initChildrenHandlers() {
            const hasChildren = document.getElementById('has_children');
            const countContainer = document.getElementById('childrenCountContainer');
            const agesContainer = document.getElementById('childrenAgesContainer');
            const childrenCount = document.getElementById('children_count');

            if (!hasChildren || !countContainer || !agesContainer) return;

            hasChildren.addEventListener('change', () => {
                const show = hasChildren.checked;
                countContainer.style.display = show ? 'block' : 'none';
                agesContainer.style.display = 'none';
                if (!show) {
                    if (childrenCount) childrenCount.value = '';
                    const row = document.getElementById('childrenAgesRow');
                    if (row) row.innerHTML = '';
                } else {
                    // Initialize Materialize select if available
                    if (M && M.FormSelect && childrenCount) {
                        M.FormSelect.init(childrenCount);
                    }
                }
            });

            if (childrenCount) {
                childrenCount.addEventListener('change', () => {
                    const val = parseInt(childrenCount.value, 10);
                    if (Number.isInteger(val) && val > 0) {
                        generateAgeInputs(val);
                        agesContainer.style.display = 'block';
                    } else {
                        agesContainer.style.display = 'none';
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setMinBookingDate();
            initChildrenHandlers();
        });

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

            // Booking date validation (must be today or later)
            const bookingDateEl = document.getElementById('booking_date');
            const bookingDateError = document.getElementById('booking-date-error');
            if (bookingDateEl) {
                const val = bookingDateEl.value;
                if (!val) {
                    if (bookingDateError) bookingDateError.style.display = 'block';
                    hasError = true;
                } else {
                    const tzOffsetMs = (new Date()).getTimezoneOffset() * 60000;
                    const todayLocal = new Date(Date.now() - tzOffsetMs).toISOString().slice(0,10);
                    if (val < todayLocal) {
                        if (bookingDateError) bookingDateError.style.display = 'block';
                        hasError = true;
                    } else if (bookingDateError) {
                        bookingDateError.style.display = 'none';
                    }
                }
            }

            // Children validation if applicable
            const hasChildren = document.getElementById('has_children');
            if (hasChildren && hasChildren.checked) {
                const childrenCount = document.getElementById('children_count');
                const agesError = document.getElementById('children-ages-error');
                const respAck = document.getElementById('children_responsibility');
                const respError = document.getElementById('children-responsibility-error');
                let validAges = true;
                const ageInputs = document.querySelectorAll('.child-age');
                if (!ageInputs.length) validAges = false;
                ageInputs.forEach(inp => {
                    const v = inp.value.trim();
                    const n = Number(v);
                    if (v === '' || !Number.isInteger(n) || n < 0 || n > 17) {
                        validAges = false;
                    }
                });
                if (!childrenCount || !childrenCount.value || !validAges) {
                    if (agesError) agesError.style.display = 'block';
                    hasError = true;
                } else if (agesError) {
                    agesError.style.display = 'none';
                }
                if (!respAck || !respAck.checked) {
                    if (respError) respError.style.display = 'block';
                    hasError = true;
                } else if (respError) {
                    respError.style.display = 'none';
                }
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
    <script>
        const translations = {
            en: {
                pool_title: 'Maalum Natural Swimming Pool',
                terms_conditions: 'Terms & Conditions',
                consideration: 'In consideration for being allowed to access the Maalum Natural Swimming Pool and its facilities, the sufficiency of which is hereby acknowledged:',
                rule1: '1. In order to preserve the natural resource and in the interests of hygiene, swimmers must visit the toilet and shower before entering the water.',
                rule2: '2. No use of sun creams, chemical products or insect repellents before swimming.',
                rule3: '3. No diving from above the cave area if so, management will hold no responsibility.',
                rule4: '4. No food is allowed on-premises.',
                rule5: '5. Children under 15 must be accompanied by an adult for supervision and they must sign this form on their behalf.',
                rule6: '6. Management hold no liability for any loss or damage for personal items, or any injuries (minor or major).',
                rule7: '7. All rubbish must be disposed of in allocated bins.',
                rule8: '8. Please be aware there is no lifeguard enter pool at your own risk.',
                rule9: '9. No drones are allowed on-premises unless previously notified when booking.',
                rule10: '10. The duration of your slot is 1h30 inside the cave area.',
                rule11: '11. Shouting or making loud noise inside the cave is strictly prohibited.',
                respect: 'All visitors of Maalum are requested to treat the facilities with respect and as intended. We kindly ask you to report any observed defect and any accidents immediately.',
                agree_terms: 'By checking this box, I confirm that I have read, understood, and agree to the terms and conditions outlined in this waiver form I agree to abide by the rules.',
                booking_reference: 'Booking Name',
                supervisor_name: 'Supervisor Name',
                email: 'Email',
                telephone: 'Telephone',
                contact_error: 'Please provide at least an Email or a Telephone number.',
                sign_below: 'Please sign below',
                signature_error: 'Please provide a signature.',
                draw_signature: 'Draw',
                type_signature: 'Type',
                upload_signature: 'Upload',
                clear_signature: 'Clear',
                typed_signature: 'Typed Signature',
                signature_text_error: 'Please type your signature.',
                signature_upload_info: 'Accepted: JPG, PNG. Max 3MB.',
                signature_file_error: 'Please upload a signature image (JPG/PNG).',
                submit_waiver: 'Submit Waiver',
                success_title: 'Success!',
                success_message: 'Your waiver form has been submitted successfully. A confirmation PDF has been generated and sent via email.',
                got_it: 'Got it!',
                error_title: 'Oops!',
                error_message: 'Something went wrong. Please try again.',
                try_again: 'Try Again',
                booking_date: 'Arrival Date',
                booking_date_error: 'Please choose a valid date that is today or later.',
                children_in_party: "I'm visiting with children (under 13)",
                number_of_children: 'Number of children',
                child_age_label: 'Child Age',
                child_age_n: 'Child {n} Age',
                children_ages_error: 'Please provide the age for each child (0-17).',
                children_responsibility_ack: 'I acknowledge responsibility for the children listed above during our visit.',
                children_responsibility_error: 'Please acknowledge responsibility for the listed children.'
            },
            es: {
                pool_title: 'Piscina Natural Maalum',
                terms_conditions: 'Términos y Condiciones',
                consideration: 'En consideración por permitir el acceso a la Piscina Natural Maalum y sus instalaciones, cuya suficiencia se reconoce por la presente:',
                rule1: '1. Para preservar el recurso natural y por razones de higiene, los nadadores deben visitar el baño y ducharse antes de entrar al agua.',
                rule2: '2. No usar cremas solares, productos químicos o repelentes de insectos antes de nadar.',
                rule3: '3. No bucear desde arriba del área de la cueva; si lo hace, la gerencia no se hará responsable.',
                rule4: '4. No se permite comida en las instalaciones.',
                rule5: '5. Los niños menores de 15 años deben estar acompañados por un adulto para su supervisión y deben firmar este formulario en su nombre.',
                rule6: '6. La gerencia no se hace responsable por cualquier pérdida o daño de artículos personales, o cualquier lesión (menor o mayor).',
                rule7: '7. Toda la basura debe ser depositada en los contenedores asignados.',
                rule8: '8. Tenga en cuenta que no hay socorrista; entre a la piscina bajo su propio riesgo.',
                rule9: '9. No se permiten drones en las instalaciones a menos que se notifique previamente al hacer la reserva.',
                rule10: '10. La duración de su turno es de 1h30 dentro del área de la cueva.',
                rule11: '11. Está estrictamente prohibido gritar o hacer ruidos fuertes dentro de la cueva.',
                respect: 'Se solicita a todos los visitantes de Maalum que traten las instalaciones con respeto y según lo previsto. Le pedimos amablemente que informe de inmediato cualquier defecto observado y cualquier accidente.',
                agree_terms: 'Al marcar esta casilla, confirmo que he leído, entendido y acepto los términos y condiciones descritos en este formulario de exención y me comprometo a cumplir las reglas.',
                booking_reference: 'Nombre de la reserva',
                supervisor_name: 'Nombre del Supervisor',
                email: 'Correo Electrónico',
                telephone: 'Teléfono',
                contact_error: 'Proporcione al menos un correo electrónico o un número de teléfono.',
                sign_below: 'Por favor, firme a continuación',
                signature_error: 'Por favor, proporcione una firma.',
                draw_signature: 'Dibujar',
                type_signature: 'Escribir',
                upload_signature: 'Subir',
                clear_signature: 'Limpiar',
                typed_signature: 'Firma Escrita',
                signature_text_error: 'Por favor, escriba su firma.',
                signature_upload_info: 'Aceptado: JPG, PNG. Máx. 3MB.',
                signature_file_error: 'Por favor, suba una imagen de firma (JPG/PNG).',
                submit_waiver: 'Enviar Exención',
                success_title: '¡Éxito!',
                success_message: 'Su formulario de exención ha sido enviado con éxito. Se ha generado y enviado un PDF de confirmación por correo electrónico.',
                got_it: '¡Entendido!',
                error_title: '¡Ups!',
                error_message: 'Algo salió mal. Por favor, inténtelo de nuevo.',
                try_again: 'Intentar de Nuevo',
                booking_date: 'Fecha de llegada',
                booking_date_error: 'Elija una fecha válida que sea hoy o posterior.',
                children_in_party: 'Visito con niños (menores de 13)',
                number_of_children: 'Número de niños',
                child_age_label: 'Edad del niño',
                child_age_n: 'Edad del niño {n}',
                children_ages_error: 'Indique la edad de cada niño (0-17).',
                children_responsibility_ack: 'Reconozco la responsabilidad de los niños indicados durante nuestra visita.',
                children_responsibility_error: 'Reconozca la responsabilidad por los niños indicados.'
            },
            fr: {
                pool_title: 'Piscine Naturelle Maalum',
                terms_conditions: 'Termes et Conditions',
                consideration: 'En contrepartie de l\'accès à la Piscine Naturelle Maalum et à ses installations, dont la suffisance est reconnue :',
                rule1: '1. Afin de préserver la ressource naturelle et pour des raisons d\'hygiène, les nageurs doivent utiliser les toilettes et se doucher avant d\'entrer dans l\'eau.',
                rule2: '2. L\'utilisation de crèmes solaires, de produits chimiques ou de répulsifs anti-insectes avant la baignade est interdite.',
                rule3: '3. Il est strictement interdit de plonger ou de sauter depuis la partie supérieure de la grotte ; la direction n\'assume aucune responsabilité en cas de non-respect.',
                rule4: '4. La nourriture n\'est pas autorisée dans l\'enceinte de l\'établissement.',
                rule5: '5. Les enfants de moins de 15 ans doivent être accompagnés d\'un adulte, qui devra signer ce formulaire en leur nom.',
                rule6: '6. La direction décline toute responsabilité en cas de perte ou de dommage d\'objets personnels ou de blessures (mineures ou graves).',
                rule7: '7. Tous les déchets doivent être jetés dans les poubelles prévues à cet effet.',
                rule8: '8. Aucun maître-nageur n\'est présent ; l\'entrée dans la piscine se fait à vos propres risques.',
                rule9: '9. Les drones sont interdits dans l\'enceinte, sauf autorisation préalable lors de la réservation.',
                rule10: '10. La durée de votre session est de 1 h 30 à l\'intérieur de la grotte.',
                rule11: '11. Crier ou faire du bruit à l\'intérieur de la grotte est strictement interdit.',
                respect: 'Tous les visiteurs de Maalum sont priés de respecter les lieux et de les utiliser conformément à leur destination. Nous vous demandons de signaler immédiatement tout défaut ou tout accident.',
                booking_reference: 'Nom de la réservation',
                supervisor_name: 'Nom du superviseur',
                email: 'E-mail',
                telephone: 'Téléphone',
                contact_error: 'Veuillez fournir au moins un e-mail ou un numéro de téléphone.',
                agree_terms: 'En cochant cette case, je confirme avoir lu, compris et accepter les termes et conditions de cette décharge et m\'engage à respecter les règles.',
                sign_below: 'Veuillez signer ci-dessous',
                signature_error: 'Veuillez fournir une signature.',
                draw_signature: 'Dessiner',
                type_signature: 'Saisir',
                upload_signature: 'Téléverser',
                clear_signature: 'Effacer',
                typed_signature: 'Signature saisie',
                signature_text_error: 'Veuillez saisir votre signature.',
                signature_upload_info: 'Accepté : JPG, PNG. Max 3 Mo.',
                signature_file_error: 'Veuillez téléverser une image de signature (JPG/PNG).',
                submit_waiver: 'Envoyer la décharge',
                success_title: 'Succès !',
                success_message: 'Votre formulaire a été soumis avec succès. Un PDF de confirmation a été envoyé par e-mail.',
                got_it: 'Compris !',
                error_title: 'Oups !',
                error_message: 'Une erreur est survenue. Veuillez réessayer.',
                try_again: 'Réessayer',
                booking_date: 'Date d’arrivée',
                children_in_party: 'Je viens avec des enfants (moins de 13 ans)',
                booking_date: 'Date de réservation',
                booking_date_error: 'Veuillez choisir une date valide à partir d’aujourd’hui.',
                children_in_party: 'Je viens avec des enfants (moins de 15 ans)',
                number_of_children: 'Nombre d’enfants',
                child_age_label: 'Âge de l’enfant',
                children_ages_error: 'Veuillez indiquer l’âge de chaque enfant (0–17).',
                children_responsibility_ack: 'Je reconnais être responsable des enfants indiqués ci-dessus pendant notre visite.',
                children_responsibility_error: 'Veuillez confirmer la responsabilité pour les enfants indiqués.'
            },
            de: {
                pool_title: 'Maalum Natur-Schwimmbad',
                terms_conditions: 'Allgemeine Geschäftsbedingungen',
                consideration: 'Als Gegenleistung für den Zugang zum Maalum Natur-Schwimmbad und seinen Einrichtungen, deren Angemessenheit hiermit anerkannt wird:',
                rule1: '1. Um die natürliche Ressource zu erhalten und aus hygienischen Gründen müssen Schwimmer vor dem Betreten des Wassers die Toilette aufsuchen und duschen.',
                rule2: '2. Keine Verwendung von Sonnencremes, chemischen Produkten oder Insektenschutzmitteln vor dem Schwimmen.',
                rule3: '3. Kein Springen von oberhalb des Höhlenbereichs; andernfalls übernimmt die Leitung keine Verantwortung.',
                rule4: '4. Keine Speisen auf dem Gelände erlaubt.',
                rule5: '5. Kinder unter 15 Jahren müssen von einem Erwachsenen beaufsichtigt werden; dieser muss das Formular in ihrem Namen unterschreiben.',
                rule6: '6. Die Geschäftsleitung übernimmt keine Haftung für Verlust oder Beschädigung persönlicher Gegenstände oder für Verletzungen (leichte oder schwere).',
                rule7: '7. Sämtlicher Müll ist in die dafür vorgesehenen Behälter zu entsorgen.',
                rule8: '8. Bitte beachten Sie, dass kein Bademeister anwesend ist; Betreten des Pools auf eigene Gefahr.',
                rule9: '9. Drohnen sind auf dem Gelände nicht erlaubt, es sei denn, dies wurde bei der Buchung vorher angemeldet.',
                rule10: '10. Die Dauer Ihres Zeitfensters beträgt 1 Std. 30 Min. im Höhlenbereich.',
                rule11: '11. Rufen oder lautes Lärmen in der Höhle ist strengstens verboten.',
                respect: 'Alle Besucher von Maalum werden gebeten, die Einrichtungen respektvoll und bestimmungsgemäß zu behandeln. Bitte melden Sie etwaige Mängel und Unfälle umgehend.',
                booking_reference: 'Buchungsname',
                supervisor_name: 'Name des Aufsehers',
                email: 'E-Mail',
                telephone: 'Telefon',
                contact_error: 'Bitte geben Sie mindestens eine E-Mail oder Telefonnummer an.',
                agree_terms: 'Durch Ankreuzen dieses Kästchens bestätige ich, dass ich die Bedingungen gelesen, verstanden und akzeptiert habe und die Regeln einhalten werde.',
                sign_below: 'Bitte unten unterschreiben',
                signature_error: 'Bitte geben Sie eine Unterschrift an.',
                draw_signature: 'Zeichnen',
                type_signature: 'Tippen',
                upload_signature: 'Hochladen',
                clear_signature: 'Löschen',
                typed_signature: 'Getippte Unterschrift',
                signature_text_error: 'Bitte tippen Sie Ihre Unterschrift.',
                signature_upload_info: 'Akzeptiert: JPG, PNG. Max. 3 MB.',
                signature_file_error: 'Bitte laden Sie ein Bild der Unterschrift hoch (JPG/PNG).',
                submit_waiver: 'Verzichtserklärung senden',
                success_title: 'Erfolg!',
                success_message: 'Ihr Formular wurde erfolgreich eingereicht. Eine Bestätigungs-PDF wurde per E-Mail gesendet.',
                got_it: 'Verstanden!',
                error_title: 'Hoppla!',
                error_message: 'Etwas ist schiefgelaufen. Bitte erneut versuchen.',
                try_again: 'Erneut versuchen',
                booking_date: 'Anreisedatum',
                booking_date_error: 'Bitte wählen Sie ein gültiges Datum ab heute.',
                children_in_party: 'Ich komme mit Kindern (unter 13)',
                number_of_children: 'Anzahl der Kinder',
                child_age_label: 'Alter des Kindes',
                child_age_n: 'Alter des Kindes {n}',
                children_ages_error: 'Bitte geben Sie für jedes Kind das Alter an (0–17).',
                children_responsibility_ack: 'Ich bestätige die Verantwortung für die oben aufgeführten Kinder während unseres Besuchs.',
                children_responsibility_error: 'Bitte bestätigen Sie die Verantwortung für die aufgeführten Kinder.'
            },
            it: {
                pool_title: 'Piscina Naturale Maalum',
                terms_conditions: 'Termini e Condizioni',
                consideration: 'In considerazione dell\'accesso alla Piscina Naturale Maalum e alle sue strutture, la cui sufficienza è qui riconosciuta:',
                rule1: '1. Per preservare la risorsa naturale e per motivi di igiene, i bagnanti devono usare i servizi igienici e fare la doccia prima di entrare in acqua.',
                rule2: '2. Vietato l\'uso di creme solari, prodotti chimici o repellenti per insetti prima di nuotare.',
                rule3: '3. Vietato tuffarsi dall\'area superiore della grotta; in caso contrario, la direzione declina ogni responsabilità.',
                rule4: '4. Non è consentito introdurre cibo nei locali.',
                rule5: '5. I minori di 15 anni devono essere accompagnati da un adulto per la supervisione e quest\'ultimo deve firmare il modulo per loro conto.',
                rule6: '6. La direzione non si assume alcuna responsabilità per perdita o danneggiamento di effetti personali, né per lesioni (lievi o gravi).',
                rule7: '7. Tutti i rifiuti devono essere gettati negli appositi cestini.',
                rule8: '8. Si prega di notare che non è presente un bagnino; l\'accesso alla piscina è a proprio rischio.',
                rule9: '9. I droni non sono ammessi nei locali, salvo previa comunicazione al momento della prenotazione.',
                rule10: '10. La durata della fascia oraria è di 1 h e 30 min all\'interno dell\'area della grotta.',
                rule11: '11. È severamente vietato urlare o fare rumori forti all\'interno della grotta.',
                respect: 'Si chiede a tutti i visitatori di Maalum di trattare le strutture con rispetto e secondo la loro destinazione d\'uso. Si prega di segnalare immediatamente eventuali difetti o incidenti.',
                booking_reference: 'Nome della prenotazione',
                supervisor_name: 'Nome del supervisore',
                email: 'Email',
                telephone: 'Telefono',
                contact_error: 'Fornisci almeno un indirizzo e-mail o un numero di telefono.',
                agree_terms: 'Selezionando questa casella confermo di aver letto, compreso e accettato i termini e le condizioni di questa liberatoria e di rispettare le regole.',
                sign_below: 'Si prega di firmare qui sotto',
                signature_error: 'Si prega di fornire una firma.',
                draw_signature: 'Disegna',
                type_signature: 'Digita',
                upload_signature: 'Carica',
                clear_signature: 'Cancella',
                typed_signature: 'Firma digitata',
                signature_text_error: 'Per favore digita la tua firma.',
                signature_upload_info: 'Accettati: JPG, PNG. Max 3MB.',
                signature_file_error: 'Carica un\'immagine della firma (JPG/PNG).',
                submit_waiver: 'Invia liberatoria',
                success_title: 'Successo!',
                success_message: 'Il modulo è stato inviato con successo. Un PDF di conferma è stato inviato via e-mail.',
                got_it: 'Ho capito!',
                error_title: 'Ops!',
                error_message: 'Qualcosa è andato storto. Riprova.',
                try_again: 'Riprova',
                booking_date: 'Data di arrivo',
                booking_date_error: 'Seleziona una data valida a partire da oggi.',
                children_in_party: 'Visito con bambini (sotto i 13 anni)',
                number_of_children: 'Numero di bambini',
                child_age_label: 'Età del bambino',
                child_age_n: 'Età del bambino {n}',
                children_ages_error: 'Inserisci l’età di ogni bambino (0–17).',
                children_responsibility_ack: 'Riconosco la responsabilità per i bambini elencati sopra durante la visita.',
                children_responsibility_error: 'Conferma la responsabilità per i bambini elencati.'
            },
            pl: {
                pool_title: 'Naturalny Basen Maalum',
                terms_conditions: 'Warunki i Zasady',
                consideration: 'W zamian za dostęp do Naturalnego Basenu Maalum i jego udogodnień, którego wystarczalność jest niniejszym potwierdzona:',
                rule1: '1. W celu ochrony zasobów naturalnych oraz ze względów higienicznych pływający muszą skorzystać z toalety i wziąć prysznic przed wejściem do wody.',
                rule2: '2. Zakaz używania kremów do opalania, środków chemicznych oraz repelentów na owady przed pływaniem.',
                rule3: '3. Zakaz skakania z górnej części obszaru jaskini; w przeciwnym razie zarząd nie ponosi odpowiedzialności.',
                rule4: '4. Na terenie obiektu obowiązuje zakaz spożywania jedzenia.',
                rule5: '5. Dzieci poniżej 15. roku życia muszą pozostawać pod opieką osoby dorosłej; opiekun musi podpisać formularz w ich imieniu.',
                rule6: '6. Zarząd nie ponosi odpowiedzialności za utratę lub uszkodzenie rzeczy osobistych ani za jakiekolwiek obrażenia (drobne lub poważne).',
                rule7: '7. Wszelkie śmieci należy wyrzucać do wyznaczonych pojemników.',
                rule8: '8. Prosimy pamiętać, że na terenie nie ma ratownika; wchodzisz do basenu na własne ryzyko.',
                rule9: '9. Drony są zabronione na terenie obiektu, chyba że zgłoszono to wcześniej podczas rezerwacji.',
                rule10: '10. Czas trwania Twojego slotu wynosi 1 godz. 30 min w strefie jaskini.',
                rule11: '11. Krzyczenie lub wydawanie głośnych dźwięków w jaskini jest surowo zabronione.',
                respect: 'Prosimy wszystkich odwiedzających Maalum o traktowanie obiektu z szacunkiem i zgodnie z jego przeznaczeniem. Prosimy o niezwłoczne zgłaszanie wszelkich zauważonych usterek i wypadków.',
                booking_reference: 'Nazwa rezerwacji',
                supervisor_name: 'Imię i nazwisko opiekuna',
                email: 'E-mail',
                telephone: 'Telefon',
                contact_error: 'Podaj co najmniej adres e-mail lub numer telefonu.',
                agree_terms: 'Zaznaczając to pole, potwierdzam, że przeczytałem/am, zrozumiałem/am i akceptuję warunki tej zgody oraz zobowiązuję się przestrzegać zasad.',
                sign_below: 'Proszę podpisać poniżej',
                signature_error: 'Proszę podać podpis.',
                draw_signature: 'Rysuj',
                type_signature: 'Pisz',
                upload_signature: 'Prześlij',
                clear_signature: 'Wyczyść',
                typed_signature: 'Podpis pisemny',
                signature_text_error: 'Proszę wpisać swój podpis.',
                signature_upload_info: 'Akceptowane: JPG, PNG. Maks. 3MB.',
                signature_file_error: 'Prześlij obraz podpisu (JPG/PNG).',
                submit_waiver: 'Wyślij formularz',
                success_title: 'Sukces!',
                success_message: 'Formularz został pomyślnie wysłany. PDF potwierdzający został wysłany e-mailem.',
                got_it: 'Rozumiem!',
                error_title: 'Ups!',
                error_message: 'Coś poszło nie tak. Spróbuj ponownie.',
                try_again: 'Spróbuj ponownie',
                booking_date: 'Data przyjazdu',
                booking_date_error: 'Wybierz prawidłową datę od dziś.',
                children_in_party: 'Odwiedzam z dziećmi (poniżej 13 lat)',
                number_of_children: 'Liczba dzieci',
                child_age_label: 'Wiek dziecka',
                child_age_n: 'Wiek dziecka {n}',
                children_ages_error: 'Podaj wiek każdego dziecka (0–17).',
                children_responsibility_ack: 'Potwierdzam odpowiedzialność za wymienione powyżej dzieci podczas wizyty.',
                children_responsibility_error: 'Potwierdź odpowiedzialność za wymienione dzieci.'
            },
            cs: {
                pool_title: 'Přírodní bazén Maalum',
                terms_conditions: 'Obchodní podmínky',
                consideration: 'Vzhledem k povolenému přístupu do Přírodního bazénu Maalum a jeho zařízení, jehož dostatečnost je tímto uznána:',
                rule1: '1. Za účelem zachování přírodního zdroje a z hygienických důvodů musí plavci před vstupem do vody použít toaletu a osprchovat se.',
                rule2: '2. Před plaváním nepoužívejte opalovací krémy, chemické přípravky ani repelenty proti hmyzu.',
                rule3: '3. Zákaz skákání z horní části oblasti jeskyně; v opačném případě vedení nepřebírá žádnou odpovědnost.',
                rule4: '4. Jídlo není v areálu povoleno.',
                rule5: '5. Děti mladší 15 let musí být pod dohledem dospělé osoby, která za ně musí tento formulář podepsat.',
                rule6: '6. Vedení nenese odpovědnost za ztrátu nebo poškození osobních věcí ani za jakékoli zranění (menší či větší).',
                rule7: '7. Veškerý odpad odhazujte do vyhrazených košů.',
                rule8: '8. Upozorňujeme, že zde není plavčík; vstup do bazénu je na vlastní nebezpečí.',
                rule9: '9. Drony nejsou v areálu povoleny, pokud to nebylo předem nahlášeno při rezervaci.',
                rule10: '10. Doba vašeho časového slotu je 1 hodina 30 minut v prostoru jeskyně.',
                rule11: '11. Křik nebo vydávání hlasitých zvuků uvnitř jeskyně je přísně zakázáno.',
                respect: 'Všechny návštěvníky Maalum žádáme, aby zařízení používali s respektem a v souladu s jejich účelem. Jakékoli zjištěné závady a nehody prosím neprodleně hlaste.',
                booking_reference: 'Název rezervace',
                supervisor_name: 'Jméno supervizora',
                email: 'E-mail',
                telephone: 'Telefon',
                contact_error: 'Uveďte prosím alespoň e-mail nebo telefonní číslo.',
                agree_terms: 'Zaškrtnutím tohoto políčka potvrzuji, že jsem si přečetl/a, porozuměl/a a přijímám podmínky této výjimky a budu dodržovat pravidla.',
                sign_below: 'Prosím, podepište níže',
                signature_error: 'Uveďte prosím podpis.',
                draw_signature: 'Kreslit',
                type_signature: 'Psát',
                upload_signature: 'Nahrát',
                clear_signature: 'Vymazat',
                typed_signature: 'Psaná podpis',
                signature_text_error: 'Prosím, napište svůj podpis.',
                signature_upload_info: 'Povoleno: JPG, PNG. Max. 3 MB.',
                signature_file_error: 'Nahrajte prosím obrázek podpisu (JPG/PNG).',
                submit_waiver: 'Odeslat formulář',
                success_title: 'Úspěch!',
                success_message: 'Formulář byl úspěšně odeslán. Potvrzující PDF bylo zasláno e-mailem.',
                got_it: 'Rozumím!',
                error_title: 'Jejda!',
                error_message: 'Něco se pokazilo. Zkuste to prosím znovu.',
                try_again: 'Zkusit znovu',
                booking_date: 'Datum příjezdu',
                booking_date_error: 'Vyberte platné datum ode dneška.',
                children_in_party: 'Navštěvuji s dětmi (do 13 let)',
                number_of_children: 'Počet dětí',
                child_age_label: 'Věk dítěte',
                child_age_n: 'Věk dítěte {n}',
                children_ages_error: 'Uveďte věk každého dítěte (0–17).',
                children_responsibility_ack: 'Potvrzuji odpovědnost za výše uvedené děti během naší návštěvy.',
                children_responsibility_error: 'Potvrďte prosím odpovědnost za uvedené děti.'
            },
            zh: {
                pool_title: 'Maalum 自然游泳池',
                terms_conditions: '条款与条件',
                consideration: '鉴于允许进入 Maalum 自然游泳池及其设施，特此承认该许可的充分性：',
                rule1: '1. 为了保护自然资源并出于卫生原因，游泳者在入水前必须先上厕所并淋浴。',
                rule2: '2. 游泳前不得使用防晒霜、化学产品或驱虫剂。',
                rule3: '3. 禁止从洞穴上方跳水；否则管理方概不负责。',
                rule4: '4. 场地内禁止携带和食用食物。',
                rule5: '5. 15岁以下儿童必须由成人陪同并监督，且需由成人代为签署本表格。',
                rule6: '6. 管理方不对个人物品的任何遗失或损坏，或任何伤害（轻微或严重）承担责任。',
                rule7: '7. 所有垃圾必须丢入指定的垃圾桶。',
                rule8: '8. 请注意，现场没有救生员；进入泳池需自担风险。',
                rule9: '9. 场地内禁止无人机，除非在预订时已事先说明。',
                rule10: '10. 您在洞穴区域的时段为1小时30分钟。',
                rule11: '11. 在洞穴内严禁喊叫或制造噪音。',
                respect: '请所有来访者尊重并按用途使用设施。请立即报告任何发现的缺陷和事故。',
                booking_reference: '预订姓名',
                supervisor_name: '监督员姓名',
                email: '电子邮件',
                telephone: '电话',
                contact_error: '请至少提供电子邮件或电话号码。',
                agree_terms: '勾选此框即表示我已阅读、理解并同意本免责条款，并承诺遵守相关规定。',
                sign_below: '请在下方签名',
                signature_error: '请提供签名。',
                draw_signature: '手写',
                type_signature: '输入',
                upload_signature: '上传',
                clear_signature: '清除',
                typed_signature: '输入签名',
                signature_text_error: '请输入您的签名。',
                signature_upload_info: '接受：JPG、PNG。最大 3MB。',
                signature_file_error: '请上传签名图片（JPG/PNG）。',
                submit_waiver: '提交豁免',
                success_title: '成功！',
                success_message: '您的表单已成功提交。确认 PDF 已通过电子邮件发送。',
                got_it: '知道了！',
                error_title: '哎呀！',
                error_message: '出了点问题。请重试。',
                try_again: '重试',
                booking_date: '到达日期',
                booking_date_error: '请选择今天或之后的有效日期。',
                children_in_party: '我将携带儿童（13岁以下）',
                number_of_children: '儿童人数',
                child_age_label: '儿童年龄',
                child_age_n: '儿童{n}年龄',
                children_ages_error: '请为每位儿童填写年龄（0-17）。',
                children_responsibility_ack: '我确认在参观期间对以上所列儿童负有责任。',
                children_responsibility_error: '请确认对所列儿童承担责任。'
            }
        };

        let currentLang = 'en';

        function changeLanguage(lang) {
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                let text;
                if (key === 'child_age_n') {
                    const n = element.getAttribute('data-n') || '';
                    const tmpl = (translations[lang] && translations[lang]['child_age_n'])
                        ? translations[lang]['child_age_n']
                        : (translations['en']['child_age_n'] || element.innerHTML);
                    text = tmpl.replace('{n}', n);
                } else {
                    text = (translations[lang] && translations[lang][key])
                        ? translations[lang][key]
                        : (translations['en'][key] || element.innerHTML);
                }
                element.innerHTML = text;
            });
            
            // Close the dropdown after selection
            langDropdown.classList.remove('active');
            currentLang = lang;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const userLang = navigator.language || navigator.userLanguage || 'en';
            const initial = ['es','fr','de','it','pl','cs','zh'].some(code => userLang.startsWith(code))
                ? userLang.slice(0,2)
                : 'en';
            changeLanguage(initial);
        });
    </script>
</body>

</html>