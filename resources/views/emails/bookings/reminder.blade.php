@php
    $service = $booking->salonService;
    $vendorName = $service?->user?->name ?? 'Service Provider';
@endphp
<h2>Booking Reminder</h2>
<p>This is a reminder for your upcoming appointment.</p>
<ul>
    <li><strong>Booking #:</strong> {{ $booking->booking_number }}</li>
    <li><strong>Service:</strong> {{ $service?->name }}</li>
    <li><strong>Vendor:</strong> {{ $vendorName }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date->format('M d, Y') }}</li>
    <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</li>
</ul>
<p>We look forward to seeing you.</p>

