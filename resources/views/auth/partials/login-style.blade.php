@push('css')
<style>
    .sn-login { --sn-brand:#8c1d2c; --sn-ink:#25343b; --sn-muted:#5d696f; min-height:100svh; background:#f8f6f3; color:var(--sn-ink); font:16px/1.6 Arial,Helvetica,sans-serif; padding:0 40px; }
    .sn-login *, .sn-login *::before, .sn-login *::after { box-sizing:border-box; }
    .sn-login a { color:inherit; text-underline-offset:4px; }
    .sn-login a:focus-visible, .sn-login button:focus-visible, .sn-login input:focus-visible, .sn-login form:focus-visible { outline:3px solid #4e748c; outline-offset:4px; }
    .sn-login-skip { position:absolute; left:24px; top:-100px; z-index:100; background:#fff; padding:12px 20px; }
    .sn-login-skip:focus { top:12px; }
    .sn-login-header { max-width:1280px; min-height:112px; margin:auto; display:flex; justify-content:space-between; align-items:center; gap:24px; }
    .sn-login-brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
    .sn-login-brand img { width:56px; height:56px; object-fit:contain; }
    .sn-login-brand strong { display:block; font-size:19px; font-weight:700; color:#2b2b2b; letter-spacing:-.05em; line-height:1.2; }
    .sn-login-brand small { display:block; font-size:11px; color:#2b2b2b; text-transform:uppercase; letter-spacing:.13em; margin-top:5px; }
    .sn-login-back { font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:12px; min-height:44px; }
    .sn-login-back:hover { color:var(--sn-brand); text-decoration:underline; }
    .sn-login-main { max-width:1280px; margin:auto; display:grid; grid-template-columns:1.05fr 1fr; background:white; border:1px solid #e8e2dc; border-radius:24px; overflow:hidden; box-shadow:0 20px 65px #49342608; }
    .sn-login-story { position:relative; min-height:660px; display:flex; align-items:flex-end; padding:48px; isolation:isolate; background:#302b25; }
    .sn-login-story > img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:85% center; z-index:-2; }
    .sn-login-story::before { content:""; position:absolute; inset:0; z-index:-1; background:linear-gradient(180deg,#1c1b1800 24%,#1c1b1866 48%,#1c1b18f0 100%); }
    .sn-login-story-copy { color:#fff; }
    .sn-login .sn-login-eyebrow { margin:0 0 18px; text-transform:uppercase; font-size:10px; letter-spacing:2px; font-weight:700; color:var(--sn-brand); }
    .sn-login-story .sn-login-eyebrow { color:#f4d3ac; }
    .sn-login-story h2 { color:#fff; font:700 clamp(34px,3.4vw,49px)/1.08 Arial,Helvetica,sans-serif; letter-spacing:-1.8px; margin:0 0 20px; }
    .sn-login-story-copy > p:not(.sn-login-eyebrow) { font-size:15px; line-height:1.7; color:#f3ede6; margin:0 0 34px; }
    .sn-login-story-sign { display:block; border-top:1px solid #ffffff42; padding-top:20px; font-size:11px; letter-spacing:.2px; color:#e6ded6; }
    .sn-login-story-sign span { margin:0 9px; color:#f4d3ac; }
    .sn-login-panel { padding:60px 56px; display:flex; align-items:center; justify-content:center; }
    .sn-login-form-wrap { max-width:390px; width:100%; }
    .sn-login h1 { font:700 38px/1.15 Arial,Helvetica,sans-serif; color:var(--sn-ink); letter-spacing:-1.3px; margin:0 0 16px; }
    .sn-login-intro { color:var(--sn-muted); font-size:15px; line-height:1.7; margin:0 0 32px; }
    .sn-login-field { margin-bottom:20px; }
    .sn-login-field > label { display:block; color:var(--sn-ink); font-size:16px; font-weight:400; margin-bottom:10px; }
    .sn-login-field > label span { color:var(--sn-brand); }
    .sn-login .form-control { width:100%; height:52px; border:1px solid #8a959a; border-radius:8px; background:#fff; padding:12px 15px; color:var(--sn-ink); font:400 16px/1.5 Arial,Helvetica,sans-serif; box-shadow:none; }
    .sn-login .form-control::placeholder { color:#6c7479; font-size:15px; }
    .sn-login .form-control:focus { border-color:var(--sn-brand); box-shadow:0 0 0 3px #8c1d2c14; }
    .sn-login .form-control.is-invalid { border-color:var(--sn-brand); background-image:none; }
    .sn-login-password { position:relative; }
    .sn-login-password .form-control { padding-right:54px; }
    .sn-login-toggle { position:absolute; inset:4px 4px 4px auto; width:44px; border:0; border-radius:5px; background:transparent; color:var(--sn-muted); font-size:20px; cursor:pointer; }
    .sn-login-toggle:hover { background:#f8f1f1; color:var(--sn-brand); }
    .sn-login-error { font-size:13px; line-height:1.5; color:#8c1d2c; margin:9px 0 0; }
    .sn-login-options { display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:14px; margin:-3px 0 24px; }
    .sn-login-options > a { color:var(--sn-brand); min-height:44px; display:flex; align-items:center; }
    .sn-login-remember { display:flex; align-items:center; gap:9px; min-height:44px; }
    .sn-login-remember input { width:18px; height:18px; margin:0; flex:none; border-color:#8a959a; accent-color:var(--sn-brand); }
    .sn-login-remember input:checked { background-color:var(--sn-brand); border-color:var(--sn-brand); }
    .sn-login-remember label { cursor:pointer; padding-block:10px; margin:0; }
    .sn-login-submit { width:100%; min-height:52px; display:flex; align-items:center; justify-content:space-between; padding:13px 20px; border:1px solid var(--sn-brand); border-radius:8px; background:var(--sn-brand); color:white; font:700 15px/1.6 Arial,Helvetica,sans-serif; cursor:pointer; transition:background .15s; }
    .sn-login-submit:hover { background:#741826; border-color:#741826; }
    .sn-login-submit span { font-size:23px; line-height:1; }
    .sn-login-help { display:flex; gap:14px; align-items:center; border-top:1px solid #ece8e3; margin-top:32px; padding-top:25px; font-size:12px; color:var(--sn-muted); }
    .sn-login-help p { margin:0; line-height:1.8; }
    .sn-login-help a { color:var(--sn-brand); font-weight:700; display:inline-block; padding-block:3px; }
    .sn-login-help-icon { background:#f8f3f0; border-radius:50%; width:40px; height:40px; display:grid; place-items:center; font-size:21px; color:var(--sn-brand); flex-shrink:0; }
    .sn-login-footer { max-width:1280px; margin:auto; padding:26px 0; display:flex; justify-content:space-between; gap:16px; color:var(--sn-muted); font-size:11px; }
    @media(min-width:1500px) { .sn-login-header { min-height:132px; } .sn-login-story { min-height:710px; } }
    @media(max-width:1050px) { .sn-login { padding-inline:24px; } .sn-login-panel { padding:40px 32px; } .sn-login-story { padding:32px; min-height:630px; } .sn-login-story h2 { font-size:38px; } .sn-login-desktop-break { display:none; } }
    @media(max-width:760px) { .sn-login { padding-inline:20px; } .sn-login-header { min-height:98px; gap:12px; } .sn-login-main { grid-template-columns:1fr; max-width:520px; border-radius:18px; } .sn-login-story { min-height:210px; padding:24px 28px; } .sn-login-story > img { object-position:center 58%; } .sn-login-story::before { background:linear-gradient(90deg,#1c1b18e8,#1c1b18a6 55%,#1c1b1822); } .sn-login-story h2 { font-size:30px; letter-spacing:-1px; margin:0; } .sn-login-story-copy > p:not(.sn-login-eyebrow), .sn-login-story-sign { display:none; } .sn-login-story .sn-login-eyebrow { margin-bottom:12px; font-size:9px; } .sn-login-panel { padding:32px 28px; } .sn-login h1 { font-size:32px; } .sn-login-intro { margin-bottom:25px; } .sn-login-footer { max-width:520px; flex-direction:column; gap:4px; text-align:center; padding:22px 0; } .sn-login-brand strong { font-size:16px; } .sn-login-brand small { font-size:11px; } .sn-login-brand { gap:8px; } .sn-login-brand img { width:45px; height:45px; } .sn-login-back { font-size:12px; gap:6px; } }
    @media(max-width:360px) { .sn-login { padding-inline:12px; } .sn-login-panel { padding:28px 20px; } .sn-login-options { flex-wrap:wrap; gap:0 12px; } .sn-login-brand strong { font-size:14px; } }
    @media(prefers-reduced-motion:reduce) { .sn-login-submit { transition:none; } }

    .sn-login .sn-login-help { align-items:flex-start; font-size:14px; }
    .sn-login .sn-login-help-icon { margin-top:4px; }
    .sn-login .sn-login-whatsapp { display:flex; align-items:center; gap:8px; color:#24634a; font-size:15px; font-weight:400; min-height:44px; text-decoration:none; }
    .sn-login-whatsapp i { font-size:22px; }
    .sn-login-whatsapp:hover { text-decoration:underline; }
    .sn-login .sn-login-contact { display:inline-flex; align-items:center; min-height:36px; font-size:13px; font-weight:400; }
    .sn-login-about { text-align:center; max-width:1280px; margin:26px auto 0; color:var(--sn-muted); font-size:14px; }
    .sn-login-about p { margin:5px 0; }
    .sn-login .sn-login-credit { display:table; margin:8px auto 0; padding:8px 0; font-size:14px; color:#4e4a45; text-decoration:underline; }
    .sn-login-credit:hover { color:var(--sn-brand); }
    .sn-login-heart { color:#8c1d2c; display:inline-block; margin-inline:2px; }
    .sn-login .sn-login-footer { display:block; text-align:center; padding:18px 0 26px; font-size:12px; }
    @media(max-width:1050px) { .sn-login-options { flex-wrap:wrap; gap:0 12px; } }
    @media(max-width:760px) { .sn-login-about { max-width:520px; font-size:13px; margin-top:22px; } .sn-login .sn-login-footer { font-size:11px; } }

/* Footer pubblico e contatto coerenti con il sito. */
.sn-login .site-footer { background:#fff; border-top:1px solid #e8dfd5; margin:40px -40px 0; padding:55px 40px 90px; color:#4e4a45; }
.sn-login .site-footer .container { max-width:1280px; margin:auto; }
.sn-login .footer-grid { display:grid; grid-template-columns:1.35fr 1fr 1fr 1fr; gap:40px; padding-bottom:45px; }
.sn-login .site-footer .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:#2b2b2b; }
.sn-login .site-footer .brand-logo { width:56px; height:56px; object-fit:contain; }
.sn-login .site-footer .brand strong { display:block; font-size:19px; letter-spacing:-.05em; line-height:1.2; }
.sn-login .site-footer .brand small { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.13em; margin-top:5px; }
.sn-login .footer-grid h2 { font-size:13px; text-transform:uppercase; letter-spacing:.12em; margin:0 0 22px; }
.sn-login .footer-grid > div > a:not(.brand) { display:block; font-size:15px; text-decoration:none; margin:10px 0; }
.sn-login .footer-grid p { font-size:15px; margin-top:25px; max-width:260px; }
.sn-login .footer-bottom { display:flex; flex-wrap:wrap; justify-content:space-between; gap:20px; padding-top:22px; border-top:1px solid #e8dfd5; font-size:14px; }
.sn-login .site-footer a:hover { color:#8c1d2c; text-decoration:underline; }
.sn-login .sn-login-story-copy .sn-login-story-detail { display:block; font-size:16px; margin:18px 0 0; }
.sn-login .sn-login-story-copy .sn-login-story-copyright { display:block; font-size:12px; margin:22px 0 0; }
.sn-login-story .sn-login-heart { color:#fff; }
.sn-login .whatsapp-contact { position:fixed; z-index:60; right:max(20px,env(safe-area-inset-right,0px)); bottom:calc(20px + env(safe-area-inset-bottom,0px) + var(--whatsapp-lift,0px)); width:64px; height:64px; display:grid; place-items:center; border-radius:50%; color:#fff; background:#2f7a57; box-shadow:0 5px 20px #192e3933; border:1px solid #ffffff66; }
.sn-login .whatsapp-contact svg { width:34px; height:34px; }
.sn-login .whatsapp-contact:hover { background:#256346; }
.sn-login .whatsapp-contact.is-obscured { visibility:hidden; }
@media(max-width:1050px) { .sn-login .site-footer { margin-inline:-24px; padding-inline:24px; } .sn-login .footer-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:760px) { .sn-login .site-footer { margin-inline:-20px; padding-inline:20px; } .sn-login .sn-login-story { min-height:390px; } .sn-login .sn-login-story-copy .sn-login-story-detail { font-size:14px; } .sn-login .sn-login-story-copy .sn-login-story-copyright { font-size:11px; } .sn-login .footer-grid { gap:28px; } .sn-login .footer-bottom { flex-direction:column; } .sn-login .whatsapp-contact { right:16px; } .sn-login:has(#accesso :focus) .whatsapp-contact { visibility:hidden; } }
@media(max-width:480px) { .sn-login .footer-grid { grid-template-columns:1fr; } }
@media(max-width:360px) { .sn-login .site-footer { margin-inline:-12px; padding-inline:12px; } }

</style>
@endpush
