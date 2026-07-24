<?php

/**
 * Tax Rebate Application Form — drop-in include.
 *
 * Usage: replace <multi-form></multi-form> with:
 *   <?php include 'rebate-form.php'; ?>
 *
 * Everything below (CSS + HTML + JS) is namespaced under the single
 * wrapper class ".itr-rf" and the "itrrf-" id prefix, so it cannot
 * collide with or be affected by styles/scripts/ids elsewhere on the
 * page. Point ITR_RF_SUBMIT_URL at your real backend endpoint.
 */
$itr_rf_submit_url = '/multiformsubmit/';
?>
<div class="itr-rf" data-itrrf-root>

  <style>
    /* =====================================================================
    Everything is scoped under .itr-rf so it cannot bleed into the rest
    of the page, and nothing outside .itr-rf can accidentally style it.
    ===================================================================== */
    .itr-rf,
    .itr-rf * {
      box-sizing: border-box;
    }

    /* IMPORTANT: this must win over every other display rule below, or the
    native [hidden] attribute silently gets overridden (e.g. a flex/inline-flex
    rule elsewhere would make a "hidden" element visible again). */
    .itr-rf [hidden] {
      display: none !important;
    }

    .itr-rf {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      font-size: 14px;
      color: #1f2430;
      max-width: 400px;
      margin: 0 auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
      overflow: hidden;
    }

    .itr-rf__inner {
      padding: 18px 20px 24px;
    }

    .itr-rf__intro h2 {
      margin: 0 0 4px;
      font-size: 1.15rem;
    }

    .itr-rf__intro p {
      margin: 0 0 14px;
      color: #5a6072;
      font-size: .82rem;
      line-height: 1.4;
    }

    .itr-rf__back a {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: #5a6072;
      text-decoration: none;
      font-size: .78rem;
      margin-bottom: 10px;
    }

    .itr-rf__back svg {
      width: 9px;
      height: 9px;
    }

    .itr-rf__back svg path {
      fill: #5a6072;
    }

    /* ---- inputs / floating labels ---- */
    .itr-rf__field {
      position: relative;
      margin-bottom: 12px;
    }

    .itr-rf__field input,
    .itr-rf__field select {
      width: 100%;
      padding: 9px 28px 9px 8px;
      border: 1px solid #d3d7e0;
      border-radius: 4px;
      font-size: .85rem;
      background: #fff;
      font-family: inherit;
    }

    .itr-rf__field input:focus,
    .itr-rf__field select:focus {
      outline: none;
      border-color: #2e6bff;
    }

    .itr-rf__field label {
      position: absolute;
      left: 8px;
      top: 9px;
      color: #8a90a0;
      pointer-events: none;
      transition: all .15s ease;
      background: #fff;
      padding: 0 3px;
      font-size: .85rem;
    }

    .itr-rf__field input:focus+label,
    .itr-rf__field input:not(:placeholder-shown)+label,
    .itr-rf__field select:focus+label {
      top: -7px;
      left: 6px;
      font-size: .68rem;
      color: #2e6bff;
    }

    .itr-rf__tick {
      position: absolute;
      right: 8px;
      top: 8px;
      color: #16a34a;
      font-weight: bold;
      font-size: .85rem;
    }

    .itr-rf__error {
      color: #d92d20;
      font-size: .72rem;
      margin-top: 4px;
    }

    .itr-rf__email-warning {
      background: #fff7e6;
      border: 1px solid #ffd591;
      padding: 8px 10px;
      border-radius: 4px;
      font-size: .75rem;
      margin-bottom: 12px;
    }

    .itr-rf__legal {
      font-size: .68rem;
      color: #8a90a0;
      margin: 10px 0 14px;
      line-height: 1.4;
    }

    .itr-rf__legal a {
      color: #2e6bff;
    }

    /* ---- date of birth ---- */
    .itr-rf__dob-group {
      border: none;
      padding: 0;
      margin: 0 0 4px;
    }

    .itr-rf__dob-legend {
      font-size: .78rem;
      color: #5a6072;
      margin-bottom: 6px;
    }

    .itr-rf__dob-legend span {
      color: #b0b5c0;
      margin-left: 4px;
    }

    .itr-rf__dob-row {
      display: flex;
      gap: 8px;
    }

    .itr-rf__dob-day,
    .itr-rf__dob-month {
      flex: 0 0 56px;
    }

    .itr-rf__dob-year {
      flex: 0 0 76px;
    }

    .itr-rf__dob-error {
      margin-top: -2px;
      margin-bottom: 10px;
    }

    /* ---- buttons ---- */
    .itr-rf__page-button {
      margin-top: 4px;
    }

    .itr-rf__btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      width: 100%;
      background: #4CAF50;
      color: #fff;
      border: none;
      padding: 11px 18px;
      border-radius: 999px;
      font-weight: 600;
      letter-spacing: .02em;
      cursor: pointer;
      font-size: .8rem;
      font-family: inherit;
    }

    .itr-rf__btn:disabled {
      opacity: .6;
      cursor: not-allowed;
    }

    .itr-rf__btn svg {
      width: 9px;
      height: 9px;
    }

    .itr-rf__btn svg path {
      fill: #fff;
    }

    #itrrf-button-submit {
      background: #2e6bff;
    }

    /* ---- signature ---- */
    .itr-rf__sig-block {
      margin-bottom: 12px;
    }

    .itr-rf__sig-legend {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
    }

    .itr-rf__sig-legend>div a {
      margin-right: 8px;
      font-size: .74rem;
      color: #8a90a0;
      text-decoration: none;
      padding-bottom: 2px;
    }

    .itr-rf__sig-legend>div a.itr-rf__active {
      color: #d92d20;
      border-bottom: 2px solid #d92d20;
    }

    .itr-rf__help-link {
      font-size: .74rem;
      color: #8a90a0;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      position: relative;
    }

    .itr-rf__help-link svg {
      width: 10px;
      height: 10px;
    }

    .itr-rf__canvas-label {
      font-size: .68rem;
      color: #8a90a0;
      margin-bottom: 3px;
    }

    .itr-rf__canvas {
      border: 1px dashed #c2c7d1;
      border-radius: 4px;
      width: 100%;
      max-width: 100%;
      height: 60px;
      touch-action: none;
      background: #fff;
    }

    .itr-rf__sig-typed {
      width: 100%;
      padding: 10px;
      border: 1px dashed #c2c7d1;
      border-radius: 4px;
      font-family: cursive;
      font-size: 1.1rem;
    }

    .itr-rf__controls {
      margin: 6px 0 10px;
    }

    .itr-rf__controls a {
      font-size: .74rem;
      color: #d92d20;
      text-decoration: none;
    }

    .itr-rf__tooltip {
      background: #1f2430;
      color: #fff;
      padding: 8px 10px;
      border-radius: 4px;
      font-size: .68rem;
      margin-top: 6px;
    }

    /* ---- checkboxes ---- */
    .itr-rf__checkbox-row {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      margin-bottom: 6px;
    }

    .itr-rf__checkbox {
      width: 15px;
      height: 15px;
      margin-top: 2px;
      flex: 0 0 15px;
    }

    .itr-rf__checkbox-row label {
      position: static;
      font-size: .72rem;
      color: #5a6072;
      line-height: 1.4;
    }

    .itr-rf__terms-row {
      margin-bottom: 10px;
    }

    .itr-rf__advisory {
      font-size: .66rem;
      color: #8a90a0;
      margin-bottom: 10px;
      line-height: 1.4;
    }

    /* ---- result screens ---- */
    .itr-rf__page[data-itrrf-page="5"] {
      text-align: center;
      padding: 12px 0;
    }

    .itr-rf__page[data-itrrf-page="5"] h2 {
      font-size: 1.05rem;
    }

    .itr-rf__page[data-itrrf-page="5"] p {
      font-size: .82rem;
      color: #5a6072;
    }

    /* ---- terms modal ---- */
    .itr-rf__modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .itr-rf__modal-inner {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      max-width: 400px;
      width: 90%;
      font-size: .85rem;
    }

    .itr-rf__modal-inner .itr-rf__closer {
      float: right;
      text-decoration: none;
      color: #8a90a0;
      font-size: .8rem;
    }
  </style>

  <div class="itr-rf__inner" id="form_auth" data-itrrf-anchor>

    <!-- ================= INTRO / STEP HEADINGS ================= -->
    <div class="itr-rf__intro">

      <div class="itr-rf__intro-step" data-itrrf-intro-for="1">
        <h3 style="margin-bottom: 10px;">Tax Rebate Application Form</h3>
        <p class="itr-rf__intro-text">
          Simply complete this form and we'll review your taxes to
          see if you're due a rebate.
        </p>
      </div>

      <div class="itr-rf__intro-step" data-itrrf-intro-for="2" hidden>
        <h2>About You</h2>
        <p>Please add your details below to help us to process your application.</p>
      </div>

      <div class="itr-rf__intro-step" data-itrrf-intro-for="3" hidden>
        <h2>Contact Details</h2>
        <p>Please enter your full postal address.</p>
      </div>

      <div class="itr-rf__intro-step" data-itrrf-intro-for="4" hidden>
        <h2>Final Step</h2>
        <p>Please draw or type your signature below to complete your registration.</p>
      </div>

      <div class="itr-rf__back">
        <a href="#" id="itrrf-back-link" hidden>
          Back
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
            <path d="M5.5,0l-1,1L8.286,4.786H0V6.214H8.286L4.5,10l1,1L11,5.5Z" transform="translate(11 11) rotate(180)" />
          </svg>
        </a>
      </div>
    </div>

    <!-- ================= FORM ================= -->
    <form id="itrrf-form" method="post" novalidate>

      <!-- ---------- STEP 1 ---------- -->
      <div class="itr-rf__page" data-itrrf-page="1">

        <div class="itr-rf__field">
          <input type="text" id="itrrf-first-name" name="first_name" placeholder=" " autocomplete="given-name" data-itrrf-scope="stepOne" required>
          <label for="itrrf-first-name">First Name</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="first_name" hidden>Please enter your first name.</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-last-name" name="last_name" placeholder=" " autocomplete="family-name" data-itrrf-scope="stepOne" required>
          <label for="itrrf-last-name">Last Name</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="last_name" hidden>Please enter your last name</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-maiden-name" name="maiden_name" placeholder=" " autocomplete="on">
          <label for="itrrf-maiden-name">Maiden Name (If Applicable)</label>
        </div>

        <div class="itr-rf__field">
          <input type="email" id="itrrf-email" name="email" placeholder=" " autocomplete="email" data-itrrf-scope="stepOne" required>
          <label for="itrrf-email">Email Address</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="email" hidden></div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-phone-number" name="phone_number" pattern="^[\d+]+$" placeholder=" " autocomplete="tel" data-itrrf-scope="stepOne" required>
          <label for="itrrf-phone-number">Contact Number</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="phone_number" hidden>Please add your phone number.</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-whatsapp-number" name="whatsapp_number" pattern="^[\d+]+$" placeholder=" " autocomplete="tel" data-itrrf-scope="stepOne" required>
          <label for="itrrf-whatsapp-number">WhatsApp Number</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="whatsapp_number" hidden>Please add your WhatsApp number.</div>
        </div>

        <div class="itr-rf__email-warning" id="itrrf-email-warning" hidden>
          Please double check your email address before continuing. When you're
          ready, click the button below to continue.
        </div>

        <div class="itr-rf__legal">
          By applying for this rebate you agree for your data to be used in accordance with our
          <a href="/privacy-policy/">privacy policy</a> and for Irish Tax Rebates to process your
          application with The Revenue Commissioners.
          <!-- We may send you marketing communications,
          news, updates and promotional offers related to our services. You can opt-out at any time. -->
        </div>

        <div class="itr-rf__page-button">
          <button type="button" class="itr-rf__btn" data-itrrf-next="1">
            APPLY FOR MY REBATE NOW
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
              <path d="M5.5,0l-1,1L8.286,4.786H0V6.214H8.286L4.5,10l1,1L11,5.5Z" fill="#fff" />
            </svg>
          </button>
        </div>
      </div>

      <!-- ---------- STEP 2 ---------- -->
      <div class="itr-rf__page" data-itrrf-page="2" hidden>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-occupation" name="occupation" placeholder=" " autocomplete="on" data-itrrf-scope="stepTwo" required maxlength="100">
          <label for="itrrf-occupation">Occupation</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="occupation" hidden></div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-pps-number" name="pps_number" placeholder=" " autocomplete="on" data-itrrf-scope="stepTwo" required>
          <label for="itrrf-pps-number">PPS Number</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="pps_number" hidden>Please enter a valid PPS Number</div>
        </div>

        <div class="itr-rf__field">
          <select id="itrrf-marital-status" name="marital_status" data-itrrf-scope="stepTwo" required>
            <option disabled value="">-- Choose Status --</option>
            <option value="Married">Married</option>
            <option value="Single">Single</option>
            <option value="Civil Partnership">Civil Partnership</option>
            <option value="Separated">Separated</option>
            <option value="Divorced">Divorced</option>
            <option value="Widowed">Widowed</option>
            <option value="Single Parent">Single Parent</option>
          </select>
          <label for="itrrf-marital-status">Marital Status</label>
          <div class="itr-rf__error" data-itrrf-error-for="marital_status" hidden>Please select a valid marital status</div>
        </div>

        <fieldset class="itr-rf__dob-group">
          <legend class="itr-rf__dob-legend">Date of birth <span>(e.g: 05/12/1976)</span></legend>
          <div class="itr-rf__dob-row">
            <div class="itr-rf__field itr-rf__dob-day">
              <input type="number" id="itrrf-dob-day" name="date_of_birth_day" min="1" max="31" placeholder="DD" autocomplete="bday-day" data-itrrf-scope="stepTwo" required>
              <label for="itrrf-dob-day">Day</label>
            </div>
            <div class="itr-rf__field itr-rf__dob-month">
              <input type="number" id="itrrf-dob-month" name="date_of_birth_month" min="1" max="12" placeholder="MM" autocomplete="bday-month" data-itrrf-scope="stepTwo" required>
              <label for="itrrf-dob-month">Month</label>
            </div>
            <div class="itr-rf__field itr-rf__dob-year">
              <input type="number" id="itrrf-dob-year" name="date_of_birth_year" min="1900" max="2020" placeholder="YYYY" autocomplete="bday-year" data-itrrf-scope="stepTwo" required>
              <label for="itrrf-dob-year">Year</label>
            </div>
          </div>
          <div class="itr-rf__error itr-rf__dob-error" data-itrrf-error-for="date_of_birth" hidden>
            Please add your date of birth in the format dd/mm/yyyy
          </div>
        </fieldset>

        <div class="itr-rf__page-button">
          <button type="button" class="itr-rf__btn" data-itrrf-next="2">
            CONTINUE MY APPLICATION
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
              <path d="M5.5,0l-1,1L8.286,4.786H0V6.214H8.286L4.5,10l1,1L11,5.5Z" fill="#fff" />
            </svg>
          </button>
        </div>
      </div>

      <!-- ---------- STEP 3 ---------- -->
      <div class="itr-rf__page" data-itrrf-page="3" hidden>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-address-one" name="address_one" placeholder=" " autocomplete="street-address address-line1" data-itrrf-scope="stepThree" required>
          <label for="itrrf-address-one">Street Address</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="address_one" hidden>Please complete your postal address</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-address-two" name="address_two" placeholder=" " autocomplete="street-address address-line2" data-itrrf-scope="stepThree" required>
          <label for="itrrf-address-two">Town/City</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="address_two" hidden>Please complete your postal address</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-county" name="county" list="itrrf-counties" placeholder=" " autocomplete="address-level1" data-itrrf-scope="stepThree" required>
          <label for="itrrf-county">County</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="county" hidden>Please add your county</div>
          <datalist id="itrrf-counties"></datalist>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-eircode" name="eircode" placeholder=" " data-itrrf-scope="stepThree">
          <label for="itrrf-eircode">Eircode</label>
          <span class="itr-rf__tick" hidden>&#10003;</span>
          <div class="itr-rf__error" data-itrrf-error-for="eircode" hidden>Please enter a valid Eircode</div>
        </div>

        <div class="itr-rf__field">
          <input type="text" id="itrrf-promotion-code" name="promotion_code" placeholder=" " autocomplete="on">
          <label for="itrrf-promotion-code">Promotion Code</label>
        </div>

        <div class="itr-rf__page-button">
          <button type="button" class="itr-rf__btn" data-itrrf-next="3">
            CONTINUE TO FINAL STEP
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
              <path d="M5.5,0l-1,1L8.286,4.786H0V6.214H8.286L4.5,10l1,1L11,5.5Z" fill="#fff" />
            </svg>
          </button>
        </div>
      </div>

      <!-- ---------- STEP 4 ---------- -->
      <div class="itr-rf__page" data-itrrf-page="4" hidden>

        <div class="itr-rf__sig-block">
          <div class="itr-rf__sig-legend">
            <div>
              <a href="#" id="itrrf-draw-link" class="itr-rf__active">Draw Signature</a>
              or
              <a href="#" id="itrrf-type-link">Type In Signature</a>
            </div>
            <a href="#" id="itrrf-help-link" class="itr-rf__help-link">
              Help
              <svg xmlns="http://www.w3.org/2000/svg" width="13.064" height="13.064" viewBox="0 0 13.064 13.064">
                <path d="M6.532,0a6.532,6.532,0,1,0,6.532,6.532A6.532,6.532,0,0,0,6.532,0Z" fill="#8b8b8b" />
              </svg>
            </a>
          </div>
          <div class="itr-rf__canvas-label">Signature</div>
          <canvas id="itrrf-signature-canvas" class="itr-rf__canvas" width="350" height="80"></canvas>
          <input type="text" id="itrrf-signature-typed" class="itr-rf__sig-typed" name="signature_typed" hidden>
          <input type="hidden" id="itrrf-signature" name="signature" data-itrrf-scope="stepFour" required>
          <div class="itr-rf__error" data-itrrf-error-for="signature" hidden></div>

          <div class="itr-rf__controls">
            <a href="#" id="itrrf-clear">Clear</a>
          </div>
          <div class="itr-rf__tooltip" id="itrrf-tooltip-help" hidden>
            Please draw your signature using your mouse or finger, or alternatively
            click on 'type in signature' and type it in directly.
          </div>
        </div>

        <div class="itr-rf__terms-row">
          <div class="itr-rf__checkbox-row">
            <input type="checkbox" id="itrrf-terms" name="terms" value="1" class="itr-rf__checkbox" data-itrrf-scope="stepFour" required>
            <label for="itrrf-terms">
              I have read and agree to the authorisation and the
              <a href="#" id="itrrf-show-terms">Terms &amp; Conditions</a>
              and I give my explicit consent for claims to be made on my behalf for any applicable tax years.
            </label>
          </div>
          <div class="itr-rf__error" data-itrrf-error-for="terms" hidden>Please accept the terms and conditions.</div>
        </div>

        <div class="itr-rf__terms-row">
          <div class="itr-rf__checkbox-row">
            <input type="checkbox" id="itrrf-fees" name="fees" value="1" class="itr-rf__checkbox" data-itrrf-scope="stepFour" required>
            <label for="itrrf-fees">
              I confirm that I am aware of, and agree to, the payment of the fees charged
              by Irish Tax Rebates in respect of the services carried out on my behalf.
            </label>
          </div>
          <div class="itr-rf__error" data-itrrf-error-for="fees" hidden>Please agree to the payment of fees.</div>
        </div>

        <div class="itr-rf__advisory">
          By Clicking Agree &amp; Sign, I confirm that this signature will be the electronic
          representation of my signature for all purposes, when I or my agent use them on
          documents including legally binding contracts, just the same as a pen and paper signature.
        </div>
        <button type="button" class="itr-rf__btn" id="itrrf-button-submit">
          AGREE &amp; SIGN
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
            <path d="M5.5,0l-1,1L8.286,4.786H0V6.214H8.286L4.5,10l1,1L11,5.5Z" fill="#fff" />
          </svg>
        </button>
      </div>

      <!-- hidden tracking fields -->
      <input type="hidden" id="itrrf-source" name="source">
      <input type="hidden" id="itrrf-originating-url" name="originating_url">
      <input type="hidden" id="itrrf-campaign-name" name="campaign_name">
      <input type="hidden" id="itrrf-campaign-source" name="campaign_source">
      <input type="hidden" id="itrrf-campaign-medium" name="campaign_medium">
      <input type="hidden" id="itrrf-form-source" name="form_source">
    </form>

    <!-- ---------- STEP 5: result ---------- -->
    <div class="itr-rf__page" data-itrrf-page="5" hidden>
      <div id="itrrf-success-panel" hidden>
        <h2>Thanks — your application is on its way!</h2>
        <p>We've received your details and will be in touch shortly to confirm your rebate.</p>
      </div>
      <div id="itrrf-error-panel" hidden>
        <h2>Something went wrong</h2>
        <p>We couldn't submit your application. Please try again, or contact us directly.</p>
      </div>
    </div>

  </div>

  <!-- terms modal -->
  <div id="itrrf-modal" class="itr-rf__modal" hidden>
    <div class="itr-rf__modal-inner">
      <a href="#" class="itr-rf__closer">Close</a>
      <h3>Terms &amp; Conditions</h3>
      <p>Your terms and conditions copy goes here.</p>
    </div>
  </div>

