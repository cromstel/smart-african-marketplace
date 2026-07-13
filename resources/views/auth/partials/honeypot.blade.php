{{-- Anti-spam honeypot. Visually hidden so humans never interact with it,
     but bots that auto-fill every field will populate it and be rejected
     by the `antispam` middleware. --}}
<div aria-hidden="true" style="position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0 0 0 0)!important;white-space:nowrap!important;">
    <label for="website_url">Website</label>
    <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off" value="">
</div>

{{-- Server-rendered timestamp used to detect too-fast (automated) submissions. --}}
<input type="hidden" name="form_loaded_at" value="{{ time() }}">
