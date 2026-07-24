/* =====================================================================
   Multi-step "Tax Rebate Application Form" — vanilla JS reconstruction
   Mirrors the step flow / validation rules of the original Vue component:
     Step 1 (stepOne)   -> personal + contact details
     Step 2 (stepTwo)   -> occupation, PPS, marital status, DOB
     Step 3 (stepThree) -> postal address + eircode
     Step 4 (stepFour)  -> signature, terms, fees, submit
     Step 5             -> success / error screen
   ===================================================================== */

(function () {
  "use strict";

  /* ---------------------------------------------------------------
     Reference data (copied from the original bundle)
  --------------------------------------------------------------- */
  const COUNTIES = [
    "Antrim","Armagh","Carlow","Cavan","Clare","Cork","Derry","Donegal","Down",
    "Dublin (County)","Dublin 1","Dublin 2","Dublin 3","Dublin 4","Dublin 5",
    "Dublin 6","Dublin 6W","Dublin 7","Dublin 8","Dublin 9","Dublin 10","Dublin 11",
    "Dublin 12","Dublin 13","Dublin 14","Dublin 15","Dublin 16","Dublin 17",
    "Dublin 18","Dublin 20","Dublin 22","Dublin 24","Fermanagh","Galway","Kerry",
    "Kildare","Kilkenny","Laois","Leitrim","Limerick","Longford","Louth","Mayo",
    "Meath","Monaghan","Offaly","Roscommon","Sligo","Tipperary","Tyrone",
    "Waterford","Westmeath","Wexford","Wicklow"
  ];

  const POSTAL_AREAS = [
    { name: "Antrim", codes: ["0"] },
    { name: "Armagh", codes: ["0"] },
    { name: "Carlow", codes: ["R","Y"] },
    { name: "Cavan", codes: ["H","N","A"] },
    { name: "Clare", codes: ["V","H","E"] },
    { name: "Cork", codes: ["P","T","V"] },
    { name: "Derry", codes: ["0"] },
    { name: "Donegal", codes: ["F"] },
    { name: "Down", codes: ["0"] },
    { name: "Dublin (County)", codes: ["W","D","A","K"] },
    { name: "Dublin 1", codes: ["D"] },
    { name: "Dublin 2", codes: ["D"] },
    { name: "Dublin 3", codes: ["D"] },
    { name: "Dublin 4", codes: ["D"] },
    { name: "Dublin 5", codes: ["D"] },
    { name: "Dublin 6", codes: ["D"] },
    { name: "Dublin 7", codes: ["D"] },
    { name: "Dublin 8", codes: ["D"] },
    { name: "Dublin 9", codes: ["D"] },
    { name: "Dublin 10", codes: ["D"] },
    { name: "Dublin 11", codes: ["D"] },
    { name: "Dublin 12", codes: ["D"] },
    { name: "Dublin 13", codes: ["D"] },
    { name: "Dublin 14", codes: ["D"] },
    { name: "Dublin 15", codes: ["D"] },
    { name: "Dublin 16", codes: ["D"] },
    { name: "Dublin 17", codes: ["D"] },
    { name: "Dublin 18", codes: ["D"] },
    { name: "Dublin 20", codes: ["D"] },
    { name: "Dublin 22", codes: ["D"] },
    { name: "Dublin 24", codes: ["D"] },
    { name: "Fermanagh", codes: ["0"] },
    { name: "Galway", codes: ["H","N","R","F"] },
    { name: "Kerry", codes: ["V","P"] },
    { name: "Kildare", codes: ["R","W"] },
    { name: "Kilkenny", codes: ["Y","X","R","E"] },
    { name: "Laois", codes: ["R"] },
    { name: "Leitrim", codes: ["N","H","F"] },
    { name: "Limerick", codes: ["V","P","E"] },
    { name: "Louth", codes: ["A"] },
    { name: "Mayo", codes: ["F","H"] },
    { name: "Meath", codes: ["C","A","W","K","D"] },
    { name: "Monaghan", codes: ["H","A","N"] },
    { name: "Offaly", codes: ["E","R","N"] },
    { name: "Roscommon", codes: ["F","H","N"] },
    { name: "Sligo", codes: ["F","N"] },
    { name: "Tipperary", codes: ["E","V","R"] },
    { name: "Tyrone", codes: ["0"] },
    { name: "Waterford", codes: ["P","X","E"] },
    { name: "Westmeath", codes: ["N","A","C"] },
    { name: "Wexford", codes: ["Y","R"] },
    { name: "Wicklow", codes: ["R","A","Y"] }
  ];

  const EMAIL_DOMAINS = ["gmail.com","googlemail.com","yahoo.com","ymail.com","hotmail.com","live.com","eircom.net"];

  const PPS_REGEX = /^[0-9]{7}[A-Za-z]{1,2}$/;
  const PHONE_REGEX = /^[\d+]+$/;
  const EIRCODE_REGEX = /^[ACDEFHKNPRTVWXYacdefhknprtvwxy]{1}[0-9]{1}[0-9W]{1}[ \-]?[0-9ACDEFHKNPRTVWXYacdefhknprtvwxy]{4}$/;

  /* Where the finished application gets POSTed. Point this at your own
     backend endpoint. */
  const SUBMIT_URL = "/multiformsubmit/";

  /* ---------------------------------------------------------------
     State
  --------------------------------------------------------------- */
  const state = {
    currentPage: 1,
    details: {
      email_confirmation: null,
      emailValidated: false,
      emailConfirmationValidated: false,
      addressValidated: false,
      signature: null
    },
    signatureMode: "draw", // "draw" | "type"
    submitting: false
  };

  /* ---------------------------------------------------------------
     Helpers
  --------------------------------------------------------------- */
  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $all = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  function showError(fieldName, message) {
    const el = document.querySelector(`.v-error[data-error-for="${fieldName}"]`);
    if (!el) return;
    if (message) el.textContent = message;
    el.hidden = false;
  }

  function hideError(fieldName) {
    const el = document.querySelector(`.v-error[data-error-for="${fieldName}"]`);
    if (el) el.hidden = true;
  }

  function showTick(input) {
    const wrap = input.closest(".input");
    const tick = wrap && wrap.querySelector(".tick");
    if (tick) tick.hidden = false;
  }

  function hideTick(input) {
    const wrap = input.closest(".input");
    const tick = wrap && wrap.querySelector(".tick");
    if (tick) tick.hidden = true;
  }

  /* ---------------------------------------------------------------
     Page navigation
  --------------------------------------------------------------- */
  function showPage(n) {
    $all(".form-page").forEach(p => { p.hidden = Number(p.dataset.page) !== n; });
    $all(".intro-step").forEach(s => { s.hidden = Number(s.dataset.introFor) !== n; });

    const backLink = $("#back-link");
    if (backLink) backLink.hidden = n === 1 || n > 4;

    if (n <= 4) {
      const page = $(`.form-page[data-page="${n}"]`);
      const firstInput = page && page.querySelector("input, select");
      if (firstInput) firstInput.focus();
    }
    state.currentPage = n;
  }

  function reversePage() {
    showPage(state.currentPage - 1);
  }

  /* Validates every required field with data-scope="scopeName" that's
     currently in the DOM. Returns true only if all of them pass. */
  function validateScope(scopeName) {
    let allValid = true;
    $all(`[data-scope="${scopeName}"]`).forEach(input => {
      const name = input.name;
      let valid = true;

      if (input.type === "checkbox") {
        valid = input.checked;
      } else if (input.hasAttribute("required") && !String(input.value || "").trim()) {
        valid = false;
      } else if (input.id === "phone_number" && input.value && !PHONE_REGEX.test(input.value)) {
        valid = false;
      } else if (input.id === "pps_number" && input.value && !PPS_REGEX.test(input.value)) {
        valid = false;
      } else if (input.id === "eircode" && input.value && !EIRCODE_REGEX.test(input.value)) {
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

  /* Equivalent of the original turnPage(step, scope) */
  function turnPage(step, scopeName) {
    if (step === 1) {
      if (!state.details.emailValidated) checkEmail();
      checkEmailConfirmation();
      if (!state.details.emailConfirmationValidated) return false;
    }

    if (!validateScope(scopeName)) return false;

    // Step 3 has an eircode/county cross-check before it's allowed through
    if (step === 3 && !state.details.addressValidated) {
      checkEircode();
      return;
    }

    showPage(step + 1);
  }

  /* ---------------------------------------------------------------
     Email checks
  --------------------------------------------------------------- */
  function levenshtein(a, b) {
    const m = [];
    for (let i = 0; i <= b.length; i++) m[i] = [i];
    for (let j = 0; j <= a.length; j++) m[0][j] = j;
    for (let i = 1; i <= b.length; i++) {
      for (let j = 1; j <= a.length; j++) {
        m[i][j] = b.charAt(i - 1) === a.charAt(j - 1)
          ? m[i - 1][j - 1]
          : Math.min(m[i - 1][j - 1] + 1, m[i][j - 1] + 1, m[i - 1][j] + 1);
      }
    }
    return m[b.length][a.length];
  }

  function checkEmail() {
    const warningBox = $("#email-warning");
    const emailInput = $("#email");
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
    state.details.emailValidated = true;
  }

  function checkEmailConfirmation() {
    const email = $("#email").value;
    const confirm = $("#email_confirmation").value;
    state.details.emailConfirmationValidated = email === confirm && email !== "";
    if (!state.details.emailConfirmationValidated) {
      showError("email_confirmation");
    } else {
      hideError("email_confirmation");
      showTick($("#email_confirmation"));
    }
  }

  /* ---------------------------------------------------------------
     Eircode / county cross-check
  --------------------------------------------------------------- */
  function checkEircode() {
    const countyVal = $("#county").value.trim();
    const eircodeVal = $("#eircode").value.trim();
    const countyEntry = POSTAL_AREAS.find(
      c => c.name.toLowerCase() === countyVal.toLowerCase()
    );

    let ok = true;

    if (!countyEntry) {
      ok = false;
      showError("county", "Please add your county");
    } else {
      hideError("county");
    }

    if (eircodeVal && countyEntry) {
      const firstLetter = eircodeVal.charAt(0).toUpperCase();
      if (!countyEntry.codes.map(c => c.toUpperCase()).includes(firstLetter)) {
        ok = false;
        showError("eircode", "That Eircode doesn't look right for the county entered");
      } else {
        hideError("eircode");
      }
    }

    state.details.addressValidated = true;
    if (ok) showPage(4);
  }

  /* ---------------------------------------------------------------
     Signature pad (freehand + typed)
  --------------------------------------------------------------- */
  let drawing = false;
  let ctx;

  function initSignaturePad() {
    const canvas = $("#itrxl_signature");
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

    const start = e => { drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
    const move = e => {
      if (!drawing) return;
      e.preventDefault();
      const p = pos(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    };
    const end = () => { if (drawing) { drawing = false; sign(); } };

    canvas.addEventListener("mousedown", start);
    canvas.addEventListener("mousemove", move);
    window.addEventListener("mouseup", end);
    canvas.addEventListener("touchstart", start, { passive: true });
    canvas.addEventListener("touchmove", move, { passive: false });
    canvas.addEventListener("touchend", end);
  }

  function canvasIsBlank(canvas) {
    const blank = document.createElement("canvas");
    blank.width = canvas.width;
    blank.height = canvas.height;
    return canvas.toDataURL() === blank.toDataURL();
  }

  function sign() {
    const canvas = $("#itrxl_signature");
    const typedInput = $("#signature-typed");

    if (state.signatureMode === "type" && typedInput.value.trim()) {
      // Render the typed name onto a small offscreen canvas so the stored
      // signature is always an image, same as the drawn version.
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
      $("#signature").value = tmp.toDataURL();
    } else if (!canvasIsBlank(canvas)) {
      $("#signature").value = canvas.toDataURL();
    } else {
      $("#signature").value = "";
    }
  }

  function checkBlank() {
    if ($("#signature-typed").value.trim().length < 1) {
      $("#signature").value = "";
    }
  }

  function clearIt() {
    const canvas = $("#itrxl_signature");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    $("#signature-typed").value = "";
    $("#signature").value = "";
  }

  function drawLink() {
    state.signatureMode = "draw";
    $("#itrxl_signature").hidden = false;
    $("#signature-typed").hidden = true;
    $("#draw-link").classList.add("active");
    $("#type-link").classList.remove("active");
    clearIt();
  }

  function typeLink() {
    state.signatureMode = "type";
    const firstName = $("#first_name").value || "";
    const lastName = $("#last_name").value || "";
    clearIt();
    const typedInput = $("#signature-typed");
    typedInput.hidden = false;
    typedInput.value = (firstName + " " + lastName).trim();
    $("#itrxl_signature").hidden = true;
    $("#type-link").classList.add("active");
    $("#draw-link").classList.remove("active");
    typedInput.focus();
    sign();
  }

  /* ---------------------------------------------------------------
     Final submit
  --------------------------------------------------------------- */
  function collectPayload() {
    const form = $("#rebate-form");
    const data = {};
    $all("input[name], select[name], textarea[name]", form).forEach(el => {
      if (el.type === "checkbox") data[el.name] = el.checked;
      else data[el.name] = el.value;
    });
    return data;
  }

  function submit() {
    sign();

    if (!validateScope("stepFour")) return;

    const btn = $("#button_submit");
    state.submitting = true;
    btn.disabled = true;
    btn.textContent = "AUTHORISING";

    fetch(SUBMIT_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(collectPayload())
    })
      .then(res => {
        if (!res.ok) throw new Error("Submission failed");
        return res.json().catch(() => ({}));
      })
      .then(() => {
        showPage(5);
        $("#success-panel").hidden = false;
      })
      .catch(() => {
        showPage(5);
        $("#error-panel").hidden = false;
      })
      .finally(() => {
        state.submitting = false;
        btn.disabled = false;
        btn.innerHTML = 'AGREE &amp; SIGN';
      });
  }

  /* ---------------------------------------------------------------
     Misc UI wiring
  --------------------------------------------------------------- */
  function populateCountyList() {
    const list = $("#counties");
    COUNTIES.forEach(name => {
      const opt = document.createElement("option");
      opt.value = name;
      list.appendChild(opt);
    });
  }

  function captureSourceFields() {
    const params = new URLSearchParams(window.location.search);
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ""; };
    set("source", document.referrer || window.location.href);
    set("originating_url", document.referrer || "");
    set("campaign_name", params.get("utm_campaign"));
    set("campaign_source", params.get("utm_source"));
    set("campaign_medium", params.get("utm_medium"));
    set("form_source", "itr-main-form");
  }

  function wireEvents() {
    $all("[data-next]").forEach(btn => {
      const step = Number(btn.dataset.next);
      const scopeMap = { 1: "stepOne", 2: "stepTwo", 3: "stepThree" };
      btn.addEventListener("click", () => turnPage(step, scopeMap[step]));
    });

    const backLink = $("#back-link");
    if (backLink) backLink.addEventListener("click", e => { e.preventDefault(); reversePage(); });

    $("#email").addEventListener("change", checkEmail);
    $("#email_confirmation").addEventListener("change", checkEmailConfirmation);

    $("#draw-link").addEventListener("click", e => { e.preventDefault(); drawLink(); });
    $("#type-link").addEventListener("click", e => { e.preventDefault(); typeLink(); });
    $("#clear").addEventListener("click", e => { e.preventDefault(); clearIt(); });
    $("#signature-typed").addEventListener("input", sign);
    $("#signature-typed").addEventListener("keyup", checkBlank);

    $("#help").addEventListener("click", e => { e.preventDefault(); $("#tooltip-help").hidden = false; });
    $("#help").addEventListener("mouseleave", () => { $("#tooltip-help").hidden = true; });

    $("#show-terms").addEventListener("click", e => {
      e.preventDefault();
      $("#modal").hidden = false;
    });
    $all("#modal .closer").forEach(c => c.addEventListener("click", e => {
      e.preventDefault();
      $("#modal").hidden = true;
    }));

    $("#button_submit").addEventListener("click", e => { e.preventDefault(); submit(); });

    // Only allow alphanumeric characters in the PPS number field.
    $("#pps_number").addEventListener("keypress", e => {
      if (!/[a-zA-Z0-9]/.test(e.key)) e.preventDefault();
    });
  }

  /* ---------------------------------------------------------------
     Init
  --------------------------------------------------------------- */
  document.addEventListener("DOMContentLoaded", () => {
    populateCountyList();
    captureSourceFields();
    initSignaturePad();
    wireEvents();
    showPage(1);
  });
})();