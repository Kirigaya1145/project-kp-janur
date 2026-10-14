@php($wa=preg_replace('/\D/','',(string)config('contact.whatsapp')))
@if($wa && !request()->is('admin*') && !(Auth::check() && in_array(Auth::user()->role,['admin','staff'],true)))
<a class="btn btn-success mt-2" href="https://wa.me/{{ $wa }}?text={{ rawurlencode($message ?? config('contact.message')) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp me-1"></i> Hubungi via WhatsApp</a>
@endif