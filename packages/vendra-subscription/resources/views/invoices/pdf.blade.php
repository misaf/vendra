@php
    /** @var \Misaf\VendraSubscription\Models\SubscriptionInvoice $invoice */
    $start = $rtl ? 'right' : 'left';
    $end = $rtl ? 'left' : 'right';
    // Keep dates, numbers and identifiers left to right inside right-to-left text.
    $ltr = fn (string $value): string => '<bdo dir="ltr">'.e($value).'</bdo>';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body { font-size: 10pt; color: #1f2937; }
        h1 { font-size: 20pt; margin: 0 0 4pt; }
        .muted { color: #6b7280; }
        .parties td { vertical-align: top; width: 50%; padding: 0; }
        .label { font-size: 8pt; text-transform: uppercase; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        .lines { margin-top: 18pt; }
        .lines th { font-size: 8pt; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; padding: 6pt 0; text-align: {{ $start }}; }
        .lines td { border-bottom: 1px solid #e5e7eb; padding: 8pt 0; }
        .amount { text-align: {{ $end }}; white-space: nowrap; }
        .totals { margin-top: 12pt; width: 45%; margin-{{ $start }}: 55%; }
        .totals td { padding: 3pt 0; }
        .grand td { border-top: 1px solid #1f2937; font-weight: bold; padding-top: 6pt; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <h1>{{ __('vendra-subscription::invoices.invoice') }}</h1>
                <div class="muted">{{ __('vendra-subscription::invoices.number') }}: {!! $ltr($invoice->number) !!}</div>
                <div class="muted">{{ __('vendra-subscription::invoices.issued_at') }}: {!! $ltr($invoice->issued_at->toDateString()) !!}</div>
            </td>
        </tr>
    </table>

    <table class="parties" style="margin-top: 18pt;">
        <tr>
            @foreach (['seller' => $invoice->seller, 'bill_to' => $invoice->buyer] as $heading => $party)
                <td>
                    <div class="label">{{ __('vendra-subscription::invoices.'.$heading) }}</div>
                    <div><strong>{{ $party['name'] }}</strong></div>
                    @if (! empty($party['email']))
                        <div>{!! $ltr($party['email']) !!}</div>
                    @endif
                    @if (! empty($party['address']))
                        <div>{!! nl2br(e($party['address'])) !!}</div>
                    @endif
                    @if (! empty($party['tax_id']))
                        <div>{!! __('vendra-subscription::invoices.tax_id', ['tax_id' => $ltr($party['tax_id'])]) !!}</div>
                    @endif
                </td>
            @endforeach
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>{{ __('vendra-subscription::invoices.description') }}</th>
                <th class="amount">{{ __('vendra-subscription::invoices.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>
                        <div>{{ $line['description'] }}</div>
                        @if ($line['period_start'] !== null && $line['period_end'] !== null)
                            <div class="muted">{!! __('vendra-subscription::invoices.period', ['start' => $ltr($line['period_start']), 'end' => $ltr($line['period_end'])]) !!}</div>
                        @endif
                        @if ($line['prorated'])
                            <div class="muted">{{ __('vendra-subscription::invoices.prorated') }}</div>
                        @endif
                    </td>
                    <td class="amount">{!! $ltr($invoice->formattedAmount($line['amount'])) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>{{ __('vendra-subscription::invoices.net') }}</td>
            <td class="amount">{!! $ltr($invoice->formattedAmount($invoice->net_amount)) !!}</td>
        </tr>
        <tr>
            <td>{!! __('vendra-subscription::invoices.tax_line', ['label' => e($invoice->tax_label), 'rate' => $ltr($invoice->taxPercentage().'%')]) !!}</td>
            <td class="amount">{!! $ltr($invoice->formattedAmount($invoice->tax_amount)) !!}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('vendra-subscription::invoices.total') }}</td>
            <td class="amount">{!! $ltr($invoice->formattedTotal()) !!}</td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 24pt;">{{ __('vendra-subscription::invoices.paid') }}</p>
</body>
</html>