</div><!-- /.itr-rf -->

<script>
  (function() {
    "use strict";

    const root = document.querySelector('[data-itrrf-root]');
    if (!root) return;

    const SUBMIT_URL = <?php echo json_encode($itr_rf_submit_url); ?>;

    /* ---- reference data (unchanged from the original site) ---- */
    const COUNTIES = [
      "Antrim", "Armagh", "Carlow", "Cavan", "Clare", "Cork", "Derry", "Donegal", "Down",
      "Dublin (County)", "Dublin 1", "Dublin 2", "Dublin 3", "Dublin 4", "Dublin 5",
      "Dublin 6", "Dublin 6W", "Dublin 7", "Dublin 8", "Dublin 9", "Dublin 10", "Dublin 11",
      "Dublin 12", "Dublin 13", "Dublin 14", "Dublin 15", "Dublin 16", "Dublin 17",
      "Dublin 18", "Dublin 20", "Dublin 22", "Dublin 24", "Fermanagh", "Galway", "Kerry",
      "Kildare", "Kilkenny", "Laois", "Leitrim", "Limerick", "Longford", "Louth", "Mayo",
      "Meath", "Monaghan", "Offaly", "Roscommon", "Sligo", "Tipperary", "Tyrone",
      "Waterford", "Westmeath", "Wexford", "Wicklow"
    ];

    const POSTAL_AREAS = [{
        name: "Antrim",
        codes: ["0"]
      }, {
        name: "Armagh",
        codes: ["0"]
      },
      {
        name: "Carlow",
        codes: ["R", "Y"]
      }, {
        name: "Cavan",
        codes: ["H", "N", "A"]
      },
      {
        name: "Clare",
        codes: ["V", "H", "E"]
      }, {
        name: "Cork",
        codes: ["P", "T", "V"]
      },
      {
        name: "Derry",
        codes: ["0"]
      }, {
        name: "Donegal",
        codes: ["F"]
      },
      {
        name: "Down",
        codes: ["0"]
      }, {
        name: "Dublin (County)",
        codes: ["W", "D", "A", "K"]
      },
      {
        name: "Dublin 1",
        codes: ["D"]
      }, {
        name: "Dublin 2",
        codes: ["D"]
      },
      {
        name: "Dublin 3",
        codes: ["D"]
      }, {
        name: "Dublin 4",
        codes: ["D"]
      },
      {
        name: "Dublin 5",
        codes: ["D"]
      }, {
        name: "Dublin 6",
        codes: ["D"]
      },
      {
        name: "Dublin 7",
        codes: ["D"]
      }, {
        name: "Dublin 8",
        codes: ["D"]
      },
      {
        name: "Dublin 9",
        codes: ["D"]
      }, {
        name: "Dublin 10",
        codes: ["D"]
      },
      {
        name: "Dublin 11",
        codes: ["D"]
      }, {
        name: "Dublin 12",
        codes: ["D"]
      },
      {
        name: "Dublin 13",
        codes: ["D"]
      }, {
        name: "Dublin 14",
        codes: ["D"]
      },
      {
        name: "Dublin 15",
        codes: ["D"]
      }, {
        name: "Dublin 16",
        codes: ["D"]
      },
      {
        name: "Dublin 17",
        codes: ["D"]
      }, {
        name: "Dublin 18",
        codes: ["D"]
      },
      {
        name: "Dublin 20",
        codes: ["D"]
      }, {
        name: "Dublin 22",
        codes: ["D"]
      },
      {
        name: "Dublin 24",
        codes: ["D"]
      }, {
        name: "Fermanagh",
        codes: ["0"]
      },
      {
        name: "Galway",
        codes: ["H", "N", "R", "F"]
      }, {
        name: "Kerry",
        codes: ["V", "P"]
      },
      {
        name: "Kildare",
        codes: ["R", "W"]
      }, {
        name: "Kilkenny",
        codes: ["Y", "X", "R", "E"]
      },
      {
        name: "Laois",
        codes: ["R"]
      }, {
        name: "Leitrim",
        codes: ["N", "H", "F"]
      },
      {
        name: "Limerick",
        codes: ["V", "P", "E"]
      }, {
        name: "Louth",
        codes: ["A"]
      },
      {
        name: "Mayo",
        codes: ["F", "H"]
      }, {
        name: "Meath",
        codes: ["C", "A", "W", "K", "D"]
      },
      {
        name: "Monaghan",
        codes: ["H", "A", "N"]
      }, {
        name: "Offaly",
        codes: ["E", "R", "N"]
      },
      {
        name: "Roscommon",
        codes: ["F", "H", "N"]
      }, {
        name: "Sligo",
        codes: ["F", "N"]
      },
      {
        name: "Tipperary",
        codes: ["E", "V", "R"]
      }, {
        name: "Tyrone",
        codes: ["0"]
      },
      {
        name: "Waterford",
        codes: ["P", "X", "E"]
      }, {
        name: "Westmeath",
        codes: ["N", "A", "C"]
      },
      {
        name: "Wexford",
        codes: ["Y", "R"]
      }, {
        name: "Wicklow",
        codes: ["R", "A", "Y"]
      }
    ];

    const EMAIL_DOMAINS = ["gmail.com", "googlemail.com", "yahoo.com", "ymail.com", "hotmail.com", "live.com", "eircom.net"];
    const PPS_REGEX = /^[0-9]{7}[A-Za-z]{1,2}$/;
    const PHONE_REGEX = /^[\d+]+$/;
    const EIRCODE_REGEX = /^[ACDEFHKNPRTVWXYacdefhknprtvwxy]{1}[0-9]{1}[0-9W]{1}[ \-]?[0-9ACDEFHKNPRTVWXYacdefhknprtvwxy]{4}$/;

    const state = {
      currentPage: 1,
      emailValidated: false,
      addressValidated: false,
      signatureMode: "draw",
      submitting: false
    };

    /* scope every lookup to this component's root, never the whole document */
    const $ = sel => root.querySelector(sel);
    const $all = sel => Array.from(root.querySelectorAll(sel));

    function showError(fieldName, message) {
      const el = root.querySelector(`[data-itrrf-error-for="${fieldName}"]`);
      if (!el) return;
      if (message) el.textContent = message;
      el.hidden = false;
    }

    function hideError(fieldName) {
      const el = root.querySelector(`[data-itrrf-error-for="${fieldName}"]`);
      if (el) el.hidden = true;
    }

    function showTick(input) {
      const wrap = input.closest(".itr-rf__field");
      const tick = wrap && wrap.querySelector(".itr-rf__tick");
      if (tick) tick.hidden = false;
    }

    function hideTick(input) {
      const wrap = input.closest(".itr-rf__field");
      const tick = wrap && wrap.querySelector(".itr-rf__tick");
      if (tick) tick.hidden = true;
    }

    function showPage(n) {
      $all("[data-itrrf-page]").forEach(p => {
        p.hidden = Number(p.dataset.itrrfPage) !== n;
      });
      $all("[data-itrrf-intro-for]").forEach(s => {
        s.hidden = Number(s.dataset.itrrfIntroFor) !== n;
      });

      const backLink = $("#itrrf-back-link");
      if (backLink) backLink.hidden = n === 1 || n > 4;

      if (n <= 4) {
        const page = root.querySelector(`[data-itrrf-page="${n}"]`);
        const firstInput = page && page.querySelector("input, select");
        if (firstInput) firstInput.focus();
      }
      state.currentPage = n;
    }

    function reversePage() {
      showPage(state.currentPage - 1);
    }

    function validateScope(scopeName) {
      let allValid = true;
      $all(`[data-itrrf-scope="${scopeName}"]`).forEach(input => {
        const name = input.name;
        let valid = true;

        if (input.type === "checkbox") {
          valid = input.checked;
        } else if (input.hasAttribute("required") && !String(input.value || "").trim()) {
          valid = false;
        } else if (input.id === "itrrf-phone-number" && input.value && !PHONE_REGEX.test(input.value)) {
          valid = false;
        } else if (input.id === "itrrf-whatsapp-number" && input.value && !PHONE_REGEX.test(input.value)) {
          valid = false;
        } else if (input.id === "itrrf-pps-number" && input.value && !PPS_REGEX.test(input.value)) {
          valid = false;
        } else if (input.id === "itrrf-eircode" && input.value && !EIRCODE_REGEX.test(input.value)) {
          valid = false;
        } else if (input.type === "email" && input.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
          valid = false;
        }

        if (valid) {
          hideError(name);
          if (input.type !== "checkbox" && input.type !== "hidden") showTick(input);
        } else {
          allValid = false;
          showError(name);
          if (input.type !== "checkbox" && input.type !== "hidden") hideTick(input);
        }
      });
      return allValid;
    }

    function turnPage(step, scopeName) {
      if (step === 1 && !state.emailValidated) {
        checkEmail();
      }
      if (!validateScope(scopeName)) return;
      if (step === 3 && !state.addressValidated) {
        checkEircode();
        return;
      }
      showPage(step + 1);
    }

    function levenshtein(a, b) {
      const m = [];
      for (let i = 0; i <= b.length; i++) m[i] = [i];
      for (let j = 0; j <= a.length; j++) m[0][j] = j;
      for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
          m[i][j] = b.charAt(i - 1) === a.charAt(j - 1) ?
            m[i - 1][j - 1] :
            Math.min(m[i - 1][j - 1] + 1, m[i][j - 1] + 1, m[i - 1][j] + 1);
        }
      }
      return m[b.length][a.length];
    }

    function checkEmail() {
      const warningBox = $("#itrrf-email-warning");
      const emailInput = $("#itrrf-email");
      if (!emailInput.value) return;

      const domain = emailInput.value.split("@").pop();
      let looksMisspelled = false;
      try {
        new URL("https://" + domain);
      } catch (e) {
        looksMisspelled = true;
      }

      if (!looksMisspelled && !EMAIL_DOMAINS.includes(domain)) {
        looksMisspelled = EMAIL_DOMAINS.some(known => levenshtein(known, domain) <= 3);
      }
      if (warningBox) warningBox.hidden = !looksMisspelled;
      state.emailValidated = true;
    }

    function checkEircode() {
      const countyVal = $("#itrrf-county").value.trim();
      const eircodeVal = $("#itrrf-eircode").value.trim();
      const countyEntry = POSTAL_AREAS.find(c => c.name.toLowerCase() === countyVal.toLowerCase());
      let ok = true;

      if (!countyEntry) {
        ok = false;
        showError("county", "Please add your county");
      } else hideError("county");

      if (eircodeVal && countyEntry) {
        const firstLetter = eircodeVal.charAt(0).toUpperCase();
        if (!countyEntry.codes.map(c => c.toUpperCase()).includes(firstLetter)) {
          ok = false;
          showError("eircode", "That Eircode doesn't look right for the county entered");
        } else hideError("eircode");
      }

      state.addressValidated = true;
      if (ok) showPage(4);
    }

    /* ---- signature pad ---- */
    let drawing = false;
    let ctx;

    function initSignaturePad() {
      const canvas = $("#itrrf-signature-canvas");
      ctx = canvas.getContext("2d");
      ctx.lineWidth = 1.5;
      ctx.lineCap = "round";
      ctx.strokeStyle = "black";

      const pos = evt => {
        const rect = canvas.getBoundingClientRect();
        const point = evt.touches ? evt.touches[0] : evt;
        return {
          x: (point.clientX - rect.left) * (canvas.width / rect.width),
          y: (point.clientY - rect.top) * (canvas.height / rect.height)
        };
      };
      const start = e => {
        drawing = true;
        const p = pos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
      };
      const move = e => {
        if (!drawing) return;
        e.preventDefault();
        const p = pos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
      };
      const end = () => {
        if (drawing) {
          drawing = false;
          sign();
        }
      };

      canvas.addEventListener("mousedown", start);
      canvas.addEventListener("mousemove", move);
      window.addEventListener("mouseup", end);
      canvas.addEventListener("touchstart", start, {
        passive: true
      });
      canvas.addEventListener("touchmove", move, {
        passive: false
      });
      canvas.addEventListener("touchend", end);
    }

    function canvasIsBlank(canvas) {
      const blank = document.createElement("canvas");
      blank.width = canvas.width;
      blank.height = canvas.height;
      return canvas.toDataURL() === blank.toDataURL();
    }

    function sign() {
      const canvas = $("#itrrf-signature-canvas");
      const typedInput = $("#itrrf-signature-typed");

      if (state.signatureMode === "type" && typedInput.value.trim()) {
        const tmp = document.createElement("canvas");
        tmp.width = 350;
        tmp.height = 80;
        const tctx = tmp.getContext("2d");
        tctx.fillStyle = "#fff";
        tctx.fillRect(0, 0, tmp.width, tmp.height);
        tctx.fillStyle = "#000";
        tctx.font = "40px cursive";
        tctx.textBaseline = "middle";
        tctx.fillText(typedInput.value.trim(), 10, tmp.height / 2);
        $("#itrrf-signature").value = tmp.toDataURL();
      } else if (!canvasIsBlank(canvas)) {
        $("#itrrf-signature").value = canvas.toDataURL();
      } else {
        $("#itrrf-signature").value = "";
      }
    }

    function checkBlank() {
      if ($("#itrrf-signature-typed").value.trim().length < 1) $("#itrrf-signature").value = "";
    }

    function clearIt() {
      const canvas = $("#itrrf-signature-canvas");
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      $("#itrrf-signature-typed").value = "";
      $("#itrrf-signature").value = "";
    }

    function drawLink() {
      state.signatureMode = "draw";
      $("#itrrf-signature-canvas").hidden = false;
      $("#itrrf-signature-typed").hidden = true;
      $("#itrrf-draw-link").classList.add("itr-rf__active");
      $("#itrrf-type-link").classList.remove("itr-rf__active");
      clearIt();
    }

    function typeLink() {
      state.signatureMode = "type";
      const firstName = $("#itrrf-first-name").value || "";
      const lastName = $("#itrrf-last-name").value || "";
      clearIt();
      const typedInput = $("#itrrf-signature-typed");
      typedInput.hidden = false;
      typedInput.value = (firstName + " " + lastName).trim();
      $("#itrrf-signature-canvas").hidden = true;
      $("#itrrf-type-link").classList.add("itr-rf__active");
      $("#itrrf-draw-link").classList.remove("itr-rf__active");
      typedInput.focus();
      sign();
    }

    function collectPayload() {
      const form = $("#itrrf-form");
      const data = {};
      Array.from(form.querySelectorAll("input[name], select[name], textarea[name]")).forEach(el => {
        data[el.name] = el.type === "checkbox" ? el.checked : el.value;
      });
      return data;
    }

    function submit() {
      sign();
      if (!validateScope("stepFour")) return;
      const btn = $("#itrrf-button-submit");
      state.submitting = true;
      btn.disabled = true;
      btn.textContent = "AUTHORISING";

      fetch('request/form.php', {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(collectPayload())
        })
        .then(async (res) => {
          const text = await res.text();
          let data;
          try {
            data = JSON.parse(text);
          } catch {
            data = {
              message: text
            };
          }
          if (!res.ok) {
            throw new Error(data.message || "Submission failed");
          }
          return data;
        })
        .then(data => {
          showPage(5);
          $("#itrrf-success-panel").hidden = false;
          $("#itrrf-success-panel").innerHTML = "<h2>Thanks — your application is on its way!</h2><p>We've received your details and will be in touch shortly to confirm your rebate.</p>";
          console.log(data)
        })
        .catch(error => {
          showPage(5);
          $("#itrrf-error-panel").hidden = false;
          $("#itrrf-error-panel").innerHTML = error.message;
        })
        .finally(() => {
          state.submitting = false;
          btn.disabled = false;
          btn.innerHTML = 'AGREE &amp; SIGN';
      });
    }

    function populateCountyList() {
      const list = $("#itrrf-counties");
      COUNTIES.forEach(name => {
        const opt = document.createElement("option");
        opt.value = name;
        list.appendChild(opt);
      });
    }

    function captureSourceFields() {
      const params = new URLSearchParams(window.location.search);
      const set = (sel, val) => {
        const el = $(sel);
        if (el) el.value = val || "";
      };
      set("#itrrf-source", document.referrer || window.location.href);
      set("#itrrf-originating-url", document.referrer || "");
      set("#itrrf-campaign-name", params.get("utm_campaign"));
      set("#itrrf-campaign-source", params.get("utm_source"));
      set("#itrrf-campaign-medium", params.get("utm_medium"));
      set("#itrrf-form-source", "itr-main-form");
    }

    function wireEvents() {
      $all("[data-itrrf-next]").forEach(btn => {
        const step = Number(btn.dataset.itrrfNext);
        const scopeMap = {
          1: "stepOne",
          2: "stepTwo",
          3: "stepThree"
        };
        btn.addEventListener("click", () => turnPage(step, scopeMap[step]));
      });

      const backLink = $("#itrrf-back-link");
      if (backLink) backLink.addEventListener("click", e => {
        e.preventDefault();
        reversePage();
      });

      $("#itrrf-email").addEventListener("change", checkEmail);

      $("#itrrf-draw-link").addEventListener("click", e => {
        e.preventDefault();
        drawLink();
      });
      $("#itrrf-type-link").addEventListener("click", e => {
        e.preventDefault();
        typeLink();
      });
      $("#itrrf-clear").addEventListener("click", e => {
        e.preventDefault();
        clearIt();
      });
      $("#itrrf-signature-typed").addEventListener("input", sign);
      $("#itrrf-signature-typed").addEventListener("keyup", checkBlank);

      $("#itrrf-help-link").addEventListener("click", e => {
        e.preventDefault();
        $("#itrrf-tooltip-help").hidden = false;
      });
      $("#itrrf-help-link").addEventListener("mouseleave", () => {
        $("#itrrf-tooltip-help").hidden = true;
      });

      $("#itrrf-show-terms").addEventListener("click", e => {
        e.preventDefault();
        $("#itrrf-modal").hidden = false;
      });
      $all("#itrrf-modal .itr-rf__closer").forEach(c => c.addEventListener("click", e => {
        e.preventDefault();
        $("#itrrf-modal").hidden = true;
      }));

      $("#itrrf-button-submit").addEventListener("click", e => {
        e.preventDefault();
        submit();
      });

      $("#itrrf-pps-number").addEventListener("keypress", e => {
        if (!/[a-zA-Z0-9]/.test(e.key)) e.preventDefault();
      });
    }

    populateCountyList();
    captureSourceFields();
    initSignaturePad();
    wireEvents();
    showPage(1);
  })();
</script>