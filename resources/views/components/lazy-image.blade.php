<img 
    src="{{ $eager ? $src : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E' }}"
    @if(!$eager) data-src="{{ $src }}" @endif
    alt="{{ $alt }}"
    @if($class) class="{{ $class }}" @endif
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    loading="{{ $eager ? 'eager' : 'lazy' }}"
    decoding="async"
    {{ $attributes }}
>

@if(!$eager)
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }
});
</script>
@endpush
@endonce
@endif

