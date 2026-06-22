@php
    $service = $booking->salonService;
    $vendorName = $service?->user?->name ?? 'Service Provider';
@endphp
<h2>Booking Cancelled</h2>
<p>Your booking has been cancelled.</p>
<ul>
    <li><strong>Booking #:</strong> {{ $booking->booking_number }}</li>
    <li><strong>Service:</strong> {{ $service?->name }}</li>
    <li><strong>Vendor:</strong> {{ $vendorName }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date->format('M d, Y') }}</li>
    <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</li>
</ul>
<p>If you have questions, reply to this email.</p>

