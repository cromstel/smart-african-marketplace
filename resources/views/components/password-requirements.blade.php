@props(['for'])

{{-- Dynamic password-compatibility checklist. Hidden by default; the
     password-toggle-script reveals it only while the password field is
     focused/typed and the password does NOT yet meet all requirements. --}}
<div {{ $attributes->merge(['class' => 'hidden mt-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-xs']) }}
     data-password-requirements-for="{{ $for }}">
    <p class="mb-1 font-medium text-gray-600">Your password must include:</p>
    <ul class="space-y-0.5">
        <li data-req="length" class="flex items-center gap-1.5 text-gray-400">
            <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-current text-[10px] leading-none">○</span>
            <span>At least 8 characters</span>
        </li>
        <li data-req="uppercase" class="flex items-center gap-1.5 text-gray-400">
            <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-current text-[10px] leading-none">○</span>
            <span>An uppercase letter (A–Z)</span>
        </li>
        <li data-req="lowercase" class="flex items-center gap-1.5 text-gray-400">
            <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-current text-[10px] leading-none">○</span>
            <span>A lowercase letter (a–z)</span>
        </li>
        <li data-req="number" class="flex items-center gap-1.5 text-gray-400">
            <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-current text-[10px] leading-none">○</span>
            <span>A number (0–9)</span>
        </li>
        <li data-req="symbol" class="flex items-center gap-1.5 text-gray-400">
            <span data-req-icon class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-current text-[10px] leading-none">○</span>
            <span>A symbol (e.g. !@#$%)</span>
        </li>
    </ul>
</div>
